<?php

namespace App\Http\Controllers;

use App\Models\Filament;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FilamentController extends Controller
{
    public function index(Request $request)
    {
        $query = Filament::where('user_id', auth()->id());

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $filaments = $query->orderBy('name')->get();
        $types = Filament::where('user_id', auth()->id())->distinct()->pluck('type')->filter();

        // Calculate remaining percentages safely
        $filaments->each(function ($filament) {
            if ($filament->weight_grams > 0) {
                $filament->remaining_percent = min(100, max(0, ($filament->remaining_grams / $filament->weight_grams) * 100));
            } else {
                $filament->remaining_percent = 0;
            }
        });

        return Inertia::render('Filaments/Index', [
            'filaments' => $filaments,
            'types' => $types->values(),
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'color' => 'nullable|string|max:100',
            'brand' => 'required|string|max:255',
            'price_per_kg' => 'required|numeric|min:0',
            'weight_grams' => 'required|numeric|min:0',
            'remaining_grams' => 'nullable|numeric|min:0',
            'diameter_mm' => 'required|numeric|min:0',
            'print_temp_min' => 'nullable|integer',
            'print_temp_max' => 'nullable|integer',
            'bed_temp_min' => 'nullable|integer',
            'bed_temp_max' => 'nullable|integer',
            'notes' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1|max:100',
        ]);

        $quantity = $validated['quantity'] ?? 1;
        unset($validated['quantity']);

        $validated['name'] = trim($validated['brand'] . ' ' . $validated['type'] . ($validated['color'] ? ' ' . $validated['color'] : ''));
        $validated['remaining_grams'] = $validated['remaining_grams'] ?? $validated['weight_grams'];
        $validated['user_id'] = auth()->id();

        for ($i = 0; $i < $quantity; $i++) {
            Filament::create($validated);
        }

        $msg = $quantity > 1
            ? "{$quantity} rolos de \"{$validated['name']}\" adicionados com sucesso!"
            : 'Filamento adicionado com sucesso!';

        return redirect()->route('filaments.index')
            ->with('success', $msg);
    }

    public function update(Request $request, Filament $filament)
    {
        if ($filament->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'color' => 'nullable|string|max:100',
            'brand' => 'required|string|max:255',
            'price_per_kg' => 'required|numeric|min:0',
            'weight_grams' => 'required|numeric|min:0',
            'remaining_grams' => 'nullable|numeric|min:0',
            'diameter_mm' => 'required|numeric|min:0',
            'print_temp_min' => 'nullable|integer',
            'print_temp_max' => 'nullable|integer',
            'bed_temp_min' => 'nullable|integer',
            'bed_temp_max' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $validated['name'] = trim($validated['brand'] . ' ' . $validated['type'] . ($validated['color'] ? ' ' . $validated['color'] : ''));
        $validated['active'] = $request->has('active');
        $filament->update($validated);

        return redirect()->route('filaments.index')
            ->with('success', 'Filamento atualizado!');
    }

    public function destroy(Filament $filament)
    {
        if ($filament->user_id !== auth()->id()) abort(403);
        $filament->delete();
        return redirect()->route('filaments.index')
            ->with('success', 'Filamento removido!');
    }

    public function consume(Request $request, Filament $filament)
    {
        if ($filament->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'grams' => 'required|numeric|min:0.01',
        ]);

        $grams = $validated['grams'];

        if ($grams > $filament->remaining_grams) {
            return redirect()->route('filaments.index')
                ->with('error', "Não é possível consumir {$grams}g — restam apenas {$filament->remaining_grams}g de \"{$filament->name}\".");
        }

        $filament->remaining_grams -= $grams;
        if ($filament->remaining_grams < 0) $filament->remaining_grams = 0;
        $filament->save();

        return redirect()->route('filaments.index')
            ->with('success', "{$grams}g consumidos de \"{$filament->name}\" — restam {$filament->remaining_grams}g.");
    }
}
