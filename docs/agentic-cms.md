# Agentic CMS - AI Command System

LaraDashboard's Agentic CMS transforms the dashboard into an AI-powered command center. Instead of navigating through menus, users can simply describe what they want to accomplish in natural language.

## Overview

The Agentic CMS follows a **Command-Driven AI Agent** architecture:

1. **User Command** - Natural language input from the user
2. **Intent Resolution** - AI parses the command into structured intent
3. **Action Matching** - System matches command to available actions
4. **Execution** - Action executes with real-time progress streaming
5. **Report** - Results shown with action buttons

## Quick Start

### Using the AI Agent

1. Click the **sparkles icon** in the header navbar
2. Type your command (e.g., "Write a post about healthy living with images")
3. Press `Cmd/Ctrl + Enter` or click the send button
4. Watch real-time progress as the AI works
5. View the results and follow action links

### Example Commands

```
Write a blog post about Laravel best practices
Create a post about healthy lifestyle with 2 images
Generate SEO meta for post ID 5
Write a casual article about travel tips with illustrations
Draft an about us page
```

## Architecture

### Directory Structure

```
app/Ai/
├── Contracts/                    # Interfaces
│   ├── AiActionInterface.php     # Action contract
│   ├── AiCapabilityInterface.php # Capability contract
│   └── AiContextProviderInterface.php
├── Data/                         # Data Transfer Objects
│   ├── AiIntent.php
│   ├── AiPlan.php
│   ├── AiStep.php
│   └── AiResult.php
├── Registry/                     # Component Registration
│   ├── ActionRegistry.php        # Action discovery & resolution
│   ├── CapabilityRegistry.php
│   └── ContextRegistry.php
├── Engine/                       # Core Processing
│   └── AiCommandProcessor.php    # Main command processor
├── Actions/                      # Built-in Actions
│   ├── CreatePostAction.php      # Post creation with images
│   └── GenerateSeoMetaAction.php
├── Capabilities/
│   └── PostAiCapability.php
├── Context/
│   ├── SystemContextProvider.php
│   └── PostContextProvider.php
└── Providers/
    └── AiServiceProvider.php     # Registers AI components
```

### Data Flow

```
User Command: "Write a post about AI with images"
                    ↓
           AiCommandProcessor
                    ↓
    ┌───────────────┴───────────────┐
    │     matchCommandToAction()    │
    │  - Pattern matching           │
    │  - AI-assisted parsing        │
    │  - Extract: topic, tone,      │
    │    length, include_images     │
    └───────────────┬───────────────┘
                    ↓
           Action Resolution
                    ↓
    ┌───────────────┴───────────────┐
    │     CreatePostAction          │
    │  handleWithProgress()         │
    │  - Stream progress events     │
    │  - Generate content           │
    │  - Create post                │
    │  - Generate images (optional) │
    └───────────────┬───────────────┘
                    ↓
              AiResult DTO
                    ↓
           UI Response + Actions
```

## Streaming Progress (SSE)

The AI system supports real-time progress streaming using Server-Sent Events (SSE).

### How Streaming Works

```
┌─────────────────────────────────────────┐
│  Frontend (EventSource)                 │
│                                         │
│  POST /admin/ai/command/process-stream  │
│       ↓                                 │
│  Receive SSE events:                    │
│  - progress: Current step updates       │
│  - complete: Final result               │
│  - error: Error details                 │
└─────────────────────────────────────────┘
```

### SSE Event Format

```
event: progress
data: {"step":"Generating content...","status":"in_progress","data":{"phase":"content"}}

event: progress
data: {"step":"Content generated","status":"completed","data":{"phase":"content"}}

event: progress
data: {"step":"Creating post...","status":"in_progress","data":{"phase":"post"}}

event: progress
data: {"step":"Post created","status":"completed","data":{"phase":"post","post_id":57}}

event: progress
data: {"step":"Generating image 1 of 2...","status":"in_progress","data":{"phase":"images","current":1,"total":2}}

event: complete
data: {"success":true,"message":"Post created successfully.","data":{...}}
```

### Implementing Streaming in Actions

