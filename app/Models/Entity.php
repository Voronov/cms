<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    protected $fillable = [
        'type',
        'content',
        'seo',
        'status',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'content' => 'array',
        'seo' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'published' 
            && $this->published_at <= now()
            && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function getField(string $field, $default = null)
    {
        return $this->content[$field] ?? $default;
    }

    public function setField(string $field, $value): void
    {
        $content = $this->content;
        $content[$field] = $value;
        $this->content = $content;
    }

    public function getSlug(): ?string
    {
        return $this->content['slug'] ?? null;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seo['title'] ?? null;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seo['description'] ?? null;
    }

    public function getSeoImage(): ?string
    {
        return $this->seo['image'] ?? null;
    }

    public function files(): HasMany
    {
        return $this->hasMany(EntityFile::class);
    }

    public function getFilesByField(string $fieldName)
    {
        return $this->files()->where('field_name', $fieldName)->orderBy('order')->get();
    }
}
