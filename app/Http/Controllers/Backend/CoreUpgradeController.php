<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\CoreUpgradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CoreUpgradeController extends Controller
{
    public function __construct(
        protected CoreUpgradeService $upgradeService
    ) {
    }

    /**
     * Display the core upgrade management page.
     */
    public function index(): View
    {
        $this->checkAuthorization(Auth::user(), ['settings.view']);

        $currentVersion = $this->upgradeService->getCurrentVersion();
        $updateInfo = $this->upgradeService->getStoredUpdateInfo();
        $backups = $this->upgradeService->getBackups();

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
        $this->checkAuthorization(Auth::user(), ['settings.manage']);

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
    }

    /**
     * Perform the upgrade.
     */
    public function upgrade(Request $request): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['settings.manage']);

        $request->validate([
            'version' => 'required|string|max:20',
            'create_backup' => 'boolean',
        ]);

        $version = $request->input('version');
        $createBackup = $request->boolean('create_backup', true);

        $backupFile = null;
        if ($createBackup) {
            $backupFile = $this->upgradeService->createBackup();
            if (! $backupFile) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to create backup. Upgrade aborted.'),
                ]);
            }
        }

        $result = $this->upgradeService->performUpgrade($version, $backupFile);

        return response()->json($result);
    }

    /**
     * Restore from a backup.
     */
    public function restore(Request $request): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['settings.manage']);

        $request->validate([
            'backup_file' => 'required|string',
        ]);

        $backupPath = storage_path('app/core-backups/'.$request->input('backup_file'));

        $result = $this->upgradeService->restoreFromBackup($backupPath);

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
    }

    /**
     * Delete a backup.
     */
    public function deleteBackup(Request $request): RedirectResponse
    {
        $this->checkAuthorization(Auth::user(), ['settings.manage']);

        $request->validate([
            'backup_file' => 'required|string',
        ]);

        $result = $this->upgradeService->deleteBackup($request->input('backup_file'));

        if ($result) {
            return back()->with('success', __('Backup deleted successfully.'));
        }

        return back()->with('error', __('Failed to delete backup.'));
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
