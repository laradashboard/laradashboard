<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the API keys for AI providers. These values will be used as
    | fallbacks when no API key is configured in the admin settings.
    |
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-6-luna'),
        // Image generation model. DALL-E 3 was retired from the API on 2026-05-12.
        'image_model' => env('OPENAI_IMAGE_MODEL', 'gpt-image-2.5-flare'),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-haiku-20240307'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2'),
        // Ollama usually runs on localhost or the private network. Set to false when it is
        // hosted publicly, so the base URL can't point at internal services.
        'allow_private_network' => (bool) env('OLLAMA_ALLOW_PRIVATE_NETWORK', true),
    ],

];
