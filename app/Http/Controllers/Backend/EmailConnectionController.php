<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailConnection\StoreEmailConnectionRequest;
use App\Http\Requests\EmailConnection\UpdateEmailConnectionRequest;
use App\Models\EmailConnection;
use App\Models\Setting;
use App\Services\EmailConnectionService;
use App\Services\EmailProviderRegistry;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailConnectionController extends Controller
{
    public function __construct(
        private readonly EmailConnectionService $connectionService,
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->setBreadcrumbTitle(__('Email Connections'))
            ->setBreadcrumbIcon('lucide:plug')
            ->setBreadcrumbActionClick(
                "openProviderSelector()",
                __('New Connection'),
                'feather:plus',
                'media.create'
            );

        $providers = EmailProviderRegistry::getProviderCards();

        return $this->renderViewWithBreadcrumbs('backend.pages.email-connections.index', [
            'providers' => $providers,
        ]);
    }

    public function store(StoreEmailConnectionRequest $request): JsonResponse
    {
        $connection = $this->connectionService->create($request->validated());

        $this->storeActionLog(ActionType::CREATED, [
            'email_connection' => $connection->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Email connection created successfully.'),
            'connection' => $connection,
        ]);
    }

    public function show(EmailConnection $emailConnection): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $provider = EmailProviderRegistry::getProvider($emailConnection->provider_type);

        // Get credentials for editing (mask sensitive fields like password)
        $credentials = $emailConnection->credentials ?? [];
        $safeCredentials = [];
        if ($provider) {
            foreach ($provider->getFormFields() as $field) {
                if (! ($field['is_credential'] ?? false)) {
                    continue;
                }
                $fieldName = $field['name'];
                // Return non-password credentials, mask passwords
                if ($field['type'] === 'password') {
                    // Indicate if password exists (for UI feedback) but don't return actual value
                    $safeCredentials[$fieldName] = isset($credentials[$fieldName]) && ! empty($credentials[$fieldName])
                        ? '********'
                        : '';
                } else {
                    $safeCredentials[$fieldName] = $credentials[$fieldName] ?? '';
                }
            }
        }

        // Merge safe credentials into connection data
        $connectionData = $emailConnection->toArray();
        $connectionData['credentials'] = $safeCredentials;

        return response()->json([
            'connection' => $connectionData,
            'provider' => $provider ? [
                'key' => $provider->getKey(),
                'name' => $provider->getName(),
                'icon' => $provider->getIcon(),
                'fields' => $provider->getFormFields(),
            ] : null,
        ]);
    }

    public function update(UpdateEmailConnectionRequest $request, EmailConnection $emailConnection): JsonResponse
    {
        $connection = $this->connectionService->update($emailConnection, $request->validated());

        $this->storeActionLog(ActionType::UPDATED, [
            'email_connection' => $connection->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Email connection updated successfully.'),
            'connection' => $connection,
        ]);
    }

    public function destroy(EmailConnection $emailConnection): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $this->storeActionLog(ActionType::DELETED, [
            'email_connection' => $emailConnection->toArray(),
        ]);

        $this->connectionService->delete($emailConnection);

        return response()->json([
            'success' => true,
            'message' => __('Email connection deleted successfully.'),
        ]);
    }

    public function testConnection(Request $request, EmailConnection $emailConnection): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $result = $this->connectionService->testConnection(
            $emailConnection,
            $request->input('email')
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    public function setDefault(EmailConnection $emailConnection): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $this->connectionService->setDefault($emailConnection);

        $this->storeActionLog(ActionType::UPDATED, [
            'email_connection_default' => $emailConnection->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Connection set as default successfully.'),
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:email_connections,id'],
        ]);

        $this->connectionService->reorderPriorities($request->input('ids'));

        return response()->json([
            'success' => true,
            'message' => __('Connections reordered successfully.'),
        ]);
    }

    public function getProviders(): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        return response()->json([
            'providers' => EmailProviderRegistry::getProviderCards(),
        ]);
    }

    public function getProviderFields(string $providerType): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        $provider = EmailProviderRegistry::getProvider($providerType);

        if (! $provider) {
            return response()->json([
                'success' => false,
                'message' => __('Provider not found.'),
            ], 404);
        }

        return response()->json([
            'provider' => [
                'key' => $provider->getKey(),
                'name' => $provider->getName(),
                'icon' => $provider->getIcon(),
                'description' => $provider->getDescription(),
                'fields' => $provider->getFormFields(),
            ],
        ]);
    }
}
