<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin\Addons;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Reverse Proxy Manager
 * Nginx automation for reverse proxies
 */
class ReverseProxyController extends Controller
{
    /**
     * List proxies
     */
    public function list(Request $request)
    {
        // TODO: Get proxies from database
        $proxies = [];

        return response()->json(['proxies' => $proxies]);
    }

    /**
     * Create reverse proxy
     */
    public function store(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string',
            'target_url' => 'required|url',
            'ssl' => 'nullable|boolean',
            'cache_enabled' => 'nullable|boolean',
        ]);

        OfaServerAction::create([
            'user_id' => $request->user()->id,
            'action' => 'proxy_create',
            'details' => json_encode(['subdomain' => $request->input('subdomain')]),
            'status' => 'pending',
        ]);

        // TODO: Generate Nginx config
        return response()->json(['success' => true]);
    }

    /**
     * Update proxy configuration
     */
    public function update(Request $request, $proxyId)
    {
        // TODO: Update Nginx config
        return response()->json(['success' => true]);
    }

    /**
     * Delete proxy
     */
    public function destroy(Request $request, $proxyId)
    {
        // TODO: Remove Nginx config
        return response()->json(['success' => true]);
    }
}