```php
<?php

namespace App\Ai\Actions;

use App\Ai\Contracts\AiActionInterface;
use App\Ai\Data\AiResult;

class MyAction implements AiActionInterface
{
    // ... other methods

    /**
     * Handle without streaming (backwards compatible).
     */
    public function handle(array $payload): AiResult
    {
        return $this->handleWithProgress($payload, null);
    }

    /**
     * Handle with progress callback for streaming updates.
     *
     * @param callable|null $onProgress fn(string $step, string $status, ?array $data)
     */
    public function handleWithProgress(array $payload, ?callable $onProgress = null): AiResult
    {
        // Helper to report progress
        $progress = function (string $step, string $status = 'in_progress', ?array $data = null) use ($onProgress) {
            if ($onProgress) {
                $onProgress($step, $status, $data);
            }
        };

        // Step 1
        $progress(__('Starting task...'), 'in_progress', ['phase' => 'init']);

        // Do work...

        $progress(__('Task started'), 'completed', ['phase' => 'init']);

        // Step 2
        $progress(__('Processing data...'), 'in_progress', ['phase' => 'process']);

        // Do more work...

        $progress(__('Data processed'), 'completed', ['phase' => 'process']);

        // Final step
        $progress(__('Completed!'), 'completed', ['phase' => 'done']);

        return AiResult::success(
            message: __('Task completed successfully.'),
            data: ['result' => $result],
            actions: [__('View Result') => route('result.show', $id)],
            completedSteps: [__('Step 1 done'), __('Step 2 done')]
        );
    }
}
```

## Image Generation

The AI system supports automatic image generation using OpenAI DALL-E 3.

### Enabling Image Generation

Images are generated when the user's command contains image-related keywords:
- `image`, `images`, `picture`, `pictures`
- `photo`, `photos`, `illustration`, `illustrations`
- `visual`, `visuals`, `graphic`, `graphics`

### How It Works

```php
// In CreatePostAction payload schema
'include_images' => [
    'type' => 'boolean',
    'description' => 'Whether to generate and include AI images',
    'default' => false,
],
'image_count' => [
    'type' => 'integer',
    'description' => 'Number of images to generate (1-3)',
    'default' => 1,
    'minimum' => 1,
    'maximum' => 3,
],
```

### Image Generation Flow

```
1. User: "Write about healthy eating with 2 images"
           ↓
2. Command Processor detects "images" keyword
           ↓
3. Sets payload: { include_images: true, image_count: 2 }
           ↓
4. CreatePostAction:
   a. Generate text content (fast)
   b. Create post immediately (ensures no data loss)
   c. Generate images via DALL-E 3 (30-60s each)
   d. Download & store images locally
   e. Update post with image blocks
           ↓
5. Result: Post with embedded images
```

### Using AiContentGeneratorService for Images

```php
use App\Services\AiContentGeneratorService;

$aiService = app(AiContentGeneratorService::class);

// Check if image generation is available
if ($aiService->canGenerateImages()) {
    // Generate an image
    $result = $aiService->generateImage(
        prompt: 'A professional header image about healthy eating',
        size: '1792x1024' // or '1024x1024', '1024x1792'
    );

    if ($result) {
        // DALL-E URLs expire! Download and store locally
        $localUrl = $aiService->downloadAndStoreImage(
            imageUrl: $result['url'],
            storagePath: 'posts/images' // relative to storage/app/public
        );

        // $localUrl = 'http://localhost:8000/storage/posts/images/ai_xxx.png'
    }
}
```

## Extending the AI System

### Creating a New AI Action

Actions are the building blocks of the AI system. They define what the AI can do.

