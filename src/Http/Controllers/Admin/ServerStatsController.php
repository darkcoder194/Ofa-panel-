<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * Server Stats & Power Management
 * CPU, RAM, Disk monitoring and server power controls
 */
class ServerStatsController extends Controller
{
    /**
     * Get real-time server stats
     */
    public function stats(Request $request, $serverId)
    {
        // TODO: Get stats from Wings API
        $stats = [
            'cpu' => 0,
            'ram' => 0,
            'disk' => 0,
            'uptime' => 0,
            'status' => 'offline',
        ];

        return response()->json($stats);
    }

    /**
     * Start server
     */
    public function start(Request $request, $serverId)
    {
        // TODO: Send start signal via Wings
        return response()->json(['success' => true]);
    }

    /**
     * Stop server
     */
    public function stop(Request $request, $serverId)
    {
        // TODO: Send stop signal via Wings
        return response()->json(['success' => true]);
    }

    /**
     * Restart server
     */
    public function restart(Request $request, $serverId)
    {
        // TODO: Send restart signal via Wings
        return response()->json(['success' => true]);
    }

    /**
     * Force kill server
     */
    public function kill(Request $request, $serverId)
    {
        // TODO: Send kill signal via Wings
        return response()->json(['success' => true]);
    }

    /**
     * Send signal to server
     */
    public function sendSignal(Request $request, $serverId)
    {
        $request->validate([
            'signal' => 'required|string|in:SIGTERM,SIGKILL,SIGSTOP',
        ]);

        // TODO: Send custom signal via Wings
        return response()->json(['success' => true]);
    }

    /**
     * Get resource limits
     */
    public function limits(Request $request, $serverId)
    {
        // TODO: Get limits from Pterodactyl
        $limits = [
            'memory' => 2048,
            'cpu' => 100,
            'disk' => 5120,
            'io' => 500,
        ];

        return response()->json($limits);
    }
}
