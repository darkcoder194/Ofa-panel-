<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Startup & Variables Management
 * Manage server startup settings and environment variables
 */
class StartupController extends Controller
{
    /**
     * Get startup settings
     */
    public function show(Request $request, $serverId)
    {
        // TODO: Get startup info from Pterodactyl
        $startup = [
            'command' => '',
            'variables' => [],
            'egg' => [],
        ];

        return response()->json($startup);
    }

    /**
     * Update startup command
     */
    public function updateCommand(Request $request, $serverId)
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'startup_update_command',
            'details' => json_encode(['command' => $request->input('command')]),
            'status' => 'pending',
        ]);

        // TODO: Update command via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Update environment variable
     */
    public function updateVariable(Request $request, $serverId)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'variable_update',
            'details' => json_encode(['key' => $request->input('key')]),
            'status' => 'pending',
        ]);

        // TODO: Update variable via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Change egg
     */
    public function changeEgg(Request $request, $serverId)
    {
        $request->validate([
            'egg_id' => 'required|integer',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'egg_change',
            'details' => json_encode(['egg_id' => $request->input('egg_id')]),
            'status' => 'pending',
        ]);

        // TODO: Change egg via Pterodactyl API
        return response()->json(['success' => true]);
    }
}
