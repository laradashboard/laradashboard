<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum FrontendFilterHook: string
{
    case HOMEPAGE_QUERY = 'filter.frontend.homepage_query';
    case POSTS_QUERY = 'filter.frontend.posts_query';
    case SINGLE_POST_QUERY = 'filter.frontend.single_post_query';
    case SEARCH_QUERY = 'filter.frontend.search_query';
    case TAXONOMY_QUERY = 'filter.frontend.taxonomy_query';
    case RELATED_POSTS = 'filter.frontend.related_posts';
    case SEO_PARAMS = 'filter.frontend.seo_params';
    case MENU_ITEMS = 'filter.frontend.menu_items';
    case SOCIAL_LINKS = 'filter.frontend.social_links';
    case SITE_IDENTITY = 'filter.frontend.site_identity';
}
