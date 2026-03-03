<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\PixelDrainService;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('user_id', auth()->id())->with('variations', 'stockMovements');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->orderBy('name')->paginate(24)->withQueryString();
        $categories = Product::where('user_id', auth()->id())->distinct()->whereNotNull('category')->pluck('category');

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $user = auth()->user();
        $plan = $user->currentPlan();

        if ($plan && $plan->max_products !== null) {
            $productCount = $user->products()->count();
            if ($productCount >= $plan->max_products) {
                return redirect()->route('plans.index')
                    ->with('error', "Você atingiu o limite de {$plan->max_products} produtos do seu plano. Faça um upgrade para continuar crescendo!");
            }
        }

        $categories = Product::where('user_id', auth()->id())->distinct()->whereNotNull('category')->pluck('category');
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $plan = $user->currentPlan();

        if ($plan && $plan->max_products !== null) {
            $productCount = $user->products()->count();
            if ($productCount >= $plan->max_products) {
                return redirect()->route('plans.index')
                    ->with('error', 'Limite de produtos atingido. Faça um upgrade no seu plano.');
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'base_cost' => 'required|numeric|min:0',
            'print_time_hours' => 'nullable|numeric|min:0',
            'weight_grams' => 'nullable|numeric|min:0',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['print_time_hours'] = $validated['print_time_hours'] ?? 0;
        $validated['weight_grams'] = $validated['weight_grams'] ?? 0;

        $localPath = null;
        if ($request->filled('cropped_image')) {
            $data = explode(',', $request->cropped_image, 2);
            $imageData = base64_decode($data[1] ?? $data[0]);
            $localPath = 'products/' . uniqid() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($localPath, $imageData);
            $validated['image_path'] = $localPath;
        } elseif ($request->hasFile('image')) {
            $localPath = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $localPath;
        }

        $product = Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produto criado com sucesso!');
    }

    public function show(Product $product)
    {
        $this->authorizeProduct($product);
        $product->load('variations', 'stockMovements', 'printCosts');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);
        $product->load('variations');
        $categories = Product::where('user_id', auth()->id())->distinct()->whereNotNull('category')->pluck('category');
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'base_cost' => 'required|numeric|min:0',
            'print_time_hours' => 'nullable|numeric|min:0',
            'weight_grams' => 'nullable|numeric|min:0',
        ]);

        $validated['print_time_hours'] = $validated['print_time_hours'] ?? 0;
        $validated['weight_grams'] = $validated['weight_grams'] ?? 0;

        $localPath = null;
        if ($request->filled('cropped_image')) {
            $data = explode(',', $request->cropped_image, 2);
            $imageData = base64_decode($data[1] ?? $data[0]);
            $localPath = 'products/' . uniqid() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($localPath, $imageData);
            $validated['image_path'] = $localPath;
        } elseif ($request->hasFile('image')) {
            $localPath = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $localPath;
        }

        $validated['active'] = $request->has('active');
        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Produto removido!');
    }

    public function addVariation(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:color,size,material',
            'price_modifier' => 'nullable|numeric',
            'cost_modifier' => 'nullable|numeric',
            'sku' => 'nullable|string|max:100',
        ]);

        $product->variations()->create($validated);

        return redirect()->route('products.edit', $product)
            ->with('success', 'Variação adicionada!');
    }

    public function destroyVariation(Product $product, ProductVariation $variation)
    {
        $this->authorizeProduct($product);
        $variation->delete();
        return redirect()->route('products.edit', $product)
            ->with('success', 'Variação removida!');
    }

    private function authorizeProduct(Product $product): void
    {
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }
    }

    public function imageStatus(Product $product)
    {
        $this->authorizeProduct($product);
        $isPending = $product->image_path && str_starts_with($product->image_path, 'products/tmp_');
        return response()->json([
            'thumbnail_url' => $product->thumbnail_url,
            'is_pending' => $isPending
        ]);
    }

}
