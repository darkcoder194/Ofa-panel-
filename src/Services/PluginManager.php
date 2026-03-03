<?php

namespace DarkCoder\Ofa\Services;

use DarkCoder\Ofa\Models\OfaPlugin;
use DarkCoder\Ofa\Models\PluginMarketplace;
use DarkCoder\Ofa\Models\PluginLog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PluginManager
{
    protected $pluginPath = 'plugins/';

    /**
     * Install a plugin
     */
    public function installPlugin(string $pluginPath, string $zipPath = null): ?OfaPlugin
    {
        try {
            // Extract plugin metadata
            $metadata = $this->extractPluginMetadata($pluginPath);
            
            if (!$metadata) {
                throw new \Exception("Invalid plugin metadata");
            }

            // Check dependencies
            if (!$this->checkDependencies($metadata)) {
                throw new \Exception("Plugin dependencies not satisfied");
            }

            // Create database record
            $plugin = OfaPlugin::create([
                'identifier' => $metadata['identifier'],
                'name' => $metadata['name'],
                'version' => $metadata['version'],
                'description' => $metadata['description'] ?? null,
                'author' => $metadata['author'] ?? null,
                'license' => $metadata['license'] ?? null,
                'main_class' => $metadata['main_class'],
                'path' => $pluginPath,
                'requirements' => $metadata['requirements'] ?? [],
                'permissions_required' => $metadata['permissions'] ?? [],
                'enabled' => false,
                'active' => false,
                'status' => 'inactive',
            ]);

            $this->logPluginAction($plugin, 'installation', true, 'Plugin installed successfully');

            return $plugin;
        } catch (\Exception $e) {
            Log::error("Plugin installation failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Activate a plugin
     */
    public function activatePlugin(OfaPlugin $plugin): bool
    {
        try {
            if (!$plugin->isCompatible()) {
                throw new \Exception("Plugin is not compatible with current system");
            }

            if (!$plugin->hasDependencies()) {
                throw new \Exception("Plugin dependencies not satisfied");
            }

            // Load plugin class
            $mainClass = $plugin->main_class;
            if (!class_exists($mainClass)) {
                throw new \Exception("Main class not found: {$mainClass}");
            }

            // Call plugin boot method if exists
            $instance = new $mainClass();
            if (method_exists($instance, 'boot')) {
                $instance->boot();
            }

            // Register hooks
            $this->registerPluginHooks($plugin);

            $plugin->activate();
            $this->logPluginAction($plugin, 'activation', true, 'Plugin activated successfully');

            return true;
        } catch (\Exception $e) {
            $plugin->update([
                'status' => 'error',
                'last_error' => $e->getMessage(),
            ]);
            $this->logPluginAction($plugin, 'activation', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Deactivate a plugin
     */
    public function deactivatePlugin(OfaPlugin $plugin): bool
    {
        try {
            // Call plugin shutdown method if exists
            $mainClass = $plugin->main_class;
            if (class_exists($mainClass)) {
                $instance = new $mainClass();
                if (method_exists($instance, 'shutdown')) {
                    $instance->shutdown();
                }
            }

            $plugin->deactivate();
            $this->logPluginAction($plugin, 'deactivation', true, 'Plugin deactivated successfully');

            return true;
        } catch (\Exception $e) {
            $this->logPluginAction($plugin, 'deactivation', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Uninstall a plugin
     */
    public function uninstallPlugin(OfaPlugin $plugin): bool
    {
        try {
            // Deactivate if active
            if ($plugin->active) {
                $this->deactivatePlugin($plugin);
            }

            // Remove plugin files
            if (File::exists($plugin->path)) {
                File::deleteDirectory($plugin->path);
            }

            // Clean up database records
            $plugin->hooks()->delete();
            $plugin->settings()->delete();
            $plugin->permissions()->delete();
            $plugin->logs()->delete();
            $plugin->delete();

            $this->logPluginAction($plugin, 'uninstall', true, 'Plugin uninstalled successfully');

            return true;
        } catch (\Exception $e) {
            $this->logPluginAction($plugin, 'uninstall', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Update a plugin
     */
    public function updatePlugin(OfaPlugin $plugin, string $newPluginPath): bool
    {
        try {
            $wasActive = $plugin->active;
            
            if ($wasActive) {
                $this->deactivatePlugin($plugin);
            }

            // Replace plugin files
            if (File::exists($plugin->path)) {
                File::deleteDirectory($plugin->path);
            }
            File::copyDirectory($newPluginPath, $plugin->path);

            // Update version
            $metadata = $this->extractPluginMetadata($plugin->path);
            $plugin->update([
                'version' => $metadata['version'] ?? $plugin->version,
                'status' => 'inactive',
            ]);

            if ($wasActive) {
                $this->activatePlugin($plugin);
            }

            $this->logPluginAction($plugin, 'update', true, 'Plugin updated successfully');

            return true;
        } catch (\Exception $e) {
            $plugin->update([
                'status' => 'error',
                'last_error' => $e->getMessage(),
            ]);
            $this->logPluginAction($plugin, 'update', false, $e->getMessage());
            return false;
        }
    }

    /**
     * Extract plugin metadata from plugin.json
     */
    private function extractPluginMetadata(string $pluginPath): ?array
    {
        $metadataFile = $pluginPath . '/plugin.json';
        
        if (!File::exists($metadataFile)) {
            return null;
        }

        return json_decode(File::get($metadataFile), true);
    }

    /**
     * Check plugin dependencies
     */
    private function checkDependencies(array $metadata): bool
    {
        if (empty($metadata['requires'])) {
            return true;
        }

        foreach ($metadata['requires'] as $requirement) {
            $plugin = OfaPlugin::where('identifier', $requirement['plugin'])
                ->where('active', true)
                ->first();

            if (!$plugin) {
                return false;
            }
        }

        return true;
    }

    /**
     * Register plugin hooks
     */
    private function registerPluginHooks(OfaPlugin $plugin): void
    {
        $plugin->hooks()->where('active', true)->each(function ($hook) {
            // Register hook in system
            // Implementation depends on hook system design
        });
    }

    /**
     * Execute plugin hook
     */
    public function executeHook(string $hookName, ...$args): void
    {
        $hooks = OfaPlugin::where('active', true)
            ->with(['hooks' => function ($query) use ($hookName) {
                $query->where('hook_name', $hookName)->where('active', true);
            }])
            ->get();

        foreach ($hooks as $plugin) {
            foreach ($plugin->hooks as $hook) {
                try {
                    $this->executeInternalCallback($hook->callback_class, $hook->callback_method, $args);
                } catch (\Exception $e) {
                    $this->logPluginMessage($plugin, 'error', "Hook execution failed: {$e->getMessage()}");
                }
            }
        }
    }

    /**
     * Execute internal callback
     */
    private function executeInternalCallback(string $class, string $method, array $args = []): void
    {
        if (!class_exists($class)) {
            throw new \Exception("Class not found: {$class}");
        }

        $instance = new $class();
        
        if (!method_exists($instance, $method)) {
            throw new \Exception("Method not found: {$method}");
        }

        call_user_func_array([$instance, $method], $args);
    }

    /**
     * Log plugin action
     */
    private function logPluginAction(OfaPlugin $plugin, string $action, bool $success, string $message): void
    {
        PluginLog::create([
            'plugin_id' => $plugin->id,
            'level' => $success ? 'info' : 'error',
            'message' => $message,
            'event_type' => $action,
        ]);
    }

    /**
     * Log plugin message
     */
    public function logPluginMessage(OfaPlugin $plugin, string $level, string $message, array $context = []): void
    {
        PluginLog::create([
            'plugin_id' => $plugin->id,
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ]);
    }

    /**
     * Get active plugins
     */
    public function getActivePlugins()
    {
        return OfaPlugin::where('active', true)->get();
    }

    /**
     * Get installed plugins
     */
    public function getInstalledPlugins()
    {
        return OfaPlugin::where('enabled', true)->get();
    }

    /**
     * Get marketplace plugins
     */
    public function getMarketplacePlugins(int $limit = 25, int $page = 1)
    {
        return PluginMarketplace::verified()
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Get top-rated plugins
     */
    public function getTopRatedPlugins(int $limit = 10)
    {
        return PluginMarketplace::verified()
            ->topRated()
            ->limit($limit)
            ->get();
    }

    /**
     * Download plugin from marketplace
     */
    public function downloadPlugin(PluginMarketplace $marketplacePlugin): ?OfaPlugin
    {
        try {
            // Download plugin zip file
            $response = \Illuminate\Support\Facades\Http::get($marketplacePlugin->download_url);
            
            if (!$response->successful()) {
                throw new \Exception("Failed to download plugin");
            }

            // Save to temporary location
            $tempPath = storage_path('plugins/temp/' . $marketplacePlugin->identifier . '.zip');
            File::put($tempPath, $response->body());

            // Extract zip
            $extractPath = storage_path('plugins/temp/' . $marketplacePlugin->identifier);
            $zip = new \ZipArchive();
            $zip->open($tempPath);
            $zip->extractTo($extractPath);
            $zip->close();

            // Install plugin
            $plugin = $this->installPlugin($extractPath);

            // Update marketplace stats
            $marketplacePlugin->increment('download_count');

            return $plugin;
        } catch (\Exception $e) {
            Log::error("Plugin download failed: {$e->getMessage()}");
            return null;
        }
    }
}
