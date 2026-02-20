<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ServeAndDev extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serve:dev
                            {--vite : Run npm run dev}
                            {--home : Run on 192.168.178.100}
                            {--ip= : Run on a custom IP address}
                            {--url= : Temporarily override APP_URL}
                            {--port= : Specify a custom port for Laravel server}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run php artisan serve and optionally npm run dev with --vite. Temporarily override APP_URL and Vite host based on --home/--ip/--url.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Determine the host IP
        $host = '0.0.0.0'; // Default host
        if ($this->option('home')) {
            $host = '192.168.178.100'; // Use home IP
        } elseif ($this->option('ip')) {
            $host = $this->option('ip'); // Use custom IP
        }

        // Determine the port
        $port = $this->option('port') ?: config('app.port', '90'); // Use custom port or default

        // Temporarily override APP_URL if --url is provided
        if ($this->option('url')) {
            $this->info("Temporarily overriding APP_URL to: " . $this->option('url'));
            Config::set('app.url', $this->option('url'));
        } else {
            // Automatically set APP_URL based on host and port
            $appUrl = "http://{$host}:{$port}";
            $this->info("Automatically setting APP_URL to: {$appUrl}");
            Config::set('app.url', $appUrl);
        }

        // Run php artisan serve in a separate process
        $this->info("Starting Laravel development server on {$host}:{$port}...");
        $serveCommand = "php artisan serve --host={$host} --port={$port}";
        $serveProcess = popen($serveCommand, 'r');

        if ($serveProcess) {
            $this->info('Laravel development server started.');

            // Run npm run dev if --vite is provided
            if ($this->option('vite')) {
                $this->info('Starting npm run dev...');

                // Determine Vite host and port
                $viteHost = $host; // Use the same host as Laravel
                $vitePort = 5173; // Default Vite port

                // Build the npm run dev command with dynamic host
                $npmCommand = "npm run dev -- --host={$viteHost}";
                $npmProcess = popen($npmCommand, 'r');

                if ($npmProcess) {
                    $this->info('npm run dev started.');
                } else {
                    $this->error('Failed to start npm run dev.');
                }
            }
        } else {
            $this->error('Failed to start Laravel development server.');
            return;
        }

        // Keep the command running
        while (!feof($serveProcess)) {
            echo fgets($serveProcess);
        }

        if (isset($npmProcess) && !feof($npmProcess)) {
            while (!feof($npmProcess)) {
                echo fgets($npmProcess);
            }
        }

        pclose($serveProcess);
        if (isset($npmProcess)) {
            pclose($npmProcess);
        }
    }
}
