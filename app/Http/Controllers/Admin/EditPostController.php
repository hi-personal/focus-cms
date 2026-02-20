<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\MarkdownService;
use App\Models\PostImageRelationship;
use App\Models\PostTermRelationship;
use App\Models\PostImage;
use App\Models\PostTerm;
use App\Models\PostMeta;
use App\Models\Option;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Traits\SortTermsHierarchically;


class EditPostController extends Controller
{
    protected $currentThemeName;
    protected int $homePageId;

    use SortTermsHierarchically;

    public function __construct()
    {
        $this->currentThemeName = Option::find('currentThemeName')->value;
        $this->homePageId = Option::find('website_setting_start_page_id')->value;
    }

    /**
     * Method show
     *
     * @param Post $post [explicite description]
     * @param string $postType [explicite description]
     *
     * @return void
     */
    public function show(Request $request, string $postType, Post $post)
    {
        $viewName = match($postType) {
            default => 'admin.edit-post'
        };

        $categories = PostTerm::where('post_taxonomy_name', 'categories')->where('parent_id', 0)->get();
        $allTCategories = $this->sortTermsHierarchically($categories);
        $terms = PostTerm::where('post_taxonomy_name', 'tags')->orderBy('title', 'asc')->get();
        $categoryId = $post->terms->where('post_taxonomy_name', 'categories')->first()->id ?? 0;
        $selectedTagIds = $post->terms->where('post_taxonomy_name', 'tags')->pluck('id')->toArray(); // vagy amit használsz

        $head_image = PostMeta::where('post_id', $post->id)->where('name', 'head_image')->first()?->value;
        $meta_title = PostMeta::where('post_id', $post->id)->where('name', 'meta_title')->first()?->value;
        $meta_description = PostMeta::where('post_id', $post->id)->where('name', 'meta_description')->first()?->value;
        $head_image_url = PostMeta::where('post_id', $post->id)->where('name', 'head_image_url')->first()?->value;

        return view($viewName, [
            'post'             => $post,
            'users'            => User::all(),
            'statuses'         => ['published', 'draft', 'private', 'trash'],
            'postType'         => $postType,
            'terms'            => $terms,
            'allTCategories'   => $allTCategories,
            'categoryId'       => $categoryId,
            'selectedTagIds'   => $selectedTagIds,
            'head_image'       => $head_image,
            'meta_title'       => $meta_title,
            'meta_description' => $meta_description,
            'head_image_url'   => $head_image_url,
        ]);
    }

