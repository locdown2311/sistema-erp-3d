<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)->orderBy('price')->get();
        $user = auth()->user();
        $currentPlan = $user->currentPlan();

        $usage = [
            'products' => $user->getPlanUsage('products'),
            'sales' => $user->getPlanUsage('sales'),
            'wishlists' => $user->getPlanUsage('wishlists'),
            'flexi_cuts' => $user->getPlanUsage('flexi_cuts'),
        ];

        return view('plans.index', compact('plans', 'currentPlan', 'usage'));
    }
}
