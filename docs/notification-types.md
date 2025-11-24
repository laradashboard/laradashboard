# Notification Types Registry

This repository uses a registry pattern for notification types rather than hard-coded enums. Modules can register additional notification types without touching core code.

How it works:

- The registry is `App\\Services\\NotificationTypeRegistry`.
- Core types are registered automatically by `App\\Models\\NotificationType`.
- Existing `Hook::applyFilters('notification_type_values', ...)` filters are still supported by the registry.
- You can pass `label` and `icon` metadata when registering a type; `label` may be a closure for translations.

Examples:

In a module's service provider boot method:

```php
use App\\Services\\NotificationTypeRegistry;

public function boot()
{
    NotificationTypeRegistry::register('crm_follow_up', ['label' => fn () => __('CRM Follow Up'), 'icon' => 'lucide:repeat']);
}
```

Using in validation:

```php
'notification_type' => ['required', 'string', Rule::in(NotificationTypeRegistry::all())]
```

APIs / Helpers:

- `NotificationTypeRegistry::register(string $type, array $meta = [])`
- `NotificationTypeRegistry::registerMany(array $types)` where each element may be string or an array ['type' => 'x', 'meta' => []]
- `NotificationTypeRegistry::all()` returns array of type values (applies `Hook::applyFilters`) 
- `NotificationTypeRegistry::getLabel(string $type)` returns label if registered or `null` 
- `NotificationTypeRegistry::getIcon(string $type)` returns icon if registered or `null`
