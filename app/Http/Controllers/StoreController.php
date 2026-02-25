<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Offer;

class StoreController extends Controller
{
    public function show(string $slug)
    {
        $store = User::where('slug', $slug)->firstOrFail();
        $products = Product::where('user_id', $store->id)
            ->where('active', true)
            ->with('variations')
            ->orderBy('name')
            ->get();

        $isOwner = auth()->id() === $store->id;

        return view('store.index', compact('store', 'products', 'isOwner'));
    }

    public function offers(string $slug)
    {
        $store = User::where('slug', $slug)->firstOrFail();
        $offers = Offer::where('active', true)->orderBy('created_at', 'desc')->get();

        return view('store.offers', compact('store', 'offers'));
    }

    public function product(string $slug, Product $product)
    {
        $store = User::where('slug', $slug)->firstOrFail();

        if ($product->user_id !== $store->id) {
            abort(404);
        }

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
        $offer = Offer::where('active', true)
            ->where('created_at', '>', date('Y-m-d H:i:s', strtotime($since)))
            ->orderBy('created_at', 'desc')
            ->first();

        if ($offer) {
            return response()->json([
                'has_new' => true,
                'offer' => [
                    'name' => $offer->name,
                    'created_at' => $offer->created_at->toISOString(),
                    'image_url' => $offer->image_path ? asset('storage/' . $offer->image_path) : null,
                ]
            ]);
        }

        return response()->json(['has_new' => false]);
    }
}
