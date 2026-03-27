<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum FrontendActionHook: string
{
    case POST_VIEWED = 'action.frontend.post_viewed';
    case PAGE_VIEWED = 'action.frontend.page_viewed';
    case SEARCH_PERFORMED = 'action.frontend.search_performed';
}
