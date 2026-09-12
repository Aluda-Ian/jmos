<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use App\Services\SystemUpgradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemUpgradeController extends Controller
{
    public function __construct(
        protected SystemUpgradeService $upgradeService,
        protected DatabaseBackupService $backupService
    ) {}

    /**
     * Check if the requesting user has IT / Owner privileges.
     */
    protected function checkAuthorized(Request $request): bool
    {
        $user = $request->user();
        if (! $user) {
            return true; // Local or token-less development fallback
        }

        return in_array($user->role, ['owner', 'admin'], true);
    }

    /**
     * Get system environment status, pending migrations, and upgrade history.
     */
    public function status(Request $request): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Only IT Managers and Owners can view system maintenance status.',
            ], 403);
        }

        $status = $this->upgradeService->getSystemStatus();
        $history = $this->upgradeService->getUpgradeHistory();
        $backups = $this->backupService->listBackups();

        return response()->json([
            'status' => 'success',
            'data' => [
                'system' => $status,
                'history' => $history,
                'backups' => $backups,
            ],
        ]);
    }

    /**
     * Upload an updated code archive (.zip) and safely run upgrade & migrations.
     */
    public function upgrade(Request $request): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Only IT Managers and Owners can execute system software upgrades.',
            ], 403);
        }

        $request->validate([
            'archive' => 'required|file|mimes:zip|max:153600', // max 150MB
            'run_migrations' => 'nullable|boolean',
            'clear_caches' => 'nullable|boolean',
            'create_backup' => 'nullable|boolean',
        ]);

        $file = $request->file('archive');
        if (! $file || ! $file->isValid()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or incomplete archive upload. Check PHP file upload limits.',
            ], 422);
        }

        $userName = $request->user()?->name ?? 'IT Manager';
        $options = [
            'run_migrations' => $request->boolean('run_migrations', true),
            'clear_caches' => $request->boolean('clear_caches', true),
            'create_backup' => $request->boolean('create_backup', true),
            'user_name' => $userName,
        ];

        // Ensure generous execution time for extracting large archives and migrations
        @set_time_limit(300);

        $result = $this->upgradeService->applyUpgrade($file, $options);

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => $result,
        ], $statusCode);
    }

    /**
     * Run pending migrations on-demand without uploading an archive.
     */
    public function migrate(Request $request): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $result = $this->upgradeService->runPendingMigrations();

        return response()->json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => [
                'output' => $result['output'],
                'system' => $this->upgradeService->getSystemStatus(),
            ],
        ], $result['success'] ? 200 : 422);
    }

    /**
     * Trigger an on-demand database backup.
     */
    public function createBackup(Request $request): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $result = $this->backupService->createBackup('manual_backup_');

        if (! $result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database backup failed: '.($result['error'] ?? 'Unknown error'),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Database backup created successfully: {$result['filename']}",
            'data' => [
                'backup' => $result,
                'backups' => $this->backupService->listBackups(),
            ],
        ]);
    }

    /**
     * List all database and rollback backups.
     */
    public function backups(Request $request): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->backupService->listBackups(),
        ]);
    }

    /**
     * Download a specific backup file safely.
     */
    public function downloadBackup(Request $request, string $filename): BinaryFileResponse|JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $path = $this->backupService->getBackupPath($filename);
        if (! $path) {
            return response()->json([
                'status' => 'error',
                'message' => 'Backup file not found or invalid filename.',
            ], 404);
        }

        return response()->download($path, basename($path));
    }

    /**
     * Clear all application and compiled caches on-demand.
     */
    public function clearCache(Request $request): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $result = $this->upgradeService->clearApplicationCaches();

        return response()->json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => [
                'output' => $result['output'],
            ],
        ]);
    }
}
