<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class OfferController extends Controller
{
    public function __construct()
    {
        // All methods require admin
    }

    private function authorizeAdmin(): void
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Acesso restrito a administradores.');
        }
    }

    public function index()
    {
        $this->authorizeAdmin();
        $offers = Offer::orderBy('created_at', 'desc')->get();
        return view('offers.index', compact('offers'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        return view('offers.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'affiliate_url' => 'required|url|max:2000',
            'category' => 'nullable|string|max:100',
            'active' => 'sometimes|boolean',
        ]);

        // Handle image
        $localPath = null;
        if ($request->filled('cropped_image')) {
            $localPath = $this->saveBase64Image($request->cropped_image);
            $validated['image_path'] = $localPath;
        } elseif ($request->hasFile('image')) {
            $pixelDrain = app(\App\Services\PixelDrainService::class);
            $url = $pixelDrain->uploadFile($request->file('image'));
            if ($url) {
                $validated['image_path'] = str_replace('https://pixeldrain.com/api/file/', '', $url);
            }
        } elseif ($request->filled('og_image_url')) {
            $localPath = $this->downloadRemoteImage($request->og_image_url);
            $validated['image_path'] = $localPath;
        }

        $validated['active'] = $request->has('active');

        $offer = Offer::create($validated);

        return redirect()->route('offers.index')
            ->with('success', 'Oferta criada com sucesso!');
    }

    public function edit(Offer $offer)
    {
        $this->authorizeAdmin();
        return view('offers.edit', compact('offer'));
    }

    public function update(Request $request, Offer $offer)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'affiliate_url' => 'required|url|max:2000',
            'category' => 'nullable|string|max:100',
            'active' => 'sometimes|boolean',
        ]);

        $localPath = null;
        if ($request->filled('cropped_image')) {
            $localPath = $this->saveBase64Image($request->cropped_image);
            $validated['image_path'] = $localPath;
        } elseif ($request->hasFile('image')) {
            $pixelDrain = app(\App\Services\PixelDrainService::class);
            $url = $pixelDrain->uploadFile($request->file('image'));
            if ($url) {
                $validated['image_path'] = str_replace('https://pixeldrain.com/api/file/', '', $url);
            }
        }

        $validated['active'] = $request->has('active');

        $offer->update($validated);

        return redirect()->route('offers.index')
            ->with('success', 'Oferta atualizada!');
    }

    public function destroy(Offer $offer)
    {
        $this->authorizeAdmin();
        $offer->delete();
        return redirect()->route('offers.index')
            ->with('success', 'Oferta removida!');
    }

    private function saveBase64Image(string $base64): string
    {
        $pixelDrain = app(\App\Services\PixelDrainService::class);
        $url = $pixelDrain->uploadBase64($base64, uniqid() . '.png');
        
        if ($url) {
            return str_replace('https://pixeldrain.com/api/file/', '', $url);
        }
        
        throw new \Exception('Falha ao enviar imagem da oferta para o Pixeldrain');
    }

    private function downloadRemoteImage(string $url): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; bot)'])
                ->get($url);

            if (!$response->successful()) return null;

            $ext = 'jpg';
            $contentType = $response->header('Content-Type');
            if (str_contains($contentType, 'png')) $ext = 'png';
            elseif (str_contains($contentType, 'webp')) $ext = 'webp';

            $base64 = 'data:' . ($contentType ?? 'image/jpeg') . ';base64,' . base64_encode($response->body());

            $pixelDrain = app(\App\Services\PixelDrainService::class);
            $url = $pixelDrain->uploadBase64($base64, uniqid() . '.' . $ext);
            
            if ($url) {
                return str_replace('https://pixeldrain.com/api/file/', '', $url);
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function fetchMeta(Request $request)
    {
        $this->authorizeAdmin();

        $url = $request->input('url');
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'URL inválida'], 422);
        }

        try {
            // Use facebookexternalhit UA so SPAs (like Shopee) serve pre-rendered HTML with og: tags
            $response = Http::timeout(12)
                ->withHeaders([
                    'User-Agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
                    'Accept' => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'pt-BR,pt;q=0.9',
                ])
                ->withOptions(['allow_redirects' => ['max' => 5]])
                ->get($url);

            $html = $response->body();
            $meta = [];

            // Parse og:image
            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                $meta['image'] = $m[1];
            } elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $html, $m)) {
                $meta['image'] = $m[1];
            }

            // Parse og:title
            if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                $meta['title'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            } elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:title["\']/i', $html, $m)) {
                $meta['title'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            }

            // Parse og:description
            if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                $meta['description'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            } elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:description["\']/i', $html, $m)) {
                $meta['description'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            }

            return response()->json($meta);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível acessar a URL: ' . $e->getMessage()], 500);
        }
    }
}
