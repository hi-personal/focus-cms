<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Option;

class OptimizeDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-db
                            {--dry-run : Only show what would be deleted}
                            {--D|detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes unused website settings from options table using Option model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $validationRules = config('validation_rules.options.website_settings');
        $protectedKeys = array_keys($validationRules);

        // // Előkészítjük a védett kulcsokat
        // $protectedKeys = array_map(function($key) {
        //     return 'website_setting_'.$key;
        // }, $keys);

        // Lekérdezés az Option modellel
        $query = Option::where('name', 'like', 'website_setting_%')
            ->whereNotIn('name', $protectedKeys);

            $toDelete = $query->get();

            if ($toDelete->isEmpty()) {
                $this->info("No unused website settings found to delete.");
                return 0;
            }

            $this->table(['Name'], $toDelete->map(function ($option) {
                return [
                    'name' => $option->name,
                ];
            }));

            $this->info($toDelete->count()." records would be deleted.");

        // Dry run esetén csak listázunk
        if ($this->option('dry-run')) {
            return 0;
        }

        // Tényleges törlés
        $deletedCount = $query->count();

        if ($deletedCount === 0) {
            $this->info("No unused website settings found to delete.");
            return 0;
        }

        if ($this->confirm("Are you sure you want to delete {$deletedCount} unused website settings?", false)) {
            $deleted = $query->delete();

            $this->info("Successfully deleted {$deleted} unused website settings.");

            // Részletes kimenet, ha verbose módban vagyunk
            if ($this->option('verbose')) {
                $deletedItems = Option::withTrashed()
                    ->where('name', 'like', 'website_setting_%')
                    ->whereNotIn('name', $protectedKeys)
                    ->whereNotNull('deleted_at')
                    ->latest('deleted_at')
                    ->limit(20)
                    ->get();

                $this->table(['ID', 'Name', 'Deleted At'], $deletedItems->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->name,
                        'deleted_at' => $option->deleted_at->format('Y-m-d H:i:s')
                    ];
                }));
            }

            return 0;
        }

        $this->info("Deletion cancelled.");
        return 1;
    }
}