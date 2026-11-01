<?php

declare(strict_types=1);

namespace App\Casts;

use App\Support\Security\RichHtmlSanitizer;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * JSON array cast for LaraBuilder `design_json` that strips active content from every block.
 *
 * Cleaning on write keeps new payloads out of the database whatever the entry point
 * (builder, REST API, MCP tools, AI actions). Cleaning on read covers rows stored before
 * this cast existed, so the React editor never receives a script it would inject with
 * innerHTML / dangerouslySetInnerHTML.
 *
 * @implements CastsAttributes<array<mixed>|null, array<mixed>|string|null>
 */
class SanitizedDesignJson implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<mixed>|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $decoded = is_array($value) ? $value : json_decode((string) $value, true);

        return is_array($decoded) ? RichHtmlSanitizer::sanitizeDesign($decoded) : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (! is_array($value)) {
            return null;
        }

        return json_encode(RichHtmlSanitizer::sanitizeDesign($value), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
