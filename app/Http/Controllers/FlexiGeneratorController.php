<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;

class FlexiGeneratorController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $usage = $user->getPlanUsage('flexi_cuts');
        
        $currentPlan = $user->currentPlan();
        $canUploadCutter = $currentPlan ? in_array($currentPlan->slug, ['basic', 'pro']) : false;

        return Inertia::render('FlexiGenerator/Index', [
            'flexi_current' => $usage['current'],
            'flexi_limit' => $usage['limit'],
            'canUploadCutter' => $canUploadCutter,
        ]);
    }

    public function trackUsage(Request $request)
    {
        $user = auth()->user();

        if ($user->planLimitReached('flexi_cuts')) {
            return response()->json([
                'allowed' => false,
                'message' => 'Você atingiu o limite de cortes do seu plano gratuito. Faça upgrade para continuar utilizando o gerador!',
            ]);
        }

        // Increment usage
        $user->increment('flexi_cuts_count');
        
        $usage = $user->getPlanUsage('flexi_cuts');

        return response()->json([
            'allowed' => true,
            'current' => $usage['current'],
            'limit' => $usage['limit'],
        ]);
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
