<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\QueryBuilderTrait;
use App\Enums\TemplateType;
use App\Services\Builder\BlockRenderer;
use App\Services\TemplateTypeRegistry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use HasFactory;
    use QueryBuilderTrait;

    protected $fillable = [
        'uuid',
        'name',
        'subject',
        'body_html',
        'design_json',
        'type',
        'description',
        'is_active',
        'is_deleteable',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_deleteable' => 'boolean',
        'design_json' => 'array',
        'is_default' => 'boolean',
        'type' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'template_id');
    }

    /**
     * Render the template content with dynamic blocks processed
     *
     * Processes any dynamic blocks (like CRM Contact) through server-side
     * rendering via BlockRenderer.
     *
     * @param  string  $context  The rendering context ('email', 'page', 'campaign')
     * @return string The processed HTML content
     */
    public function renderContent(string $context = 'email'): string
    {
        if (empty($this->body_html)) {
            return '';
        }

        return app(BlockRenderer::class)->processContent($this->body_html, $context);
    }

    /**
     * Render the template with variable substitution and dynamic blocks
     *
     * @param  array  $data  Variables to substitute in the template
     * @return array{subject: string, body_html: string}
     */
    public function renderTemplate(array $data = []): array
    {
        $subject = $this->subject ?? '';
        $bodyHtml = $this->body_html ?? '';

        // Replace variables in content
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $subject = str_replace($placeholder, (string) $value, $subject);
            $bodyHtml = str_replace($placeholder, (string) $value, $bodyHtml);
        }

        // Process dynamic blocks
        $bodyHtml = app(BlockRenderer::class)->processContent($bodyHtml, 'email');

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        $value = $type instanceof TemplateType ? $type->value : (string) $type;

        return $query->where('type', $value);
    }

    public function getTypeLabelAttribute(): string
    {
        $value = $this->type ?? '';
        if (empty($value)) {
            return '';
        }
        $enum = TemplateType::tryFrom($value);
        if ($enum) {
            return (string) $enum->label();
        }
        $label = TemplateTypeRegistry::getLabel($value);

        return $label ?? ucfirst(str_replace('_', ' ', $value));
    }

    public function getTypeIconAttribute(): ?string
    {
        $value = $this->type ?? '';
        if (empty($value)) {
            return null;
        }
        $enum = TemplateType::tryFrom($value);
        if ($enum) {
            return (string) $enum->icon();
        }

        return TemplateTypeRegistry::getIcon($value);
    }

    public function getTypeColorAttribute(): ?string
    {
        $value = $this->type ?? '';
        if (empty($value)) {
            return null;
        }
        $enum = TemplateType::tryFrom($value);
        if ($enum) {
            return (string) $enum->color();
        }

        return TemplateTypeRegistry::getColor($value);
    }
}
