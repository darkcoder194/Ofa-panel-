<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin\Minecraft;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Minecraft Server Configuration
 * server.properties, MOTD, and basic settings
 */
class ConfigController extends Controller
{
    /**
     * Get server properties
     */
    public function getProperties(Request $request, $serverId)
    {
        // TODO: Parse server.properties file
        $properties = [];

        return response()->json($properties);
    }

    /**
     * Update server properties
     */
    public function updateProperties(Request $request, $serverId)
    {
        $properties = $request->input('properties', []);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'minecraft_config_update',
            'details' => json_encode($properties),
            'status' => 'pending',
        ]);

        // TODO: Write to server.properties
        return response()->json(['success' => true]);
    }

    /**
     * Get MOTD
     */
    public function getMotd(Request $request, $serverId)
    {
        // TODO: Get MOTD from server.properties
        return response()->json(['motd' => 'A Minecraft Server']);
    }

    /**
     * Update MOTD
     */
    public function updateMotd(Request $request, $serverId)
    {
        $request->validate(['motd' => 'required|string|max:59']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'minecraft_motd_update',
            'details' => json_encode(['motd' => $request->input('motd')]),
            'status' => 'pending',
        ]);

        // TODO: Update MOTD in server.properties
        return response()->json(['success' => true]);
    }

    /**
     * Upload server icon
     */
    public function uploadIcon(Request $request, $serverId)
    {
        $request->validate(['icon' => 'required|image|max:8192']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'minecraft_icon_upload',
            'status' => 'pending',
        ]);

        // TODO: Upload server.png
        return response()->json(['success' => true]);
    }

    /**
     * Get current Minecraft version
     */
    public function getVersion(Request $request, $serverId)
    {
        // TODO: Detect from server.jar or manifest
        return response()->json(['version' => '1.20.1']);
    }

    /**
     * Change Minecraft version
     */
    public function changeVersion(Request $request, $serverId)
    {
        $request->validate(['version' => 'required|string']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'minecraft_version_change',
            'details' => json_encode(['version' => $request->input('version')]),
            'status' => 'pending',
        ]);

        // TODO: Download and replace server.jar
        return response()->json(['success' => true, 'status' => 'downloading']);
    }
}
