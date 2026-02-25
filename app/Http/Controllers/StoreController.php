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
}
