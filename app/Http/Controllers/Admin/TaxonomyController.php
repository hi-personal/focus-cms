<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostTerm;
use App\Models\PostImage;
use App\Models\Option;
use App\Models\PostTermMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Traits\SortTermsHierarchically;
use Illuminate\Support\Facades\Log;

class TaxonomyController extends Controller
{
    private bool $hierarchial = true;
    private array $config = [];

    use SortTermsHierarchically;


    /**
     * Method __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = config('taxonomies');
    }

    /**
     * Method isHierarchial
     *
     * @param $taxonomy_name $taxonomy_name
     *
     * @return void
     */
    private function isHierarchial($taxonomy_name)
    {

        if (!empty($this->config)) {
            $this->hierarchial = $this->config[$taxonomy_name]['hierarchial'];
        }

        return $this->hierarchial;
    }

    /**
     * Method index
     *
     * @param Request $request
     * @param string $postType
     *
     * @return void
     */
    public function index(Request $request, string $taxonomy_name)
    {
        if ($this->isHierarchial($taxonomy_name)) {
            $terms = PostTerm::where('post_taxonomy_name', $taxonomy_name)->where('parent_id', 0)->get();
        } else {
            $terms = PostTerm::where('post_taxonomy_name', $taxonomy_name)->get();
        }

        $allTerms = $this->hierarchial ? $this->sortTermsHierarchically($terms) : $terms;

        return view(
            'admin.post-terms.hierarchial-terms',
            [
                'taxonomy_name' => $taxonomy_name,
                'terms'        => $terms,
                'allTerms'     => $allTerms,
                'hierarchial'  => $this->hierarchial
            ]
        );
    }


