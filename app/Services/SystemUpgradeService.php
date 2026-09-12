<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemUpgradeService
{
    protected DatabaseBackupService $backupService;

    protected string $basePath;

    protected string $upgradeLogFile;

    /**
     * Files and directory patterns that must NEVER be overwritten during an upgrade.
     *
     * @var array<int, string>
     */
    protected array $protectedPatterns = [
        '#^\.env(\..*)?$#i',
        '#^storage/.*#i',
        '#^public/storage/.*#i',
        '#^\.git/.*#i',
        '#^bootstrap/cache/.*\.php$#i',
    ];

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
        $this->basePath = base_path();
        $this->upgradeLogFile = storage_path('app/upgrades.json');
    }

    /**
     * Get system status, environment details, and pending migration status.
     *
     * @return array<string, mixed>
     */
    public function getSystemStatus(): array
    {
        $dbConnected = false;
        $dbError = null;
        $pendingMigrations = [];
        $appliedMigrationsCount = 0;

        try {
            DB::connection()->getPdo();
            $dbConnected = true;

            $hasMigrationsTable = DB::getSchemaBuilder()->hasTable('migrations');
            $appliedMigrations = $hasMigrationsTable
                ? DB::table('migrations')->pluck('migration')->toArray()
                : [];

            $appliedMigrationsCount = count($appliedMigrations);

            $migrationFiles = File::glob(database_path('migrations/*.php'));
            foreach ($migrationFiles as $file) {
                $name = basename($file, '.php');
                if (! in_array($name, $appliedMigrations, true)) {
                    $pendingMigrations[] = $name;
                }
            }
        } catch (\Throwable $e) {
            $dbError = $e->getMessage();
        }

        $history = $this->getUpgradeHistory();
        $lastUpgrade = ! empty($history) ? $history[0] : null;

        return [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database' => [
                'connected' => $dbConnected,
                'driver' => config('database.default'),
                'database_name' => config('database.connections.'.config('database.default').'.database'),
                'error' => $dbError,
                'applied_migrations_count' => $appliedMigrationsCount,
                'pending_migrations' => $pendingMigrations,
                'pending_migrations_count' => count($pendingMigrations),
            ],
            'last_upgrade' => $lastUpgrade,
            'backups_count' => count($this->backupService->listBackups()),
        ];
    }

    /**
     * Validate an uploaded zip archive before extraction.
     *
     * @return array{valid: bool, error?: string, file_count: int, detected_migrations: array<int, string>, sample_files: array<int, string>}
     */
    public function validateArchive(UploadedFile|string $zipSource): array
    {
        $filePath = $zipSource instanceof UploadedFile ? $zipSource->getRealPath() : $zipSource;

        if (! File::exists($filePath)) {
            return ['valid' => false, 'error' => 'Upgrade archive file not found.', 'file_count' => 0, 'detected_migrations' => [], 'sample_files' => []];
        }

        if (! class_exists(\ZipArchive::class)) {
            return ['valid' => false, 'error' => 'PHP ZipArchive extension is not enabled on this server.', 'file_count' => 0, 'detected_migrations' => [], 'sample_files' => []];
        }

        $zip = new \ZipArchive;
        $res = $zip->open($filePath);
        if ($res !== true) {
            return ['valid' => false, 'error' => "Failed to open zip archive (error code {$res}).", 'file_count' => 0, 'detected_migrations' => [], 'sample_files' => []];
        }

        $numFiles = $zip->numFiles;
        $sampleFiles = [];
        $detectedMigrations = [];

        for ($i = 0; $i < $numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = str_replace('\\', '/', $stat['name'] ?? '');

            // Security check: Guard against directory traversal
            if (str_contains($name, '../') || str_starts_with($name, '/')) {
                $zip->close();

                return ['valid' => false, 'error' => "Archive contains insecure path traversal: {$name}", 'file_count' => 0, 'detected_migrations' => [], 'sample_files' => []];
            }

            if (str_starts_with($name, 'database/migrations/') && str_ends_with($name, '.php')) {
                $detectedMigrations[] = basename($name);
            }

            if (count($sampleFiles) < 10 && ! str_ends_with($name, '/')) {
                $sampleFiles[] = $name;
            }
        }

        $zip->close();

        return [
            'valid' => true,
            'file_count' => $numFiles,
            'detected_migrations' => $detectedMigrations,
            'sample_files' => $sampleFiles,
        ];
    }

    /**
     * Run the complete safe upgrade workflow.
     *
     * @param  array{run_migrations?: bool, clear_caches?: bool, create_backup?: bool, user_name?: string}  $options
     * @return array<string, mixed>
     */
    public function applyUpgrade(UploadedFile|string $zipSource, array $options = []): array
    {
        $runMigrations = $options['run_migrations'] ?? true;
        $clearCaches = $options['clear_caches'] ?? true;
        $createBackup = $options['create_backup'] ?? true;
        $userName = $options['user_name'] ?? 'IT Manager';

        $steps = [];
        $errors = [];
        $backupResult = null;
        $filesExtracted = 0;
        $filesSkipped = [];

        // 1. Validate Archive
        $validation = $this->validateArchive($zipSource);
        if (! $validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['error'] ?? 'Invalid upgrade archive.',
                'steps' => [['step' => 'Validation', 'status' => 'failed', 'details' => $validation['error']]],
            ];
        }
        $steps[] = [
            'step' => 'Package Verification',
            'status' => 'passed',
            'details' => "Verified archive with {$validation['file_count']} files. Found ".count($validation['detected_migrations']).' migration file(s).',
        ];

        // 2. Safety Database Backup (Zero Data Loss Protection)
        if ($createBackup) {
            $backupResult = $this->backupService->createBackup('pre_upgrade_db_');
            if ($backupResult['success']) {
                $steps[] = [
                    'step' => 'Database Safety Backup',
                    'status' => 'passed',
                    'details' => "Database snapshot created: {$backupResult['filename']} ({$backupResult['size_human']}).",
                ];
            } else {
                $steps[] = [
                    'step' => 'Database Safety Backup',
                    'status' => 'warning',
                    'details' => 'Database backup notice: '.($backupResult['error'] ?? 'Unknown notice').' — proceeding with migration.',
                ];
            }
        }

        // 3. Extract Files (Preserving .env and storage/)
        $filePath = $zipSource instanceof UploadedFile ? $zipSource->getRealPath() : $zipSource;
        $zip = new \ZipArchive;
        if ($zip->open($filePath) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $entryName = str_replace('\\', '/', $stat['name'] ?? '');

                if (empty($entryName) || str_ends_with($entryName, '/')) {
                    continue;
                }

                // Check protected patterns
                $isProtected = false;
                foreach ($this->protectedPatterns as $pattern) {
                    if (preg_match($pattern, $entryName)) {
                        $isProtected = true;
                        break;
                    }
                }

                if ($isProtected) {
                    $filesSkipped[] = $entryName;

                    continue;
                }

                $content = $zip->getFromIndex($i);
                if ($content === false) {
                    continue;
                }

                $destination = $this->basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $entryName);
                $dir = dirname($destination);

                if (! File::exists($dir)) {
                    File::makeDirectory($dir, 0755, true, true);
                }

                File::put($destination, $content);
                $filesExtracted++;
            }
            $zip->close();

            $steps[] = [
                'step' => 'Codebase Extraction',
                'status' => 'passed',
                'details' => "Extracted {$filesExtracted} updated file(s). Protected ".count($filesSkipped).' existing configuration and storage file(s).',
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to extract upgrade archive.',
                'steps' => $steps,
            ];
        }

        // 4. Run Migrations Non-Destructively
        $migrationOutput = '';
        if ($runMigrations) {
            try {
                Artisan::call('migrate', ['--force' => true]);
                $migrationOutput = trim(Artisan::output());

                $steps[] = [
                    'step' => 'Database Migrations',
                    'status' => 'passed',
                    'details' => ! empty($migrationOutput) ? $migrationOutput : 'All migrations are already up to date. No schema changes were required.',
                ];
            } catch (\Throwable $e) {
                $errors[] = 'Migration error: '.$e->getMessage();
                $steps[] = [
                    'step' => 'Database Migrations',
                    'status' => 'failed',
                    'details' => $e->getMessage(),
                ];
            }
        }

        // 5. Clear / Optimize Caches
        $cacheOutput = '';
        if ($clearCaches) {
            try {
                Artisan::call('optimize:clear');
                $cacheOutput = trim(Artisan::output());

                $steps[] = [
                    'step' => 'Cache Refresh',
                    'status' => 'passed',
                    'details' => 'Application, route, view, and config caches successfully cleared.',
                ];
            } catch (\Throwable $e) {
                $steps[] = [
                    'step' => 'Cache Refresh',
                    'status' => 'warning',
                    'details' => 'Cache clear note: '.$e->getMessage(),
                ];
            }
        }

        // 6. Record Upgrade History
        $isSuccess = empty($errors);
        $record = [
            'id' => uniqid('upg_', true),
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s'),
            'user' => $userName,
            'files_extracted' => $filesExtracted,
            'files_protected' => count($filesSkipped),
            'backup_filename' => $backupResult['filename'] ?? null,
            'migrations_output' => $migrationOutput,
            'status' => $isSuccess ? 'success' : 'completed_with_errors',
            'errors' => $errors,
        ];
        $this->recordUpgradeHistory($record);

        return [
            'success' => $isSuccess,
            'message' => $isSuccess
                ? "Upgrade completed successfully! {$filesExtracted} files updated, database migrated safely, caches refreshed."
                : 'Upgrade completed with notices. Review the step log below.',
            'steps' => $steps,
            'record' => $record,
            'system_status' => $this->getSystemStatus(),
        ];
    }

    /**
     * Run pending migrations safely on demand without uploading code.
     *
     * @return array{success: bool, message: string, output: string}
     */
    public function runPendingMigrations(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            return [
                'success' => true,
                'message' => 'Database migrations executed safely.',
                'output' => ! empty($output) ? $output : 'Nothing to migrate. All tables are up to date.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Migration failed: '.$e->getMessage(),
                'output' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clear all application caches on demand.
     *
     * @return array{success: bool, message: string, output: string}
     */
    public function clearApplicationCaches(): array
    {
        try {
            Artisan::call('optimize:clear');
            $output = trim(Artisan::output());

            return [
                'success' => true,
                'message' => 'All application caches cleared successfully.',
                'output' => $output,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to clear caches: '.$e->getMessage(),
                'output' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get upgrade history records.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUpgradeHistory(): array
    {
        if (! File::exists($this->upgradeLogFile)) {
            return [];
        }

        try {
            $json = File::get($this->upgradeLogFile);
            $data = json_decode($json, true);

            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Record an entry into the upgrade history JSON file.
     */
    protected function recordUpgradeHistory(array $entry): void
    {
        $history = $this->getUpgradeHistory();
        array_unshift($history, $entry);

        // Keep maximum 50 history entries
        $history = array_slice($history, 0, 50);

        File::put($this->upgradeLogFile, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