    /**
     * Method update
     *
     * @param Request $request [explicite description]
     * @param Post $post [explicite description]
     * @param string $postType [explicite description]
     *
     * @return void
     */
    public function update(Request $request, string $postType, Post $post)
    {
        $rules = [
            'title'            => 'required|string|max:255',
            'meta_title'       => 'nullable|string|max:255',
            'name'             => 'nullable|string|max:255',
            'content'          => 'nullable|string',
            'meta_description' => 'nullable|string',
            'status'           => ['required', Rule::in(['published', 'draft', 'private', 'trash', 'system'])],
            'head_image'       => 'nullable|string|max:255',
        ];

        if (in_array($post->post_type_name, ['post'])) {
            $rules = array_merge($rules, [
                'category_id' => 'required|integer',
                'tags'        => 'nullable|array',
                'created_at'  => 'required|date',
            ]);
        }

        $rules = $request->user_id == 0 ? $rules : array_merge($rules, ['user_id' => 'required|exists:users,id']);

        $validated = $request->validate($rules);

        if (in_array($post->post_type_name, config('protected_post_names'))) {
            $newData = [
                'content'        => $validated['content'],
            ];
        } elseif (in_array($post->post_type_name, ['post'])) {
            $newData = [
                'title'            => $validated['title'],
                'name'             => $validated['name'],
                'content'          => $validated['content'],
                'status'           => $validated['status'],
                'created_at'       => $validated['created_at'],
                'head_image'       => 'nullable|string|max:255',
            ];
        } else {
            $newData = [
                'title'   => $validated['title'],
                'name'    => $validated['name'],
                'content' => $validated['content'],
                'status'  => $validated['status'],
                'head_image'       => 'nullable|string|max:255',
            ];
        }

        $newData = ($request->user_id == 0) ? $newData : array_merge($newData, ['user_id' => $validated['user_id']]);

        $post->update($newData);

        $currentCategoryId = $post->terms()
            ->where('post_taxonomy_name', 'categories')
            ->value('id') ?? 0;

        $newCategoryId = (int) ($validated['category_id'] ?? 0);

        if ($currentCategoryId && $currentCategoryId != $newCategoryId) {
            PostTermRelationship::where('post_id', $post->id)
                ->where('post_term_id', $currentCategoryId)
                ->delete();
        }

        if ($newCategoryId > 0 && $currentCategoryId != $newCategoryId) {
            PostTermRelationship::updateOrCreate([
                'post_id'      => $post->id,
                'post_term_id' => $newCategoryId,
            ]);
        }

        $availableTagIds = $post->terms->where('post_taxonomy_name', 'tags')->pluck('id')->toArray();
        $newTagIds = $validated['tags'] ?? [];

        if (!empty($availableTagIds)) {
            foreach($availableTagIds as $tagId) {
                if (!in_array($tagId, $newTagIds)) {
                    PostTermRelationship::where('post_id', $post->id)->where('post_term_id', $tagId)->delete();
                }
            }
        }

        if(!empty($newTagIds)) {
            foreach($newTagIds as $tagId) {
                if (!in_array($tagId, $availableTagIds)) {
                    PostTermRelationship::create([
                        'post_id'      => $post->id,
                        'post_term_id' => $tagId
                    ]);
                }
            }
        }

        PostMeta::updateOrCreate(
            [
                'post_id' => $post->id,
                'name'    => 'meta_title'
            ],
            ['value' => $validated['meta_title'] ?? null]
        );

        PostMeta::updateOrCreate(
            [
                'post_id' => $post->id,
                'name'    => 'meta_description'
            ],
            ['value' => $validated['meta_description'] ?? null]
        );

        if (in_array($post->post_type_name, ['post', 'page'])) {
            $head_image_data = !empty($validated['head_image']) ? explode('@', $validated['head_image']) : [];
            $head_image_url = empty($validated['head_image']) ? null : (PostImage::find($head_image_data[0])->getImageUrl($head_image_data[1]) ?? null);

            PostMeta::updateOrCreate(
                [
                    'post_id' => $post->id,
                    'name'    => 'head_image'
                ],
                ['value' => $validated['head_image'] ?? null]
            );

            PostMeta::updateOrCreate(
                [
                    'post_id' => $post->id,
                    'name'    => 'head_image_url'
                ],
                ['value' => $head_image_url ?? null]
            );
        }

        return redirect()->route(
            "post.edit",
            [
                'post_type'    => $postType,
                'post'         => $post,
            ]
        )->with([
            'success'      => 'Post updated successfully!',
            'editorStatus' => $request->editor_status ?? null
        ]);
    }

    /**
     * Method delete
     *
     * @param Request $request [explicite description]
     * @param string $postType [explicite description]
     * @param Post $post [explicite description]
     *
     * @return void
     */
    public function delete(Request $request, string $postType, Post $post)
    {
        if(
            !empty($post)
            && !in_array($post->name, config('protected_post_names'))
        ) {
            $images = $post->images;
            $files = $post->files;

            //Delete post images and image sizes
            if(!empty($images)) {
                foreach ($images as $image) {
                    $image->deleteImage();
                }
            }

            //Delete post files
            if(!empty($files)) {
                foreach ($files as $file) {
                    $file->deleteFile();
                }
            }

            //Delete post
            $post->delete();
        }

        return redirect()->route("posts.index", ['post_type' => $postType]);
    }

