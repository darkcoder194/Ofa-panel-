<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Factories\Factory;

class ThemePaletteFactory extends Factory
{
    protected $model = ThemePalette::class;

    public function definition()
    {
        return [
            'name' => 'Factory Theme',
            'slug' => 'factory-theme-'.uniqid(),
            'colors' => ['primary' => '#'.substr(md5(rand()), 0, 6)],
            'is_default' => false,
        ];
    }
}
