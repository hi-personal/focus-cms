<?php

namespace App\Providers;

use App\Models\Option;
use Illuminate\Support\ServiceProvider;

class OptionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('options.repository', function ($app) {
            return new class {
                protected array $loadedOptions = [];

                public function get(string $name, $default = null)
                {
                    if (!array_key_exists($name, $this->loadedOptions)) {
                        $this->loadedOptions[$name] = Option::where('name', $name)->value('value') ?? $default;
                    }

                    return $this->loadedOptions[$name];
                }
            };
        });
    }

    public function boot()
    {
        //
    }
}