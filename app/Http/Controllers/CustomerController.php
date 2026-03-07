<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = auth()->user()->customers()->orderBy('name')->get();
        return Inertia::render('Customers/Index', [
            'customers' => $customers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'nullable|string|max:20',
            'ie' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        auth()->user()->customers()->create($validated);

        return redirect()->back()->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'nullable|string|max:20',
            'ie' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $customer->update($validated);

        return redirect()->back()->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if customer has sales
        if ($customer->sales()->count() > 0) {
            return redirect()->back()->with('error', 'Não é possível excluir este cliente pois existem vendas vinculadas a ele.');
        }

        $customer->delete();

        return redirect()->back()->with('success', 'Cliente removido com sucesso!');
    }
}
