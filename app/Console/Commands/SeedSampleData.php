<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;
use App\Models\{
    Link,
    Media,
    Option,
    Post,
    PostImage,
    PostImageAlbum,
    PostMeta,
    PostTaxonomy,
    PostTerm,
    PostTermMeta,
    PostTermRelationship,
    User,
    UserMeta
};

class SeedSampleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-sample-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Taxonómiák regisztrálása
        $result = \Artisan::call('sync:taxonomies', []);

        //Eredmények kiírása
        $this->info('Taxonómiák regisztrálása!');
        $this->info('Eredmény: ' . \Artisan::output());

        //Bejegyzés típusok regisztrálása
        $result = \Artisan::call('sync:post-types', []);

        //Eredmények kiírása
        $this->info('Bejegyzés típusok regisztrálása!');
        $this->info('Eredmény: ' . \Artisan::output());

        //Példa adatok feltöltése az adatbázis táblákba
        $this->info('Példa adatok feltöltése az adatbázis táblákba...');
        User::factory()->count(10)->create();
        UserMeta::factory()->count(10)->create();
        Option::factory()->count(10)->create();
        Post::factory()->count(10)->create();
        PostMeta::factory()->count(10)->create();
        //PostTaxonomy::factory()->count(10)->create();
        PostTerm::factory()->count(10)->create();
        PostTermMeta::factory()->count(10)->create();
        PostTermRelationship::factory()->count(10)->create();
        Link::factory()->count(10)->create();
        Media::factory()->count(10)->create();
        PostImageAlbum::factory()->count(10)->create();
        PostImage::factory()->count(10)->create();
        $this->info('Példa adatok feltöltése az adatbázis táblákba megtörtént!');
    }
}
