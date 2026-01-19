<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin\Minecraft;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Plugin & Mod Installation
 * Spigot, Bukkit, Hangar, CurseForge, Modrinth
 */
class InstallerController extends Controller
{
    /**
     * Search plugins
     */
    public function searchPlugins(Request $request)
    {
        $query = $request->input('q');

        // TODO: Query Hangar or Spigot API
        $results = [];

        return response()->json(['plugins' => $results]);
    }

    /**
     * Install plugin
     */
    public function installPlugin(Request $request, $serverId)
    {
        $request->validate([
            'plugin_id' => 'required|string',
            'source' => 'required|string|in:hangar,spigot,bukkit',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'plugin_install',
            'details' => json_encode(['plugin_id' => $request->input('plugin_id')]),
            'status' => 'downloading',
        ]);

        // TODO: Download and install plugin
        return response()->json(['success' => true, 'status' => 'installing']);
    }

    /**
     * Search mods
     */
    public function searchMods(Request $request)
    {
        $query = $request->input('q');

        // TODO: Query CurseForge or Modrinth API
        $results = [];

        return response()->json(['mods' => $results]);
    }

    /**
     * Install mod
     */
    public function installMod(Request $request, $serverId)
    {
        $request->validate([
            'mod_id' => 'required|string',
            'source' => 'required|string|in:curseforge,modrinth',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'mod_install',
            'details' => json_encode(['mod_id' => $request->input('mod_id')]),
            'status' => 'downloading',
        ]);

        // TODO: Download and install mod
        return response()->json(['success' => true, 'status' => 'installing']);
    }

    /**
     * Install modpack
     */
    public function installModpack(Request $request, $serverId)
    {
        $request->validate([
            'modpack_url' => 'required|url',
            'format' => 'required|string|in:curseforge,modrinth',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'modpack_install',
            'details' => json_encode(['modpack_url' => $request->input('modpack_url')]),
            'status' => 'downloading',
        ]);

        // TODO: Download and install modpack
        return response()->json(['success' => true, 'status' => 'installing']);
    }

    /**
     * Get installed plugins
     */
    public function getInstalledPlugins(Request $request, $serverId)
    {
        // TODO: Scan plugins folder
        $plugins = [];

        return response()->json(['plugins' => $plugins]);
    }

    /**
     * Remove plugin
     */
    public function removePlugin(Request $request, $serverId)
    {
        $request->validate(['plugin_name' => 'required|string']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'plugin_remove',
            'details' => json_encode(['plugin_name' => $request->input('plugin_name')]),
            'status' => 'pending',
        ]);

        // TODO: Delete plugin file
        return response()->json(['success' => true]);
    }
}
