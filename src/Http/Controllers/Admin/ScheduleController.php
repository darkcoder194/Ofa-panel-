<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Schedule Management
 * Create and manage server scheduled tasks
 */
class ScheduleController extends Controller
{
    /**
     * List schedules for a server
     */
    public function list(Request $request, $serverId)
    {
        // TODO: Query Pterodactyl schedules
        $schedules = [];

        return response()->json(['schedules' => $schedules]);
    }

    /**
     * Create schedule
     */
    public function store(Request $request, $serverId)
    {
        $request->validate([
            'name' => 'required|string',
            'minute' => 'required|string',
            'hour' => 'required|string',
            'day_of_week' => 'required|string',
            'day_of_month' => 'required|string',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'schedule_create',
            'details' => json_encode($request->only(['name', 'minute', 'hour', 'day_of_week', 'day_of_month'])),
            'status' => 'pending',
        ]);

        // TODO: Create schedule via Pterodactyl API
        return response()->json(['success' => true, 'schedule_id' => 'uuid']);
    }

    /**
     * Update schedule
     */
    public function update(Request $request, $serverId, $scheduleId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'schedule_update',
            'details' => json_encode(['schedule_id' => $scheduleId]),
            'status' => 'pending',
        ]);

        // TODO: Update schedule via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Delete schedule
     */
    public function destroy(Request $request, $serverId, $scheduleId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'schedule_delete',
            'details' => json_encode(['schedule_id' => $scheduleId]),
            'status' => 'pending',
        ]);

        // TODO: Delete schedule via Pterodactyl API
        return response()->json(['success' => true]);
    }

    /**
     * Execute schedule now
     */
    public function execute(Request $request, $serverId, $scheduleId)
    {
        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'schedule_execute',
            'details' => json_encode(['schedule_id' => $scheduleId]),
            'status' => 'pending',
        ]);

        // TODO: Execute schedule via Pterodactyl API
        return response()->json(['success' => true]);
    }
}
