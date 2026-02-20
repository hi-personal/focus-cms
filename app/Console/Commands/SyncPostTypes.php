<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use App\Models\PostType;

class SyncPostTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:post-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync post types from configuration file to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting post types synchronization...');

        // Get post types from config
        $postTypes = Config::get('post_types', []);

        if (empty($postTypes)) {
            $this->warn('No post types found in the configuration file.');
            return;
        }

        foreach ($postTypes as $name => $attributes) {
            $title = $attributes['title'] ?? ucfirst($name);
            $hierarchial = $attributes['hierarchical'] ?? false; // Fix: 'hierarchial' -> 'hierarchical'
            $description = $attributes['description'] ?? null;

            // Check if post type exists by name
            $postType = PostType::where('name', $name)->first();

            if ($postType) {
                // If post type exists, check if any of the attributes have changed
                $this->info("Checking post type '{$name}'...");

                // Check for changes and update if needed
                if ($postType->title !== $title || $postType->hierarchial !== $hierarchial || $postType->description !== $description) {
                    $this->info("Updating post type '{$name}'...");
                    $postType->update([
                        'title' => $title,
                        'hierarchial' => $hierarchial,
                        'description' => $description,
                    ]);
                } else {
                    $this->info("No changes for post type '{$name}'.");
                }
            } else {
                // If post type doesn't exist, create a new one
                $this->info("Creating post type '{$name}'...");
                PostType::create([
                    'name' => $name,
                    'title' => $title,
                    'hierarchial' => $hierarchial,
                    'description' => $description,
                ]);
            }
        }

        // Delete post types that are no longer in the configuration
        $existingNames = array_keys($postTypes);
        PostType::whereNotIn('name', $existingNames)->delete();

        $this->info('Post types synchronization completed successfully!');
    }
}