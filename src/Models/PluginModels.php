<?php

namespace DarkCoder\Ofa\Models;

use Illuminate\Database\Eloquent\Model;

class OfaPlugin extends Model
{
    protected $table = 'ofa_plugins';

    protected $fillable = [
        'identifier', 'name', 'version', 'description', 'author',
        'license', 'repository', 'requirements', 'permissions_required',
        'main_class', 'path', 'enabled', 'active', 'activated_at',
        'status', 'last_error', 'config', 'metadata'
    ];

    protected $casts = [
        'requirements' => 'array',
        'permissions_required' => 'array',
        'activated_at' => 'datetime',
        'config' => 'array',
        'metadata' => 'array',
    ];

    public function dependencies()
    {
        return $this->hasMany(PluginDependency::class, 'plugin_id');
    }

    public function hooks()
    {
        return $this->hasMany(PluginHook::class, 'plugin_id');
    }

    public function settings()
    {
        return $this->hasMany(PluginSetting::class, 'plugin_id');
    }

    public function logs()
    {
        return $this->hasMany(PluginLog::class, 'plugin_id');
    }

    public function activate(): bool
    {
        if ($this->isCompatible() && $this->hasDependencies()) {
            $this->update([
                'active' => true,
                'enabled' => true,
                'status' => 'active',
                'activated_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    public function deactivate(): void
    {
        $this->update([
            'active' => false,
            'status' => 'inactive',
        ]);
    }

    public function isCompatible(): bool
    {
        // Check PHP, Laravel, and other requirements
        return true;
    }

    public function hasDependencies(): bool
    {
        // Check if all dependencies are met
        return true;
    }

    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function setSetting($key, $value): void
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => 'string']
        );
    }
}

class PluginDependency extends Model
{
    protected $table = 'ofa_plugin_dependencies';
    public $timestamps = true;

    protected $fillable = [
        'plugin_id', 'dependency_identifier', 'dependency_version', 'dependency_type'
    ];

    public function plugin()
    {
        return $this->belongsTo(OfaPlugin::class, 'plugin_id');
    }
}

class PluginHook extends Model
{
    protected $table = 'ofa_plugin_hooks';
    public $timestamps = true;

    protected $fillable = [
        'plugin_id', 'hook_name', 'callback_class', 'callback_method',
        'priority', 'active'
    ];

    public function plugin()
    {
        return $this->belongsTo(OfaPlugin::class, 'plugin_id');
    }
}

class PluginSetting extends Model
{
    protected $table = 'ofa_plugin_settings';
    public $timestamps = true;

    protected $fillable = [
        'plugin_id', 'key', 'value', 'type', 'user_configurable'
    ];

    public function plugin()
    {
        return $this->belongsTo(OfaPlugin::class, 'plugin_id');
    }
}

class PluginLog extends Model
{
    protected $table = 'ofa_plugin_logs';
    public $timestamps = true;

    protected $fillable = [
        'plugin_id', 'level', 'message', 'context', 'event_type'
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function plugin()
    {
        return $this->belongsTo(OfaPlugin::class, 'plugin_id');
    }

    public function scopeErrors($query)
    {
        return $query->whereIn('level', ['error', 'critical']);
    }
}

class PluginPermission extends Model
{
    protected $table = 'ofa_plugin_permissions';
    public $timestamps = true;

    protected $fillable = [
        'plugin_id', 'permission_name', 'description'
    ];

    public function plugin()
    {
        return $this->belongsTo(OfaPlugin::class, 'plugin_id');
    }
}

class PluginMarketplace extends Model
{
    protected $table = 'ofa_plugin_marketplace';
    public $timestamps = true;

    protected $fillable = [
        'identifier', 'name', 'version', 'description', 'long_description',
        'author', 'tags', 'download_url', 'changelog', 'download_count',
        'rating', 'reviews_count', 'verified', 'featured', 'published_at'
    ];

    protected $casts = [
        'tags' => 'array',
        'changelog' => 'array',
        'published_at' => 'datetime',
    ];

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeTopRated($query)
    {
        return $query->orderByDesc('rating');
    }
}
