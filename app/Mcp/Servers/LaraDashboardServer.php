<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Services\Mcp\McpRegistryService;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Lara Dashboard')]
#[Version('1.0.0')]
#[Instructions(<<<'MARKDOWN'
    This MCP server connects AI agents to a Lara Dashboard installation.

    Use the available tools to manage content, CRM, forms, email, and day-to-day operations.
    Authentication requires a Lara Dashboard MCP agent token from Settings > MCP.

    Daily workflow:
    1. get-daily-briefing — personalized checklist across core and installed modules
    2. Act on briefing items with the matching tools below

    Content workflow:
    - list-posts / get-post — inspect content
    - create-post — draft LaraBuilder-compatible content (topic AI or manual title/content)
    - update-post — publish pending posts, edit drafts, or change status
    - delete-post — remove drafts or unwanted posts/pages
    - list-terms / assign-post-terms — categories and tags (use term IDs from list-terms)
    - list-media / upload-media / attach-featured-image — upload heroes and set featured images on posts
    - generate-seo-meta — optimize SEO for a post (requires OpenAI)

    CRM workflow:
    - list-contacts / create-contact / list-deals / create-deal
    - list-tickets / get-ticket / reply-ticket / assign-ticket / update-ticket
    - list-contact-activities / create-contact-activity / update-contact-activity
    - update-deal — move deal stage, owner, or follow-up date

    Forms workflow:
    - list-form-submissions — review new leads (use unviewed_only=true)
    - mark-form-submission-viewed — clear reviewed submissions from briefing

    Email workflow:
    - list-email-templates / get-email-template / send-email

    Documentation workflow (DocForge module):
    - list-doc-projects — discover documentation projects and versions
    - search-docs — find LaraDashboard guides by keyword (use before guessing behavior)
    - list-docs — browse documentation pages
    - get-doc — read full markdown for a page (project_route + document_slug)

    Operations:
    - clear-cache — after settings or module changes
    - list-logs / get-log-tail — inspect and read storage log files
    - get-site-health — Laravel version, drivers, MCP status, enabled modules

    Discovery:
    - list-mcp-tools — tools this token can actually use (respects permissions)
    - list-modules — installed modules with enabled/disabled status and versions
    - activate-module / deactivate-module — enable or disable installed modules (Superadmin required to activate)
    MARKDOWN)]
class LaraDashboardServer extends Server
{
    /**
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [];

    public function createContext(): \Laravel\Mcp\Server\ServerContext
    {
        $this->tools = app(McpRegistryService::class)->toolClasses();

        return parent::createContext();
    }
}
