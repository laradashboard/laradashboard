# Form Components Usage Guide

The PageGenerator form field components have been refactored into reusable individual components following your existing form control patterns.

## Available Components

### 1. Text Input Component
```blade
<x-inputs.text
    name="first_name"
    label="First Name"
    type="text"
    placeholder="Enter your first name"
    required="true"
    help="This field is required"
/>

<!-- Email input -->
<x-inputs.text
    name="email"
    label="Email Address"
    type="email"
    placeholder="user@example.com"
    required="true"
/>

<!-- Number input -->
<x-inputs.text
    name="age"
    label="Age"
    type="number"
    min="18"
    max="100"
    step="1"
/>
```

### 2. Password Component (with show/hide and auto-generate)
```blade
<x-inputs.password
    name="password"
    label="Password"
    placeholder="Enter your password"
    required="true"
    show-auto-generate="true"
    autocomplete="new-password"
/>
```

### 3. Textarea Component
```blade
<x-inputs.textarea
    name="description"
    label="Description"
    placeholder="Enter description..."
    rows="4"
    help="Provide a detailed description"
/>
```

### 4. Select Component
```blade
<x-inputs.select
    name="category"
    label="Category"
    placeholder="Select a category"
    :options="[
        'tech' => 'Technology',
        'news' => 'News',
        'sports' => 'Sports',
    ]"
    required="true"
/>

<!-- Multiple select -->
<x-inputs.select
    name="tags"
    label="Tags"
    :options="$tagsArray"
    multiple="true"
    help="Select multiple tags"
/>
```

### 5. Combobox Component (Advanced Select with Search)
```blade
<x-inputs.combobox
    name="user_id"
    label="Select User"
    placeholder="Choose a user"
    :options="$users"
    :selected="old('user_id')"
    searchable="true"
    required="true"
/>
```

### 6. Checkbox Component
```blade
<x-inputs.checkbox
    name="is_active"
    label="Active"
    value="1"
    :checked="old('is_active', $user->is_active ?? false)"
    help="Check to activate this user"
/>
```

### 7. Radio Component
```blade
<x-inputs.radio
    name="status"
    label="Status"
    :options="[
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ]"
    selected="draft"
    required="true"
/>
```

### 8. DateTime Picker Component
```blade
<!-- Date and Time -->
<x-inputs.datetime-picker
    name="published_at"
    label="Publish Date & Time"
    :value="$post->published_at"
    placeholder="Select date and time"
    enable-time="true"
    date-format="Y-m-d H:i"
/>

<!-- Date Only -->
<x-inputs.datetime-picker
    name="birth_date"
    label="Birth Date"
    :value="$user->birth_date"
    enable-time="false"
    date-format="Y-m-d"
    alt-format="F j, Y"
/>
```

### 9. File Input Component
```blade
<x-inputs.file-input
    name="avatar"
    label="Profile Picture"
    :existing-attachment="$user->avatar_url"
    existing-alt-text="User Avatar"
    remove-checkbox-label="Remove current avatar"
/>

<!-- Multiple files -->
<x-inputs.file-input
    name="documents[]"
    label="Upload Documents"
    multiple="true"
/>
```

### 10. Hidden Component
```blade
<x-inputs.hidden
    name="user_id"
    :value="auth()->id()"
/>
```

## Using with Existing Form Controls

All components follow your existing CSS class patterns:

- `form-control` - Standard input styling
- `form-control-textarea` - Textarea styling
- `form-control-file` - File input styling
- `form-label` - Label styling
- `form-checkbox` - Checkbox styling
- `form-radio` - Radio button styling
- `error-message` - Error message styling

## CSS Classes Added

The following classes were added to `resources/css/components.css`:

```css
.form-radio {
    @apply h-4 w-4 text-primary border-gray-300 focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600;
}

.error-message {
    @apply text-red-600 dark:text-red-400;
}
```

## Examples in Regular Forms

### Basic Contact Form
```blade
<form action="{{ route('contact.store') }}" method="POST">
    @csrf
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-inputs.text
            name="first_name"
            label="First Name"
            placeholder="John"
            required="true"
        />
        
        <x-inputs.text
            name="last_name"
            label="Last Name"
            placeholder="Doe"
            required="true"
        />
    </div>
    
    <x-inputs.text
        name="email"
        label="Email"
        type="email"
        placeholder="john@example.com"
        required="true"
    />
    
    <x-inputs.select
        name="subject"
        label="Subject"
        placeholder="Select a subject"
        :options="[
            'general' => 'General Inquiry',
            'support' => 'Technical Support',
            'billing' => 'Billing Question',
        ]"
        required="true"
    />
    
    <x-inputs.textarea
        name="message"
        label="Message"
        placeholder="Your message..."
        rows="5"
        required="true"
    />
    
    <x-inputs.checkbox
        name="newsletter"
        label="Subscribe to newsletter"
        value="1"
    />
    
    <button type="submit" class="btn-primary">
        Send Message
    </button>
</form>
```

### User Profile Form
```blade
<form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="space-y-6">
        <x-inputs.file
            name="avatar"
            label="Profile Picture"
            accept="image/*"
            help="Upload a new profile picture"
            :preview="true"
            :model="$user"
            :value="$user->avatar_url"
        />
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-inputs.text
                name="first_name"
                label="First Name"
                :value="$user->first_name"
                required="true"
            />
            
            <x-inputs.text
                name="last_name"
                label="Last Name"
                :value="$user->last_name"
                required="true"
            />
        </div>
        
        <x-inputs.textarea
            name="bio"
            label="Biography"
            :value="$user->bio"
            rows="4"
            placeholder="Tell us about yourself..."
        />
        
        <x-inputs.radio
            name="notification_preference"
            label="Notification Preference"
            :options="[
                'email' => 'Email Only',
                'sms' => 'SMS Only',
                'both' => 'Email and SMS',
                'none' => 'No Notifications',
            ]"
            :selected="$user->notification_preference"
        />
    </div>
    
    <button type="submit" class="btn-primary">
        Update Profile
    </button>
</form>
```

## Benefits

1. **Reusable**: Use these components anywhere in your application
2. **Consistent**: Follow your existing form control patterns
3. **Maintainable**: Updates to styling happen in one place
4. **Accessible**: Built-in error handling and validation display
5. **Flexible**: Support for all common input types and attributes
6. **Laravel Integration**: Works with old() helper and validation errors

## Integration with PageGenerator

The PageGenerator automatically uses these components, but you can also use them independently in any Blade template for consistent form styling across your application.