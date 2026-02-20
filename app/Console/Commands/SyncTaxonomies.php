<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use App\Models\PostTaxonomy;

class SyncTaxonomies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:taxonomies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize taxonomies with the configuration file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Synchronizing taxonomies...');

        // Get taxonomies from the config file
        $taxonomies = Config::get('taxonomies');

        if (empty($taxonomies)) {
            $this->warn('No taxonomies found in the configuration file.');
            return;
        }

        foreach ($taxonomies as $name => $attributes) {
            $title = $attributes['title'] ?? ucfirst($name);
            $hierarchial = $attributes['hierarchial'] ?? false;
            $description = $attributes['description'] ?? null;

            // Check if the taxonomy exists by name
            $taxonomy = PostTaxonomy::where('name', $name)->first();

            if ($taxonomy) {
                // If taxonomy exists, check if any of the attributes have changed
                $this->info("Checking taxonomy '{$name}'...");

                // Log the current value before update
                $this->info("Current hierarchial value: {$taxonomy->hierarchial}");

                // Check for changes and update if needed
                if ($taxonomy->title !== $title || $taxonomy->hierarchial !== $hierarchial || $taxonomy->description !== $description) {
                    $this->info("Updating taxonomy '{$name}'...");

                    // Directly change the attributes and save
                    $taxonomy->title = $title;
                    $taxonomy->hierarchial = (bool) $hierarchial; // Force cast to boolean
                    $taxonomy->description = $description;

                    // Save the updated record
                    $taxonomy->save();

                    // Log the updated value
                    $this->info("Updated hierarchial value: {$taxonomy->hierarchial}");
                } else {
                    $this->info("No changes for taxonomy '{$name}'.");
                }
            } else {
                // If taxonomy doesn't exist, create a new one
                $this->info("Creating taxonomy '{$name}'...");
                PostTaxonomy::create([
                    'name' => $name,
                    'title' => $title,
                    'hierarchial' => (bool)$hierarchial, // Ensure this is a boolean value
                    'description' => $description,
                ]);
            }
        }

        // Delete taxonomies that are no longer in the configuration
        $existingNames = array_keys($taxonomies);
        PostTaxonomy::whereNotIn('name', $existingNames)->delete();

        $this->info('Taxonomies synchronization complete.');
    }
}