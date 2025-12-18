<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DarkCoder\Ofa\Models\ThemePalette;

class OfaThemeSeeder extends Seeder
{
    public function run(): void
    {
        ThemePalette::firstOrCreate([
            'slug' => 'dark-coder-default',
        ], [
            'name' => 'Dark Coder Default',
            'colors' => [
                'primary' => '#0f172a',
                'accent' => '#7c3aed',
                'background' => '#0b1220',
                'text' => '#e6eef8',
            ],
            'is_default' => true,
        ]);

        ThemePalette::firstOrCreate([
            'slug' => 'light',
        ], [
            'name' => 'Light',
            'colors' => [
                'primary' => '#1f2937',
                'accent' => '#4f46e5',
                'background' => '#ffffff',
                'text' => '#111827',
            ],
            'is_default' => false,
        ]);
    }
}