    /**
     * Method createNewTerm
     *
     * @param Request $request
     * @param string $taxonomy_name
     *
     * @return void
     */
    public function createNewTerm(Request $request, string $taxonomy_name)
    {
        try {
            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ];

            if ($this->isHierarchial($taxonomy_name)) {
                $rules = array_merge($rules, [
                    'parent_id'   => 'required|integer'
                ]);
            }

            $validated = $request->validate($rules);

            $postTerm = PostTerm::create([
                'title'              => $this->hierarchial ? $validated['title'] : Str::lower($validated['title']),
                'name'               => Str::slug($validated['title']),
                'post_taxonomy_name' => $taxonomy_name,
                'parent_id'          => $this->hierarchial ? $validated['parent_id'] : 0,
            ]);

            if (!empty($validated['description'])) {
                PostTermMeta::create([
                    'post_term_id' => $postTerm->id,
                    'name'         => 'description',
                    'value'        => $validated['description'],
                ]);
            }

            return redirect()->route(
                "taxonomies.index",
                [
                    'taxonomy_name' => $taxonomy_name
                ]
            )->with([
                'success'   => $postTerm->title.' - sikeresen létrehozva!',
                'parent_id' => $this->hierarchial ? $validated['parent_id'] : 0
            ]);
        } catch (\Exception $e) {
            return redirect()->route(
                "taxonomies.index",
                [
                    'taxonomy_name' => $taxonomy_name
                ]
            )->with([
                'error'     => 'Hiba: '.$e->getMessage(),
                'parent_id' => $this->hierarchial ? $validated['parent_id'] : 0
            ]);
        }
    }

    /**
     * Method show
     *
     * @param Request $request
     * @param $taxonomy_name $taxonomy_name
     * @param PostTerm $term
     *
     * @return void
     */
    public function show(Request $request, $taxonomy_name, PostTerm $term)
    {
        $description = PostTermMeta::where('post_term_id', $term->id)->where('name', 'description')->first()?->value;
        $head_image = PostTermMeta::where('post_term_id', $term->id)->where('name', 'head_image')->first()?->value;
        $head_image_url = PostTermMeta::where('post_term_id', $term->id)->where('name', 'head_image_url')->first()?->value;

        $terms = PostTerm::where('post_taxonomy_name', $taxonomy_name)->where('parent_id', 0)->get();
        $allTerms = $this->sortTermsHierarchically($terms);

        $imageContainerId = Option::find('website_setting_categories_image_container_id')?->value;

        if (empty($imageContainerId )) {
            $post = Post::create([
                'title'          => 'Kategóriák kép tároló',
                'name'           => 'categories-image-container',
                'user_id'        => $request->user()->id,
                'status'         => 'system',
                'post_type_name' => 'image_container',
                'content'        => 'Kategóriákhoz feltöltött képek alapértelmezett mentési helye.',
            ]);

            Option::updateOrCreate(
                ['name' => 'website_setting_categories_image_container_id'],
                ['value' => $post->id]
            );

            $imageContainerId = $post->id;
        }

        $post = Post::find($imageContainerId);

        return view(
            'admin.post-terms.edit-hierarchial-term',
            array_merge(compact(
                'taxonomy_name',
                'term',
                'description',
                'terms',
                'allTerms',
                'post',
                'head_image',
                'head_image_url'
            ), ['hierarchial' => $this->isHierarchial($taxonomy_name)])
        );
    }

    /**
     * Method update
     *
     * @param Request $request
     * @param $taxonomy_name $taxonomy_name
     * @param PostTerm $term
     *
     * @return void
     */
    public function update(Request $request, $taxonomy_name, PostTerm $term)
    {
        try {
            $rules = [
                'title'       => 'required|string|max:255',
                'name'        => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ];

            if ($this->isHierarchial($taxonomy_name)) {
                $rules = array_merge($rules, [
                    'parent_id'   => 'required|integer',
                    'head_image'  => 'nullable|string|max:255',
                ]);
            }

            $validated = $request->validate($rules);

            if ($this->hierarchial) {
                $newData = [
                    'title'       => $validated['title'],
                    'name'        => $validated['name'],
                    'parent_id'   => $validated['parent_id'],
                ];

                $term->update($newData);

                $head_image_data = explode('@', $validated['head_image']);
                $head_image_url = empty($validated['head_image']) ? null : (PostImage::find($head_image_data[0])->getImageUrl($head_image_data[1]) ?? null);

                PostTermMeta::updateOrCreate(
                    [
                        'post_term_id' => $term->id,
                        'name'         => 'head_image'
                    ],
                    ['value' => $validated['head_image'] ?? null]
                );

                PostTermMeta::updateOrCreate(
                    [
                        'post_term_id' => $term->id,
                        'name'         => 'head_image_url'
                    ],
                    ['value' => $head_image_url ?? null]
                );
            } else {
                $newData = [
                    'title' => Str::lower($validated['title']),
                    'name'  => $validated['name'],
                ];

                $term->update($newData);
            }


                PostTermMeta::updateOrCreate(
                    [
                        'post_term_id' => $term->id,
                        'name'         => 'description'
                    ],
                    ['value' => $validated['description'] ?? null]
                );


            return redirect()->route(
                "taxonomy.edit",
                [
                    'taxonomy_name' => $taxonomy_name,
                    'term' => $term->id
                ]
            )->with([
                'success'   => 'Mentés sikeresen megtörtént!'
            ]);
        } catch (\Exception $e) {
            return redirect()->route(
                "taxonomy.edit",
                [
                    'taxonomy_name' => $taxonomy_name,
                    'term' => $term->id
                ]
            )->with([
                'error'     => 'Hiba: '.$e->getMessage(),
                'parent_id' => $this->hierarchial ? $validated['parent_id'] : 0
            ]);
        }
    }

    public function delete(Request $request, string $taxonomy_name, PostTerm $term)
    {
        if(
            !empty($term)
        ) {
            $children = $term->directChildren;

            $parentId = $term->parent_id > 0 ? $term->parent_id : 0;

            foreach ($children as $child) {
                unset($child->depth);
                $child->update([
                    'parent_id' => $parentId
                ]);
            }

            $term->delete();
        }

        return redirect()->route("taxonomies.index", ['taxonomy_name' => $taxonomy_name]);
    }
}