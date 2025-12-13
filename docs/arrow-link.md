# Arrow Link Component

A simple Blade component for creating links with a right arrow icon.

## Basic Usage

```blade
<x-arrow-link text="Continue Reading" href="/article/123" />
```

## Available Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `text` | string | `''` | The link text (alternative to using slot content) |
| `color` | string | `'blue'` | Text color: `'blue'`, `'primary'`, `'green'`, `'red'`, `'gray'`, `'purple'` |
| `href` | string | `'#'` | Link destination URL |
| `underline` | boolean | `false` | Show underline on hover |

## Examples

### Different Colors
```blade
<x-arrow-link text="Blue Link" color="blue" href="#" />
<x-arrow-link text="Green Link" color="green" href="#" />
<x-arrow-link text="Red Link" color="red" href="#" />
```

### With Underline
```blade
<x-arrow-link text="Underlined Link" href="#" :underline="true" />
```

### Using Slot Content
```blade
<x-arrow-link href="/learn-more" color="primary">
    Learn More About This Feature
</x-arrow-link>
```

### External Links
```blade
<x-arrow-link 
    text="Visit External Site" 
    href="https://example.com" 
    target="_blank"
    color="blue"
/>
```