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
        $schemaFile = $baseFile . '_schema.sql';
        $dataFile = $baseFile . '_data.sql.gz';

        $charset = $this->option('charset');
        $collation = $this->option('collation');

        // 1. Csak struktúra exportálása
        // $schemaCommand = sprintf(
        //     'MYSQL_PWD=%s /usr/bin/mysqldump ' .
        //     '--no-tablespaces ' .              // KULCS: Tablespace-ek kihagyása
        //     '--no-data ' .
        //     '--default-character-set=%s ' .
        //     '--set-charset ' .
        //     '--add-drop-database ' .
        //     '--add-drop-table ' .
        //     '--complete-insert ' .
        //     '--skip-lock-tables ' .            // További biztonság shared hostingra
        //     '-h %s -u %s %s > %s',
        //     escapeshellarg($db['password']),
        //     $charset,
        //     escapeshellarg($db['host']),
        //     escapeshellarg($db['username']),
        //     escapeshellarg($db['database']),
        //     escapeshellarg($schemaFile)
        // );

        $schemaCommand = sprintf(
            'MYSQL_PWD=%s /usr/bin/mysqldump ' .
            '--no-tablespaces ' .
            '--no-data ' .
            '--default-character-set=%s ' .
            '--set-charset ' .
            '--add-drop-table ' .
            '--complete-insert ' .
            '--skip-lock-tables ' .
            '--disable-keys ' .
            '--extended-insert=FALSE ' .
            '--quote-names ' .
            '-h %s -u %s %s > %s',
            escapeshellarg($db['password']),
            $charset,
            escapeshellarg($db['host']),
            escapeshellarg($db['username']),
            escapeshellarg($db['database']),
            escapeshellarg($schemaFile)
        );

        // 2. Csak adatok exportálása
        $dataCommand = sprintf(
            'MYSQL_PWD=%s /usr/bin/mysqldump ' .
            '--no-tablespaces ' .              // KULCS: Tablespace-ek kihagyása
            '--no-create-info ' .
            '--default-character-set=%s ' .
            '--set-charset ' .
            '--hex-blob ' .
            '--complete-insert ' .
            '--extended-insert ' .
            '--skip-lock-tables ' .            // További biztonság shared hostingra
            '-h %s -u %s %s | gzip > %s',
            escapeshellarg($db['password']),
            $charset,
            escapeshellarg($db['host']),
            escapeshellarg($db['username']),
            escapeshellarg($db['database']),
            escapeshellarg($dataFile)
        );

        $this->info('Exporting database schema...');
        exec($schemaCommand, $schemaOutput, $schemaExit);

        if ($schemaExit !== 0) {
            $this->error('Schema export failed.');
            $this->error('Command: ' . $schemaCommand);
            return Command::FAILURE;
        }

        $this->info('Exporting database data...');
        exec($dataCommand, $dataOutput, $dataExit);

        if ($dataExit !== 0) {
            $this->error('Data export failed.');
            $this->error('Command: ' . $dataCommand);
            return Command::FAILURE;
        }

        // 3. Összefűzés
        $finalFile = $baseFile . '_full.sql.gz';
        $finalCommand = sprintf(
            'echo "/*!40101 SET NAMES %s COLLATE %s */;" | cat - %s | gzip > %s',
            $charset,
            $collation,
            escapeshellarg($schemaFile),
            escapeshellarg($finalFile)
        );

        exec($finalCommand);

        // Adatok hozzáfűzése
        $finalCommand2 = sprintf('gunzip -c %s >> %s',
            escapeshellarg($dataFile),
            escapeshellarg($finalFile)
        );

        exec($finalCommand2);

        // Temp fájlok törlése
        File::delete($schemaFile, $dataFile);

        // Ellenőrizd a végső fájl méretét
        if (!File::exists($finalFile) || File::size($finalFile) === 0) {
            $this->error('Final backup file is empty or missing!');
            return Command::FAILURE;
        }

        $size = File::size($finalFile) / 1024 / 1024;
        $this->info("Full backup created: {$finalFile}");
        $this->info("Backup size: " . number_format($size, 2) . " MB");
        $this->info("Charset in backup: {$charset}");
        $this->info("Collation in backup: {$collation}");

        // Ellenőrizd a backup tartalmát
        $checkCommand = sprintf('gunzip -c %s | head -5', escapeshellarg($finalFile));
        exec($checkCommand, $checkOutput);
        $this->info("Backup header: " . implode("\n", array_slice($checkOutput, 0, 3)));

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

        $command = sprintf(
            'gunzip -c %s | mysql ' .
            '--no-tablespaces ' .           // Restore-nál is opcionális
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