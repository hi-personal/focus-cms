<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use App\Models\PostTaxonomy;

class SyncTaxonomies extends Command
{
    protected $signature='sync:taxonomies';

    protected $description='Synchronize taxonomies with configuration';

    public function handle()
    {
        $this->info('Synchronizing taxonomies...');

        $taxonomies=Config::get('taxonomies');

        if(empty($taxonomies)){
            $this->warn('No taxonomies found in config.');
            return;
        }

        foreach($taxonomies as $name=>$attributes){

            $title=$attributes['title']??ucfirst($name);

            $key=$attributes['key']??$name;

            $hierarchical=$attributes['hierarchical']??false;

            $description=$attributes['description']??null;

            $routeEnabled=$attributes['route']['enabled']??true;

            $routeSlug=$attributes['route']['slug']??[];

            $views=$attributes['views']??[];


            $taxonomy=PostTaxonomy::where(
                'name',
                $name
            )->first();


            $data=[
                'title'=>$title,
                'key'=>$key,
                'hierarchical'=>(bool)$hierarchical,
                'description'=>$description,
                'route_enabled'=>(bool)$routeEnabled,
                'route_slug'=>json_encode($routeSlug),
                'views'=>json_encode($views)
            ];


            if($taxonomy){

                $this->info("Checking {$name}");

                $changed=false;

                foreach($data as $field=>$value){

                    if($taxonomy->$field!=$value){

                        $this->line("Updating {$field}");

                        $taxonomy->$field=$value;

                        $changed=true;
                    }
                }

                if($changed){

                    $taxonomy->save();

                    $this->info("Updated {$name}");

                }
                else{

                    $this->line("No changes");

                }

            }
            else{

                $this->info("Creating {$name}");

                PostTaxonomy::create(
                    array_merge(
                        ['name'=>$name],
                        $data
                    )
                );

            }

        }


        $existingNames=array_keys($taxonomies);

        PostTaxonomy::whereNotIn(
            'name',
            $existingNames
        )->delete();


        $this->info('Taxonomy sync complete');
    }
}