<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FlexiGeneratorController extends Controller
{
    public function index()
    {
        return view('flexi-generator.index');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'stl_file' => 'required|file|max:51200', // max 50MB
        ]);

        $file = $request->file('stl_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'stl') {
            return response()->json(['error' => 'Apenas arquivos .stl são aceitos.'], 422);
        }

        $contents = file_get_contents($file->getRealPath());

        return response()->json([
            'success' => true,
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'data' => base64_encode($contents),
        ]);
    }
}
