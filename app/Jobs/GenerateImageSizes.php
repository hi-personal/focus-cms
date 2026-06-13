<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // GD driver
use Illuminate\Support\Facades\Storage;
use App\Models\Option;

// vagy
// use Intervention\Image\Drivers\Imagick\Driver; // ImageMagick driver

class GenerateImageSizes implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $imagePath;

    public function __construct($imagePath)
    {
        $this->imagePath = $imagePath;

        Option::updateOrCreate(['name'=>'last_file'], ['value'=>$imagePath]);
    }

    public function handle()
    {
        // Kézi példányosítás a GD driverrel
        $imageManager = new ImageManager(new Driver());

        // Képgenerálás logika
        $sizes = config("media.image_sizes");

        foreach ($sizes as $sizeName => $sizeData) {
            // Kép betöltése a storage-ból
            $image = $imageManager->read(storage_path($this->imagePath));
            Option::updateOrCreate(['name'=>'last_file_2'], ['value'=>storage_path($this->imagePath)]);

            if ($sizeData['cropped']) {
                $image->coverDown($sizeData['width'], $sizeData['height'], 'center');
            } else {
                $image->scaleDown($sizeData['width'], $sizeData['height']);
            }

            // Új elérési út generálása
            $newPath = storage_path('app/public/uploads/images/thumbnails/'.date('Y/m/d').'/' . $sizeName . '_' . basename($this->imagePath));

            // Kép mentése
            $directory = dirname($newPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Kép mentése helyi lemezre
            $image->save($newPath);



            // Mentés az adatbázisba (ha szükséges)
            // ...
        }
    }
}