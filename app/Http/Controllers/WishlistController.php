<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::with(['histories' => function($q) {
                                $q->orderBy('created_at', 'asc');
                             }])
                             ->where('user_id', auth()->id())
                             ->orderBy('created_at', 'desc')
                             ->get();
                             
        return view('wishlists.index', compact('wishlists'));
    }

    private function scrapeShopeeData($url)
    {
        $data = [
            'title' => null,
            'price' => null,
            'image_url' => null
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
                    'Accept-Language' => 'pt-BR,pt;q=0.9',
                ])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();

                // 1. Extract Title (og:title)
                if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m) ||
                    preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:title["\']/i', $html, $m) ||
                    preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
                    $title = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
                    $title = preg_replace('/ \| Shopee Brasil$/', '', $title);
                    $data['title'] = preg_replace('/ - Compre na Shopee.*$/', '', $title);
                }

                // 2. Extract Image (og:image)
                if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m) ||
                    preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $html, $m)) {
                    $data['image_url'] = $m[1];
                }

                // 3. Extract Price
                if (preg_match('/"price"\s*:\s*"?([\d\.]+)"?/', $html, $m)) {
                    $data['price'] = (float) $m[1];
                }
                elseif (preg_match('/R\$\s*(\d{1,3}(?:\.\d{3})*,\d{2})/i', preg_replace('/<[^>]+>/', '', $html), $m)) {
                    $priceStr = str_replace('.', '', $m[1]);
                    $priceStr = str_replace(',', '.', $priceStr);
                    $data['price'] = (float) $priceStr;
                }
                elseif (preg_match('/"price"\s*:\s*(\d+)/', $html, $m)) {
                    $val = (float) $m[1];
                    if ($val > 10000) {
                        $data['price'] = $val / 100000;
                    } else {
                        $data['price'] = $val;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Wishlist scraper failed for $url: " . $e->getMessage());
        }
        
        return $data;
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->planLimitReached('wishlists')) {
            $plan = $user->currentPlan();
            $limit = $plan ? $plan->max_wishlists : '?';
            return redirect()->route('plans.index')
                ->with('error', "Você atingiu o limite de {$limit} itens na Lista de Desejos do seu plano. Faça um upgrade!");
        }

        $request->validate([
            'url' => 'required|url|max:2000'
        ]);

        $url = $request->url;
        
        // Check if already in wishlist
        $exists = Wishlist::where('user_id', auth()->id())->where('url', $url)->exists();
        if ($exists) {
            return back()->with('error', 'Este item já está na sua lista de desejos!');
        }

        $data = $this->scrapeShopeeData($url);
        
        $title = $data['title'];
        $price = $data['price'];
        $imageUrl = $data['image_url'];

        // If title wasn't found, try to guess from URL slug
        if (!$title) {
            $parsedPath = parse_url($url, PHP_URL_PATH);
            $title = 'Item Salvo (' . basename($parsedPath) . ')';
        }

        $wishlist = Wishlist::create([
            'user_id' => auth()->id(),
            'url' => $url,
            'title' => Str::limit($title, 250),
            'price' => $price,
            'image_url' => $imageUrl,
            'last_price_update' => $price !== null ? now() : null,
        ]);

        if ($price !== null) {
            $wishlist->histories()->create(['price' => $price]);
        }

        return redirect()->route('wishlists.index')->with('success', 'Produto adicionado à Lista de Desejos!');
    }
    
    public function refreshAll()
    {
        $wishlists = Wishlist::where('user_id', auth()->id())->get();
        if ($wishlists->isEmpty()) {
            return back()->with('error', 'Sua lista está vazia.');
        }

        $updatedCount = 0;
        foreach ($wishlists as $wishlist) {
            $data = $this->scrapeShopeeData($wishlist->url);
            
            // Only update if we successfully scraped a title or price
            if ($data['title'] || $data['price']) {
                $previousPrice = $wishlist->previous_price;
                $newPrice = $data['price'] ?? $wishlist->price;
                
                // If we found a new price and it's different from the current one, update previous_price
                if ($data['price'] !== null && $data['price'] != $wishlist->price) {
                    $previousPrice = $wishlist->price;
                    $wishlist->histories()->create(['price' => $newPrice]);
                }

                $wishlist->update([
                    'title' => $data['title'] ? Str::limit($data['title'], 250) : $wishlist->title,
                    'price' => $newPrice,
                    'previous_price' => $previousPrice,
                    'image_url' => $data['image_url'] ?? $wishlist->image_url,
                    'last_price_update' => $data['price'] !== null ? now() : $wishlist->last_price_update,
                ]);
                
                $updatedCount++;
            }
        }

        return redirect()->route('wishlists.index')->with('success', "{$updatedCount} itens atualizados com sucesso!");
    }

    public function destroy(Wishlist $wishlist)
    {
        if ($wishlist->user_id !== auth()->id()) {
            abort(403);
        }
        
        $wishlist->delete();
        
        return redirect()->route('wishlists.index')->with('success', 'Item removido da lista!');
    }
}
