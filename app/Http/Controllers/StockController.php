<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('user_id', auth()->id())->with('variations', 'stockMovements');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->where('active', true)->orderBy('name')->get();

        $movements = StockMovement::where('user_id', auth()->id())
            ->with('product', 'variation')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('stock.index', compact('products', 'movements'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        StockMovement::create(array_merge($validated, ['user_id' => auth()->id()]));

        return redirect()->route('stock.index')
            ->with('success', 'Movimentação registrada com sucesso!');
    }
}
