<?php

namespace DarkCoder\Ofa\Http\Controllers\Admin\Addons;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DarkCoder\Ofa\Models\OfaServerAction;

/**
 * Staff Request System (Tickets)
 */
class TicketController extends Controller
{
    /**
     * Get user tickets
     */
    public function list(Request $request)
    {
        // TODO: Get tickets from database
        $tickets = [];

        return response()->json(['tickets' => $tickets]);
    }

    /**
     * Create ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'priority' => 'required|string|in:low,medium,high,critical',
        ]);

        // TODO: Create ticket in database
        return response()->json(['success' => true, 'ticket_id' => 'uuid']);
    }

    /**
     * Add reply to ticket
     */
    public function reply(Request $request, $ticketId)
    {
        $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array',
        ]);

        OfaServerAction::create([
            'user_id' => $request->user()->id,
            'action' => 'ticket_reply',
            'details' => json_encode(['ticket_id' => $ticketId]),
            'status' => 'completed',
        ]);

        // TODO: Add reply to ticket
        return response()->json(['success' => true]);
    }

    /**
     * Close ticket
     */
    public function close(Request $request, $ticketId)
    {
        // TODO: Update ticket status to closed
        return response()->json(['success' => true]);
    }

    /**
     * Reopen ticket
     */
    public function reopen(Request $request, $ticketId)
    {
        // TODO: Update ticket status to reopened
        return response()->json(['success' => true]);
    }
}
