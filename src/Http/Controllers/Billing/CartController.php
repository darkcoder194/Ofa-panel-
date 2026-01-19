<?php

namespace DarkCoder\Ofa\Http\Controllers\Billing;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * Cart & Checkout
 */
class CartController extends Controller
{
    /**
     * Get cart
     */
    public function show(Request $request)
    {
        // TODO: Get cart from session/database
        $cart = [
            'items' => [],
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
        ];

        return response()->json($cart);
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        // TODO: Add to cart
        return response()->json(['success' => true]);
    }

    /**
     * Update cart item
     */
    public function updateItem(Request $request, $itemId)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        // TODO: Update quantity
        return response()->json(['success' => true]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, $itemId)
    {
        // TODO: Remove from cart
        return response()->json(['success' => true]);
    }

    /**
     * Apply coupon
     */
    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        // TODO: Validate and apply coupon
        return response()->json(['success' => true, 'discount' => 0]);
    }

    /**
     * Checkout
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'billing_address' => 'required|array',
        ]);

        // TODO: Create order and redirect to payment gateway
        return response()->json(['order_id' => 'uuid', 'payment_url' => 'https://...']);
    }
}