```php
<?php

declare(strict_types=1);

namespace Modules\Shop\Ai\Actions;

use App\Ai\Contracts\AiActionInterface;
use App\Ai\Data\AiResult;
use Modules\Shop\Models\Product;

class CreateProductAction implements AiActionInterface
{
    public function __construct(
        private ProductService $productService
    ) {
    }

    public static function name(): string
    {
        return 'shop.create_product';
    }

    public static function description(): string
    {
        return 'Create a new product in the shop with name, price, and description';
    }

    public static function payloadSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['name', 'price'],
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Product name',
                ],
                'price' => [
                    'type' => 'number',
                    'description' => 'Product price in dollars',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Product description',
                ],
                'generate_image' => [
                    'type' => 'boolean',
                    'description' => 'Generate product image with AI',
                    'default' => false,
                ],
            ],
        ];
    }

    public static function permission(): ?string
    {
        return 'products.create';
    }

    public function handle(array $payload): AiResult
    {
        return $this->handleWithProgress($payload, null);
    }

    public function handleWithProgress(array $payload, ?callable $onProgress = null): AiResult
    {
        $progress = fn($step, $status = 'in_progress', $data = null) =>
            $onProgress ? $onProgress($step, $status, $data) : null;

        try {
            $progress(__('Creating product...'), 'in_progress');

            $product = Product::create([
                'name' => $payload['name'],
                'price' => $payload['price'],
                'description' => $payload['description'] ?? '',
            ]);

            $progress(__('Product created'), 'completed');

            // Optional: Generate product image
            if ($payload['generate_image'] ?? false) {
                $progress(__('Generating product image...'), 'in_progress');
                // ... image generation logic
                $progress(__('Image generated'), 'completed');
            }

            return AiResult::success(
                message: __('Product created successfully.'),
                data: ['product_id' => $product->id],
                actions: [
                    __('Edit Product') => route('admin.products.edit', $product),
                    __('View Products') => route('admin.products.index'),
                ],
                completedSteps: [
                    __('Created product: :name', ['name' => $product->name]),
                ]
            );
        } catch (\Exception $e) {
            $progress(__('Failed'), 'failed');
            return AiResult::failed(__('Failed to create product: :error', ['error' => $e->getMessage()]));
        }
    }
}
```

### Creating a Capability Group

Capabilities group related actions together and tell the AI what features are available.

```php
<?php

declare(strict_types=1);

namespace Modules\Shop\Ai\Capabilities;

use App\Ai\Contracts\AiCapabilityInterface;
use Modules\Shop\Ai\Actions\CreateProductAction;
use Modules\Shop\Ai\Actions\UpdateInventoryAction;
use Modules\Shop\Ai\Actions\GenerateProductDescriptionAction;

class ShopAiCapability implements AiCapabilityInterface
{
    public function name(): string
    {
        return 'Shop Management';
    }

    public function description(): string
    {
        return 'Create and manage products, inventory, and orders';
    }

    public function actions(): array
    {
        return [
            CreateProductAction::class,
            UpdateInventoryAction::class,
            GenerateProductDescriptionAction::class,
        ];
    }

    public function isEnabled(): bool
    {
        // Check if shop module is active
        return class_exists(\Modules\Shop\Models\Product::class)
            && config('modules.shop.enabled', true);
    }
}
```

### Creating a Context Provider

Context providers feed live system information to the AI for better decision-making.

```php
<?php

declare(strict_types=1);

namespace Modules\Shop\Ai\Context;

use App\Ai\Contracts\AiContextProviderInterface;
use Modules\Shop\Models\Product;
use Modules\Shop\Models\Category;

class ShopContextProvider implements AiContextProviderInterface
{
    public function key(): string
    {
        return 'shop';
    }

    public function context(): array
    {
        return [
            'total_products' => Product::count(),
            'low_stock_count' => Product::where('stock', '<', 10)->count(),
            'categories' => Category::pluck('name')->toArray(),
            'currency' => config('shop.currency', 'USD'),
            'recent_products' => Product::latest()->limit(5)->pluck('name')->toArray(),
        ];
    }
}
```

### Registering in Your Module

#### Option 1: Service Provider Registration

```php
<?php

declare(strict_types=1);

namespace Modules\Shop\Providers;

use App\Ai\Registry\ActionRegistry;
use App\Ai\Registry\CapabilityRegistry;
use App\Ai\Registry\ContextRegistry;
use Illuminate\Support\ServiceProvider;
use Modules\Shop\Ai\Actions\CreateProductAction;
use Modules\Shop\Ai\Capabilities\ShopAiCapability;
use Modules\Shop\Ai\Context\ShopContextProvider;

class ShopServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerAiComponents();
    }

    protected function registerAiComponents(): void
    {
        // Option A: Register capability (includes all its actions)
        CapabilityRegistry::registerClass(ShopAiCapability::class);

        // Option B: Register individual actions directly
        ActionRegistry::register(CreateProductAction::class);

        // Register context provider
        ContextRegistry::registerClass(ShopContextProvider::class);
    }
}
```

#### Option 2: Using AiServiceProvider

Add your capability to the main AiServiceProvider:

```php
// In app/Ai/Providers/AiServiceProvider.php

protected function registerCapabilities(): void
{
    $capabilities = [
        \App\Ai\Capabilities\PostAiCapability::class,
        \Modules\Shop\Ai\Capabilities\ShopAiCapability::class, // Add here
    ];

    foreach ($capabilities as $capability) {
        CapabilityRegistry::registerClass($capability);
    }
}
```

