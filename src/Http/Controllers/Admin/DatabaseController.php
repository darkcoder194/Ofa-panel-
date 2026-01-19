<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Database Management
 * Create, delete, manage server databases
 */
class DatabaseController extends Controller
{
    /**
     * List databases for a server
     */
    public function list(Request $request, $serverId)
    {
        // TODO: Query Pterodactyl database management system
        $databases = [];

        return response()->json(['databases' => $databases]);
    }

    /**
     * Create new database
     */
    public function store(Request $request, $serverId)
    {
        $request->validate([
            'database' => 'required|string|max:64',
            'host' => 'required|string',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'database_create',
            'details' => json_encode(['database' => $request->input('database')]),
            'status' => 'pending',
        ]);

        // TODO: Create database via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Delete database
     */
    public function destroy(Request $request, $serverId, $databaseId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'database_delete',
            'details' => json_encode(['database_id' => $databaseId]),
            'status' => 'pending',
        ]);

        // TODO: Delete database via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Reset database password
     */
    public function resetPassword(Request $request, $serverId, $databaseId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'database_reset_password',
            'details' => json_encode(['database_id' => $databaseId]),
            'status' => 'pending',
        ]);

        // TODO: Reset password via Pterodactyl API
        return response()->json(['success' => true, 'password' => 'new_password']);
    }
}
