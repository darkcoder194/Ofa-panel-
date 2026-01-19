<?php

namespace DarkCoder\Ofa\Http\Controllers\Billing;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * Order & Invoice Management
 */
class OrderController extends Controller
{
    /**
     * Get user orders
     */
    public function list(Request $request)
    {
        // TODO: Get orders from database
        $orders = [];

        return response()->json(['orders' => $orders]);
    }

    /**
     * Get order details
     */
    public function show(Request $request, $orderId)
    {
        // TODO: Get order details with items
        $order = [];

        return response()->json($order);
    }

    /**
     * Get invoices
     */
    public function invoices(Request $request)
    {
        // TODO: Get invoices from database
        $invoices = [];

        return response()->json(['invoices' => $invoices]);
    }

    /**
     * Download invoice
     */
    public function downloadInvoice(Request $request, $invoiceId)
    {
        // TODO: Generate and return PDF
        return response()->json(['pdf_url' => 'https://...']);
    }

    /**
     * Get services (active servers from billing)
     */
    public function services(Request $request)
    {
        // TODO: Get active services/servers
        $services = [];

        return response()->json(['services' => $services]);
    }
}
