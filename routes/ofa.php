<?php

use Illuminate\Support\Facades\Route;
use DarkCoder\Ofa\Http\Controllers\Admin\{
    ThemeController,
    BrandingController,
    ConsoleController,
    FileManagerController,
    DatabaseController,
    BackupController,
    NetworkController,
    ScheduleController,
    UserManagementController,
    StartupController,
    ServerStatsController,
};
use DarkCoder\Ofa\Http\Controllers\Admin\Minecraft\{
    ConfigController as MinecraftConfigController,
    InstallerController,
    PlayerController,
    WorldController,
};
use DarkCoder\Ofa\Http\Controllers\Admin\Addons\{
    SubdomainController,
    TicketController,
    ServerImporterController,
    ReverseProxyController,
};

Route::group(['prefix' => 'admin/ofa', 'middleware' => ['web', 'auth', 'ofa.admin']], function () {
    // ===== ADMIN DASHBOARD =====
    Route::get('/', [ThemeController::class, 'page'])->name('ofa.admin.index');

    // ===== THEME & BRANDING =====
    Route::get('themes', [ThemeController::class, 'index']);
    Route::post('themes', [ThemeController::class, 'store']);
    Route::patch('themes/{palette}', [ThemeController::class, 'update']);
    Route::delete('themes/{palette}', [ThemeController::class, 'destroy']);
    Route::post('themes/{palette}/default', [ThemeController::class, 'setDefault']);
    Route::post('themes/{palette}/preview', [ThemeController::class, 'preview']);
    Route::get('themes/{palette}/export', [ThemeController::class, 'export']);
    Route::post('themes/import', [ThemeController::class, 'import']);

    Route::get('branding', [BrandingController::class, 'show']);
    Route::post('branding', [BrandingController::class, 'update']);

    // ===== PTERODACTYL CORE =====
    Route::prefix('servers/{serverId}')->group(function () {
        // Console
        Route::get('console/logs', [ConsoleController::class, 'logs']);
        Route::post('console/command', [ConsoleController::class, 'executeCommand']);
        Route::get('console/stream', [ConsoleController::class, 'stream']);

        // File Manager
        Route::get('files/list', [FileManagerController::class, 'listDirectory']);
        Route::post('files/upload', [FileManagerController::class, 'uploadFile']);
        Route::get('files/download', [FileManagerController::class, 'downloadFile']);
        Route::post('files/edit', [FileManagerController::class, 'editFile']);
        Route::delete('files/delete', [FileManagerController::class, 'deleteFile']);

        // Databases
        Route::get('databases', [DatabaseController::class, 'list']);
        Route::post('databases', [DatabaseController::class, 'store']);
        Route::delete('databases/{databaseId}', [DatabaseController::class, 'destroy']);
        Route::post('databases/{databaseId}/reset-password', [DatabaseController::class, 'resetPassword']);

        // Backups
        Route::get('backups', [BackupController::class, 'list']);
        Route::post('backups', [BackupController::class, 'store']);
        Route::post('backups/{backupId}/restore', [BackupController::class, 'restore']);
        Route::delete('backups/{backupId}', [BackupController::class, 'destroy']);
        Route::get('backups/{backupId}/download', [BackupController::class, 'download']);

        // Network
        Route::get('network', [NetworkController::class, 'show']);
        Route::post('network/allocations', [NetworkController::class, 'addAllocation']);
        Route::delete('network/allocations/{allocationId}', [NetworkController::class, 'removeAllocation']);
        Route::post('network/allocations/{allocationId}/primary', [NetworkController::class, 'setPrimary']);

        // Schedules
        Route::get('schedules', [ScheduleController::class, 'list']);
        Route::post('schedules', [ScheduleController::class, 'store']);
        Route::patch('schedules/{scheduleId}', [ScheduleController::class, 'update']);
        Route::delete('schedules/{scheduleId}', [ScheduleController::class, 'destroy']);
        Route::post('schedules/{scheduleId}/execute', [ScheduleController::class, 'execute']);

        // Users
        Route::get('users', [UserManagementController::class, 'list']);
        Route::post('users', [UserManagementController::class, 'store']);
        Route::patch('users/{userId}', [UserManagementController::class, 'update']);
        Route::delete('users/{userId}', [UserManagementController::class, 'destroy']);

        // Startup
        Route::get('startup', [StartupController::class, 'show']);
        Route::post('startup/command', [StartupController::class, 'updateCommand']);
        Route::post('startup/variable', [StartupController::class, 'updateVariable']);
        Route::post('startup/egg', [StartupController::class, 'changeEgg']);

        // Server Stats & Power
        Route::get('stats', [ServerStatsController::class, 'stats']);
        Route::get('limits', [ServerStatsController::class, 'limits']);
        Route::post('power/start', [ServerStatsController::class, 'start']);
        Route::post('power/stop', [ServerStatsController::class, 'stop']);
        Route::post('power/restart', [ServerStatsController::class, 'restart']);
        Route::post('power/kill', [ServerStatsController::class, 'kill']);
        Route::post('power/signal', [ServerStatsController::class, 'sendSignal']);

        // ===== MINECRAFT SYSTEM =====
        // Configuration
        Route::get('minecraft/config', [MinecraftConfigController::class, 'getProperties']);
        Route::post('minecraft/config', [MinecraftConfigController::class, 'updateProperties']);
        Route::get('minecraft/motd', [MinecraftConfigController::class, 'getMotd']);
        Route::post('minecraft/motd', [MinecraftConfigController::class, 'updateMotd']);
        Route::post('minecraft/icon', [MinecraftConfigController::class, 'uploadIcon']);
        Route::get('minecraft/version', [MinecraftConfigController::class, 'getVersion']);
        Route::post('minecraft/version', [MinecraftConfigController::class, 'changeVersion']);

        // Installers
        Route::get('minecraft/plugins/search', [InstallerController::class, 'searchPlugins']);
        Route::post('minecraft/plugins/install', [InstallerController::class, 'installPlugin']);
        Route::get('minecraft/plugins/installed', [InstallerController::class, 'getInstalledPlugins']);
        Route::delete('minecraft/plugins/remove', [InstallerController::class, 'removePlugin']);

        Route::get('minecraft/mods/search', [InstallerController::class, 'searchMods']);
        Route::post('minecraft/mods/install', [InstallerController::class, 'installMod']);

        Route::post('minecraft/modpack/install', [InstallerController::class, 'installModpack']);

        // Player Management
        Route::get('minecraft/players', [PlayerController::class, 'getPlayers']);
        Route::post('minecraft/players/op', [PlayerController::class, 'makeOp']);
        Route::post('minecraft/players/deop', [PlayerController::class, 'removeOp']);
        Route::post('minecraft/players/ban', [PlayerController::class, 'ban']);
        Route::post('minecraft/players/unban', [PlayerController::class, 'unban']);
        Route::post('minecraft/players/kick', [PlayerController::class, 'kick']);
        Route::post('minecraft/players/whitelist', [PlayerController::class, 'whitelist']);
        Route::post('minecraft/players/unwhitelist', [PlayerController::class, 'unwhitelist']);
        Route::get('minecraft/players/bans', [PlayerController::class, 'getBans']);
        Route::get('minecraft/players/whitelist', [PlayerController::class, 'getWhitelist']);

        // World Management
        Route::get('minecraft/worlds', [WorldController::class, 'list']);
        Route::post('minecraft/worlds', [WorldController::class, 'create']);
        Route::delete('minecraft/worlds', [WorldController::class, 'delete']);
        Route::post('minecraft/worlds/default', [WorldController::class, 'setDefault']);
        Route::post('minecraft/worlds/upload', [WorldController::class, 'upload']);
        Route::get('minecraft/worlds/download', [WorldController::class, 'download']);
    });

    // ===== ADDONS =====
    // Subdomain Manager
    Route::get('subdomains', [SubdomainController::class, 'list']);
    Route::post('subdomains', [SubdomainController::class, 'store']);
    Route::patch('subdomains/{subdomainId}', [SubdomainController::class, 'update']);
    Route::delete('subdomains/{subdomainId}', [SubdomainController::class, 'destroy']);

    // Reverse Proxy
    Route::get('proxies', [ReverseProxyController::class, 'list']);
    Route::post('proxies', [ReverseProxyController::class, 'store']);
    Route::patch('proxies/{proxyId}', [ReverseProxyController::class, 'update']);
    Route::delete('proxies/{proxyId}', [ReverseProxyController::class, 'destroy']);

    // Server Importer
    Route::get('import/available', [ServerImporterController::class, 'available']);
    Route::post('import', [ServerImporterController::class, 'import']);
});

// ===== USER ROUTES (not admin-only) =====
Route::group(['prefix' => 'ofa', 'middleware' => ['web', 'auth']], function () {
    // Tickets
    Route::get('tickets', [TicketController::class, 'list']);
    Route::post('tickets', [TicketController::class, 'store']);
    Route::post('tickets/{ticketId}/reply', [TicketController::class, 'reply']);
    Route::post('tickets/{ticketId}/close', [TicketController::class, 'close']);
    Route::post('tickets/{ticketId}/reopen', [TicketController::class, 'reopen']);
});

// Public route for frontend to clear preview (admin only as well)
Route::post('admin/ofa/preview/clear', [ThemeController::class, 'clearPreview'])->middleware(['web', 'auth', 'ofa.admin']);
