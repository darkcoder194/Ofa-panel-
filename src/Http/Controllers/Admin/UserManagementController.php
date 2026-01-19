<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * User Management
 * Manage server subusers and permissions
 */
class UserManagementController extends Controller
{
    /**
     * List subusers
     */
    public function list(Request $request, $serverId)
    {
        // TODO: Get subusers from Pterodactyl
        $users = [];

        return response()->json(['users' => $users]);
    }

    /**
     * Add subuser
     */
    public function store(Request $request, $serverId)
    {
        $request->validate([
            'email' => 'required|email',
            'permissions' => 'required|array',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'subuser_create',
            'details' => json_encode(['email' => $request->input('email')]),
            'status' => 'pending',
        ]);

        // TODO: Create subuser via Pterodactyl API
        return response()->json(['success' => true, 'user_id' => 'uuid']);
    }

    /**
     * Update subuser permissions
     */
    public function update(Request $request, $serverId, $userId)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'subuser_update',
            'details' => json_encode(['subuser_id' => $userId]),
            'status' => 'pending',
        ]);

        // TODO: Update subuser via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Remove subuser
     */
    public function destroy(Request $request, $serverId, $userId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'subuser_delete',
            'details' => json_encode(['subuser_id' => $userId]),
            'status' => 'pending',
        ]);

        // TODO: Delete subuser via Pterodactyl API
        return response()->json(['success' => true]);
    }
}