    /**
     * Method preview
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function preview(Request $request)
    {
        $token = $request->query('token');
        $content = session()->get('preview_' . $token);

        if (!$content) {
            abort(404);
        }

        $post = Post::find($request->id);

        if (!$post) {
            abort(404);
        }

        $content = json_decode($content, true)['content'] ?? '';
        $post->content = markdownToHtml($content);

        $features = $this->detectContentFeatures($post->content);
        $meta_title = PostMeta::where('post_id', $post->id)->where('name', 'meta_title')->first()?->value;
        $meta_description = PostMeta::where('post_id', $post->id)->where('name', 'meta_description')->first()?->value;
        $isHome = $this->homePageId == $post->id;
        $headImageUrl = PostMeta::where('post_id', $post->id)
            ->where('name', 'head_image_url')
            ->value('value')
            ?? PostMeta::where('post_id', $this->homePageId)
                ->where('name', 'head_image_url')
                ->value('value');
        $category = $post->terms->where('post_taxonomy_name', 'categories')->first();
        $tags = $post->terms->where('post_taxonomy_name', 'tags');

        return view('theme::post')
            ->with('post', $post)
            ->with('currentTheme', $this->currentThemeName)
            ->with('features', $features)
            ->with('meta_title', $meta_title)
            ->with('meta_description', $meta_description)
            ->with('isHome', $isHome)
            ->with('category', $category)
            ->with('tags', $tags)
            ->with('headImageUrl', $headImageUrl)
            ->with('homePageId', $this->homePageId)
            ->with('isMinimalViewFromController', 'true');
    }

    /**
     * Method saveTemp
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function saveTemp(Request $request)
    {
        $token = Str::random(40);
        session()->put('preview_' . $token, $request->content);
        return response()->json($token);
    }


    /**
     * Method groupAction
     *
     * @param Request $request [explicite description]
     * @param string $postType [explicite description]
     *
     * @return void
     */
    public function groupAction(Request $request, string $postType)
    {
        if (
            empty($request->selected_post)
            || empty($request->group_actions_select)
            || $request->group_actions_select == "none"
        )
            return back();

        if (
            $request->group_actions_select == 'setNewCategory'
        ) {
            if (!empty($request->new_category)) {
                foreach ($request->selected_post as $id) {
                    $categoryId = Post::find($id)->terms->where('post_taxonomy_name', 'categories')->first()?->id;
                    PostTermRelationship::where('post_id', $id)->where('post_term_id', $categoryId)->delete();
                    PostTermRelationship::create([
                        'post_id'      => $id,
                        'post_term_id' => $request->new_category
                    ]);
                }
            }
        } elseif ($request->group_actions_select == 'setNewAuthor') {
            if (!empty($request->new_author)) {
                foreach ($request->selected_post as $id) {
                    $post = Post::find($id);
                    $post->user_id = $request->new_author;
                    $post->update();
                }
            }
        } else {
            foreach ($request->selected_post as $id) {
                $post = Post::find($id);
                if (!empty($post)) {
                    if ($request->group_actions_select == "delete") {
                        $this->delete($request, $postType, $post);
                    } else {
                        $post->status = $request->group_actions_select;
                        $post->update();
                    }
                }
            }
        }

        return back();
    }

    private function detectContentFeatures(?string $content): array
    {
        if (empty($content)) {
            return [
                'code'    => false,
                'image'   => false,
                'gallery' => false,
                'file'    => false,
            ];
        }

        return [
            // univerzális kód detektálás
            'code' =>
                str_contains($content, '```')
                || preg_match('/\{\{code(?::[a-z0-9_-]+)?\}\}/i', $content)
                || str_contains($content, '<pre')
                || str_contains($content, '<code'),

            // képek
            'image' =>
                preg_match('/\[image\s+id\(/i', $content),

            // galéria
            'gallery' =>
                preg_match('/\[gallery\s+ids\(/i', $content),

            // fájl
            'file' =>
                preg_match('/\[file\s+id\(/i', $content),
        ];
    }
}