### Adding Command Patterns to Processor

To add custom command patterns for your module, extend the `matchCommandToAction` method:

```php
// In app/Ai/Engine/AiCommandProcessor.php

private function matchCommandToAction(string $command, array $actions): ?array
{
    $commandLower = strtolower($command);

    // Existing patterns...

    // Add your module's patterns
    if (preg_match('/(?:create|add)\s+(?:a\s+)?product\s+(?:called|named)?\s*["\']?(.+?)["\']?\s+(?:for|at|priced?\s+at?)?\s*\$?(\d+(?:\.\d{2})?)/i', $command, $matches)) {
        foreach ($actions as $action) {
            if ($action['name'] === 'shop.create_product') {
                return [
                    'action' => $action['name'],
                    'payload' => [
                        'name' => trim($matches[1]),
                        'price' => (float) $matches[2],
                        'generate_image' => $this->extractImageRequest($command)['include'],
                    ],
                ];
            }
        }
    }

    // Fall back to AI parsing
    return $this->parseCommandWithAi($command, $actions);
}
```

## API Endpoints

### Check AI Status

```
GET /admin/ai/command/status
```

Response:
```json
{
    "success": true,
    "data": {
        "configured": true,
        "provider": "openai",
        "actions_count": 2,
        "actions": [
            {
                "name": "posts.create",
                "description": "Create a new post with AI-generated content..."
            },
            {
                "name": "posts.generate_seo",
                "description": "Generate SEO meta for an existing post"
            }
        ]
    }
}
```

### Process Command (JSON Response)

```
POST /admin/ai/command/process
Content-Type: application/json

{
    "command": "Write a post about healthy eating"
}
```

Response:
```json
{
    "success": true,
    "message": "Post created successfully as draft.",
    "data": {
        "status": "success",
        "completed_steps": [
            "Generated content for: healthy eating",
            "Created draft post: The Art of Healthy Eating"
        ],
        "actions": {
            "Edit Post": "/admin/posts/post/123/edit",
            "View Post": "/admin/posts/post/123"
        },
        "result_data": {
            "post_id": 123,
            "title": "The Art of Healthy Eating",
            "has_images": false
        }
    }
}
```

### Process Command with Streaming (SSE)

```
POST /admin/ai/command/process-stream
Content-Type: application/json
Accept: text/event-stream

{
    "command": "Write a post about AI technology with images"
}
```

Response (Server-Sent Events):
```
event: progress
data: {"step":"Analyzing your request...","status":"in_progress","data":null}

event: progress
data: {"step":"Understanding command...","status":"in_progress","data":null}

event: progress
data: {"step":"Command understood","status":"completed","data":null}

event: progress
data: {"step":"Generating content...","status":"in_progress","data":{"phase":"content"}}

event: progress
data: {"step":"Content generated","status":"completed","data":{"phase":"content"}}

event: progress
data: {"step":"Creating post...","status":"in_progress","data":{"phase":"post"}}

event: progress
data: {"step":"Post created","status":"completed","data":{"phase":"post","post_id":57}}

event: progress
data: {"step":"Generating images...","status":"in_progress","data":{"phase":"images","count":1}}

event: progress
data: {"step":"Generating image 1 of 1...","status":"in_progress","data":{"phase":"images","current":1,"total":1}}

event: progress
data: {"step":"Saving image 1...","status":"in_progress","data":{"phase":"images","current":1,"total":1}}

event: progress
data: {"step":"Image 1 generated","status":"completed","data":{"phase":"images","current":1,"total":1}}

event: progress
data: {"step":"Adding images to post...","status":"in_progress","data":{"phase":"update"}}

event: progress
data: {"step":"Images added to post","status":"completed","data":{"phase":"images"}}

event: progress
data: {"step":"Completed!","status":"completed","data":{"phase":"done"}}

event: complete
data: {"success":true,"message":"Post created successfully as draft.","data":{"status":"success","completed_steps":["Generated content","Created draft post","Added 1 image(s) to post"],"actions":{"Edit Post":"http://localhost:8000/admin/posts/post/57/edit","View Post":"http://localhost:8000/admin/posts/post/57"},"result_data":{"post_id":57,"title":"The Future of AI Technology","has_images":true}}}
```

