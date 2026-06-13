<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostFile;
use App\Models\PostImageRelationship;
use App\Models\PostFileRelationship;
use App\Jobs\GenerateImageSizes;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // GD driver
use App\Models\Option;
use App\Models\PostImageSize;



class UploadController extends Controller
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected null|array $isImage = null;

    /**
     * Method index
     *
     * @return void
     */
    public function index(Request $request)
    {
        $post_id = $request->query('post');
        $post = Post::find($post_id);

        return view('admin.upload', ['post' => $post]);
    }

    /**
     * Check if the uploaded file is an image.
     *
     * @param Request $request
     * @return bool
     */
    public function isImage($file)
    {
        // Ellenőrizzük a MIME típus alapján
        $mimeType = $file->getClientMimeType();

        // A képek MIME típusai
        $imageMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/svg+xml',
        ];

        // Kiterjesztés
        $extension = strtolower($file->getClientOriginalExtension());
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

        // Ha nem egyezik sem MIME sem kiterjesztés alapján, akkor nem kép
        if (!in_array($mimeType, $imageMimeTypes) && !in_array($extension, $imageExtensions)) {
            return null;
        }

        // Képméret lekérdezés GD (getimagesize) segítségével, ha nem SVG
        if ($extension !== 'svg' && $mimeType !== 'image/svg+xml') {
            $path = $file->getPathname(); // ideiglenes fájl elérési útja
            $size = @getimagesize($path);

            if ($size) {
                return [
                    'is_image' => true,
                    'width' => $size[0],
                    'height' => $size[1],
                    'mime' => $size['mime'],
                ];
            } else {
                // Hibás vagy nem olvasható kép
                return false;
            }
        }

        // SVG esetén nincs width/height, de ettől még kép
        return [
            'is_image' => true,
            'width' => null,
            'height' => null,
            'mime' => $mimeType,
            'note' => 'SVG image, dimensions not detected via GD',
        ];
    }

    /**
     * Method store
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function store(Request $request)
    {
        $post_id = $request->post_id;
        $post = Post::find($post_id)->first();

        try {
            $config = config('media.allowed_uploaded_files');

            $validated = $request->validate([
                'file' => [
                    'required',
                    'file',
                    'max:' . $config['max_size'], // Fájlméret korlát a konfigurációból
                    'mimes:' . implode(',', $config['mimes']), // Engedélyezett fájltípusok
                    'mimetypes:' . implode(',', $config['mimetypes']), // Mimetípusok
                    function ($attribute, $value, $fail) {
                        if (in_array($value->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif'])) {
                            // Csak képfájlokra vonatkozik a méretellenőrzés
                            Validator::make(
                                ['file' => $value],
                                ['file' => Rule::dimensions()->maxWidth(10000)->maxHeight(10000)]
                            )->validate();
                        }
                    }
                ]
            ]);


            $file = $request->file('file');
            $this->isImage = $this->isImage($file);


            // Fájl mentése egy külön try-catch blokkban
            try {
                list ($storedFilePath, $storedUniqueFilename) = $this->saveUploadedFile($file);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'A fájl mentése sikertelen: ' . $e->getMessage(),
                ], 500);
            }

            if (!empty($this->isImage)) {
                // Adatbázisba mentés egy külön try-catch blokkban
                try {
                    $postImage = PostImage::updateOrCreate([
                        'title'     =>  $file->getClientOriginalName(),
                        'name'      =>  $storedUniqueFilename,
                        'file_uri'  =>  "storage".DIRECTORY_SEPARATOR.$storedFilePath,
                        'file_url'  =>  "storage".DIRECTORY_SEPARATOR.$storedFilePath,
                        'file_extension'    =>  pathinfo($storedFilePath, PATHINFO_EXTENSION),
                        'mime_type' =>  $file->getClientMimeType(),
                        'file_size' =>  $file->getSize(),
                        'width'     =>  $this->isImage['width'],
                        'height'    =>  $this->isImage['height'],
                    ]);

                    $postImageRelationship = PostImageRelationship::updateOrCreate([
                        'post_id'       =>  $post_id,
                        'post_image_id' =>  $postImage->id,
                        'order'         =>  0
                    ]);
                } catch (\Throwable $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Feltöltés sikeres, de az adatbázis mentés sikertelen: ' . $e->getMessage(),
                    ], 500);
                }

                //Képméretek generálása
                if (pathinfo($storedFilePath, PATHINFO_EXTENSION) !== 'svg' && $file->getClientMimeType() !== 'image/svg+xml') {
                    $this->generateImageSizes(
                        "app".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR.$storedFilePath,
                        $postImage->id
                    );
                }


                return response()->json([
                    'success'   => true,
                    'url'       => Storage::url($storedFilePath),
                    'post_id'   => $post_id,
                ]);
            } else {
                // Adatbázisba mentés egy külön try-catch blokkban
                try {
                    $postFile = PostFile::updateOrCreate([
                        'title'     =>  $file->getClientOriginalName(),
                        'name'      =>  $storedUniqueFilename,
                        'file_uri'  =>  "storage".DIRECTORY_SEPARATOR.$storedFilePath,
                        'file_url'  =>  "storage".DIRECTORY_SEPARATOR.$storedFilePath,
                        'file_extension'    =>  pathinfo($storedFilePath, PATHINFO_EXTENSION),
                        'mime_type' =>  $file->getClientMimeType(),
                        'file_size' =>  $file->getSize(),
                    ]);

                    $postFileRelationship = PostFileRelationship::updateOrCreate([
                        'post_id'       =>  $post_id,
                        'post_file_id'  =>  $postFile->id,
                        'order'         =>  0
                    ]);
                } catch (\Throwable $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Feltöltés sikeres, de az adatbázis mentés sikertelen: ' . $e->getMessage(),
                    ], 500);
                }


                return response()->json([
                    'success'   => true,
                    'url'       => Storage::url($storedFilePath),
                    'post_id'   => $post_id,
                ]);
            }


        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Érvénytelen fájl: ' . implode(', ', array_map(fn($errors) => implode(', ', $errors), $e->errors())),
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) { // Ezt használjuk, hogy minden hibát elkapjunk
            return response()->json([
                'success' => false,
                'message' => 'Szerverhiba: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Method generateUniqueFilename
     *
     * @param $directory $directory [explicite description]
     * @param $originalFilename $originalFilename [explicite description]
     * @param $maxAttempts $maxAttempts [explicite description]
     *
     * @return void
     */
    function generateUniqueFilename($directory, $originalFilename, $maxAttempts = 20) {
        // Fájlnévből kiterjesztés kinyerése
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);

        if (empty($extension)) {
            throw new Exception("Érvénytelen fájlnév vagy hiányzó kiterjesztés.");
        }

        $attempts = 0;

        while ($attempts < $maxAttempts) {
            // Generálunk egy új UUID-t
            $uuid = Str::uuid()->toString();
            $filename = date('Y-m-d') . "_{$uuid}.{$extension}"; // Pl.: 2025-02-05_550e8400-e29b-41d4-a716-446655440000.jpg
            $filepath = $directory . DIRECTORY_SEPARATOR . $filename;

            // Ellenőrizzük, hogy létezik-e már ilyen fájl
            if (!file_exists($filepath)) {
                return $filename; // Ha nincs ilyen fájl, visszaadjuk az új nevet
            }

            $attempts++;
        }

        throw new Exception("Nem sikerült egyedi fájlnevet generálni {$maxAttempts} próbálkozás után.");
    }

    /**
     * Method saveUploadedFile
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function saveUploadedFile($file) {
        // Alapértelmezett mentési mappa az év/hónap/nap szerint

        $basePath = (!empty($this->isImage) ? 'uploads/images/originals/' : 'uploads/files/') . date('Y/m/d');

        if (!$file) {
            throw new Exception("Nincs feltöltött fájl.");
        }

        // Eredeti fájlnév lekérése
        $originalFilename = $file->getClientOriginalName();

        // Egyedi fájlnév generálása
        try {
            $uniqueFilename = $this->generateUniqueFilename(storage_path($basePath), $originalFilename);

            // Fájl mentése a generált névvel
            $path = $file->storeAs($basePath, $uniqueFilename);  // Itt a disk név 'public'

            return [$path, $uniqueFilename]; // Pl.: "public/uploads/2025/02/05/2025-02-05_550e8400-e29b-41d4-a716-446655440000.jpg"
        } catch (Exception $e) {
            \Log::error("Fájlfeltöltési hiba: " . $e->getMessage());
            throw new Exception("Fájlfeltöltés sikertelen, próbáld újra később.");
        }
    }

    public function generateImageSizes($imagePath, $postImageId)
    {
        $imageManager = new ImageManager(new Driver());
        $originalImage = $imageManager->read(storage_path($imagePath));

        $originalWidth = $originalImage->width();
        $originalHeight = $originalImage->height();

        $sizes = config("media.image_sizes");

        foreach ($sizes as $sizeName => $sizeData) {
            try {
                $targetWidth = $sizeData['width'];
                $targetHeight = $sizeData['height'];

                if ($originalWidth <= $targetWidth && $originalHeight <= $targetHeight) {
                    continue;
                }

                // Klónozás
                $image = clone $originalImage;

                // Méretezés
                if ($sizeData['cropped']) {
                    $image->coverDown($targetWidth, $targetHeight, 'center');
                } else {
                    $image->scaleDown($targetWidth, $targetHeight);
                }

                // Relatív elérési út
                $relativePath = 'uploads/images/thumbnails/' . date('Y/m/d') . '/' . $sizeName ."_OID-" . $postImageId . '_' . basename($imagePath);
                $newPath = storage_path('app/public/' . $relativePath);

                // Könyvtár létrehozása
                $directory = dirname($newPath);
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Kép mentése
                $image->save($newPath);

                // MIME típus meghatározása
                $mimeType = mime_content_type($newPath) ?? 'image/jpeg'; // Alapértelmezettként image/jpeg

                // Új méretek lekérdezése
                $width = $image->width();
                $height = $image->height();

                // Adatbázis mentés
                try {
                    PostImageSize::create([
                        'post_image_id' => $postImageId,
                        'name' => $sizeName,
                        'file_uri' => 'storage/' . $relativePath,
                        'file_url' => 'storage/' . $relativePath,
                        'mime_type' => $mimeType,
                        'file_size' => filesize($newPath),
                        'width' => $width,
                        'height' => $height,
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Adatbázis hiba: " . $e->getMessage());
                }

            } catch (\Exception $e) {
                \Log::error("Képgenerálás hiba: " . $e->getMessage());
            }
        }
    }

}