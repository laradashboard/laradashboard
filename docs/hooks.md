# LaraDashboard Hooks System

LaraDashboard provides a beautiful hooks system that allows module developers to extend and customize functionality without modifying core files. This system uses **action hooks** and **filter hooks** powered by the [Eventy](https://github.com/tormjens/eventy) package.

## Table of Contents

- [Overview](#overview)
- [Getting Started](#getting-started)
- [Action Hooks vs Filter Hooks](#action-hooks-vs-filter-hooks)
- [Using Hooks](#using-hooks)
  - [Using the Hook Facade](#using-the-hook-facade)
  - [Using Helper Functions](#using-helper-functions)
  - [Using Hook Enums](#using-hook-enums)
- [Available Hooks](#available-hooks)
  - [Permission Hooks](#permission-hooks)
  - [User Hooks](#user-hooks)
  - [Post Hooks](#post-hooks)
  - [Role Hooks](#role-hooks)
  - [Term/Taxonomy Hooks](#termtaxonomy-hooks)
  - [Module Hooks](#module-hooks)
  - [Email Hooks](#email-hooks)
  - [Notification Hooks](#notification-hooks)
  - [Media Hooks](#media-hooks)
  - [Setting Hooks](#setting-hooks)
  - [Dashboard Hooks](#dashboard-hooks)
  - [Admin UI Hooks](#admin-ui-hooks)
  - [Authentication Hooks](#authentication-hooks)
  - [Action Log Hooks](#action-log-hooks)
  - [Datatable Hooks](#datatable-hooks)
  - [Common Hooks](#common-hooks)
- [Creating Custom Hooks in Your Module](#creating-custom-hooks-in-your-module)
- [Best Practices](#best-practices)
- [Examples](#examples)

---

## Overview

The hooks system allows you to:

- **Extend functionality** without modifying core code
- **Add custom permissions** for your module
- **Inject custom UI elements** into pages
- **Modify data** before it's saved or displayed
- **React to events** like user creation, module activation, etc.
- **Add custom dashboard widgets** and stats

---

## Getting Started

### Registering Hooks in Your Module

The best place to register hooks is in your module's Service Provider:

```php
<?php

namespace Modules\YourModule\Providers;

use App\Enums\Hooks\PermissionFilterHook;
use App\Enums\Hooks\DashboardFilterHook;
use App\Support\Facades\Hook;
use Illuminate\Support\ServiceProvider;

class YourModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register your hooks here
        $this->registerHooks();
    }

    protected function registerHooks(): void
    {
        // Add custom permissions
        Hook::addFilter(PermissionFilterHook::PERMISSION_GROUPS, function ($groups) {
            $groups[] = [
                'group_name' => 'your_module',
                'permissions' => [
                    'your_module.view',
                    'your_module.create',
                    'your_module.edit',
                    'your_module.delete',
                ],
            ];
            return $groups;
        });

        // Add dashboard widget
        Hook::addFilter(DashboardFilterHook::DASHBOARD_WIDGETS, function ($widgets) {
            $widgets[] = [
                'id' => 'your_module_stats',
                'component' => 'your-module::widgets.stats',
            ];
            return $widgets;
        });
    }
}
```

---

## Action Hooks vs Filter Hooks

### Action Hooks

Action hooks are **fire-and-forget**. They notify listeners that something happened but don't expect a return value. Use them for:

- Logging events
- Sending notifications
- Triggering side effects
- Cleanup operations

```php
// Listening to an action hook
Hook::addAction(UserActionHook::USER_CREATED_AFTER, function ($user) {
    // Send welcome email, log event, etc.
    Log::info("New user created: {$user->email}");
});

// Firing an action hook (internal use)
Hook::doAction(UserActionHook::USER_CREATED_AFTER, $user);
```

### Filter Hooks

Filter hooks **modify and return data**. They receive a value, can modify it, and must return it. Use them for:

- Adding custom fields to forms
- Modifying validation rules
- Transforming data before save/display
- Adding custom permissions or menu items

```php
// Listening to a filter hook
Hook::addFilter(UserFilterHook::USER_STORE_VALIDATION_RULES, function ($rules) {
    // Add custom validation rules
    $rules['custom_field'] = 'required|string|max:255';
    return $rules;
});

// Applying a filter (internal use)
$rules = Hook::applyFilters(UserFilterHook::USER_STORE_VALIDATION_RULES, $rules);
```

---

## Using Hooks

### Using the Hook Facade

```php
use App\Support\Facades\Hook;
use App\Enums\Hooks\UserActionHook;
use App\Enums\Hooks\UserFilterHook;

// Add an action listener
Hook::addAction(UserActionHook::USER_CREATED_AFTER, function ($user) {
    // Your code here
}, priority: 20, accepted_args: 1);

// Add a filter listener
Hook::addFilter(UserFilterHook::USER_STORE_VALIDATION_RULES, function ($rules) {
    return $rules;
}, priority: 20, accepted_args: 1);

// Fire an action
Hook::doAction(UserActionHook::USER_CREATED_AFTER, $user);

// Apply a filter
$modifiedRules = Hook::applyFilters(UserFilterHook::USER_STORE_VALIDATION_RULES, $rules);
```

### Using Helper Functions

```php
// Add action listener.
ld_add_action(UserActionHook::USER_CREATED_AFTER, function ($user) {
    // Your code here
});

// Add filter listener.
ld_add_filter(UserFilterHook::USER_STORE_VALIDATION_RULES, function ($rules) {
    return $rules;
});

// Fire and apply
ld_do_action(UserActionHook::USER_CREATED_AFTER, $user);
$rules = ld_apply_filters(UserFilterHook::USER_STORE_VALIDATION_RULES, $rules);
```

### Using Hook Enums

All hooks are defined as PHP enums for type safety and IDE auto-completion:

```php
use App\Enums\Hooks\UserActionHook;
use App\Enums\Hooks\UserFilterHook;
use App\Enums\Hooks\PermissionFilterHook;

// Use enum cases for hook names
Hook::addFilter(PermissionFilterHook::PERMISSION_GROUPS, $callback);

// String values also work (but enums are preferred)
Hook::addFilter('filter.permission.groups', $callback);
```

---

## Available Hooks

### Permission Hooks

**Location:** `App\Enums\Hooks\PermissionActionHook` and `App\Enums\Hooks\PermissionFilterHook`

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `PERMISSION_CREATED_BEFORE` | Before permission creation | `array $data` |
| `PERMISSION_CREATED_AFTER` | After permission creation | `Permission $permission` |
| `PERMISSION_UPDATED_BEFORE` | Before permission update | `Permission $permission, array $data` |
| `PERMISSION_UPDATED_AFTER` | After permission update | `Permission $permission` |
| `PERMISSION_DELETED_BEFORE` | Before permission deletion | `Permission $permission` |
| `PERMISSION_DELETED_AFTER` | After permission deletion | `int $permissionId` |
| `PERMISSIONS_SYNC_BEFORE` | Before permissions sync/seed | `array $permissions` |
| `PERMISSIONS_SYNC_AFTER` | After permissions sync/seed | `array $permissions` |
| `PERMISSION_ASSIGNED_TO_ROLE` | When permission is assigned | `Permission $permission, Role $role` |
| `PERMISSION_REVOKED_FROM_ROLE` | When permission is revoked | `Permission $permission, Role $role` |

#### Filter Hooks

| Hook | Description | Parameters | Return |
|------|-------------|------------|--------|
| `PERMISSION_GROUPS` | Filter permission groups list | `array $groups` | `array` |
| `PERMISSIONS_BY_GROUP` | Filter permissions by group | `array $permissions, string $groupName` | `array` |
| `PERMISSION_STORE_VALIDATION_RULES` | Filter store validation | `array $rules` | `array` |
| `PERMISSION_UPDATE_VALIDATION_RULES` | Filter update validation | `array $rules` | `array` |

#### UI Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `PERMISSIONS_AFTER_BREADCRUMBS` | After permissions page breadcrumbs | - |
| `PERMISSIONS_BEFORE_TABLE` | Before permissions table | - |
| `PERMISSIONS_AFTER_TABLE` | After permissions table | - |
| `PERMISSION_SHOW_AFTER_BREADCRUMBS` | After permission show page breadcrumbs | `Permission $permission` |
| `PERMISSION_SHOW_AFTER_MAIN_CONTENT` | After permission show main content | `Permission $permission` |
| `PERMISSION_SHOW_AFTER_SIDEBAR` | After permission show sidebar | `Permission $permission` |
| `PERMISSION_SHOW_AFTER_CONTENT` | After all permission show content | `Permission $permission` |

**Example - Adding Custom Permissions:**

```php
Hook::addFilter(PermissionFilterHook::PERMISSION_GROUPS, function ($groups) {
    $groups[] = [
        'group_name' => 'crm',
        'permissions' => [
            'crm.view',
            'crm.create',
            'crm.edit',
            'crm.delete',
            'crm.export',
        ],
    ];
    return $groups;
});
```

---

### User Hooks

**Location:** `App\Enums\Hooks\UserActionHook` and `App\Enums\Hooks\UserFilterHook`

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `USER_CREATED_BEFORE` | Before user creation | `array $data` |
| `USER_CREATED_AFTER` | After user creation | `User $user` |
| `USER_UPDATED_BEFORE` | Before user update | `User $user, array $data` |
| `USER_UPDATED_AFTER` | After user update | `User $user` |
| `USER_DELETED_BEFORE` | Before user deletion | `User $user` |
| `USER_DELETED_AFTER` | After user deletion | `int $userId` |
| `USER_BULK_DELETED_BEFORE` | Before bulk deletion | `array $userIds` |
| `USER_BULK_DELETED_AFTER` | After bulk deletion | `array $userIds` |
| `USER_PROFILE_UPDATE_AFTER` | After profile update | `User $user` |

#### Filter Hooks

| Hook | Description | Parameters | Return |
|------|-------------|------------|--------|
| `USER_STORE_VALIDATION_RULES` | Filter store validation | `array $rules` | `array` |
| `USER_UPDATE_VALIDATION_RULES` | Filter update validation | `array $rules` | `array` |

#### UI Hooks

| Hook | Description |
|------|-------------|
| `USER_AFTER_BREADCRUMBS` | After users page breadcrumbs |
| `USER_AFTER_TABLE` | After users table |
| `USER_FORM_AFTER_AVATAR` | After avatar field |
| `USER_FORM_AFTER_FIRST_NAME` | After first name field |
| `USER_FORM_AFTER_LAST_NAME` | After last name field |
| `USER_FORM_AFTER_EMAIL` | After email field |
| `USER_FORM_AFTER_PASSWORD` | After password field |
| `USER_FORM_AFTER_ROLES` | After roles field |

---

### Post Hooks

**Location:** `App\Enums\Hooks\PostActionHook` and `App\Enums\Hooks\PostFilterHook`

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `POST_CREATED_BEFORE` | Before post creation | `array $data` |
| `POST_CREATED_AFTER` | After post creation | `Post $post` |
| `POST_UPDATED_BEFORE` | Before post update | `Post $post, array $data` |
| `POST_UPDATED_AFTER` | After post update | `Post $post` |
| `POST_DELETED_BEFORE` | Before post deletion | `Post $post` |
| `POST_DELETED_AFTER` | After post deletion | `int $postId` |
| `POST_PUBLISHED_BEFORE` | Before post publish | `Post $post` |
| `POST_PUBLISHED_AFTER` | After post publish | `Post $post` |
| `POST_TAXONOMIES_UPDATED` | When post taxonomies change | `Post $post, array $taxonomies` |
| `POST_META_UPDATED` | When post meta changes | `Post $post, string $key, mixed $value` |

#### Filter Hooks

| Hook | Description | Parameters | Return |
|------|-------------|------------|--------|
| `POST_STORE_VALIDATION_RULES` | Filter store validation | `array $rules` | `array` |
| `POST_UPDATE_VALIDATION_RULES` | Filter update validation | `array $rules` | `array` |
| `POST_CONTENT_FILTER` | Filter post content | `string $content, Post $post` | `string` |
| `POST_TITLE_FILTER` | Filter post title | `string $title, Post $post` | `string` |
| `POST_STATUS_OPTIONS` | Filter available statuses | `array $statuses` | `array` |

---

### Role Hooks

**Location:** `App\Enums\Hooks\RoleActionHook` and `App\Enums\Hooks\RoleFilterHook`

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `ROLE_CREATED_BEFORE` | Before role creation | `array $data` |
| `ROLE_CREATED_AFTER` | After role creation | `Role $role` |
| `ROLE_UPDATED_BEFORE` | Before role update | `Role $role, array $data` |
| `ROLE_UPDATED_AFTER` | After role update | `Role $role` |
| `ROLE_DELETED_BEFORE` | Before role deletion | `Role $role` |
| `ROLE_DELETED_AFTER` | After role deletion | `int $roleId` |
| `ROLE_BULK_DELETED_BEFORE` | Before bulk deletion | `array $roleIds` |
| `ROLE_BULK_DELETED_AFTER` | After bulk deletion | `array $roleIds` |

#### UI Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `ROLES_AFTER_BREADCRUMBS` | After roles page breadcrumbs | - |
| `ROLES_BEFORE_TABLE` | Before roles table | - |
| `ROLES_AFTER_TABLE` | After roles table | - |
| `ROLE_CREATE_BEFORE_FORM` | Before create form | - |
| `ROLE_CREATE_AFTER_FORM` | After create form | - |
| `ROLE_EDIT_BEFORE_FORM` | Before edit form | `Role $role` |
| `ROLE_EDIT_AFTER_FORM` | After edit form | `Role $role` |
| `ROLE_FORM_AFTER_NAME` | After role name field | `Role $role` |
| `ROLE_FORM_BEFORE_PERMISSION_GROUPS` | Before permission groups | `Role $role` |
| `ROLE_FORM_AFTER_PERMISSIONS` | After permissions section | `Role $role` |
| `ROLE_SHOW_AFTER_BREADCRUMBS` | After role show page breadcrumbs | `Role $role` |
| `ROLE_SHOW_AFTER_MAIN_CONTENT` | After role show main content | `Role $role` |
| `ROLE_SHOW_AFTER_SIDEBAR` | After role show sidebar | `Role $role` |
| `ROLE_SHOW_AFTER_CONTENT` | After all role show content | `Role $role` |

**Example - Adding Custom Field to Role Form:**

```php
Hook::addFilter(RoleFilterHook::ROLE_FORM_AFTER_NAME, function ($html, $role) {
    return $html . view('your-module::partials.role-custom-fields', compact('role'))->render();
}, accepted_args: 2);
```

---

### Term/Taxonomy Hooks

**Location:** `App\Enums\Hooks\TermActionHook` and `App\Enums\Hooks\TermFilterHook`

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `TERM_CREATED_BEFORE` | Before term creation | `array $data, string $taxonomy` |
| `TERM_CREATED_AFTER` | After term creation | `Term $term` |
| `TERM_UPDATED_BEFORE` | Before term update | `Term $term, array $data` |
| `TERM_UPDATED_AFTER` | After term update | `Term $term` |
| `TERM_DELETED_BEFORE` | Before term deletion | `Term $term` |
| `TERM_DELETED_AFTER` | After term deletion | `int $termId, string $taxonomy` |
| `TERM_PARENT_CHANGED` | When parent changes | `Term $term, ?int $oldParentId, ?int $newParentId` |
| `TERM_ASSIGNED_TO_POST` | When term assigned to post | `Term $term, Post $post` |
| `TERM_REMOVED_FROM_POST` | When term removed from post | `Term $term, Post $post` |
| `TAXONOMY_REGISTERED` | When custom taxonomy registered | `string $taxonomy, array $config` |
| `TERM_FEATURED_IMAGE_ADDED` | When featured image added | `Term $term, mixed $media` |
| `TERM_FEATURED_IMAGE_REMOVED` | When featured image removed | `Term $term` |

#### UI Hooks - Index Page

| Hook | Description | Parameters |
|------|-------------|------------|
| `TERM_AFTER_BREADCRUMBS` | After terms page breadcrumbs | `Taxonomy $taxonomy` |
| `TERM_BEFORE_TABLE` | Before terms table | `Taxonomy $taxonomy` |
| `TERM_AFTER_TABLE` | After terms table | `Taxonomy $taxonomy` |

#### UI Hooks - Form

| Hook | Description | Parameters |
|------|-------------|------------|
| `TERM_FORM_START` | At the start of term form | `Term $term, string $taxonomy` |
| `TERM_FORM_AFTER_NAME` | After term name field | `Term $term, string $taxonomy` |
| `TERM_FORM_AFTER_SLUG` | After term slug field | `Term $term, string $taxonomy` |
| `TERM_FORM_AFTER_DESCRIPTION` | After term description field | `Term $term, string $taxonomy` |
| `TERM_FORM_AFTER_ADDITIONAL_SETTINGS` | After additional settings | `Term $term, string $taxonomy` |
| `TERM_FORM_END` | At the end of term form | `Term $term, string $taxonomy` |

#### UI Hooks - Show Page

| Hook | Description | Parameters |
|------|-------------|------------|
| `TERM_SHOW_AFTER_BREADCRUMBS` | After term show page breadcrumbs | `Term $term` |
| `TERM_SHOW_AFTER_MAIN_CONTENT` | After term show main content | `Term $term` |
| `TERM_SHOW_AFTER_SIDEBAR` | After term show sidebar | `Term $term` |
| `TERM_SHOW_AFTER_CONTENT` | After all term show content | `Term $term` |

**Example - Adding Custom Field to Term Form:**

```php
Hook::addFilter(TermFilterHook::TERM_FORM_AFTER_NAME, function ($html, $term, $taxonomy) {
    if ($taxonomy === 'categories') {
        return $html . '<div class="mt-4"><label>Custom Field</label><input type="text" name="custom_field" /></div>';
    }
    return $html;
}, accepted_args: 3);
```

---

### Module Hooks

**Location:** `App\Enums\Hooks\ModuleActionHook` and `App\Enums\Hooks\ModuleFilterHook`

These hooks are critical for module developers to handle lifecycle events.

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `MODULE_INSTALLING_BEFORE` | Before module installation | `string $moduleName, array $moduleData` |
| `MODULE_INSTALLED_AFTER` | After module installation | `string $moduleName, string $modulePath` |
| `MODULE_INSTALL_FAILED` | When installation fails | `string $moduleName, \Throwable $exception` |
| `MODULE_ENABLING_BEFORE` | Before module enable | `string $moduleName` |
| `MODULE_ENABLED_AFTER` | After module enable | `string $moduleName` |
| `MODULE_DISABLING_BEFORE` | Before module disable | `string $moduleName` |
| `MODULE_DISABLED_AFTER` | After module disable | `string $moduleName` |
| `MODULE_UPDATING_BEFORE` | Before module update | `string $moduleName, string $currentVersion, string $newVersion` |
| `MODULE_UPDATED_AFTER` | After module update | `string $moduleName, string $previousVersion, string $currentVersion` |
| `MODULE_DELETING_BEFORE` | Before module deletion | `string $moduleName` |
| `MODULE_DELETED_AFTER` | After module deletion | `string $moduleName` |
| `MODULE_MIGRATING_BEFORE` | Before migrations run | `string $moduleName` |
| `MODULE_MIGRATED_AFTER` | After migrations run | `string $moduleName` |
| `MODULE_ASSETS_PUBLISHING_BEFORE` | Before assets publish | `string $moduleName` |
| `MODULE_ASSETS_PUBLISHED_AFTER` | After assets publish | `string $moduleName` |
| `MODULES_BULK_ACTIVATING_BEFORE` | Before bulk activation | `array $moduleNames` |
| `MODULES_BULK_ACTIVATED_AFTER` | After bulk activation | `array $results` |
| `MODULES_BULK_DEACTIVATING_BEFORE` | Before bulk deactivation | `array $moduleNames` |
| `MODULES_BULK_DEACTIVATED_AFTER` | After bulk deactivation | `array $results` |

#### UI Hooks - Index Page

| Hook | Description | Parameters |
|------|-------------|------------|
| `MODULES_AFTER_BREADCRUMBS` | After modules page breadcrumbs | - |
| `MODULES_BEFORE_LIST` | Before modules list/grid | - |
| `MODULES_AFTER_LIST` | After modules list/grid | - |
| `MODULE_CARD_ACTIONS` | Module card actions | `Module $module` |

#### UI Hooks - Show Page

| Hook | Description | Parameters |
|------|-------------|------------|
| `MODULE_SHOW_AFTER_BREADCRUMBS` | After module show page breadcrumbs | `Module $module` |
| `MODULE_SHOW_AFTER_HEADER` | After module header | `Module $module` |
| `MODULE_SHOW_AFTER_DESCRIPTION` | After module description | `Module $module` |
| `MODULE_SHOW_AFTER_MAIN_CONTENT` | After module main content | `Module $module` |
| `MODULE_SHOW_SIDEBAR_BEFORE` | Before module sidebar | `Module $module` |
| `MODULE_SHOW_SIDEBAR_AFTER` | After module sidebar | `Module $module` |
| `MODULE_SHOW_AFTER_CONTENT` | After all module show content | `Module $module` |
| `MODULE_UPLOAD_FORM_AFTER` | After module upload form | - |

**Example - Module Lifecycle Handling:**

```php
// Clean up module data on deletion
Hook::addAction(ModuleActionHook::MODULE_DELETING_BEFORE, function ($moduleName) {
    if ($moduleName === 'crm') {
        // Drop module tables
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_contact_groups');
    }
});

// Run setup after module is enabled
Hook::addAction(ModuleActionHook::MODULE_ENABLED_AFTER, function ($moduleName) {
    if ($moduleName === 'crm') {
        // Seed default data
        Artisan::call('module:seed', ['module' => 'Crm']);
    }
});
```

---

### Email Hooks

**Location:** `App\Enums\Hooks\EmailActionHook` and `App\Enums\Hooks\EmailFilterHook`

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `EMAIL_SENDING_BEFORE` | Before email sends | `string $recipient, string $subject, string $content` |
| `EMAIL_SENT_AFTER` | After email sent | `string $recipient, string $subject, string $content` |
| `EMAIL_SEND_FAILED` | When email fails | `string $recipient, string $subject, \Throwable $exception` |
| `EMAIL_TEMPLATE_CREATED_BEFORE` | Before template creation | `array $data` |
| `EMAIL_TEMPLATE_CREATED_AFTER` | After template creation | `mixed $template` |
| `BULK_EMAIL_STARTED` | Before bulk campaign | `array $recipients, string $subject` |
| `BULK_EMAIL_COMPLETED` | After bulk campaign | `array $results` |

#### Filter Hooks

| Hook | Description | Parameters | Return |
|------|-------------|------------|--------|
| `EMAIL_SUBJECT` | Filter email subject | `string $subject, array $variables` | `string` |
| `EMAIL_CONTENT` | Filter email content | `string $content, array $variables` | `string` |
| `EMAIL_VARIABLES` | Filter available variables | `array $variables` | `array` |
| `EMAIL_FROM_ADDRESS` | Filter from address | `string $from` | `string` |
| `EMAIL_FROM_NAME` | Filter from name | `string $name` | `string` |
| `EMAIL_HEADERS` | Filter email headers | `array $headers` | `array` |
| `EMAIL_ATTACHMENTS` | Filter attachments | `array $attachments` | `array` |
| `EMAIL_BUILDER_BLOCKS` | Add custom email blocks | `array $blocks` | `array` |

#### UI Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `EMAIL_TEMPLATE_SHOW_AFTER_BREADCRUMBS` | After email template show page breadcrumbs | `EmailTemplate $template` |
| `EMAIL_TEMPLATE_SHOW_AFTER_CONTENT` | After email template show content | `EmailTemplate $template` |

**Example - Adding Custom Email Variables:**

```php
Hook::addFilter(EmailFilterHook::EMAIL_VARIABLES, function ($variables) {
    $variables['current_year'] = date('Y');
    $variables['company_address'] = config('settings.company_address');
    return $variables;
});
```

---

### Notification Hooks

**Location:** `App\Enums\Hooks\NotificationActionHook` and `App\Enums\Hooks\NotificationFilterHook`

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `NOTIFICATION_CREATED_BEFORE` | Before notification creation | `array $data` |
| `NOTIFICATION_CREATED_AFTER` | After notification creation | `mixed $notification` |
| `NOTIFICATION_SENDING_BEFORE` | Before sending | `mixed $notification, mixed $notifiable` |
| `NOTIFICATION_SENT_AFTER` | After sending | `mixed $notification, mixed $notifiable` |
| `NOTIFICATION_READ` | When marked as read | `mixed $notification` |
| `NOTIFICATIONS_ALL_READ` | When all marked read | `mixed $notifiable` |

#### Filter Hooks

| Hook | Description | Parameters | Return |
|------|-------------|------------|--------|
| `NOTIFICATION_TYPES` | Filter notification types | `array $types` | `array` |
| `NOTIFICATION_CHANNELS` | Filter channels | `array $channels` | `array` |
| `NOTIFICATION_CONTENT` | Filter content | `string $content, mixed $notification` | `string` |
| `NOTIFICATION_TITLE` | Filter title | `string $title, mixed $notification` | `string` |

#### UI Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `NOTIFICATION_SHOW_AFTER_BREADCRUMBS` | After notification show page breadcrumbs | `Notification $notification` |
| `NOTIFICATION_SHOW_AFTER_CONTENT` | After notification show content | `Notification $notification` |

---

### Media Hooks

**Location:** `App\Enums\Hooks\MediaActionHook` and `App\Enums\Hooks\MediaFilterHook`

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `MEDIA_UPLOADING_BEFORE` | Before upload | `UploadedFile $file, string $collection` |
| `MEDIA_UPLOADED_AFTER` | After upload | `mixed $media` |
| `MEDIA_UPLOAD_FAILED` | When upload fails | `UploadedFile $file, \Throwable $exception` |
| `MEDIA_DELETED_BEFORE` | Before deletion | `mixed $media` |
| `MEDIA_DELETED_AFTER` | After deletion | `int $mediaId` |
| `MEDIA_CONVERSIONS_BEFORE` | Before conversions | `mixed $media` |
| `MEDIA_CONVERSIONS_AFTER` | After conversions | `mixed $media, array $conversions` |

#### Filter Hooks

| Hook | Description | Parameters | Return |
|------|-------------|------------|--------|
| `MEDIA_ALLOWED_EXTENSIONS` | Filter allowed extensions | `array $extensions` | `array` |
| `MEDIA_ALLOWED_MIME_TYPES` | Filter MIME types | `array $mimeTypes` | `array` |
| `MEDIA_MAX_FILE_SIZE` | Filter max size | `int $maxSize` | `int` |
| `MEDIA_IMAGE_CONVERSIONS` | Filter image conversions | `array $conversions` | `array` |
| `MEDIA_IMAGE_QUALITY` | Filter image quality | `int $quality` | `int` |
| `MEDIA_COLLECTIONS` | Filter collections | `array $collections` | `array` |

**Example - Adding Custom File Types:**

```php
Hook::addFilter(MediaFilterHook::MEDIA_ALLOWED_EXTENSIONS, function ($extensions) {
    $extensions[] = 'webp';
    $extensions[] = 'avif';
    return $extensions;
});
```

---

### Setting Hooks

**Location:** `App\Enums\Hooks\SettingActionHook` and `App\Enums\Hooks\SettingFilterHook`

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `SETTINGS_SAVING_BEFORE` | Before settings save | `array $settings` |
| `SETTINGS_SAVED_AFTER` | After settings save | `array $settings` |
| `SETTING_CREATED_BEFORE` | Before setting creation | `string $key, mixed $value` |
| `SETTING_CREATED_AFTER` | After setting creation | `Setting $setting` |
| `SETTING_UPDATED_BEFORE` | Before setting update | `Setting $setting, mixed $newValue` |
| `SETTING_UPDATED_AFTER` | After setting update | `Setting $setting, mixed $oldValue` |
| `SETTING_DELETED_BEFORE` | Before setting deletion | `Setting $setting` |
| `SETTING_DELETED_AFTER` | After setting deletion | `string $key` |
| `SETTINGS_CACHE_CLEARED` | When cache cleared | - |

---

### Dashboard Hooks

**Location:** `App\Enums\Hooks\DashboardFilterHook`

#### Section Visibility

| Hook | Description | Parameters | Return |
|------|-------------|------------|--------|
| `DASHBOARD_SECTIONS` | Control which dashboard sections are visible | `array $sections` | `array` |

**Available Sections:**

| Section Key | Description |
|-------------|-------------|
| `quick_actions` | Quick Actions panel at the top |
| `stat_cards` | Statistics cards (Posts, Users, Roles, Permissions, Translations) |
| `user_growth` | User Growth chart |
| `quick_draft` | Quick Draft form |
| `post_chart` | Post Activity chart |
| `recent_posts` | Recent Posts list |

**Example - Hiding Dashboard Sections:**

```php
// Hide specific sections
Hook::addFilter(DashboardFilterHook::DASHBOARD_SECTIONS, function (array $sections) {
    return array_diff($sections, ['quick_actions', 'recent_posts']);
});

// Show only specific sections
Hook::addFilter(DashboardFilterHook::DASHBOARD_SECTIONS, function (array $sections) {
    return ['stat_cards', 'user_growth'];
});

// Conditionally hide sections based on user role
Hook::addFilter(DashboardFilterHook::DASHBOARD_SECTIONS, function (array $sections) {
    if (auth()->user()->hasRole('editor')) {
        return array_diff($sections, ['user_growth', 'stat_cards']);
    }
    return $sections;
});
```

#### Layout Hooks

| Hook | Description |
|------|-------------|
| `DASHBOARD_AFTER_BREADCRUMBS` | After breadcrumbs |
| `DASHBOARD_BEFORE_CONTENT` | Before main content |
| `DASHBOARD_AFTER` | After all content |

#### Stat Cards Hooks

| Hook | Description |
|------|-------------|
| `DASHBOARD_CARDS_BEFORE_USERS` | Before users stat card |
| `DASHBOARD_CARDS_AFTER_USERS` | After users stat card |
| `DASHBOARD_CARDS_AFTER_ROLES` | After roles stat card |
| `DASHBOARD_CARDS_AFTER_PERMISSIONS` | After permissions stat card |
| `DASHBOARD_CARDS_AFTER_TRANSLATIONS` | After translations stat card |
| `DASHBOARD_CARDS_AFTER` | After all stat cards |

#### Widget Hooks

| Hook | Description |
|------|-------------|
| `DASHBOARD_WIDGETS` | Filter dashboard widgets |
| `DASHBOARD_WIDGET_ORDER` | Filter widget order |
| `DASHBOARD_WIDGETS_BEFORE` | Before widgets section |
| `DASHBOARD_WIDGETS_AFTER` | After widgets section |

#### Chart Hooks

| Hook | Description |
|------|-------------|
| `DASHBOARD_CHARTS` | Filter available charts |
| `DASHBOARD_CHART_USER_GROWTH` | Filter user growth data |
| `DASHBOARD_CHART_POST_GROWTH` | Filter post growth data |

#### Other Dashboard Hooks

| Hook | Description |
|------|-------------|
| `DASHBOARD_STATS` | Filter statistics |
| `DASHBOARD_QUICK_ACTIONS` | Filter quick action buttons |
| `DASHBOARD_RECENT_ACTIVITY` | Filter recent activity |

**Example - Adding Dashboard Widget:**

```php
Hook::addFilter(DashboardFilterHook::DASHBOARD_WIDGETS, function ($widgets) {
    $widgets[] = [
        'id' => 'crm_overview',
        'title' => 'CRM Overview',
        'component' => 'crm::widgets.overview',
        'order' => 5,
    ];
    return $widgets;
});

Hook::addFilter(DashboardFilterHook::DASHBOARD_STATS, function ($stats) {
    $stats[] = [
        'label' => 'Total Contacts',
        'value' => \Modules\Crm\Models\Contact::count(),
        'icon' => 'lucide:users',
        'color' => 'blue',
    ];
    return $stats;
});
```

**Example - Modifying Quick Actions:**

```php
// Add a custom quick action
Hook::addFilter(DashboardFilterHook::DASHBOARD_QUICK_ACTIONS, function (array $actions) {
    $actions[] = [
        'permission' => 'crm.create',
        'route' => route('crm.contacts.create'),
        'icon' => 'heroicons:user-plus',
        'label' => __('New Contact'),
        'color' => '#3B82F6',
    ];
    return $actions;
});

// Remove a quick action
Hook::addFilter(DashboardFilterHook::DASHBOARD_QUICK_ACTIONS, function (array $actions) {
    return array_filter($actions, fn($action) => $action['permission'] !== 'user.create');
});
```

---

### Admin UI Hooks

**Location:** `App\Enums\Hooks\AdminFilterHook`

| Hook | Description |
|------|-------------|
| `ADMIN_HEAD` | In admin `<head>` section |
| `ADMIN_HEAD_MIDDLE` | Middle of head section |
| `ADMIN_FOOTER_BEFORE` | Before footer |
| `ADMIN_FOOTER_AFTER` | After footer |
| `HEADER_RIGHT_MENU_BEFORE` | Before header right menu |
| `HEADER_RIGHT_MENU_AFTER` | After header right menu |
| `USER_DROPDOWN_BEFORE` | Before user dropdown |
| `USER_DROPDOWN_AFTER_USER_INFO` | After user info in dropdown |
| `USER_DROPDOWN_AFTER_PROFILE_LINKS` | After profile links |
| `SIDEBAR_MENU_GROUP_BEFORE` | Before menu group |
| `SIDEBAR_MENU_GROUP_AFTER` | After menu group |
| `SIDEBAR_MENU_BEFORE` | Before menu item |
| `SIDEBAR_MENU_AFTER` | After menu item |
| `SIDEBAR_MENU_ITEM_AFTER` | After specific menu item |
| `ADMIN_MENU_GROUPS_BEFORE_SORTING` | Filter menu groups |

**Example - Adding Admin Menu Item:**

```php
Hook::addFilter(AdminFilterHook::ADMIN_MENU_GROUPS_BEFORE_SORTING, function ($groups) {
    $groups[] = [
        'name' => 'crm',
        'label' => 'CRM',
        'icon' => 'lucide:users',
        'order' => 50,
        'items' => [
            [
                'label' => 'Contacts',
                'route' => 'crm.contacts.index',
                'permission' => 'crm.view',
            ],
            [
                'label' => 'Groups',
                'route' => 'crm.groups.index',
                'permission' => 'crm.view',
            ],
        ],
    ];
    return $groups;
});
```

---

### Authentication Hooks

**Location:** `App\Enums\Hooks\AuthActionHook` and `App\Enums\Hooks\AuthFilterHook`

#### Action Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `BEFORE_LOGIN_ATTEMPT` | Before login attempt | `array $credentials` |
| `AFTER_LOGIN_SUCCESS` | After successful login | `User $user` |
| `AFTER_LOGIN_FAILED` | After failed login | `array $credentials` |
| `BEFORE_REGISTRATION` | Before user registration | `array $data` |
| `AFTER_REGISTRATION_SUCCESS` | After successful registration | `User $user` |
| `BEFORE_PASSWORD_RESET_REQUEST` | Before password reset request | `string $email` |
| `AFTER_PASSWORD_RESET_SUCCESS` | After password reset | `User $user` |
| `AFTER_EMAIL_VERIFIED` | After email verification | `User $user` |
| `BEFORE_LOGOUT` | Before logout | `User $user` |
| `AFTER_LOGOUT` | After logout | - |

#### Filter Hooks

| Hook | Description | Parameters | Return |
|------|-------------|------------|--------|
| `LOGIN_VALIDATION_RULES` | Filter login validation | `array $rules` | `array` |
| `LOGIN_CREDENTIALS` | Filter login credentials | `array $credentials` | `array` |
| `LOGIN_REDIRECT_PATH` | Filter redirect after login | `string $path` | `string` |
| `REGISTER_VALIDATION_RULES` | Filter register validation | `array $rules` | `array` |
| `REGISTER_USER_DATA` | Filter registration data | `array $data` | `array` |
| `REGISTER_DEFAULT_ROLE` | Filter default role | `string $role` | `string` |
| `LOGOUT_REDIRECT_PATH` | Filter logout redirect | `string $path` | `string` |

#### UI Hooks

| Hook | Description |
|------|-------------|
| `LOGIN_FORM_BEFORE` | Before login form |
| `LOGIN_FORM_AFTER` | After login form |
| `LOGIN_FORM_FIELDS_BEFORE_EMAIL` | Before email field |
| `LOGIN_FORM_FIELDS_AFTER_EMAIL` | After email field |
| `LOGIN_FORM_FIELDS_BEFORE_PASSWORD` | Before password field |
| `LOGIN_FORM_FIELDS_AFTER_PASSWORD` | After password field |
| `LOGIN_FORM_FIELDS_BEFORE_SUBMIT` | Before submit button |
| `LOGIN_FORM_FIELDS_AFTER_SUBMIT` | After submit button |
| `REGISTER_FORM_BEFORE` | Before register form |
| `REGISTER_FORM_AFTER` | After register form |

---

### Action Log Hooks

**Location:** `App\Enums\Hooks\ActionLogFilterHook`

| Hook | Description | Parameters | Return |
|------|-------------|------------|--------|
| `ACTION_LOG_SHOULD_LOG` | Whether to log action | `bool $shouldLog, string $action, mixed $model` | `bool` |
| `ACTION_LOG_DATA` | Filter log data | `array $data` | `array` |
| `ACTION_LOG_EXCLUDED_FIELDS` | Filter excluded fields | `array $fields` | `array` |
| `ACTION_LOG_MASKED_FIELDS` | Filter masked fields | `array $fields` | `array` |
| `ACTION_LOG_EXCLUDED_MODELS` | Filter excluded models | `array $models` | `array` |
| `ACTION_LOG_RETENTION_DAYS` | Filter retention period | `int $days` | `int` |
| `ACTION_LOG_TYPES` | Filter log types | `array $types` | `array` |

---

### Datatable Hooks

**Location:** `App\Enums\Hooks\DatatableHook`

| Hook | Description |
|------|-------------|
| `BEFORE_SEARCHBOX` | Before search box |
| `AFTER_SEARCHBOX` | After search box |
| `BEFORE_DELETE_SELECTED` | Before delete button |
| `AFTER_DELETE_SELECTED` | After delete button |
| `MOUNTED` | When datatable mounts |
| `ON_DELETE_BEFORE` | Before row delete |
| `ON_DELETE_AFTER` | After row delete |
| `ON_BULK_DELETE_BEFORE` | Before bulk delete |
| `ON_BULK_DELETE_AFTER` | After bulk delete |

---

### Common Hooks

**Location:** `App\Enums\Hooks\CommonFilterHook`

| Hook | Description |
|------|-------------|
| `LANGUAGES` | Filter available languages |
| `AVAILABLE_KEYS` | Filter available environment keys |
| `EXCLUDED_SETTING_KEYS` | Filter excluded settings |
| `ADVANCED_FIELDS_TYPES` | Filter advanced field types |
| `RECAPTCHA_IS_ENABLED_FOR_PAGE` | Check reCAPTCHA for page |
| `RECAPTCHA_AVAILABLE_PAGES` | Filter reCAPTCHA pages |
| `MEDIA_AFTER_BREADCRUMBS` | After media breadcrumbs |
| `TRANSLATION_AFTER_BREADCRUMBS` | After translation breadcrumbs |

---

## Creating Custom Hooks in Your Module

You can create custom hooks for your module that other modules can hook into:

### 1. Create Hook Enums

Create your hook enums in your module's `app/Enums/Hooks` directory:

```php
<?php

namespace Modules\YourModule\Enums\Hooks;

enum YourModuleActionHook: string
{
    case ITEM_CREATED_BEFORE = 'action.your_module.item.created_before';
    case ITEM_CREATED_AFTER = 'action.your_module.item.created_after';
    case ITEM_UPDATED_BEFORE = 'action.your_module.item.updated_before';
    case ITEM_UPDATED_AFTER = 'action.your_module.item.updated_after';
}
```

```php
<?php

namespace Modules\YourModule\Enums\Hooks;

enum YourModuleFilterHook: string
{
    case ITEM_STORE_VALIDATION_RULES = 'filter.your_module.item.store.validation.rules';
    case ITEM_DATA_BEFORE_SAVE = 'filter.your_module.item.data_before_save';
    case ITEMS_LIST = 'filter.your_module.items.list';
}
```

### 2. Fire Hooks in Your Code

```php
use Modules\YourModule\Enums\Hooks\YourModuleActionHook;
use Modules\YourModule\Enums\Hooks\YourModuleFilterHook;
use App\Support\Facades\Hook;

class YourService
{
    public function createItem(array $data): Item
    {
        // Fire action before
        Hook::doAction(YourModuleActionHook::ITEM_CREATED_BEFORE, $data);

        // Apply filter to data
        $data = Hook::applyFilters(YourModuleFilterHook::ITEM_DATA_BEFORE_SAVE, $data);

        $item = Item::create($data);

        // Fire action after
        Hook::doAction(YourModuleActionHook::ITEM_CREATED_AFTER, $item);

        return $item;
    }
}
```

---

## Best Practices

### 1. Use Enum Cases for Type Safety

```php
// Good - type-safe, IDE auto-completion
Hook::addFilter(PermissionFilterHook::PERMISSION_GROUPS, $callback);

// Avoid - prone to typos
Hook::addFilter('filter.permission.groups', $callback);
```

### 2. Always Return Values in Filters

```php
// Good
Hook::addFilter(SomeFilterHook::SOME_FILTER, function ($value) {
    $value['new_item'] = 'something';
    return $value; // Always return!
});

// Bad - will break the chain
Hook::addFilter(SomeFilterHook::SOME_FILTER, function ($value) {
    $value['new_item'] = 'something';
    // Missing return!
});
```

### 3. Use Priority for Ordering

```php
// Run early (lower number = earlier)
Hook::addFilter(SomeFilterHook::SOME_FILTER, $callback, priority: 10);

// Run late (higher number = later)
Hook::addFilter(SomeFilterHook::SOME_FILTER, $callback, priority: 100);

// Default priority is 20
```

### 4. Document Your Hooks

Always document your custom hooks with PHPDoc:

```php
/**
 * Filter the item data before saving.
 *
 * @param array $data The item data
 * @return array Modified item data
 *
 * @example
 * Hook::addFilter(YourModuleFilterHook::ITEM_DATA_BEFORE_SAVE, function ($data) {
 *     $data['processed_at'] = now();
 *     return $data;
 * });
 */
case ITEM_DATA_BEFORE_SAVE = 'filter.your_module.item.data_before_save';
```

### 5. Handle Multiple Arguments

```php
// When a hook passes multiple arguments
Hook::addAction(SomeActionHook::SOME_ACTION, function ($arg1, $arg2, $arg3) {
    // Handle all arguments
}, priority: 20, accepted_args: 3);
```

### 6. Clean Up in Module Deactivation

```php
Hook::addAction(ModuleActionHook::MODULE_DISABLING_BEFORE, function ($moduleName) {
    if ($moduleName === 'your-module') {
        // Clean up any resources
        Cache::tags(['your_module'])->flush();
    }
});
```

---

## Examples

### Complete Module Integration Example

```php
<?php

namespace Modules\CRM\Providers;

use App\Enums\Hooks\PermissionFilterHook;
use App\Enums\Hooks\DashboardFilterHook;
use App\Enums\Hooks\AdminFilterHook;
use App\Enums\Hooks\ModuleActionHook;
use App\Support\Facades\Hook;
use Illuminate\Support\ServiceProvider;
use Modules\CRM\Models\Contact;

class CRMServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerHooks();
    }

    protected function registerHooks(): void
    {
        // Register permissions
        Hook::addFilter(PermissionFilterHook::PERMISSION_GROUPS, function ($groups) {
            $groups[] = [
                'group_name' => 'crm',
                'permissions' => [
                    'crm.view',
                    'crm.create',
                    'crm.edit',
                    'crm.delete',
                    'crm.export',
                ],
            ];
            return $groups;
        });

        // Add dashboard stats
        Hook::addFilter(DashboardFilterHook::DASHBOARD_STATS, function ($stats) {
            $stats[] = [
                'label' => __('Total Contacts'),
                'value' => Contact::count(),
                'icon' => 'lucide:contact',
                'color' => 'blue',
            ];
            return $stats;
        });

        // Add admin menu
        Hook::addFilter(AdminFilterHook::ADMIN_MENU_GROUPS_BEFORE_SORTING, function ($groups) {
            $groups[] = [
                'name' => 'crm',
                'label' => __('CRM'),
                'icon' => 'lucide:briefcase',
                'order' => 50,
                'permission' => 'crm.view',
                'items' => [
                    [
                        'label' => __('Contacts'),
                        'route' => 'crm.contacts.index',
                        'permission' => 'crm.view',
                    ],
                    [
                        'label' => __('Groups'),
                        'route' => 'crm.groups.index',
                        'permission' => 'crm.view',
                    ],
                ],
            ];
            return $groups;
        });

        // Handle module cleanup on deletion
        Hook::addAction(ModuleActionHook::MODULE_DELETING_BEFORE, function ($moduleName) {
            if ($moduleName === 'crm') {
                // Clean up module data
                \Schema::dropIfExists('crm_contacts');
                \Schema::dropIfExists('crm_contact_groups');
            }
        });
    }
}
```

### Adding Custom Form Fields

```php
// In your view
{!! Hook::applyFilters(\App\Enums\Hooks\UserFilterHook::USER_FORM_AFTER_EMAIL, '') !!}
```

```php
// In your service provider
Hook::addFilter(UserFilterHook::USER_FORM_AFTER_EMAIL, function ($html) {
    return $html . view('your-module::partials.custom-field')->render();
});
```

---

## Hook Reference Quick Card

| Type | Method | Description |
|------|--------|-------------|
| Action | `Hook::addAction($hook, $callback, $priority, $args)` | Listen to an action |
| Action | `Hook::doAction($hook, ...$args)` | Fire an action |
| Action | `Hook::removeAction($hook, $callback, $priority)` | Remove listener |
| Filter | `Hook::addFilter($hook, $callback, $priority, $args)` | Listen to a filter |
| Filter | `Hook::applyFilters($hook, $value, ...$args)` | Apply a filter |
| Filter | `Hook::removeFilter($hook, $callback, $priority)` | Remove listener |

| Helper | Description |
|--------|-------------|
| `ld_add_action()` | function example |
| `ld_do_action()` | function example|
| `ld_add_filter()` | function example |
| `ld_apply_filters()` | function example |

---

## Need Help?

- Check the [hook enum files](/app/Enums/Hooks) for all available hooks
- Review the [CRM module](/modules/Crm) for real-world examples
- Check the service files for hook usage patterns
