<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use DarkCoder\Ofa\Services\PluginManager;
use DarkCoder\Ofa\Models\OfaPlugin;
use DarkCoder\Ofa\Models\PluginMarketplace;
use DarkCoder\Ofa\Models\PluginLog;
use Illuminate\Http\Request;

class PluginManagementController
{
    protected $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * List installed plugins
     */
    public function listPlugins()
    {
        $plugins = OfaPlugin::paginate(25);

        return response()->json($plugins);
    }

    /**
     * Get plugin details
     */
    public function getPlugin(OfaPlugin $plugin)
    {
        $stats = [
            'installation_date' => $plugin->created_at,
            'activated_at' => $plugin->activated_at,
            'total_logs' => $plugin->logs()->count(),
            'errors' => $plugin->logs()->errors()->count(),
        ];

        return response()->json([
            'plugin' => $plugin,
            'stats' => $stats,
        ]);
    }

    /**
     * Activate plugin
     */
    public function activatePlugin(OfaPlugin $plugin)
    {
        if ($this->pluginManager->activatePlugin($plugin)) {
            return response()->json([
                'message' => 'Plugin activated successfully',
                'plugin' => $plugin->refresh(),
            ]);
        }

        return response()->json([
            'error' => $plugin->last_error,
        ], 422);
    }

    /**
     * Deactivate plugin
     */
    public function deactivatePlugin(OfaPlugin $plugin)
    {
        if ($this->pluginManager->deactivatePlugin($plugin)) {
            return response()->json([
                'message' => 'Plugin deactivated successfully',
                'plugin' => $plugin->refresh(),
            ]);
        }

        return response()->json([
            'error' => 'Failed to deactivate plugin',
        ], 422);
    }

    /**
     * Uninstall plugin
     */
    public function uninstallPlugin(OfaPlugin $plugin)
    {
        if ($this->pluginManager->uninstallPlugin($plugin)) {
            return response()->json([
                'message' => 'Plugin uninstalled successfully',
            ]);
        }

        return response()->json([
            'error' => 'Failed to uninstall plugin',
        ], 422);
    }

    /**
     * Get plugin logs
     */
    public function getPluginLogs(OfaPlugin $plugin, Request $request)
    {
        $query = $plugin->logs();

        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        $logs = $query->latest()->paginate(50);

        return response()->json($logs);
    }

    /**
     * Browse marketplace
     */
    public function marketplace(Request $request)
    {
        $query = PluginMarketplace::verified();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->has('sort')) {
            switch ($request->input('sort')) {
                case 'rating':
                    $query->orderByDesc('rating');
                    break;
                case 'downloads':
                    $query->orderByDesc('download_count');
                    break;
                case 'new':
                default:
                    $query->orderByDesc('published_at');
            }
        } else {
            $query->orderByDesc('downloads');
        }

        $plugins = $query->paginate(24);

        return response()->json($plugins);
    }

    /**
     * Get featured marketplace plugins
     */
    public function getFeaturedPlugins()
    {
        $plugins = PluginMarketplace::verified()
            ->featured()
            ->limit(10)
            ->get();

        return response()->json($plugins);
    }

    /**
     * Download plugin from marketplace
     */
    public function downloadPlugin(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string|exists:ofa_plugin_marketplace,identifier',
        ]);

        $marketplacePlugin = PluginMarketplace::where('identifier', $validated['identifier'])->first();

        if ($plugin = $this->pluginManager->downloadPlugin($marketplacePlugin)) {
            return response()->json([
                'message' => 'Plugin downloaded and installed successfully',
                'plugin' => $plugin,
            ], 201);
        }

        return response()->json([
            'error' => 'Failed to download plugin',
        ], 422);
    }

    /**
     * Get plugin configuration
     */
    public function getPluginConfig(OfaPlugin $plugin)
    {
        $userConfigurable = $plugin->settings()
            ->where('user_configurable', true)
            ->get();

        return response()->json($userConfigurable);
    }

    /**
     * Update plugin configuration
     */
    public function updatePluginConfig(Request $request, OfaPlugin $plugin)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $plugin->setSetting($key, $value);
        }

        return response()->json([
            'message' => 'Plugin configuration updated',
            'settings' => $plugin->settings()->get(),
        ]);
    }

    /**
     * Check plugin system health
     */
    public function systemHealth()
    {
        $totalPlugins = OfaPlugin::count();
        $activePlugins = OfaPlugin::where('active', true)->count();
        $errorPlugins = OfaPlugin::where('status', 'error')->count();

        return response()->json([
            'total_plugins' => $totalPlugins,
            'active_plugins' => $activePlugins,
            'error_plugins' => $errorPlugins,
            'health_status' => $errorPlugins === 0 ? 'healthy' : 'warning',
        ]);
    }
}
