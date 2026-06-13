<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\FocusCmsFrontModule\Services\PageCache\PageCacheService;

class CacheController extends Controller
{
    /**
     * Cache clear
     */
    public function clear()
    {
        $cachePath =
            public_path('page-cache');

        if (File::exists($cachePath)) {

            File::cleanDirectory(
                $cachePath
            );

        }

        return redirect()
            ->route('admin.settings.website')
            ->with(
                'success',
                'Az oldal cache sikeresen törölve lett.'
            )
            ->withFragment('tab-cache');
    }

    /**
     * Cache warmup
     */
    public function warmup(
        Request $request,
        PageCacheService $cache
    ) {

        try {

            $items = [];

            /*
            |--------------------------------------------------------------------------
            | HOME
            |--------------------------------------------------------------------------
            */

            $locales =
                config('app.multilang')
                ? config('app.supported_locales')
                : [config('app.locale')];

            foreach ($locales as $locale) {

                $items[] = [
                    'type' => 'home',
                    'locale' => $locale,
                ];

            }

            /*
            |--------------------------------------------------------------------------
            | POSTS
            |--------------------------------------------------------------------------
            */

            foreach (
                \App\Models\Post::where(
                    'status',
                    'published'
                )->get()
                as $post
            ) {

                $items[] = [
                    'type' => 'post',
                    'post' => $post,
                ];

            }

            /*
            |--------------------------------------------------------------------------
            | TERMS
            |--------------------------------------------------------------------------
            */

            foreach (
                \App\Models\PostTerm::all()
                as $term
            ) {

                $items[] = [
                    'type' => 'term',
                    'term' => $term,
                ];

            }

            /*
            |--------------------------------------------------------------------------
            | INDEX
            |--------------------------------------------------------------------------
            */

            $index =
                (int) $request->input(
                    'index',
                    0
                );

            if (
                !isset($items[$index])
            ) {

                return response()->json([
                    'finished' => true,
                    'done' => count($items),
                    'total' => count($items),
                    'percent' => 100,
                ]);

            }

            $item =
                $items[$index];

            $html = '';
            $url = '';

            /*
            |--------------------------------------------------------------------------
            | HOME
            |--------------------------------------------------------------------------
            */

            if ($item['type'] === 'home') {

                $locale =
                    $item['locale'];

                /*
                |--------------------------------------------------------------------------
                | FORCE LOCALE
                |--------------------------------------------------------------------------
                */

                app()->setLocale(
                    $locale
                );

                $url =
                    route(
                        "front.home.$locale"
                    );

                $fakeRequest =
                    Request::create(
                        $url,
                        'GET'
                    );

                app()->instance(
                    'request',
                    $fakeRequest
                );

                $controller =
                    app(
                        \Modules\FocusCmsFrontModule\Http\Controllers\PostController::class
                    );

                $response =
                    $controller->home(
                        $fakeRequest
                    );

                $html =
                    method_exists($response, 'getContent')
                    ? $response->getContent()
                    : (string) $response;

            }

            /*
            |--------------------------------------------------------------------------
            | POST
            |--------------------------------------------------------------------------
            */

            if ($item['type'] === 'post') {

                $post =
                    $item['post'];

                $locale =
                    $post->lang
                    ??
                    config('app.locale');

                /*
                |--------------------------------------------------------------------------
                | FORCE LOCALE
                |--------------------------------------------------------------------------
                */

                app()->setLocale(
                    $locale
                );

                $url =
                    route(
                        "post.show.$locale",
                        [
                            'slug' => $post->name
                        ]
                    );

                $fakeRequest =
                    Request::create(
                        $url,
                        'GET'
                    );

                app()->instance(
                    'request',
                    $fakeRequest
                );

                $controller =
                    app(
                        \Modules\FocusCmsFrontModule\Http\Controllers\PostController::class
                    );

                $response =
                    $controller->show(
                        $post->name
                    );

                $html =
                    method_exists($response, 'getContent')
                    ? $response->getContent()
                    : (string) $response;

            }

            /*
            |--------------------------------------------------------------------------
            | TERM
            |--------------------------------------------------------------------------
            */

            if ($item['type'] === 'term') {

                $term =
                    $item['term'];

                $taxonomy =
                    $term->post_taxonomy_name;

                if (
                    !config(
                        "taxonomies.$taxonomy.route.enabled"
                    )
                ) {

                    return response()->json([
                        'skip' => true
                    ]);

                }

                $locale =
                    $term->lang
                    ??
                    config('app.locale');

                /*
                |--------------------------------------------------------------------------
                | FORCE LOCALE
                |--------------------------------------------------------------------------
                */

                app()->setLocale(
                    $locale
                );

                $url =
                    route(
                        "taxonomy.$taxonomy.show.$locale",
                        [
                            'term' => $term->name
                        ]
                    );

                $fakeRequest =
                    Request::create(
                        $url,
                        'GET'
                    );

                app()->instance(
                    'request',
                    $fakeRequest
                );

                /*
                |--------------------------------------------------------------------------
                | CATEGORY
                |--------------------------------------------------------------------------
                */

                if ($taxonomy === 'categories') {

                    $controller =
                        app(
                            \Modules\FocusCmsFrontModule\Http\Controllers\CategoryController::class
                        );

                    $response =
                        $controller->show(
                            $fakeRequest,
                            $term->name
                        );

                }

                /*
                |--------------------------------------------------------------------------
                | TAG
                |--------------------------------------------------------------------------
                */

                elseif ($taxonomy === 'tags') {

                    $controller =
                        app(
                            \Modules\FocusCmsFrontModule\Http\Controllers\TagController::class
                        );

                    $response =
                        $controller->show(
                            $fakeRequest,
                            $term->name
                        );

                }

                else {

                    return response()->json([
                        'skip' => true
                    ]);

                }

                $html =
                    method_exists($response, 'getContent')
                    ? $response->getContent()
                    : (string) $response;

            }

            /*
            |--------------------------------------------------------------------------
            | SAVE CACHE
            |--------------------------------------------------------------------------
            */

            $cache->putByUrl(
                $url,
                $html
            );

            /*
            |--------------------------------------------------------------------------
            | PROGRESS
            |--------------------------------------------------------------------------
            */

            $done =
                $index + 1;

            $total =
                count($items);

            return response()->json([
                'finished' => false,
                'current' => $url,
                'done' => $done,
                'total' => $total,
                'percent' => round(
                    ($done / $total) * 100
                ),
            ]);

        } catch (\Throwable $e) {

            \Log::error('CACHE WARMUP ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);

        }
    }
}