# UI Development Guidelines

## Introduction

This document provides guidelines for UI development in the LaraBoard project. It aims to ensure consistency across the application by documenting the UI patterns, components, and best practices established in the codebase.

## Table of Contents

1. [Design System](#design-system)
2. [Components](#components)
3. [Layout Patterns](#layout-patterns)
4. [Form Elements](#form-elements)
5. [Typography](#typography)
6. [Colors](#colors)
7. [Icons](#icons)
8. [Responsive Design](#responsive-design)
9. [Accessibility](#accessibility)
10. [Dark Mode](#dark-mode)
11. [Best Practices](#best-practices)

## Design System

Our design system is built on Tailwind CSS with custom extensions. The core design tokens are defined in `resources/css/base.css` and component styles in `resources/css/components.css`.

### Breakpoints

Use the following breakpoints for responsive design:

-   2xsm: 375px
-   xsm: 425px
-   sm: 640px
-   md: 768px
-   lg: 1024px
-   xl: 1280px
-   2xl: 1536px
-   3xl: 2000px

Example usage:

```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3"></div>
```

## Components

### Buttons

We have several button variants defined with consistent styling:

```html
<!-- Primary Button -->
<button class="btn btn-primary">Primary Action</button>

<!-- Secondary Button -->
<button class="btn btn-secondary">Secondary Action</button>

<!-- Danger Button -->
<button class="btn btn-danger">Destructive Action</button>

<!-- Success Button -->
<button class="btn btn-success">Success Action</button>

<!-- Warning Button -->
<button class="btn btn-warning">Warning Action</button>

<!-- Info Button -->
<button class="btn btn-info">Info Action</button>

<!-- Default Button -->
<button class="btn btn-default">Default Action</button>
```

Button styling includes:

-   Rounded corners (`rounded-md`)
-   Consistent padding (`px-5 py-2.5`)
-   Focus states with rings
-   Hover effects with opacity change (`hover:opacity-90`)
-   Subtle scale effect on hover (`hover:scale-105`)
-   Loading states

For loading states, use this pattern:

```html
<button class="btn btn-primary" :disabled="isLoading">
    <span x-show="!isLoading">
        <iconify-icon icon="lucide:check" class="mr-2"></iconify-icon>Submit
    </span>
    <span x-show="isLoading" class="flex items-center">
        <iconify-icon
            icon="lucide:loader-2"
            class="animate-spin mr-2"
        ></iconify-icon
        >Loading...
    </span>
</button>
```

### Cards

Cards should have consistent styling:

```html
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <!-- Card Header -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            Card Title
        </h2>
    </div>

    <!-- Card Body -->
    <div class="p-6">Content goes here</div>

    <!-- Card Footer -->
    <div
        class="bg-gray-50 dark:bg-gray-700 px-6 py-4 rounded-b-lg border-t border-gray-200 dark:border-gray-600"
    >
        Footer content
    </div>
</div>
```

### Modals

Use the modal component for dialogs:

```html
<div
    x-show="showModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full transform transition-all duration-300 scale-100"
    >
        <!-- Modal Header -->
        <div
            class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700"
        >
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Modal Title
            </h3>
            <button
                @click="showModal = false"
                class="text-gray-400 hover:text-gray-500 focus:outline-none"
            >
                <iconify-icon
                    icon="lucide:x"
                    width="20"
                    height="20"
                ></iconify-icon>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">Modal content</div>

        <!-- Modal Footer -->
        <div
            class="flex justify-end p-6 border-t border-gray-200 dark:border-gray-700"
        >
            <button @click="showModal = false" class="btn btn-secondary mr-2">
                Cancel
            </button>
            <button class="btn btn-primary">Confirm</button>
        </div>
    </div>
</div>
```

### Drawers

For side panels, use the drawer component:

```html
<x-drawer title="Drawer Title" drawerId="my-drawer" isOpen="false">
    <!-- Drawer content -->
    <div class="p-6">Content goes here</div>

    <x-slot:footer>
        <div class="w-full flex justify-between items-center">
            <x-buttons.drawer-close />
            <x-buttons.submit-buttons id="submit-btn" />
        </div>
    </x-slot:footer>
</x-drawer>
```

### Tables

Tables should follow this pattern:

```html
<div
    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
>
    <!-- Table Header -->
    <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
            Table Title
        </h3>

        <!-- Table Actions -->
        <div class="flex items-center space-x-2">
            <!-- Search, filters, etc. -->
        </div>
    </div>

    <!-- Table -->
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700">
                <th
                    class="text-left py-3 px-4 font-medium text-gray-900 dark:text-white"
                >
                    Column 1
                </th>
                <th
                    class="text-left py-3 px-4 font-medium text-gray-900 dark:text-white"
                >
                    Column 2
                </th>
                <!-- More columns -->
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="py-4 px-4">Data 1</td>
                <td class="py-4 px-4">Data 2</td>
                <!-- More data -->
            </tr>
            <!-- More rows -->
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="my-4 px-4 sm:px-6">{{ $items->links() }}</div>
</div>
```

### Empty States

For empty states, use this pattern:

```html
<div class="px-6 py-12 text-center">
    <div class="flex flex-col items-center">
        <iconify-icon
            icon="lucide:list-todo"
            width="48"
            height="48"
            class="text-gray-400 dark:text-gray-600 mb-4"
        ></iconify-icon>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
            No items found
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4">
            Get started by creating your first item.
        </p>
        <button class="btn btn-primary">
            <iconify-icon icon="lucide:plus" class="mr-2"></iconify-icon>Create
            Now
        </button>
    </div>
</div>
```

## Form Elements

### Text Inputs

```html
<div class="space-y-1">
    <label
        for="input-id"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
    >
        Label <span class="text-red-500">*</span>
    </label>
    <input
        type="text"
        id="input-id"
        name="input_name"
        class="form-control"
        placeholder="Placeholder text"
    />
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">
        Error message goes here
    </p>
</div>
```

The `form-control` class applies consistent styling with:

-   Height (`h-11`)
-   Padding (`px-4 py-2.5`)
-   Border and rounded corners
-   Focus states
-   Dark mode support

### Textarea

```html
<div class="space-y-1">
    <label
        for="textarea-id"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
    >
        Label
    </label>
    <textarea
        id="textarea-id"
        name="textarea_name"
        rows="4"
        class="form-control !h-auto"
        placeholder="Enter text here..."
    ></textarea>
</div>
```

For textareas, use `!h-auto` to override the fixed height from `form-control` or specify a custom height with `!h-30` for example.

### Select

```html
<div class="space-y-1">
    <label
        for="select-id"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
    >
        Label
    </label>
    <select id="select-id" name="select_name" class="form-control">
        <option value="">Select an option</option>
        <option value="1">Option 1</option>
        <option value="2">Option 2</option>
    </select>
</div>
```

### Checkbox

```html
<div class="flex items-center">
    <input
        type="checkbox"
        id="checkbox-id"
        name="checkbox_name"
        value="1"
        class="form-checkbox"
    />
    <label
        for="checkbox-id"
        class="ml-2 block text-sm text-gray-900 dark:text-white"
    >
        Checkbox label
    </label>
</div>
```

### Radio Buttons

```html
<div>
    <label
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"
    >
        Options
    </label>
    <div class="space-y-2">
        <label class="flex items-center">
            <input type="radio" name="option" value="1" class="form-radio" />
            <span class="ml-2 text-sm text-gray-900 dark:text-white"
                >Option 1</span
            >
        </label>
        <label class="flex items-center">
            <input type="radio" name="option" value="2" class="form-radio" />
            <span class="ml-2 text-sm text-gray-900 dark:text-white"
                >Option 2</span
            >
        </label>
    </div>
</div>
```

### File Input

We have a simplified file input component that can be used with the `x-inputs.file-input` component:

```html
<x-inputs.file-input
    name="featured_image"
    id="featured_image"
    accept="image/*"
    label="{{ __('Featured Image') }}"
    :existingAttachment="isset($post) && $post->featured_image ? $post->featured_image : null"
    :existingAltText="isset($post) ? $post->title : ''"
    :removeCheckboxLabel="__('Remove featured image')"
    class="mt-1"
>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
        {{ __('Select an image to represent this post') }}
    </p>
</x-inputs.file-input>
```

The component handles displaying existing images and provides a checkbox to remove them. The underlying HTML uses the `form-control-file` class:

```html
<div class="mb-4 space-y-1">
    <label
        for="file-id"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
        >File Label</label
    >
    <input
        type="file"
        name="file_name"
        id="file-id"
        class="form-control-file"
    />
</div>
```

The `form-control-file` class provides styling for the file input with a button-like appearance for the file selection area.

## Typography

Use these consistent text styles:

-   Headings:

    -   `text-2xl font-bold` for page titles
    -   `text-xl font-semibold` for section titles
    -   `text-lg font-medium` for subsection titles
    -   `text-base font-medium` for smaller headings

-   Body text:

    -   `text-base` for regular text
    -   `text-sm` for smaller text
    -   `text-xs` for very small text

-   Text colors:
    -   `text-gray-900 dark:text-white` for headings
    -   `text-gray-700 dark:text-gray-300` for body text
    -   `text-gray-500 dark:text-gray-400` for secondary text
    -   `text-gray-400 dark:text-gray-500` for placeholder text

## Spacing

Use consistent spacing throughout the application:

-   Vertical spacing between sections: `space-y-6`
-   Vertical spacing between form elements: `space-y-4` or `space-y-1` for tighter grouping
-   Padding for containers: `p-6` (desktop) and `p-4` (mobile)
-   Margin between related elements: `mt-2`, `mb-4`, etc.
-   Gap between grid items: `gap-6` or `gap-4`

Example of consistent spacing in a form:

```html
<div class="space-y-6">
    <div class="space-y-1">
        <!-- Form element 1 -->
    </div>
    <div class="space-y-1">
        <!-- Form element 2 -->
    </div>
    <div class="mt-4">
        <!-- Submit buttons -->
    </div>
</div>
```

## Colors

Use our color system consistently:

-   Primary actions: `bg-primary` (blue)
-   Destructive actions: `bg-red-500`
-   Success states: `bg-green-500`
-   Warning states: `bg-yellow-500` or `bg-orange-500`
-   Info states: `bg-blue-500`

For text on colored backgrounds, ensure sufficient contrast:

-   On dark backgrounds: `text-white` or `text-gray-100`
-   On light backgrounds: `text-gray-900` or appropriate color

## Icons

We use Iconify for icons. Bootstrap Icons are deprecated and should not be used in new code.

```html
<!-- Recommended: Iconify Icons -->
<iconify-icon icon="lucide:user" width="20" height="20"></iconify-icon>
<iconify-icon icon="lucide:check-circle" class="mr-2"></iconify-icon>
<iconify-icon
    icon="lucide:plus"
    width="16"
    height="16"
    class="mr-2"
></iconify-icon>

<!-- Deprecated: Bootstrap Icons - Do Not Use -->
<i class="bi bi-check-circle mr-2"></i>
<!-- Don't use this pattern -->
```

### Icon Guidelines

-   Use appropriate size (usually 16px, 20px, or 24px)
-   Include margin when used with text (`mr-2` for right margin)
-   Use semantic icons that clearly represent their function
-   Always use Iconify icons with the `<iconify-icon>` tag
-   Prefer the "lucide" icon set for consistency

### Migrating from Bootstrap Icons to Iconify

If you need to replace Bootstrap Icons with Iconify equivalents, use this mapping:

| Bootstrap Icon       | Iconify Equivalent    |
| -------------------- | --------------------- |
| `bi bi-check`        | `lucide:check`        |
| `bi bi-check-circle` | `lucide:check-circle` |
| `bi bi-x`            | `lucide:x`            |
| `bi bi-x-circle`     | `lucide:x-circle`     |
| `bi bi-plus`         | `lucide:plus`         |
| `bi bi-plus-circle`  | `lucide:plus-circle`  |
| `bi bi-trash`        | `lucide:trash`        |
| `bi bi-pencil`       | `lucide:pencil`       |
| `bi bi-arrow-left`   | `lucide:arrow-left`   |
| `bi bi-arrow-right`  | `lucide:arrow-right`  |
| `bi bi-arrow-up`     | `lucide:arrow-up`     |
| `bi bi-arrow-down`   | `lucide:arrow-down`   |
| `bi bi-search`       | `lucide:search`       |
| `bi bi-calendar`     | `lucide:calendar`     |
| `bi bi-clock`        | `lucide:clock`        |
| `bi bi-envelope`     | `lucide:mail`         |
| `bi bi-telephone`    | `lucide:phone`        |
| `bi bi-people`       | `lucide:users`        |
| `bi bi-person`       | `lucide:user`         |
| `bi bi-gear`         | `lucide:settings`     |
| `bi bi-house`        | `lucide:home`         |
| `bi bi-bell`         | `lucide:bell`         |
| `bi bi-file`         | `lucide:file`         |
| `bi bi-folder`       | `lucide:folder`       |

For a complete list of Lucide icons, refer to the [Lucide Icons documentation](https://lucide.dev/icons/).

## Responsive Design

Follow these responsive design principles:

-   Use the grid system for layouts:

    ```html
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
    ```

-   Stack elements vertically on mobile, horizontally on larger screens:

    ```html
    <div class="flex flex-col md:flex-row gap-4"></div>
    ```

-   Hide/show elements based on screen size:

    ```html
    <div class="hidden md:block">Desktop only</div>
    <div class="md:hidden">Mobile only</div>
    ```

-   Adjust text sizes responsively:

    ```html
    <h1 class="text-xl md:text-2xl lg:text-3xl">Responsive Heading</h1>
    ```

-   Adjust padding and margins for different screen sizes:
    ```html
    <div class="p-4 md:p-6">Content with responsive padding</div>
    ```

## Accessibility

Follow these accessibility guidelines:

-   Use semantic HTML elements (`<button>`, `<a>`, `<nav>`, etc.)
-   Include proper ARIA attributes when needed
-   Ensure sufficient color contrast
-   Provide text alternatives for images
-   Make sure interactive elements are keyboard accessible
-   Test with screen readers

## Dark Mode

Our application supports dark mode. Use these patterns:

-   Text colors: `text-gray-900 dark:text-white`
-   Background colors: `bg-white dark:bg-gray-800`
-   Border colors: `border-gray-200 dark:border-gray-700`
-   Form controls: Use the `.form-control` class which includes dark mode styles

## Best Practices

1. **Consistency**: Use the established patterns and components
2. **Modularity**: Break UI into reusable components
3. **Responsiveness**: Design for all screen sizes
4. **Accessibility**: Ensure UI is accessible to all users
5. **Performance**: Optimize for performance (lazy loading, etc.)
6. **Dark Mode**: Support both light and dark modes
7. **Error Handling**: Provide clear error messages and validation
8. **Loading States**: Show loading indicators for async operations
9. **Empty States**: Design for empty or zero-data states
10. **Documentation**: Document new components and patterns

## Examples

### Page Layout Example

```html
@extends('backend.layouts.app') @section('title') {{ __('Page Title | ') .
config('app.name') }} @endsection @section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" />

    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ __('Page Title') }}
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ __('Page description') }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <a
                        href="{{ route('some.route') }}"
                        class="btn btn-secondary"
                    >
                        <iconify-icon
                            icon="lucide:arrow-left"
                            class="mr-2"
                        ></iconify-icon
                        >{{ __('Back') }}
                    </a>
                    <button class="btn btn-primary">
                        <iconify-icon
                            icon="lucide:plus"
                            class="mr-2"
                        ></iconify-icon
                        >{{ __('Create New') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="p-6">
            <!-- Your content here -->
        </div>
    </div>
</div>
@endsection
```

### Form Example

```html
<form action="{{ route('some.route') }}" method="POST" class="space-y-6">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Name Field -->
        <div class="space-y-1">
            <label
                for="name"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                {{ __('Name') }} <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                class="form-control"
                required
            />
            @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Field -->
        <div class="space-y-1">
            <label
                for="email"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                {{ __('Email') }} <span class="text-red-500">*</span>
            </label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control"
                required
            />
            @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Submit Buttons -->
    <div
        class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700"
    >
        <a href="{{ route('cancel.route') }}" class="btn btn-secondary">
            {{ __('Cancel') }}
        </a>
        <button type="submit" class="btn btn-primary">
            {{ __('Submit') }}
        </button>
    </div>
</form>
```