### Frontend SSE Handling

```javascript
async executeCommand() {
    const response = await fetch('/admin/ai/command/process-stream', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'text/event-stream',
        },
        body: JSON.stringify({ command: this.command }),
    });

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';

    while (true) {
        const { done, value } = await reader.read();

        if (value) {
            buffer += decoder.decode(value, { stream: !done });
        }

        if (done) {
            // Process remaining buffer
            this.processSSEBuffer(buffer);
            break;
        }

        // Process complete events (separated by \n\n)
        const parts = buffer.split('\n\n');
        buffer = parts.pop() || '';

        for (const part of parts) {
            this.processSSEBuffer(part);
        }
    }
}

processSSEBuffer(text) {
    const lines = text.split('\n');
    let currentEvent = null;

    for (const line of lines) {
        if (line.startsWith('event: ')) {
            currentEvent = line.substring(7).trim();
        } else if (line.startsWith('data: ') && currentEvent) {
            const data = JSON.parse(line.substring(6));
            this.handleEvent(currentEvent, data);
            currentEvent = null;
        }
    }
}

handleEvent(event, data) {
    switch (event) {
        case 'progress':
            this.currentStep = data.step;
            if (data.status === 'completed') {
                this.completedSteps.push(data.step);
            }
            break;
        case 'complete':
            this.result = data.data;
            this.message = data.message;
            break;
        case 'error':
            this.error = data.message;
            break;
    }
}
```

## Configuration

### AI Provider Setup

Configure your AI provider in **Settings > AI Settings**:

1. **Default AI Provider**: Choose between OpenAI or Claude
2. **Max Tokens**: Set the maximum tokens for AI responses (100-8000)
3. **OpenAI API Key**: Your OpenAI API key (required for image generation)
4. **Claude API Key**: Your Anthropic Claude API key

### Environment Variables

```env
# AI Configuration (managed via Settings UI)
AI_DEFAULT_PROVIDER=openai
AI_OPENAI_API_KEY=sk-...
AI_CLAUDE_API_KEY=sk-ant-...
AI_MAX_TOKENS=4096
```

### Permissions

The AI system respects Laravel's authorization:

- Each action can specify a required permission via `permission()` method
- Users without permission cannot execute restricted actions
- Actions are filtered based on user permissions in the UI

## Best Practices

### Action Design

1. **Single Responsibility**: Each action should do one thing well
2. **Descriptive Names**: Use `module.action_name` format (e.g., `shop.create_product`)
3. **Clear Descriptions**: Write descriptions the AI can understand
4. **Schema Validation**: Define complete payload schemas with types and descriptions
5. **Permission Protection**: Always specify required permissions
6. **Streaming Support**: Implement `handleWithProgress` for long-running operations

### Error Handling

```php
public function handleWithProgress(array $payload, ?callable $onProgress = null): AiResult
{
    $progress = fn($step, $status = 'in_progress', $data = null) =>
        $onProgress ? $onProgress($step, $status, $data) : null;

    try {
        $progress(__('Starting...'), 'in_progress');

        // Your logic here

        return AiResult::success('Done', $data);
    } catch (ValidationException $e) {
        $progress(__('Validation failed'), 'failed');
        return AiResult::failed(__('Validation failed: :errors', [
            'errors' => implode(', ', $e->errors())
        ]));
    } catch (Exception $e) {
        $progress(__('Error occurred'), 'failed');
        return AiResult::failed(__('An error occurred: :message', [
            'message' => $e->getMessage()
        ]));
    }
}
```

### Partial Success

When some steps succeed but others fail:

```php
return new AiResult(
    status: 'partial',
    message: __('Post created, but image generation failed.'),
    data: ['post_id' => $post->id],
    actions: [__('Edit Post') => route('admin.posts.edit', $post)],
    completedSteps: [
        __('Generated content'),
        __('Created post'),
        __('Image generation skipped'),
    ]
);
```

### Timeout Handling for Long Operations

```php
public function handleWithProgress(array $payload, ?callable $onProgress = null): AiResult
{
    // Extend timeout for image generation
    if ($payload['include_images'] ?? false) {
        set_time_limit(300); // 5 minutes
    }

    // Create content FIRST (quick operation)
    $post = $this->createPost($payload);

    // Then try images (may timeout but post is saved)
    try {
        if ($payload['include_images']) {
            $this->generateAndAttachImages($post, $payload);
        }
    } catch (Exception $e) {
        // Post exists, just return partial success
        return new AiResult(
            status: 'partial',
            message: __('Post created, images failed.'),
            // ...
        );
    }
}
```

