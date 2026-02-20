<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use App\Models\PostImage;
use App\Models\PostImageMeta;
use App\Models\PostImageRelationship;

class PostImageController extends Controller
{
    /**
     * Method imagePicker
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function imagePicker(Request $request)
    {
        try {
            // Képek lekérése
            $images = PostImageRelationship::queryByPostId($request->query('post_id'))->orderBy('post_image_id', 'asc')->get();
            //->paginate(48);

            $modalImageSizes = [];

            foreach($images as $image) {
                $modalImageSizes[$image->image->id]['original'] = $image->image->getImageUrl('original');

                foreach(array_keys(config('media.image_sizes')) as $size) {
                    if(
                        $image->image->sizes()->where('name', $size)->exists()
                        || $size == 'thumbnail'
                    ) {
                        $modalImageSizes[$image->image->id][$size] = $image->image->getImageUrl($size);
                    }
                }
            }

            return response()->json([
                'html'  =>  view(
                    'admin.posts.ajax.image-picker',
                    compact('images', 'modalImageSizes')
                )->render(),
                'modalImageSizes' => $modalImageSizes
            ]);
        } catch (\Exception $e) {
            // Hibajelzés JSON formátumban
            return response()->json([
                'success' => false,
                'message' => 'Hiba történt a képkatalógus betöltése közben: ' . $e->getMessage(),
                'url' => null,
            ], 500);
        }
    }

    /**
     * Method imageAlbumPicker
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function imageAlbumPicker(Request $request)
    {
        try {
            // Képek lekérése
            $images = PostImageRelationship::queryByPostId($request->query('post_id'))
                ->orderBy('post_image_id', 'asc')
                ->get();//->paginate(48);

            // Ha AJAX kérés, csak a modál tartalmát adjuk vissza
            if ($request->ajax()) {
                return view('admin.posts.image-album-picker', compact('images'))->render();
            }

            // Egyébként a teljes nézethez is lehetőség
            return view('admin.posts.image-picker', compact('images'));

        } catch (\Exception $e) {
            // Hibajelzés JSON formátumban
            return response()->json([
                'success' => false,
                'message' => 'Hiba történt a képkatalógus betöltése közben: ' . $e->getMessage(),
                'url' => null,
            ], 500);
        }
    }

    /**
     * Method getImageDetails
     *
     * @param $id $id [explicite description]
     *
     * @return void
     */
    public function getImageDetails($id)
    {
        $image = PostImage::findOrFail($id);
        $altText = PostImageMeta::where('post_image_id', $id)->where('name', 'alt_text')->first();
        $description = PostImageMeta::where('post_image_id', $id)->where('name', 'description')->first();

        return response()->json([
            'original_url' => $image->getImageUrl('original'),
            'title'        => $image->title,
            'name'         => $image->name,
            'mime_type'    => $image->mime_type,
            'file_size'    => $image->file_size,
            'alt_text'     => $altText->value ?? null,
            'description'  => $description->value ?? null,
        ]);
    }

    /**
     * Method updateImageDetails
     *
     * @param Request $request [explicite description]
     * @param $id $id [explicite description]
     *
     * @return void
     */
    public function updateImageDetails(Request $request, $id)
    {
        $image = PostImage::findOrFail($id);
        $image->update($request->only(['title', 'name']));
        PostImageMeta::updateOrCreate(
            [
                'post_image_id' =>  $id,
                'name'          =>  'alt_text'
            ],
            [
                'value' =>  $request->alt_text
            ]
        );
        PostImageMeta::updateOrCreate(
            [
                'post_image_id' =>  $id,
                'name'          =>  'description'
            ],
            [
                'value' =>  $request->description
            ]
        );

        return response()->json(['message' => 'Sikeres mentés!']);
    }

    /**
     * Method deleteImages
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function deleteImages(Request $request)
    {
        $deletedImages = $request->only('deleted_images');

        if (! empty($deletedImages) && is_array($deletedImages)) {
            $deletedImages = $deletedImages['deleted_images'];

            $m="";

            foreach ($deletedImages as $id) {
                $image = PostImage::find($id);

                if (! empty($image)) {
                  $res =   $image->deleteImage();
                  $m .= $image->id."  ";
                }
            }

            return $res."_".$m."  - ".implode(', ', $deletedImages)." törölve(db): ".count($deletedImages);
        }
    }
}