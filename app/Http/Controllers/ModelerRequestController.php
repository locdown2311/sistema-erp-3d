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
            'images' => 'nullable|array|max:5',
            'images.*' => 'file|mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,heic,heif,pdf|max:20480',
            'budget_range' => 'required|string|max:50',
        ], [
            'images.max' => 'Você pode enviar no máximo 5 arquivos.',
            'images.*.max' => 'Cada arquivo pode ter no máximo 20MB.',
            'images.*.mimes' => 'Formato não suportado. Envie imagens ou PDF.',
            'images.*.uploaded' => 'Falha ao carregar o arquivo. O arquivo excedeu o limite do servidor ou a conexão caiu.',
        ]);

        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = 'modeler_requests/pedido_' . uniqid() . '.' . $file->getClientOriginalExtension();
                \Illuminate\Support\Facades\Storage::disk('public')->put($path, file_get_contents($file));
                $imagePaths[] = $path;
            }
        }

        $modelerRequest = ModelerRequest::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'description' => $validated['description'],
            'image_path' => !empty($imagePaths) ? $imagePaths : null,
            'budget_range' => $validated['budget_range'],
        ]);

        // Dispatch PixelDrain upload for each image
        foreach ($imagePaths as $index => $path) {
            \App\Jobs\UploadImageToPixelDrain::dispatch($modelerRequest, $path, 'image_path', $index);
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
