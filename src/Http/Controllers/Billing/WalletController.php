<?php

namespace DarkCoder\Ofa\Http\Controllers\Billing;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * Wallet & Balance Management
 */
class WalletController extends Controller
{
    /**
     * Get wallet balance
     */
    public function show(Request $request)
    {
        // TODO: Get wallet balance from database
        $wallet = [
            'balance' => 0,
            'currency' => 'USD',
            'transactions' => [],
        ];

        return response()->json($wallet);
    }

    /**
     * Add funds to wallet
     */
    public function addFunds(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
        ]);

        // TODO: Create payment intent
        return response()->json(['success' => true, 'payment_url' => 'https://...']);
    }

    /**
     * Get transaction history
     */
    public function transactions(Request $request)
    {
        // TODO: Get transactions
        $transactions = [];

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * Refund request
     */
    public function requestRefund(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'reason' => 'required|string',
        ]);

        // TODO: Create refund request
        return response()->json(['success' => true, 'refund_id' => 'uuid']);
    }
}
