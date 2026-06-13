<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CopyTinyMCE extends Command
{
    protected $signature = 'copy:tinymce';
    protected $description = 'Copy TinyMCE assets from vendor to public/js';

    public function handle()
    {
        /*
        composer.json:
        "post-autoload-dump": [
            "@php artisan copy:tinymce",
        ]


        $source = base_path('node_modules/tinymce');
        $destination = public_path('js/tinymce');

        // Ellenőrizd, hogy a forrás mappa létezik
        if (!File::exists($source)) {
            $this->error('TinyMCE source directory not found!');
            return;
        }

        // Másold a fájlokat
        File::copyDirectory($source, $destination);

        $source = base_path('node_modules/@tinymce/tinymce-jquery');
        $destination = public_path('js/tinymce-jquery');

        // Ellenőrizd, hogy a forrás mappa létezik
        if (!File::exists($source)) {
            $this->error('TinyMCE source directory not found!');
            return;
        }

        // Másold a fájlokat
        File::copyDirectory($source, $destination);

        $this->info('TinyMCE assets copied successfully!');
        */
    }
}