<?php

declare(strict_types=1);

namespace App\Services\Mcp;

use App\Enums\Hooks\McpFilterHook;
use App\Mcp\Attributes\McpToolMeta;
use App\Mcp\Tools\ActivateModuleTool;
use App\Mcp\Tools\AssignPostTermsTool;
use App\Mcp\Tools\AttachFeaturedImageTool;
use App\Mcp\Tools\ClearCacheTool;
use App\Mcp\Tools\CreatePostTool;
use App\Mcp\Tools\DeactivateModuleTool;
use App\Mcp\Tools\DeletePostTool;
use App\Mcp\Tools\GenerateSeoMetaTool;
use App\Mcp\Tools\GetDailyBriefingTool;
use App\Mcp\Tools\GetEmailTemplateTool;
use App\Mcp\Tools\GetLogTailTool;
use App\Mcp\Tools\GetPostTool;
use App\Mcp\Tools\GetSiteHealthTool;
use App\Mcp\Tools\ListEmailTemplatesTool;
use App\Mcp\Tools\ListLogsTool;
use App\Mcp\Tools\ListMediaTool;
use App\Mcp\Tools\ListMcpToolsTool;
use App\Mcp\Tools\ListModulesTool;
use App\Mcp\Tools\ListPostsTool;
use App\Mcp\Tools\ListTermsTool;
use App\Mcp\Tools\SendEmailTool;
use App\Mcp\Tools\UpdatePostTool;
use App\Mcp\Tools\UploadMediaTool;
use App\Support\Facades\Hook;
use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use ReflectionClass;

class McpRegistryService
{
    /**
     * @return array<int, class-string<Tool>>
     */
    public function toolClasses(): array
    {
        /** @var array<int, class-string<Tool>> $tools */
        $tools = Hook::applyFilters(McpFilterHook::TOOLS, [
            ListPostsTool::class,
            GetPostTool::class,
            CreatePostTool::class,
            UpdatePostTool::class,
            DeletePostTool::class,
            AssignPostTermsTool::class,
            ListTermsTool::class,
            ListMediaTool::class,
            UploadMediaTool::class,
            AttachFeaturedImageTool::class,
            GenerateSeoMetaTool::class,
            GetDailyBriefingTool::class,
            ListEmailTemplatesTool::class,
            GetEmailTemplateTool::class,
            SendEmailTool::class,
            ClearCacheTool::class,
            ListLogsTool::class,
            GetLogTailTool::class,
            GetSiteHealthTool::class,
            ListMcpToolsTool::class,
            ListModulesTool::class,
            ActivateModuleTool::class,
            DeactivateModuleTool::class,
        ]);

        return array_values(array_unique(array_filter($tools, function (string $toolClass): bool {
            return class_exists($toolClass) && is_subclass_of($toolClass, Tool::class);
        })));
    }

    /**
     * @return array<int, array{
     *     class: class-string<Tool>,
     *     name: string,
     *     description: string,
     *     permission: string|null,
     *     ability: string,
     *     group: string
     * }>
     */
    public function toolDefinitions(): array
    {
        $definitions = [];

        foreach ($this->toolClasses() as $toolClass) {
            $definitions[] = $this->resolveToolDefinition($toolClass);
        }

        /** @var array<int, array<string, mixed>> $definitions */
        return Hook::applyFilters(McpFilterHook::TOOL_DEFINITIONS, $definitions);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function groupedToolDefinitions(): array
    {
        $grouped = [];

        foreach ($this->toolDefinitions() as $definition) {
            $group = (string) ($definition['group'] ?? __('Core'));
            $grouped[$group][] = $definition;
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * @param  class-string<Tool>  $toolClass
     * @return array{
     *     class: class-string<Tool>,
     *     name: string,
     *     description: string,
     *     permission: string|null,
     *     ability: string,
     *     group: string
     * }
     */
    protected function resolveToolDefinition(string $toolClass): array
    {
        /** @var Tool $tool */
        $tool = app($toolClass);
        $meta = $this->resolveToolMeta($toolClass);

        return [
            'class' => $toolClass,
            'name' => $tool->name(),
            'description' => $tool->description(),
            'permission' => $meta['permission'],
            'ability' => $meta['ability'],
            'group' => $meta['group'],
        ];
    }

    /**
     * @param  class-string<Tool>  $toolClass
     * @return array{permission: string|null, ability: string, group: string}
     */
    protected function resolveToolMeta(string $toolClass): array
    {
        $reflection = new ReflectionClass($toolClass);
        $attributes = $reflection->getAttributes(McpToolMeta::class);

        if ($attributes !== []) {
            /** @var McpToolMeta $meta */
            $meta = $attributes[0]->newInstance();

            return [
                'permission' => $meta->permission,
                'ability' => $meta->ability,
                'group' => $meta->group ?? $this->resolveDefaultGroup($toolClass),
            ];
        }

        return [
            'permission' => null,
            'ability' => 'mcp:access',
            'group' => $this->resolveDefaultGroup($toolClass),
        ];
    }

    /**
     * @param  class-string  $toolClass
     */
    protected function resolveDefaultGroup(string $toolClass): string
    {
        if (str_starts_with($toolClass, 'Modules\\')) {
            $segments = explode('\\', $toolClass);

            return Str::headline($segments[1] ?? __('Module'));
        }

        return __('Core');
    }
}
