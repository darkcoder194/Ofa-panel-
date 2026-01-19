<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin\Addons;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Subdomain Manager (Cloudflare-ready)
 */
class SubdomainController extends Controller
{
    /**
     * List subdomains
     */
    public function list(Request $request, $serverId)
    {
        // TODO: Get from database or Cloudflare API
        $subdomains = [];

        return response()->json(['subdomains' => $subdomains]);
    }

    /**
     * Create subdomain
     */
    public function store(Request $request, $serverId)
    {
        $request->validate([
            'subdomain' => 'required|string|unique:ofa_subdomains',
            'target' => 'required|string',
            'cloudflare_enabled' => 'nullable|boolean',
            'dns_type' => 'required|string|in:A,CNAME,MX',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'subdomain_create',
            'details' => json_encode(['subdomain' => $request->input('subdomain')]),
            'status' => 'pending',
        ]);

        // TODO: Create DNS record via Cloudflare API
        return response()->json(['success' => true]);
    }

    /**
     * Delete subdomain
     */
    public function destroy(Request $request, $serverId, $subdomainId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'subdomain_delete',
            'details' => json_encode(['subdomain_id' => $subdomainId]),
            'status' => 'pending',
        ]);

        // TODO: Delete DNS record via Cloudflare API
        return response()->json(['success' => true]);
    }

    /**
     * Update subdomain target
     */
    public function update(Request $request, $serverId, $subdomainId)
    {
        $request->validate(['target' => 'required|string']);

        // TODO: Update DNS record
        return response()->json(['success' => true]);
    }
}
