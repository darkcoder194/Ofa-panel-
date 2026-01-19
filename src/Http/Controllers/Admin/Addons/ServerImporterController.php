<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin\Addons;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Server Importer
 * Import existing servers
 */
class ServerImporterController extends Controller
{
    /**
     * Get available servers to import
     */
    public function available(Request $request)
    {
        // TODO: Scan for unmanaged servers
        $servers = [];

        return response()->json(['available' => $servers]);
    }

    /**
     * Import server
     */
    public function import(Request $request)
    {
        $request->validate([
            'server_id' => 'required|integer',
            'owner_id' => 'required|integer',
        ]);

        OfaServerAction::create([
            'server_id' => $request->input('server_id'),
            'user_id' => $request->user()->id,
            'action' => 'server_import',
            'status' => 'pending',
        ]);

        // TODO: Import server to OFA system
        return response()->json(['success' => true]);
    }
}
