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

    /**
     * The brand palette as `name => label` options for a colour <select> (e.g. a
     * block's background). The stored value is the colour's name, which the
     * frontend resolves to the matching `--brand-<slug>` CSS variable.
     *
     * @return array<string, string>
     */
    public function brandColorOptions(): array
    {
        $options = [];

        foreach ($this->colors ?? [] as $color) {
            $name = $color['name'] ?? null;

            if (! $name) {
                continue;
            }

            $role = $color['role'] ?? null;
            $options[$name] = $role ? "{$name} (".ucfirst($role).')' : $name;
        }

        return $options;
    }
}
