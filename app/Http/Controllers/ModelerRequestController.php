<?php

namespace App\Http\Controllers;

use App\Models\ModelerRequest;
use Illuminate\Http\Request;

class ModelerRequestController extends Controller
{
    public function index()
    {
        $modelerRequests = ModelerRequest::latest()->paginate(15);
        return view('modeler_requests.index', compact('modelerRequests'));
    }

    public function create()
    {
        return view('modeler_requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'description' => 'required|string',
            'image' => 'nullable|image|max:5120', // 5MB max
            'budget_range' => 'required|string|max:50',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('modeler_requests', 'public');
        }

        ModelerRequest::create([
            'user_id' => auth()->id(), // null if guest
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'budget_range' => $validated['budget_range'],
        ]);

        return redirect()->route('home')->with('success', 'Sua solicitação foi enviada com sucesso! Um modelador entrará em contato em breve.');
    }

    public function destroy(ModelerRequest $modelerRequest)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        $modelerRequest->delete();

        return back()->with('success', 'Solicitação excluída com sucesso.');
    }
}
