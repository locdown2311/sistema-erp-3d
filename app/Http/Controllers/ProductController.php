<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $products = $query->orderBy('name')->get();
        $categories = Product::where('user_id', auth()->id())->distinct()->whereNotNull('category')->pluck('category');

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Product::where('user_id', auth()->id())->distinct()->whereNotNull('category')->pluck('category');
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
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

        if ($request->filled('cropped_image')) {
            $validated['image_path'] = $this->saveBase64Image($request->cropped_image);
        } elseif ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

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

        if ($request->filled('cropped_image')) {
            $validated['image_path'] = $this->saveBase64Image($request->cropped_image);
        } elseif ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
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

    private function saveBase64Image(string $base64): string
    {
        $data = explode(',', $base64, 2);
        $imageData = base64_decode($data[1] ?? $data[0]);

        $ext = 'jpg';
        if (isset($data[0]) && str_contains($data[0], 'png')) $ext = 'png';
        elseif (isset($data[0]) && str_contains($data[0], 'webp')) $ext = 'webp';

        $filename = 'products/' . Str::random(40) . '.' . $ext;
        Storage::disk('public')->put($filename, $imageData);

        return $filename;
    }
}
