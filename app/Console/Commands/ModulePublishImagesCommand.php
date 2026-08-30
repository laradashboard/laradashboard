<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Modules\ModuleService;
use Illuminate\Console\Command;

class ModulePublishImagesCommand extends Command
{
    protected $signature = 'module:publish-images
                            {module? : The module name (e.g., Crm). If omitted, publishes all enabled modules}';

    protected $description = 'Publish module logo and banner images to public/images/modules';

    public function __construct(private readonly ModuleService $moduleService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $moduleName = $this->argument('module');

        if ($moduleName) {
            if (! $this->moduleService->publishModuleImages($moduleName)) {
                $this->warn("No images published for module '{$moduleName}'.");

                return self::FAILURE;
            }

            $this->info("Published images for module '{$moduleName}'.");

            return self::SUCCESS;
        }

        $publishedCount = $this->moduleService->publishModuleImagesForEnabledModules();

        if ($publishedCount === 0) {
            $this->info('No module images were published.');

            return self::SUCCESS;
        }

        $this->info("Published images for {$publishedCount} enabled module(s).");

        return self::SUCCESS;
    }
}
