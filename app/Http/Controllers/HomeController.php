<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    public function index()
    {
        // Try to run pending migrations on the fly (failsafe for local development issues)
        try {
            if (!\Schema::hasColumn('users', 'upvotes')) {
                Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Exception $e) {
            // Ignore if it fails, the user might need to run it via podman manually
        }

        // Top 10 stores by upvotes (if column exists)
        $topStores = collect();
        if (\Schema::hasColumn('users', 'upvotes')) {
            $topStores = User::whereNotNull('store_name')
                ->where('store_name', '!=', '')
                ->orderBy('upvotes', 'desc')
                ->take(10)
                ->get();
        }

        // Latest active offers
        $latestOffers = Offer::where('active', true)
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        return view('home', compact('topStores', 'latestOffers'));
    }
}
