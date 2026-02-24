<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'store_name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:users,slug|regex:/^[a-z0-9\-]+$/',
            'whatsapp' => 'nullable|string|max:20',
            'store_description' => 'nullable|string|max:1000',
            'captcha' => 'required|captcha',
        ], [
            'captcha.required' => 'Por favor, informe o código da imagem.',
            'captcha.captcha' => 'O código de verificação está incorreto.',
        ]);

        // Handle store logo (base64 from crop or file upload)
        $logoPath = null;
        if ($request->filled('cropped_image')) {
            $logoPath = $this->saveBase64Image($request->cropped_image);
        } elseif ($request->hasFile('store_logo')) {
            $logoPath = $request->file('store_logo')->store('logos', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'store_name' => $validated['store_name'],
            'slug' => $validated['slug'],
            'whatsapp' => $validated['whatsapp'],
            'store_description' => $validated['store_description'] ?? null,
            'store_logo' => $logoPath,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Bem-vindo! Sua loja foi criada com sucesso.');
    }

    private function saveBase64Image(string $base64): string
    {
        $data = explode(',', $base64, 2);
        $imageData = base64_decode($data[1] ?? $data[0]);

        $ext = 'jpg';
        if (isset($data[0]) && str_contains($data[0], 'png')) $ext = 'png';
        elseif (isset($data[0]) && str_contains($data[0], 'webp')) $ext = 'webp';

        $filename = 'logos/' . Str::random(40) . '.' . $ext;
        Storage::disk('public')->put($filename, $imageData);

        return $filename;
    }
}
