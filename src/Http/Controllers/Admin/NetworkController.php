<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Network Management
 * Allocations, ports, IP management
 */
class NetworkController extends Controller
{
    /**
     * Get server network info
     */
    public function show(Request $request, $serverId)
    {
        // TODO: Get allocations from Pterodactyl
        $network = [
            'allocations' => [],
            'ports' => [],
        ];

        return response()->json($network);
    }

    /**
     * Add allocation
     */
    public function addAllocation(Request $request, $serverId)
    {
        $request->validate([
            'allocation_id' => 'required|integer',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'allocation_add',
            'details' => json_encode(['allocation_id' => $request->input('allocation_id')]),
            'status' => 'pending',
        ]);

        // TODO: Add allocation via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Remove allocation
     */
    public function removeAllocation(Request $request, $serverId, $allocationId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'allocation_remove',
            'details' => json_encode(['allocation_id' => $allocationId]),
            'status' => 'pending',
        ]);

        // TODO: Remove allocation via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Set primary allocation
     */
    public function setPrimary(Request $request, $serverId, $allocationId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'allocation_set_primary',
            'details' => json_encode(['allocation_id' => $allocationId]),
            'status' => 'pending',
        ]);

        // TODO: Set primary allocation via Pterodactyl API
        return response()->json(['success' => true]);
    }
}
