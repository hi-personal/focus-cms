<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CopyJQuery extends Command
{
    protected $signature = 'copy:jquery';
    protected $description = 'Copy jQuery assets from vendor to public/js';

    public function handle()
    {
        /*
        composer.json:
        "post-autoload-dump": [
            "@php artisan copy:jquery"
        ]
        */
        $source = base_path('node_modules/jquery');
        $destination = public_path('js/jquery');

        // Ellenőrizd, hogy a forrás mappa létezik
        if (!File::exists($source)) {
            $this->error('jQuery source directory not found!');
            return;
        }

        /*
        // Másold a fájlokat
        File::copyDirectory($source, $destination);

        $source = base_path('node_modules/@fortawesome/fontawesome-free');
        $destination = public_path('assets/fontawesome');

        // Ellenőrizd, hogy a forrás mappa létezik
        if (!File::exists($source)) {
            $this->error('jQuery source directory not found!');
            return;
        }

        // Másold a fájlokat
        File::copyDirectory($source, $destination);

        */


        $this->info('jQuery assets copied successfully!');
    }
}