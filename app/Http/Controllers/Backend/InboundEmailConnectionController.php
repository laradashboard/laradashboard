<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\InboundEmailConnection\StoreInboundEmailConnectionRequest;
use App\Http\Requests\InboundEmailConnection\UpdateInboundEmailConnectionRequest;
use App\Models\EmailConnection;
use App\Models\InboundEmailConnection;
use App\Models\Setting;
use App\Services\ImapService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;

class InboundEmailConnectionController extends Controller
{
    public function __construct(
        private readonly ImapService $imapService,
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->setBreadcrumbTitle(__('Inbound Email Connections'))
            ->setBreadcrumbIcon('lucide:mail-open')
            ->setBreadcrumbActionClick(
                "window.dispatchEvent(new CustomEvent('open-inbound-connection-form'))",
                __('New Connection'),
                'feather:plus',
                'settings.edit'
            );

        $outboundConnections = EmailConnection::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'from_email']);

        return $this->renderViewWithBreadcrumbs('backend.pages.inbound-email-connections.index', [
            'outboundConnections' => $outboundConnections,
        ]);
    }

    public function store(StoreInboundEmailConnectionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        $connection = InboundEmailConnection::create($data);

        $this->storeActionLog(ActionType::CREATED, [
            'inbound_email_connection' => $connection->only(['id', 'name', 'imap_host', 'imap_username']),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Inbound email connection created successfully.'),
            'connection' => $connection,
        ]);
    }

    public function show(InboundEmailConnection $inboundEmailConnection): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $connectionData = $inboundEmailConnection->toArray();
        // Mask password for security
        $connectionData['imap_password'] = $inboundEmailConnection->imap_password ? '********' : '';

        return response()->json([
            'connection' => $connectionData,
        ]);
    }

    public function update(UpdateInboundEmailConnectionRequest $request, InboundEmailConnection $inboundEmailConnection): JsonResponse
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        // Don't update password if it's masked
        if (isset($data['imap_password']) && $data['imap_password'] === '********') {
            unset($data['imap_password']);
        }

        $inboundEmailConnection->update($data);

        $this->storeActionLog(ActionType::UPDATED, [
            'inbound_email_connection' => $inboundEmailConnection->only(['id', 'name', 'imap_host', 'imap_username']),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Inbound email connection updated successfully.'),
            'connection' => $inboundEmailConnection,
        ]);
    }

    public function destroy(InboundEmailConnection $inboundEmailConnection): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $this->storeActionLog(ActionType::DELETED, [
            'inbound_email_connection' => $inboundEmailConnection->only(['id', 'name', 'imap_host', 'imap_username']),
        ]);

        $inboundEmailConnection->delete();

        return response()->json([
            'success' => true,
            'message' => __('Inbound email connection deleted successfully.'),
        ]);
    }

    public function testConnection(InboundEmailConnection $inboundEmailConnection): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $result = $this->imapService->testConnection($inboundEmailConnection);

        // Update connection status
        $inboundEmailConnection->markAsChecked($result['success'], $result['message']);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    public function toggleActive(InboundEmailConnection $inboundEmailConnection): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $inboundEmailConnection->update([
            'is_active' => ! $inboundEmailConnection->is_active,
            'updated_by' => auth()->id(),
        ]);

        $status = $inboundEmailConnection->is_active ? __('activated') : __('deactivated');

        return response()->json([
            'success' => true,
            'message' => __('Connection :status successfully.', ['status' => $status]),
            'is_active' => $inboundEmailConnection->is_active,
        ]);
    }

    public function processNow(InboundEmailConnection $inboundEmailConnection): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $processor = app(\App\Services\InboundEmailProcessor::class);
        $stats = $processor->processConnection($inboundEmailConnection);

        return response()->json([
            'success' => empty($stats['errors']),
            'message' => sprintf(
                __('Processed: %d fetched, %d processed, %d failed'),
                $stats['fetched'],
                $stats['processed'],
                $stats['failed']
            ),
            'stats' => $stats,
        ]);
    }
}
