<?php

namespace App\Http\Controllers;

use App\Models\PrintCost;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class CostController extends Controller
{
    public function index()
    {
        $products = Product::where('user_id', auth()->id())->where('active', true)->orderBy('name')->get();
        $costs = PrintCost::where('user_id', auth()->id())->with('product')->orderBy('created_at', 'desc')->get();

        // Default settings
        $defaults = [
            'kwh_rate' => Setting::get('kwh_rate', '0.80'),
            'filament_price_kg' => Setting::get('filament_price_kg', '120.00'),
            'printer_wattage' => Setting::get('printer_wattage', '350'),
            'printer_price' => Setting::get('printer_price', '2500.00'),
            'printer_lifespan_hours' => Setting::get('printer_lifespan_hours', '5000'),
            'labor_rate' => Setting::get('labor_rate', '20.00'),
        ];

        return view('costs.index', compact('products', 'costs', 'defaults'));
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'filament_weight_g' => 'required|numeric|min:0',
            'filament_price_kg' => 'required|numeric|min:0',
            'print_time_hours' => 'required|numeric|min:0',
            'printer_wattage' => 'required|numeric|min:0',
            'kwh_rate' => 'required|numeric|min:0',
            'printer_price' => 'required|numeric|min:0',
            'printer_lifespan_hours' => 'required|numeric|min:0.01',
            'post_processing_hours' => 'nullable|numeric|min:0',
            'labor_rate' => 'required|numeric|min:0',
            'margin_percent' => 'required|numeric|min:0',
        ]);

        $filament_cost = $validated['filament_weight_g'] * ($validated['filament_price_kg'] / 1000);
        $energy_cost = ($validated['printer_wattage'] / 1000) * $validated['print_time_hours'] * $validated['kwh_rate'];
        $depreciation_cost = ($validated['printer_price'] / $validated['printer_lifespan_hours']) * $validated['print_time_hours'];
        $post_hours = $validated['post_processing_hours'] ?? 0;
        $labor_cost = $post_hours * $validated['labor_rate'];
        $total_cost = $filament_cost + $energy_cost + $depreciation_cost + $labor_cost;
        $suggested_price = $total_cost * (1 + $validated['margin_percent'] / 100);

        return response()->json([
            'filament_cost' => round($filament_cost, 2),
            'energy_cost' => round($energy_cost, 2),
            'depreciation_cost' => round($depreciation_cost, 2),
            'labor_cost' => round($labor_cost, 2),
            'total_cost' => round($total_cost, 2),
            'suggested_price' => round($suggested_price, 2),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'name' => 'nullable|string|max:255',
            'filament_weight_g' => 'required|numeric',
            'filament_price_kg' => 'required|numeric',
            'filament_cost' => 'required|numeric',
            'print_time_hours' => 'required|numeric',
            'printer_wattage' => 'required|numeric',
            'kwh_rate' => 'required|numeric',
            'energy_cost' => 'required|numeric',
            'printer_price' => 'required|numeric',
            'printer_lifespan_hours' => 'required|numeric',
            'depreciation_cost' => 'required|numeric',
            'post_processing_hours' => 'nullable|numeric',
            'labor_rate' => 'required|numeric',
            'labor_cost' => 'required|numeric',
            'total_cost' => 'required|numeric',
            'margin_percent' => 'required|numeric',
            'suggested_price' => 'required|numeric',
        ]);

        $validated['user_id'] = auth()->id();
        PrintCost::create($validated);

        return redirect()->route('costs.index')
            ->with('success', 'Cálculo de custo salvo com sucesso!');
    }

    public function destroy(PrintCost $cost)
    {
        if ($cost->user_id !== auth()->id()) abort(403);
        $cost->delete();
        return redirect()->route('costs.index')
            ->with('success', 'Cálculo removido!');
    }
}
