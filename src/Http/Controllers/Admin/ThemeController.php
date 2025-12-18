<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use DarkCoder\Ofa\Models\ThemePalette;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ThemeController extends Controller
{
    public function index()
    {
        return response()->json(ThemePalette::orderBy('is_default', 'desc')->get());
    }

    /**
     * Render the admin theme management page.
     */
    public function page()
    {
        return view('ofa::admin.themes');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'required|string|max:191|unique:ofa_theme_palettes,slug',
            'colors' => 'required|array',
        ]);

        $palette = ThemePalette::create($data + ['is_default' => $request->boolean('is_default')]);

        if ($palette->is_default) {
            ThemePalette::where('id', '!=', $palette->id)->update(['is_default' => false]);
        }

        return response()->json($palette, 201);
    }

    public function update(Request $request, ThemePalette $palette)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:191',
            'colors' => 'sometimes|required|array',
            'is_default' => 'sometimes|boolean',
        ]);

        $palette->update($data);

        if (array_key_exists('is_default', $data) && $palette->is_default) {
            ThemePalette::where('id', '!=', $palette->id)->update(['is_default' => false]);
        }

        return response()->json($palette);
    }

    public function destroy(ThemePalette $palette)
    {
        $palette->delete();
        return response()->json(null, 204);
    }

    public function setDefault(ThemePalette $palette)
    {
        ThemePalette::where('id', '!=', $palette->id)->update(['is_default' => false]);
        $palette->update(['is_default' => true]);
        return response()->json($palette);
    }

    public function preview(ThemePalette $palette)
    {
        session(['ofa_preview_palette' => $palette->id]);

        // Broadcast preview to other admins when broadcasting is configured
        if (config('broadcasting.default') !== 'sync') {
            event(new \DarkCoder\Ofa\Events\PalettePreviewed($palette));
        }

        return response()->json($palette);
    }

    public function export(ThemePalette $palette)
    {
        $filename = 'palette-'.$palette->slug.'.json';
        $payload = $palette->toArray();

        return response()->streamDownload(function() use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function import(Request $request)
    {
        // Accept raw JSON body or posted data
        $data = json_decode($request->getContent(), true) ?: $request->input();

        if (!is_array($data)) {
            return response()->json(['message' => 'Invalid JSON payload'], 422);
        }

        // If slug exists, update existing palette
        if (!empty($data['slug']) && $existing = ThemePalette::where('slug', $data['slug'])->first()) {
            $existing->update($data);
            return response()->json($existing, 200);
        }

        $validator = \Validator::make($data, [
            'name' => 'required|string|max:191',
            'slug' => 'required|string|max:191|unique:ofa_theme_palettes,slug',
            'colors' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $palette = ThemePalette::create($data + ['is_default' => $data['is_default'] ?? false]);

        if ($palette->is_default) {
            ThemePalette::where('id', '!=', $palette->id)->update(['is_default' => false]);
        }

        return response()->json($palette, 201);
    }

    public function clearPreview()
    {
        session()->forget('ofa_preview_palette');

        if (config('broadcasting.default') !== 'sync') {
            $palette = ThemePalette::where('is_default', true)->first();
            if ($palette) {
                event(new \DarkCoder\Ofa\Events\PalettePreviewed($palette));
            }
        }

        return response()->json(null, 204);
    }
}
