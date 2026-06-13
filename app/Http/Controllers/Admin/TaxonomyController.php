<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostTerm;
use App\Models\PostImage;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Traits\SortTermsHierarchically;

class TaxonomyController extends Controller
{
    use SortTermsHierarchically;

    private bool $hierarchical=false;

    private array $config=[];

    public function __construct()
    {
        $this->config=config('taxonomies');
    }

    private function isHierarchical(
        string $taxonomy_name
    ): bool {

        $taxonomy=$this->config[$taxonomy_name] ?? null;

        $this->hierarchical=
            $taxonomy['hierarchical'] ?? false;

        return $this->hierarchical;
    }

    public function index(
        Request $request,
        string $taxonomy_name
    )
    {
        $config=$this->config[$taxonomy_name] ?? [];

        $this->isHierarchical($taxonomy_name);

        if($this->hierarchical){

            $terms=PostTerm::where(
                'post_taxonomy_name',
                $taxonomy_name
            )->where(
                'parent_id',
                0
            )->get();

        }
        else{

            $terms=PostTerm::where(
                'post_taxonomy_name',
                $taxonomy_name
            )->get();

        }

        $allTerms=$this->hierarchical
            ? $this->sortTermsHierarchically($terms)
            : $terms;

        return view(
            'admin.post-terms.hierarchical-terms',
            [
                'taxonomy_name'=>$taxonomy_name,
                'terms'=>$terms,
                'allTerms'=>$allTerms,
                'hierarchical'=>$this->hierarchical,
                'config'=>$config
            ]
        );
    }

    public function createNewTerm(
        Request $request,
        string $taxonomy_name
    )
    {
        $this->isHierarchical($taxonomy_name);

        try{

            $defaultLocale=config('app.locale');

            $supportedLocales=config(
                'app.supported_locales',
                []
            );

            $rules=[];

            foreach($supportedLocales as $locale){

                $rules["title-$locale"]=
                    'nullable|string|max:255';

                $rules["name-$locale"]=
                    'nullable|string|max:255';

                $rules["description-$locale"]=
                    'nullable|string';

            }

            $rules["title-$defaultLocale"]=
                'required|string|max:255';

            if($this->hierarchical){

                $rules['parent_id']=
                    'nullable|integer';

            }

            $validated=$request->validate($rules);

            /*
            |--------------------------------------------------------------------------
            | Create term
            |--------------------------------------------------------------------------
            */

            $postTerm=PostTerm::create([
                'post_taxonomy_name'=>$taxonomy_name,
                'parent_id'=>$this->hierarchical
                    ? ($validated['parent_id'] ?? 0)
                    : 0
            ]);

            /*
            |--------------------------------------------------------------------------
            | Save multilingual metas
            |--------------------------------------------------------------------------
            */

            foreach($supportedLocales as $locale){

                $title=
                    $validated["title-$locale"] ?? null;

                $name=
                    $validated["name-$locale"] ?? null;

                $description=
                    $validated["description-$locale"] ?? null;

                /*
                |--------------------------------------------------------------------------
                | Auto slug
                |--------------------------------------------------------------------------
                */

                if(
                    empty($name)
                    && !empty($title)
                ){

                    $name=Str::slug($title);

                }

                /*
                |--------------------------------------------------------------------------
                | Non hierarchical lowercase
                |--------------------------------------------------------------------------
                */

                if(
                    !$this->hierarchical
                    && !empty($title)
                ){

                    $title=Str::lower($title);

                }

                $postTerm->setMetaValue(
                    'title',
                    $title,
                    $locale
                );

                $postTerm->setMetaValue(
                    'name',
                    $name,
                    $locale
                );

                $postTerm->setMetaValue(
                    'description',
                    $description,
                    $locale
                );

            }

            return redirect()->route(
                "taxonomies.index",
                [
                    'taxonomy_name'=>$taxonomy_name
                ]
            )->with([
                'success'=>
                    $postTerm->localizedTitle()
                    .' - sikeresen létrehozva!',
                'parent_id'=>$validated['parent_id'] ?? 0
            ]);

        }
        catch(\Exception $e){

            return redirect()->route(
                "taxonomies.index",
                [
                    'taxonomy_name'=>$taxonomy_name
                ]
            )->with([
                'error'=>'Hiba: '.$e->getMessage()
            ]);

        }
    }

