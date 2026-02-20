<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use App\Models\PostFile;
use App\Models\PostFileMeta;
use App\Models\PostFileRelationship;

class PostFileController extends Controller
{
    /**
     * Method filePicker
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function filePicker(Request $request)
    {
        try {
            // Képek lekérése
            $files = PostFileRelationship::queryByPostId($request->query('post_id'))->orderBy('post_file_id', 'asc')->get();

            return response()->json([
                'html'  =>  view(
                    'admin.posts.ajax.file-picker',
                    compact('files')
                )->render()
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
     * Method fileAlbumPicker
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function fileAlbumPicker(Request $request)
    {
        try {
            // Képek lekérése
            $files = PostFileRelationship::queryByPostId($request->query('post_id'))
                ->orderBy('post_file_id', 'asc')
                ->get();//->paginate(48);

            // Ha AJAX kérés, csak a modál tartalmát adjuk vissza
            if ($request->ajax()) {
                return view('admin.posts.file-album-picker', compact('files'))->render();
            }

            // Egyébként a teljes nézethez is lehetőség
            return view('admin.posts.file-picker', compact('files'));

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
     * Method getFileDetails
     *
     * @param $id $id [explicite description]
     *
     * @return void
     */
    public function getFileDetails($id)
    {
        $file = PostFile::findOrFail($id);
        $altText = PostFileMeta::where('post_file_id', $id)->where('name', 'alt_text')->first();
        $description = PostFileMeta::where('post_file_id', $id)->where('name', 'description')->first();

        $fileBaseName = basename($file->file_uri);
        $fileExtension = pathinfo($fileBaseName, PATHINFO_EXTENSION);
        $fileFileName = pathinfo($fileBaseName, PATHINFO_FILENAME);
        return response()->json([
            'id' => $file->id,
            'url' => $file->getFileUrl(),
            'title' => $file->title,
            'name' => $file->name,
            'mime_type' => $file->mime_type,
            'file_file_name' => $fileFileName,
            'file_file_extension' => $fileExtension,
            'file_size' => $file->file_size,
            'alt_text' => $altText->value ?? null,
            'description' => $description->value ?? null,
        ]);
    }

    /**
     * Method updateFileDetails
     *
     * @param Request $request [explicite description]
     * @param $id $id [explicite description]
     *
     * @return void
     */
    public function updateFileDetails(Request $request, $id)
    {
        $file = PostFile::findOrFail($id);
        $file->update($request->only(['title', 'name']));
        PostFileMeta::updateOrCreate(
            [
                'post_file_id' =>  $id,
                'name'          =>  'alt_text'
            ],
            [
                'value' =>  $request->alt_text
            ]
        );
        PostFileMeta::updateOrCreate(
            [
                'post_file_id' =>  $id,
                'name'          =>  'description'
            ],
            [
                'value' =>  $request->description
            ]
        );

        return response()->json(['message' => 'Sikeres mentés!']);
    }

    /**
     * Method deleteFiles
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function deleteFiles(Request $request)
    {
        $deletedFiles = $request->only('deleted_files');

        if (! empty($deletedFiles) && is_array($deletedFiles)) {
            $deletedFiles = $deletedFiles['deleted_files'];

            $m="";

            foreach ($deletedFiles as $id) {
                $file = PostFile::find($id);

                if (! empty($file)) {
                  $res =   $file->deleteFile();
                  $m .= $file->id."  ";
                }
            }

            return $res."_".$m."  - ".implode(', ', $deletedFiles)." törölve(db): ".count($deletedFiles);
        }
    }
}