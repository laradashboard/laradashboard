<?php

declare(strict_types=1);

namespace App\Ai\Providers;

use App\Ai\Capabilities\PostAiCapability;
use App\Ai\Context\PostContextProvider;
use App\Ai\Context\SystemContextProvider;
use App\Ai\Engine\AiCommandProcessor;
use App\Ai\Registry\CapabilityRegistry;
use App\Ai\Registry\ContextRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the AI Command System.
 */
class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiCommandProcessor::class);
    }

    public function boot(): void
    {
        $this->registerContextProviders();
        $this->registerCapabilities();
    }

    protected function registerContextProviders(): void
    {
        ContextRegistry::registerClass(SystemContextProvider::class);
        ContextRegistry::registerClass(PostContextProvider::class);
    }

    protected function registerCapabilities(): void
    {
        CapabilityRegistry::registerClass(PostAiCapability::class);
    }
}
