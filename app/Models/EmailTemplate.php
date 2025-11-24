<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\TemplateType;
use App\Concerns\QueryBuilderTrait;
use App\Services\TemplateTypeRegistry;
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
        'type',
        'preview_image',
        'description',
        'is_active',
        'is_deleteable',
        'created_by',
        'updated_by',
        'header_template_id',
        'footer_template_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_deleteable' => 'boolean',
        // Use string casting to support module-registered template types, provide accessors for label/icon/color
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

    public function headerTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'header_template_id');
    }

    public function footerTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'footer_template_id');
    }

    public function renderTemplate(array $data = []): array
    {
        $subject = $this->subject;
        $bodyHtml = $this->body_html ?? '';

        // Include header template if exists
        if ($this->header_template_id && $this->headerTemplate) {
            $headerHtml = $this->headerTemplate->body_html ?? '';

            // Replace variables in header
            foreach ($data as $key => $value) {
                $placeholder = '{' . $key . '}';
                $headerHtml = str_replace($placeholder, (string) $value, (string) $headerHtml);
            }

            $bodyHtml = $headerHtml . $bodyHtml;
        }

        // Include footer template if exists.
        if ($this->footer_template_id && $this->footerTemplate) {
            $footerHtml = $this->footerTemplate->body_html ?? '';

            // Replace variables in footer.
            foreach ($data as $key => $value) {
                $placeholder = '{' . $key . '}';
                $footerHtml = str_replace($placeholder, (string) $value, (string) $footerHtml);
            }

            $bodyHtml = $bodyHtml . $footerHtml;
        }

        // Replace variables in main content.
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $subject = str_replace($placeholder, (string) $value, (string) $subject);
            $bodyHtml = str_replace($placeholder, (string) $value, (string) $bodyHtml);
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
        ];
    }

    public function getRawEmailTemplate(): string
    {
        $html = '';

        if ($this->header_template_id && $this->headerTemplate) {
            $html .= $this->headerTemplate->body_html ?? '';
        }

        $html .= $this->body_html ?? '';

        if ($this->footer_template_id && $this->footerTemplate) {
            $html .= $this->footerTemplate->body_html ?? '';
        }

        return $html;
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
