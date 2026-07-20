<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Site-wide settings, stored as a single row. Everything reads it through
 * {@see self::current()}, which lazily creates the row so the app never has to
 * assume the seeder ran.
 */
class SiteSetting extends Model
{
    protected $fillable = [
        'colors',
        'semantic_colors',
    ];

    protected function casts(): array
    {
        return [
            'colors' => 'array',
            'semantic_colors' => 'array',
        ];
    }

    /** The one and only settings row (created on first access). */
    public static function current(): self
    {
        return static::firstOrCreate([]);
    }
}
