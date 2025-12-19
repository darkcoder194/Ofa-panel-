<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DarkCoder\Ofa\Models\OfaServerAction;

class ServerController extends Controller
{
    public function requestChange(Request $request)
    {
        $data = $request->validate([
            'server_uuid' => 'required|uuid',
            'type' => 'required|in:version,egg',
            'payload' => 'required|array',
        ]);

        $action = OfaServerAction::create([
            'server_uuid' => $data['server_uuid'],
            'action_type' => $data['type'],
            'payload' => $data['payload'],
            'status' => 'pending',
        ]);

        return response()->json($action, 202);
    }
}
