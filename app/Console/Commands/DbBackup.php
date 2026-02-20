<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DbBackup extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Create a MySQL database backup using mysqldump';

    public function handle(): int
    {
        $connection = config('database.default');
        $db = config("database.connections.$connection");

        if (! $db) {
            $this->error("Database connection [$connection] not found.");
            return Command::FAILURE;
        }

        $backupDir = storage_path('app/db-backups');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $file = "{$backupDir}/{$db['database']}_{$timestamp}.sql.gz";

        $host = escapeshellarg($db['host']);
        $user = escapeshellarg($db['username']);
        $pass = $db['password']; // intentionally not escaped (passed via env)
        $name = escapeshellarg($db['database']);

        $command = sprintf(
            'MYSQL_PWD=%s /usr/bin/mysqldump --no-tablespaces --single-transaction --quick -h %s -u %s %s | gzip > %s',
            escapeshellarg($pass),
            $host,
            $user,
            $name,
            escapeshellarg($file)
        );

        $this->info('Running database backup...');
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || ! File::exists($file)) {
            $this->error('Database backup failed.');
            return Command::FAILURE;
        }

        $this->info("Backup created: {$file}");
        return Command::SUCCESS;
    }
}
