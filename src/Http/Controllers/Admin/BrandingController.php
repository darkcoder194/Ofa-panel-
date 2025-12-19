<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DarkCoder\Ofa\Models\OfaSetting;

class BrandingController extends Controller
{
    public function get()
    {
        return response()->json([
            'site_name' => OfaSetting::get('branding.site_name', 'OFA Panel'),
            'logo' => OfaSetting::get('branding.logo'),
            'wallpaper' => OfaSetting::get('branding.wallpaper'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => 'sometimes|string|max:191',
            'logo' => 'sometimes|file|image|max:5120',
            'wallpaper' => 'sometimes|file|image|max:10240',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('ofa-branding', 'public');
            OfaSetting::set('branding.logo', $path);
        }

        if ($request->hasFile('wallpaper')) {
            $path = $request->file('wallpaper')->store('ofa-branding', 'public');
            OfaSetting::set('branding.wallpaper', $path);
        }

        if (array_key_exists('site_name', $data)) {
            OfaSetting::set('branding.site_name', $data['site_name']);
        }

        return response()->json(['message' => 'Branding updated']);
    }
}