    public function show(
        Request $request,
        string $taxonomy_name,
        PostTerm $term
    )
    {
        $config=$this->config[$taxonomy_name] ?? [];

        $this->isHierarchical($taxonomy_name);

        $meta=$term->metaCollection();

        $terms=PostTerm::where(
            'post_taxonomy_name',
            $taxonomy_name
        )->where(
            'parent_id',
            0
        )->get();

        $allTerms=$this->hierarchical
            ? $this->sortTermsHierarchically($terms)
            : $terms;

        $imageContainerId=Option::find(
            'website_setting_categories_image_container_id'
        )?->value;

        if(empty($imageContainerId)){

            $post=Post::create([
                'title'=>'Kategóriák kép tároló',
                'name'=>'categories-image-container',
                'user_id'=>$request->user()->id,
                'status'=>'system',
                'post_type_name'=>'image_container',
                'content'=>'Kategóriákhoz feltöltött képek alapértelmezett mentési helye.'
            ]);

            Option::updateOrCreate(
                ['name'=>'website_setting_categories_image_container_id'],
                ['value'=>$post->id]
            );

            $imageContainerId=$post->id;
        }

        $post=Post::find($imageContainerId);

        return view(
            'admin.post-terms.edit-hierarchical-term',
            [
                'taxonomy_name'=>$taxonomy_name,
                'term'=>$term,
                'meta'=>$meta,
                'terms'=>$terms,
                'allTerms'=>$allTerms,
                'post'=>$post,
                'hierarchical'=>$this->hierarchical,
                'config'=>$config
            ]
        );
    }

    public function update(
        Request $request,
        string $taxonomy_name,
        PostTerm $term
    )
    {
        $this->isHierarchical($taxonomy_name);

        try{

            $defaultLocale=config('app.locale');

            $supportedLocales=config(
                'app.supported_locales',
                []
            );

            /*
            |--------------------------------------------------------------------------
            | Validation rules
            |--------------------------------------------------------------------------
            */

            $rules=[];

            foreach($supportedLocales as $locale){

                $rules["title-$locale"]=
                    'nullable|string|max:255';

                $rules["name-$locale"]=
                    'nullable|string|max:255';

                $rules["description-$locale"]=
                    'nullable|string';

            }

            $rules["title-$defaultLocale"]=
                'required|string|max:255';

            if($this->hierarchical){

                $rules['parent_id']=
                    'nullable|integer';

                $rules['head_image']=
                    'nullable|string|max:255';

            }

            $validated=$request->validate($rules);

            /*
            |--------------------------------------------------------------------------
            | Update hierarchy only
            |--------------------------------------------------------------------------
            */

            if($this->hierarchical){

                $term->update([
                    'parent_id'=>$validated['parent_id'] ?? 0
                ]);

            }

            /*
            |--------------------------------------------------------------------------
            | Save multilingual metas
            |--------------------------------------------------------------------------
            */

            foreach($supportedLocales as $locale){

                $title=
                    $validated["title-$locale"] ?? null;

                $name=
                    $validated["name-$locale"] ?? null;

                $description=
                    $validated["description-$locale"] ?? null;

                /*
                |--------------------------------------------------------------------------
                | Auto slug
                |--------------------------------------------------------------------------
                */

                if(
                    empty($name)
                    && !empty($title)
                ){

                    $name=Str::slug($title);

                }

                /*
                |--------------------------------------------------------------------------
                | Non hierarchical lowercase
                |--------------------------------------------------------------------------
                */

                if(
                    !$this->hierarchical
                    && !empty($title)
                ){

                    $title=Str::lower($title);

                }

                $term->setMetaValue(
                    'title',
                    $title,
                    $locale
                );

                $term->setMetaValue(
                    'name',
                    $name,
                    $locale
                );

                $term->setMetaValue(
                    'description',
                    $description,
                    $locale
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Head image
            |--------------------------------------------------------------------------
            */

            if($this->hierarchical){

                $headImageUrl=null;

                if(!empty($validated['head_image'])){

                    $head_image_data=explode(
                        '@',
                        $validated['head_image']
                    );

                    $image=PostImage::find(
                        $head_image_data[0]
                    );

                    if($image){

                        $headImageUrl=$image->getImageUrl(
                            $head_image_data[1]
                        );

                    }

                }

                $term->setMetaValue(
                    'head_image',
                    $validated['head_image'] ?? null
                );

                $term->setMetaValue(
                    'head_image_url',
                    $headImageUrl
                );

            }

            return redirect()->route(
                "taxonomy.edit",
                [
                    'taxonomy_name'=>$taxonomy_name,
                    'term'=>$term->id
                ]
            )->with([
                'success'=>'Mentés sikeresen megtörtént!'
            ]);

        }
        catch(\Exception $e){

            return redirect()->route(
                "taxonomy.edit",
                [
                    'taxonomy_name'=>$taxonomy_name,
                    'term'=>$term->id
                ]
            )->with([
                'error'=>'Hiba: '.$e->getMessage()
            ]);

        }
    }

    public function delete(
        Request $request,
        string $taxonomy_name,
        PostTerm $term
    )
    {
        if($term){

            $children=$term->directChildren;

            $parentId=$term->parent_id > 0
                ? $term->parent_id
                : 0;

            foreach($children as $child){

                unset($child->depth);

                $child->update([
                    'parent_id'=>$parentId
                ]);

            }

            $term->delete();

        }

        return redirect()->route(
            "taxonomies.index",
            [
                'taxonomy_name'=>$taxonomy_name
            ]
        );
    }
}