<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class SchoolBackupCommand extends Command
{
    protected $signature = 'school:backup {--storage : Include public upload files in zip}';

    protected $description = 'Create a timestamped backup manifest (and optional storage zip) under storage/backups';

    public function handle(): int
    {
        $dir = storage_path('backups');
        File::ensureDirectoryExists($dir);

        $stamp = now()->format('Y-m-d_His');
        $manifestPath = $dir.DIRECTORY_SEPARATOR."manifest_{$stamp}.json";

        $manifest = [
            'created_at' => now()->toIso8601String(),
            'app_url' => config('app.url'),
            'database' => config('database.default'),
            'database_name' => config('database.connections.'.config('database.default').'.database'),
            'notes' => 'Run mysqldump for MySQL or copy SQLite file. Example: mysqldump -u root school_management > backup.sql',
        ];

        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
        $this->info("Manifest written: {$manifestPath}");

        if ($this->option('storage')) {
            $zipPath = $dir.DIRECTORY_SEPARATOR."storage_{$stamp}.zip";
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                $this->error('Could not create zip archive.');

                return self::FAILURE;
            }

            $publicRoot = Storage::disk('public')->path('');
            if (File::isDirectory($publicRoot)) {
                foreach (File::allFiles($publicRoot) as $file) {
                    $zip->addFile($file->getPathname(), 'public/'.$file->getRelativePathname());
                }
            }
            $zip->close();
            $this->info("Storage zip: {$zipPath}");
        }

        return self::SUCCESS;
    }
}
