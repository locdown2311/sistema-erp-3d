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
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,heic,heif,pdf|max:20480', // 20MB max
            'budget_range' => 'required|string|max:50',
        ], [
            'image.max' => 'O arquivo é muito grande. O tamanho máximo permitido é de 2MB.',
            'image.mimes' => 'Formato não suportado. Envie imagens ou PDF.',
            'image.uploaded' => 'Falha ao carregar o arquivo. O arquivo excedeu o limite do servidor (2MB) ou a conexão caiu.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = 'modeler_requests/pedido_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            \Illuminate\Support\Facades\Storage::disk('public')->put($imagePath, file_get_contents($request->file('image')));
        }

        $modelerRequest = ModelerRequest::create([
            'user_id' => auth()->id(), // null if guest
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'budget_range' => $validated['budget_range'],
        ]);

        if ($imagePath) {
            \App\Jobs\UploadImageToPixelDrain::dispatch($modelerRequest, $imagePath, 'image_path');
        }

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
