<?php

namespace DarkCoder\Ofa\Http\Controllers\Billing;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * Store & Plans Management
 * Display and manage server plans
 */
class StoreController extends Controller
{
    /**
     * Get all plans
     */
    public function plans(Request $request)
    {
        // TODO: Get plans from database with pricing
        $plans = [];

        return response()->json(['plans' => $plans]);
    }

    /**
     * Get plan details
     */
    public function getPlan(Request $request, $planId)
    {
        // TODO: Get plan with features, limits, pricing
        $plan = [];

        return response()->json($plan);
    }

    /**
     * Get store home (featured plans, promotions)
     */
    public function home(Request $request)
    {
        $data = [
            'featured_plans' => [],
            'promotions' => [],
            'popular_games' => [],
        ];

        return response()->json($data);
    }
}