## Database

### AI Command Logs

All AI commands are logged for auditing:

```sql
CREATE TABLE ai_command_logs (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    command TEXT,
    intent JSON,
    plan JSON,
    result JSON,
    status ENUM('success', 'partial', 'failed'),
    execution_time_ms INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

Query logs:

```php
use App\Models\AiCommandLog;

// Get recent commands
$logs = AiCommandLog::latest()->limit(10)->get();

// Get successful commands
$successful = AiCommandLog::where('status', 'success')->get();

// Get user's commands
$userCommands = AiCommandLog::where('user_id', $userId)->get();

// Get average execution time
$avgTime = AiCommandLog::avg('execution_time_ms');
```

## Testing

### Unit Testing Actions

```php
use App\Ai\Actions\CreatePostAction;
use App\Services\AiContentGeneratorService;
use App\Services\Builder\BlockService;

test('CreatePostAction creates post successfully', function () {
    $aiService = mock(AiContentGeneratorService::class);
    $aiService->shouldReceive('generateContent')
        ->andReturn([
            'title' => 'Test Post',
            'excerpt' => 'Test excerpt',
            'content' => 'Test content paragraph.',
        ]);

    $blockService = app(BlockService::class);
    $action = new CreatePostAction($aiService, $blockService);

    $result = $action->handle([
        'topic' => 'Testing',
        'tone' => 'professional',
        'length' => 'short',
    ]);

    expect($result->isSuccess())->toBeTrue()
        ->and($result->data)->toHaveKey('post_id');
});
```

### Testing Streaming

```php
test('CreatePostAction reports progress', function () {
    $progressSteps = [];
    $onProgress = function ($step, $status, $data) use (&$progressSteps) {
        $progressSteps[] = compact('step', 'status', 'data');
    };

    $action = app(CreatePostAction::class);
    $result = $action->handleWithProgress([
        'topic' => 'Testing',
    ], $onProgress);

    expect($progressSteps)->not->toBeEmpty()
        ->and(collect($progressSteps)->pluck('step'))->toContain('Generating content...');
});
```

### Testing Registry

```php
use App\Ai\Registry\ActionRegistry;

beforeEach(function () {
    ActionRegistry::clear();
});

test('can register and resolve action', function () {
    ActionRegistry::register(CreatePostAction::class);

    $action = ActionRegistry::resolve('posts.create');

    expect($action)->toBeInstanceOf(AiActionInterface::class);
});
```

## Security Considerations

1. **Actions are Explicit**: AI can only call registered actions, preventing hallucinated features
2. **Permission Checks**: Every action validates user permissions before execution
3. **Payload Validation**: Schemas ensure only valid data reaches actions
4. **Transaction Safety**: Actions should run within database transactions
5. **Audit Logging**: All commands are logged with full context
6. **Image Safety**: Generated images are downloaded and stored locally (DALL-E URLs expire)
7. **Rate Limiting**: Consider implementing rate limits for AI operations

## Troubleshooting

### AI Not Configured

If you see "AI not configured":
1. Go to Settings > AI Settings
2. Enter your OpenAI or Claude API key
3. Select your default provider

### Timeout During Image Generation

If image generation times out:
- Images take 30-60 seconds each via DALL-E 3
- The post is created first, so content won't be lost
- Reduce image count or try again without images
- Check server's `max_execution_time` setting

### Streaming Not Working

If progress updates don't show:
1. Check browser console for errors
2. Ensure server doesn't buffer responses (nginx: `X-Accel-Buffering: no`)
3. Verify route is correct: `/admin/ai/command/process-stream`

### Action Not Found

If the AI can't find an action:
1. Ensure the capability is registered in a service provider
2. Check that `isEnabled()` returns `true`
3. Verify the action class implements `AiActionInterface`
4. Check user has required permissions

### Permission Denied

If actions fail with permission errors:
1. Check the user has the required permission
2. Verify the permission exists in the database
3. Ensure roles are properly assigned

## Roadmap

Future enhancements planned:

- [ ] Voice command support
- [ ] Scheduled AI jobs
- [ ] Multi-step workflow builder

## Support

For issues or feature requests, please open an issue on the Lara Dashboard repository.
