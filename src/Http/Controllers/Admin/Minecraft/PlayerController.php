<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin\Minecraft;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Player Management
 * OP, Ban, Kick, Whitelist
 */
class PlayerController extends Controller
{
    /**
     * Get player list
     */
    public function getPlayers(Request $request, $serverId)
    {
        // TODO: Parse playertab or connect to server
        $players = [];

        return response()->json(['players' => $players]);
    }

    /**
     * Make player OP
     */
    public function makeOp(Request $request, $serverId)
    {
        $request->validate(['username' => 'required|string']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'player_op',
            'details' => json_encode(['username' => $request->input('username')]),
            'status' => 'pending',
        ]);

        // TODO: Execute command: op username
        return response()->json(['success' => true]);
    }

    /**
     * Remove OP
     */
    public function removeOp(Request $request, $serverId)
    {
        $request->validate(['username' => 'required|string']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'player_deop',
            'details' => json_encode(['username' => $request->input('username')]),
            'status' => 'pending',
        ]);

        // TODO: Execute command: deop username
        return response()->json(['success' => true]);
    }

    /**
     * Ban player
     */
    public function ban(Request $request, $serverId)
    {
        $request->validate([
            'username' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'player_ban',
            'details' => json_encode(['username' => $request->input('username'), 'reason' => $request->input('reason')]),
            'status' => 'pending',
        ]);

        // TODO: Execute command: ban username reason
        return response()->json(['success' => true]);
    }

    /**
     * Unban player
     */
    public function unban(Request $request, $serverId)
    {
        $request->validate(['username' => 'required|string']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'player_unban',
            'details' => json_encode(['username' => $request->input('username')]),
            'status' => 'pending',
        ]);

        // TODO: Execute command: pardon username
        return response()->json(['success' => true]);
    }

    /**
     * Kick player
     */
    public function kick(Request $request, $serverId)
    {
        $request->validate([
            'username' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'player_kick',
            'details' => json_encode(['username' => $request->input('username'), 'reason' => $request->input('reason')]),
            'status' => 'pending',
        ]);

        // TODO: Execute command: kick username reason
        return response()->json(['success' => true]);
    }

    /**
     * Add to whitelist
     */
    public function whitelist(Request $request, $serverId)
    {
        $request->validate(['username' => 'required|string']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'player_whitelist',
            'details' => json_encode(['username' => $request->input('username')]),
            'status' => 'pending',
        ]);

        // TODO: Execute command: whitelist add username
        return response()->json(['success' => true]);
    }

    /**
     * Remove from whitelist
     */
    public function unwhitelist(Request $request, $serverId)
    {
        $request->validate(['username' => 'required|string']);

        OfaServerAction::create([
            'server_id' => $serverId,
            'user_id' => $request->user()->id,
            'action' => 'player_unwhitelist',
            'details' => json_encode(['username' => $request->input('username')]),
            'status' => 'pending',
        ]);

        // TODO: Execute command: whitelist remove username
        return response()->json(['success' => true]);
    }

    /**
     * Get ban list
     */
    public function getBans(Request $request, $serverId)
    {
        // TODO: Parse banned-players.json
        $bans = [];

        return response()->json(['bans' => $bans]);
    }

    /**
     * Get whitelist
     */
    public function getWhitelist(Request $request, $serverId)
    {
        // TODO: Parse whitelist.json
        $whitelist = [];

        return response()->json(['whitelist' => $whitelist]);
    }
}
