<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin\Minecraft;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * World Management
 * Create, delete, import worlds
 */
class WorldController extends Controller
{
    /**
     * List worlds
     */
    public function list(Request $request, $serverId)
    {
        // TODO: Scan world folders
        $worlds = [];

        return response()->json(['worlds' => $worlds]);
    }

    /**
     * Create new world
     */
    public function create(Request $request, $serverId)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string|in:default,flat,large_biomes,amplified',
            'gamemode' => 'required|string|in:survival,creative,adventure,spectator',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'world_create',
            'details' => json_encode(['name' => $request->input('name')]),
            'status' => 'pending',
        ]);

        // TODO: Execute world creation command
        return response()->json(['success' => true]);
    }

    /**
     * Delete world
     */
    public function delete(Request $request, $serverId)
    {
        $request->validate(['name' => 'required|string']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'world_delete',
            'details' => json_encode(['name' => $request->input('name')]),
            'status' => 'pending',
        ]);

        // TODO: Delete world folder
        return response()->json(['success' => true]);
    }

    /**
     * Set default world
     */
    public function setDefault(Request $request, $serverId)
    {
        $request->validate(['name' => 'required|string']);

        // TODO: Update level-name in server.properties
        return response()->json(['success' => true]);
    }

    /**
     * Upload world
     */
    public function upload(Request $request, $serverId)
    {
        $request->validate(['world' => 'required|file']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'world_upload',
            'status' => 'uploading',
        ]);

        // TODO: Extract and move world
        return response()->json(['success' => true]);
    }

    /**
     * Download world
     */
    public function download(Request $request, $serverId)
    {
        $request->validate(['name' => 'required|string']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'world_download',
            'details' => json_encode(['name' => $request->input('name')]),
            'status' => 'pending',
        ]);

        // TODO: ZIP and provide download
        return response()->json(['download_url' => 'https://...']);
    }
}
