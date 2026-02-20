<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CopyJsCookie extends Command
{
    protected $signature = 'copy:js-cookie';
    protected $description = 'Copy js-cookie assets from vendor to public/js';

    public function handle()
    {
        $source = base_path('node_modules/js-cookie');
        $destination = public_path('js/js-cookie');

        // Ellenőrizd, hogy a forrás mappa létezik
        if (!File::exists($source)) {
            $this->error('js-cookie source directory not found!');
            return;
        }

        // Másold a fájlokat
        File::copyDirectory($source, $destination);

        $this->info('js-cookie assets copied successfully!');
    }
}