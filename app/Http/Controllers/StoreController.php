<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Offer;

class StoreController extends Controller
{
    public function show(string $slug)
    {
        $store = \Illuminate\Support\Facades\Cache::remember("store.{$slug}.model", now()->addHours(6), function () use ($slug) {
            return User::where('slug', $slug)->firstOrFail();
        });

        $products = Product::where('user_id', $store->id)
            ->where('active', true)
            ->with('variations', 'images')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $isOwner = auth()->id() === $store->id;

        return view('store.index', compact('store', 'products', 'isOwner'));
    }

    public function offers(string $slug)
    {
        $cacheKey = "store.{$slug}.offers";
        
        list($store, $offers) = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(6), function () use ($slug) {
            $storeModel = User::where('slug', $slug)->firstOrFail();
            $offersList = Offer::where('active', true)->orderBy('created_at', 'desc')->get();
            return [$storeModel, $offersList];
        });

        return view('store.offers', compact('store', 'offers'));
    }

    public function product(string $slug, Product $product)
    {
        $cacheKey = "store.{$slug}.product.{$product->id}";
        
        $store = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(6), function () use ($slug, $product) {
            $storeModel = User::where('slug', $slug)->firstOrFail();
            if ($product->user_id !== $storeModel->id) {
                abort(404);
            }
            return $storeModel;
        });

        $isOwner = auth()->id() === $store->id;

        return view('store.product', compact('store', 'product', 'isOwner'));
    }

    public function latestOffer(string $slug)
    {
        $store = User::where('slug', $slug)->firstOrFail();
        $since = request('since');

        if (!$since) {
            return response()->json(['has_new' => false]);
        }

        // Find the newest offer active, created strictly after the ISO 8601 $since date
        $offer = \Illuminate\Support\Facades\Cache::remember("store.offers.latest.{$since}", now()->addMinutes(5), function () use ($since) {
             return Offer::where('active', true)
                ->where('created_at', '>', date('Y-m-d H:i:s', strtotime($since)))
                ->orderBy('created_at', 'desc')
                ->first();
        });

        if ($offer) {
            return response()->json([
                'has_new' => true,
                'offer' => [
                    'name' => $offer->name,
                    'created_at' => $offer->created_at->toISOString(),
                    'image_url' => $offer->image_url,
                ]
            ]);
        }

        return response()->json(['has_new' => false]);
    }
}
