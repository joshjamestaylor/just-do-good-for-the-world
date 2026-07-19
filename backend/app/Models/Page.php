<?php

namespace App\Models;

use App\Enums\PageStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'status',
        'published_at',
        'seo',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'published_at' => 'datetime',
            'seo' => 'array',
            'content' => 'array',
        ];
    }

    protected static function booted(): void
    {
        // Publishing without picking a date means "publish now" — otherwise the
        // page would satisfy status=Published but fail scopePublished()'s
        // published_at check and be silently unreachable via the API.
        static::saving(function (Page $page): void {
            if ($page->status === PageStatus::Published && $page->published_at === null) {
                $page->published_at = now();
            }
        });
    }

    /** Only pages that are published and whose publish date has passed. */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PageStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
