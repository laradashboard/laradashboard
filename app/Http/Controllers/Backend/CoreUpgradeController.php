<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreUpgrade\CreateBackupRequest;
use App\Http\Requests\CoreUpgrade\DeleteBackupRequest;
use App\Http\Requests\CoreUpgrade\RestoreRequest;
use App\Http\Requests\CoreUpgrade\UpgradeRequest;
use App\Http\Requests\CoreUpgrade\UploadRequest;
use App\Models\Setting;
use App\Services\BackupService;
use App\Services\CoreUpgradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CoreUpgradeController extends Controller
{
    public function __construct(
        protected CoreUpgradeService $upgradeService,
        protected BackupService $backupService
    ) {
    }

    /**
     * Display the core upgrade management page.
     */
    public function index(): View
    {
        $this->authorize('viewCoreUpgrades', Setting::class);

        $currentVersion = $this->upgradeService->getCurrentVersion();
        $updateInfo = $this->upgradeService->getStoredUpdateInfo();
        $backups = $this->backupService->getBackups();

        $this->setBreadcrumbTitle(__('Core Upgrades'))
            ->setBreadcrumbIcon('lucide:package');

        return $this->renderViewWithBreadcrumbs('backend.pages.settings.core-upgrades', [
            'currentVersion' => $currentVersion,
            'updateInfo' => $updateInfo,
            'backups' => $backups,
        ]);
    }

    /**
     * Check for updates.
     */
    public function checkUpdates(): JsonResponse
    {
        $this->authorize('manageCoreUpgrades', Setting::class);

        try {
            $result = $this->upgradeService->checkForUpdates();

            if (! $result) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to check for updates. Please try again later.'),
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Core upgrade check failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred while checking for updates.'),
            ], 500);
        }
    }

    /**
     * Perform the upgrade.
     */
    public function upgrade(UpgradeRequest $request): JsonResponse
    {
        try {
            $version = $request->validated('version');
            $createBackup = $request->boolean('create_backup', true);

            $backupFile = null;
            if ($createBackup) {
                $backupFile = $this->backupService->createBackup();
                if (! $backupFile) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Failed to create backup. Upgrade aborted.'),
                    ]);
                }
            }

            $result = $this->upgradeService->performUpgrade($version, $backupFile);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Core upgrade failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred during the upgrade: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Upload and apply a manual upgrade zip file.
     */
    public function uploadUpgrade(UploadRequest $request): JsonResponse
    {
        try {
            $file = $request->file('upgrade_file');
            $createBackup = $request->boolean('create_backup', true);

            $backupFile = null;
            if ($createBackup) {
                $backupFile = $this->backupService->createBackup();
                if (! $backupFile) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Failed to create backup. Upgrade aborted.'),
                    ]);
                }
            }

            $result = $this->upgradeService->performUpgradeFromUpload($file, $backupFile);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Core upgrade from upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred during the upgrade: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Restore from a backup.
     */
    public function restore(RestoreRequest $request): JsonResponse
    {
        try {
            $backupPath = $this->backupService->getBackupPath() . '/' . $request->validated('backup_file');

            if (! file_exists($backupPath)) {
                return response()->json([
                    'success' => false,
                    'message' => __('Backup file not found.'),
                ], 404);
            }

            $result = $this->backupService->restoreFromBackup($backupPath);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => __('Successfully restored from backup.'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('Failed to restore from backup.'),
            ]);
        } catch (\Exception $e) {
            Log::error('Core restore from backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred during restore: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Create a backup with options.
     */
    public function createBackup(CreateBackupRequest $request): JsonResponse
    {
        try {
            $backupType = $request->validated('backup_type');
            $includeDatabase = $request->boolean('include_database', false);
            $includeVendor = $request->boolean('include_vendor', false);

            $backupFile = $this->backupService->createBackupWithOptions($backupType, $includeDatabase, $includeVendor);

            if (! $backupFile) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to create backup. Please try again.'),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => __('Backup created successfully.'),
                'backup_file' => basename($backupFile),
            ]);
        } catch (\Exception $e) {
            Log::error('Core backup creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred while creating the backup: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Delete a backup.
     */
    public function deleteBackup(DeleteBackupRequest $request): RedirectResponse
    {
        try {
            $result = $this->backupService->deleteBackup($request->validated('backup_file'));

            if ($result) {
                return back()->with('success', __('Backup deleted successfully.'));
            }

            return back()->with('error', __('Failed to delete backup.'));
        } catch (\Exception $e) {
            Log::error('Core backup deletion failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('An error occurred while deleting the backup.'));
        }
    }

    /**
     * Download a backup file.
     */
    public function downloadBackup(string $filename): BinaryFileResponse|RedirectResponse
    {
        $this->authorize('manageCoreUpgrades', Setting::class);

        $backupPath = $this->backupService->getBackupPath() . '/' . $filename;

        if (! file_exists($backupPath)) {
            return back()->with('error', __('Backup file not found.'));
        }

        return response()->download($backupPath, $filename);
    }

    /**
     * Get the current update status for the notification badge.
     */
    public function getUpdateStatus(): JsonResponse
    {
        $updateInfo = $this->upgradeService->getStoredUpdateInfo();

        return response()->json([
            'has_update' => $updateInfo !== null && ($updateInfo['has_update'] ?? false),
            'latest_version' => $updateInfo['latest_version'] ?? null,
            'is_critical' => $updateInfo['has_critical'] ?? false,
        ]);
    }
}
