<?php

namespace DarkCoder\Ofa\Services;

use DarkCoder\Ofa\Models\ThemePalette;

class ThemeManager
{
    public function defaultPalette(): ?ThemePalette
    {
        // If an admin has a preview palette in session, return that first
        if (session()->has('ofa_preview_palette')) {
            $id = session('ofa_preview_palette');
            $preview = ThemePalette::find($id);
            if ($preview) {
                return $preview;
            }
        }

        return ThemePalette::where('is_default', true)->first();
    }

    public function applyPaletteToCssVars(ThemePalette $palette): string
    {
        $vars = [];
        foreach ($palette->colors as $key => $value) {
            $vars[] = "--ofa-{$key}: {$value};";
        }

        return implode(' ', $vars);
    }
}
