<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Backup Management
 * Create, restore, delete server backups
 */
class BackupController extends Controller
{
    /**
     * List backups for a server
     */
    public function list(Request $request, $serverId)
    {
        // TODO: Query Pterodactyl backup system
        $backups = [];

        return response()->json(['backups' => $backups]);
    }

    /**
     * Create new backup
     */
    public function store(Request $request, $serverId)
    {
        $request->validate([
            'ignored' => 'nullable|string',
            'lock' => 'nullable|boolean',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'backup_create',
            'details' => json_encode(['ignored' => $request->input('ignored')]),
            'status' => 'pending',
        ]);

        // TODO: Trigger backup via Pterodactyl API
        return response()->json(['backup_id' => 'uuid', 'status' => 'processing']);
    }

    /**
     * Restore from backup
     */
    public function restore(Request $request, $serverId, $backupId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'backup_restore',
            'details' => json_encode(['backup_id' => $backupId]),
            'status' => 'pending',
        ]);

        // TODO: Restore backup via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Delete backup
     */
    public function destroy(Request $request, $serverId, $backupId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'backup_delete',
            'details' => json_encode(['backup_id' => $backupId]),
            'status' => 'pending',
        ]);

        // TODO: Delete backup via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Download backup
     */
    public function download(Request $request, $serverId, $backupId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'backup_download',
            'details' => json_encode(['backup_id' => $backupId]),
            'status' => 'pending',
        ]);

        // TODO: Generate download link via Pterodactyl API
        return response()->json(['download_url' => 'https://...']);
    }
}
