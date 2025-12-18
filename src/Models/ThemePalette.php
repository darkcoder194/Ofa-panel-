<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThemePalette extends Model
{
    use HasFactory;
    protected $table = 'ofa_theme_palettes';

    protected $fillable = [
        'name',
        'slug',
        'colors',
        'is_default',
    ];

    protected $casts = [
        'colors' => 'array',
        'is_default' => 'boolean',
    ];

    protected static function newFactory()
    {
        return new \DarkCoder\Ofa\Models\ThemePaletteFactory();
    }
}
