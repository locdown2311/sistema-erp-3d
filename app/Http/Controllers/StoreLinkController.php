<?php

namespace App\Http\Controllers;

use App\Models\StoreLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class StoreLinkController extends Controller
{
    public function index()
    {
        $links = StoreLink::where('user_id', auth()->id())
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('Links/Index', [
            'links' => $links,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $validated['user_id'] = auth()->id();
        StoreLink::create($validated);

        $this->clearCache();

        return redirect()->route('links.index')
            ->with('success', 'Link criado com sucesso!');
    }

    public function update(Request $request, StoreLink $link)
    {
        if ($link->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $link->update($validated);
        
        $this->clearCache();

        return redirect()->route('links.index')
            ->with('success', 'Link atualizado com sucesso!');
    }

    public function destroy(StoreLink $link)
    {
        if ($link->user_id !== auth()->id()) {
            abort(403);
        }

        $link->delete();
        
        $this->clearCache();

        return redirect()->route('links.index')
            ->with('success', 'Link removido!');
    }

    // Optional: for reordering multiple links at once (via API or Inertia form)
    public function updateOrder(Request $request)
    {
        $request->validate([
            'links' => 'required|array',
            'links.*.id' => 'required|exists:store_links,id',
            'links.*.order' => 'required|integer',
        ]);

        foreach ($request->links as $linkData) {
            StoreLink::where('id', $linkData['id'])
                ->where('user_id', auth()->id())
                ->update(['order' => $linkData['order']]);
        }
        
        $this->clearCache();

        return redirect()->route('links.index')
            ->with('success', 'Ordem atualizada!');
    }

    private function clearCache()
    {
        if (auth()->check()) {
            Cache::forget("store." . auth()->user()->slug . ".links");
        }
    }
}
