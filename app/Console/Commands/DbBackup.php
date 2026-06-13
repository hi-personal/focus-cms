<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DbBackup extends Command
{
    protected $signature = 'db:backup:full
                            {--charset=utf8mb4}
                            {--collation=utf8mb4_hungarian_ci}';

    protected $description = 'Create MySQL backup with proper charset handling';

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
        $baseFile = "{$backupDir}/{$db['database']}_{$timestamp}";

        $charset = $this->option('charset');
        $collation = $this->option('collation');

        // Egyetlen mysqldump parancs (struktúra + adatok együtt)
        $backupFile = $baseFile . '_full.sql';
        $compressedFile = $baseFile . '_full.sql.gz';

        $command = sprintf(
            'MYSQL_PWD=%s /usr/bin/mysqldump ' .
            '--no-tablespaces ' .
            '--single-transaction ' .
            '--quick ' .
            '--default-character-set=%s ' .
            '--set-charset ' .
            '--add-drop-table ' .
            '--complete-insert ' .
            '--create-options ' .
            '--hex-blob ' .
            '--skip-lock-tables ' .
            '-h %s -u %s %s > %s',
            escapeshellarg($db['password']),
            $charset,
            escapeshellarg($db['host']),
            escapeshellarg($db['username']),
            escapeshellarg($db['database']),
            escapeshellarg($backupFile)
        );

        $this->info('Exporting database...');
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Database export failed.');
            $this->error('Command: ' . $command);
            return Command::FAILURE;
        }

        // Collation javítás a backup fájlban
        $this->info('Fixing collation in backup file...');
        $fixCommand = sprintf(
            'sed -i "s/utf8mb4_unicode_ci/utf8mb4_hungarian_ci/g" %s',
            escapeshellarg($backupFile)
        );
        exec($fixCommand);

        // SET NAMES hozzáadása a fájl elejére
        $headerCommand = sprintf(
            'sed -i "1i/*!40101 SET NAMES %s COLLATE %s */;" %s',
            $charset,
            $collation,
            escapeshellarg($backupFile)
        );
        exec($headerCommand);

        // Tömörítés
        $this->info('Compressing backup...');
        $compressCommand = sprintf('gzip -f %s', escapeshellarg($backupFile));
        exec($compressCommand);

        // Ellenőrzés
        if (!File::exists($compressedFile) || File::size($compressedFile) === 0) {
            $this->error('Final backup file is empty or missing!');
            return Command::FAILURE;
        }

        $size = File::size($compressedFile) / 1024 / 1024;
        $this->info("Full backup created: {$compressedFile}");
        $this->info("Backup size: " . number_format($size, 2) . " MB");
        $this->info("Charset in backup: {$charset}");
        $this->info("Collation in backup: {$collation}");

        return Command::SUCCESS;
    }

    public function restore($backupFile)
    {
        $connection = config('database.default');
        $db = config("database.connections.$connection");

        $charset = $this->option('charset');
        $collation = $this->option('collation');

        if (!File::exists($backupFile)) {
            $this->error("Backup file not found: {$backupFile}");
            return Command::FAILURE;
        }

        // Ha gzip tömörített
        if (str_ends_with($backupFile, '.gz')) {
            $command = sprintf(
                'gunzip -c %s | mysql ' .
                '--default-character-set=%s ' .
                '--init-command="SET NAMES %s COLLATE %s" ' .
                '-h %s -u %s -p%s %s',
                escapeshellarg($backupFile),
                $charset,
                $charset,
                $collation,
                escapeshellarg($db['host']),
                escapeshellarg($db['username']),
                escapeshellarg($db['password']),
                escapeshellarg($db['database'])
            );
        } else {
            $command = sprintf(
                'mysql ' .
                '--default-character-set=%s ' .
                '--init-command="SET NAMES %s COLLATE %s" ' .
                '-h %s -u %s -p%s %s < %s',
                $charset,
                $charset,
                $collation,
                escapeshellarg($db['host']),
                escapeshellarg($db['username']),
                escapeshellarg($db['password']),
                escapeshellarg($db['database']),
                escapeshellarg($backupFile)
            );
        }

        $this->info("Restoring database from: {$backupFile}");
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error("Restore failed!");
            return Command::FAILURE;
        }

        $this->info("Database restored successfully!");
        return Command::SUCCESS;
    }
}