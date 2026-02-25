<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::where('user_id', auth()->id())->with('items.product');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        $sales = $query->orderBy('sale_date', 'desc')->paginate(15);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::where('user_id', auth()->id())->where('active', true)->with('variations')->orderBy('name')->get();
        return view('sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tracking_code' => 'nullable|string|max:255',
        ]);

        $total = collect($validated['items'])->sum(fn($item) => $item['quantity'] * $item['unit_price']);

        $sale = Sale::create([
            'user_id' => auth()->id(),
            'customer_name' => $validated['customer_name'],
            'sale_date' => $validated['sale_date'],
            'notes' => $validated['notes'] ?? null,
            'total' => $total,
            'status' => 'completed',
            'tracking_code' => $validated['tracking_code'] ?? null,
            'shipping_status' => !empty($validated['tracking_code']) ? 'Pendente' : null,
        ]);

        foreach ($validated['items'] as $item) {
            $sale->items()->create($item);

            // Auto deduct stock
            StockMovement::create([
                'user_id' => auth()->id(),
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? null,
                'type' => 'out',
                'quantity' => $item['quantity'],
                'notes' => 'Venda #' . $sale->id,
            ]);
        }

        return redirect()->route('sales.index')
            ->with('success', 'Venda registrada com sucesso!');
    }

    public function show(Sale $sale)
    {
        if ($sale->user_id !== auth()->id()) abort(403);
        $sale->load('items.product', 'items.variation');
        return view('sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        if ($sale->user_id !== auth()->id()) abort(403);
        $sale->delete();
        return redirect()->route('sales.index')
            ->with('success', 'Venda removida com sucesso!');
    }
}
