<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum PostFilterHook: string
{
    case POST_CREATED_BEFORE = 'filter.post.created_before';
    case POST_CREATED_AFTER = 'filter.post.created_after';

    case POST_UPDATED_BEFORE = 'filter.post.updated_before';
    case POST_UPDATED_AFTER = 'filter.post.updated_after';

    case POST_DELETED_BEFORE = 'filter.post.deleted_before';
    case POST_DELETED_AFTER = 'filter.post.deleted_after';

    case POST_CONTENT_FILTER = 'filter.post.content';
    case POST_TITLE_FILTER = 'filter.post.title';
    case POST_STATUS_FILTER = 'filter.post.status';

    // UI Hooks - Breadcrumbs
    case POSTS_AFTER_BREADCRUMBS = 'filter.post.after_breadcrumbs';
    case POSTS_LIST_AFTER_BREADCRUMBS = 'filter.post.list.after_breadcrumbs';
    case POSTS_CREATE_AFTER_BREADCRUMBS = 'filter.post.create.after_breadcrumbs';
    case POSTS_EDIT_AFTER_BREADCRUMBS = 'filter.post.edit.after_breadcrumbs';
    case POSTS_SHOW_AFTER_BREADCRUMBS = 'filter.post.show.after_breadcrumbs';

    // UI Hooks - Table.
    case POSTS_AFTER_TABLE = 'filter.post.after_table';

    // UI Hooks - Form.
    case INSIDE_POST_FORM_START = 'filter.post.form_start';
    case POST_FORM_AFTER_TITLE = 'filter.post.form_after_title';
    case POST_FORM_AFTER_SLUG = 'filter.post.form_after_slug';
    case POST_FORM_AFTER_CONTENT = 'filter.post.form_after_content';
    case POST_FORM_AFTER_EXCERPT = 'filter.post.form_after_excerpt';
    case POST_BUILDER_SEO_META = 'filter.post.builder_seo_meta';
    case POST_BUILDER_SEO_FIELDS = 'filter.post.builder_seo_fields';
    case POST_FORM_AFTER_STATUS = 'filter.post.form_after_status';
    case POST_FORM_AFTER_PUBLISH_DATE = 'filter.post.form_after_publish_date';
    case POST_FORM_AFTER_SUBMIT_BUTTONS = 'filter.post.form_after_submit_buttons';
    case POST_FORM_AFTER_FEATURED_IMAGE = 'filter.post.form_after_featured_image';
    case POST_FORM_AFTER_CONTENT_PARENT = 'filter.post.form_after_content_parent';
    case POST_FORM_AFTER_TAXONOMY = 'filter.post.form_after_taxonomy_';
    case AFTER_POST_FORM = 'filter.post.after_form';

    // UI Hooks - Filters.
    case POST_ACTIONS_AFTER_EDIT = 'filter.post.actions_after_edit';
    case POST_ACTIONS_AFTER_VIEW = 'filter.post.actions_after_view';
    case POST_ACTIONS_AFTER_DELETE = 'filter.post.actions_after_delete';

    // Validation rule - Filters.
    case POST_STORE_VALIDATION_RULES = 'post.store.validation.rules';
    case POST_UPDATE_VALIDATION_RULES = 'post.update.validation.rules';

    // UI Hooks - Content.
    case POSTS_SHOW_AFTER_CONTENT = 'filter.posts.show_after_content';

    // Options.
    case POST_STATUS_OPTIONS = 'filter.post.status_options';
}
