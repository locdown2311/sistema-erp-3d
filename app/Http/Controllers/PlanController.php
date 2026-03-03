<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = \App\Models\Plan::where('is_active', true)->get();
        return view('plans.index', compact('plans'));
    }
}
