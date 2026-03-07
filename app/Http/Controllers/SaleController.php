<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::where('user_id', auth()->id())->with('items.product');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        $sales = $query->orderBy('sale_date', 'desc')->paginate(15);
        $canExportReports = auth()->user()->currentPlan()?->can_export_reports ?? false;
        
        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only(['status', 'date_from', 'date_to']),
            'canExportReports' => $canExportReports
        ]);
    }

    public function reportPdf(Request $request)
    {
        if (!(auth()->user()->currentPlan()?->can_export_reports ?? false)) {
            abort(403, 'A exportação de relatórios em PDF requer um plano Premium (Basic ou Pro).');
        }

        $query = Sale::where('user_id', auth()->id())->with('items.product');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();
        $filters = $request->only(['status', 'date_from', 'date_to']);

        // ── KPIs ──────────────────────────────────────────────
        $totalSum       = $sales->sum('total');
        $totalCompleted = $sales->where('status', 'completed')->sum('total');
        $totalPending   = $sales->where('status', 'pending')->sum('total');
        $totalCancelled = $sales->where('status', 'cancelled')->sum('total');
        $countCompleted = $sales->where('status', 'completed')->count();
        $countPending   = $sales->where('status', 'pending')->count();
        $countCancelled = $sales->where('status', 'cancelled')->count();
        $totalItems     = $sales->sum(fn($s) => $s->items->sum('quantity'));
        $avgTicket      = $sales->count() > 0 ? $totalSum / $sales->count() : 0;

        // ── Top 5 produtos vendidos ───────────────────────────
        $productRanking = $sales->flatMap(fn($s) => $s->items)
            ->groupBy(fn($item) => $item->product->name ?? 'Removido')
            ->map(fn($group) => [
                'qty'   => $group->sum('quantity'),
                'total' => $group->sum(fn($i) => $i->quantity * $i->unit_price),
            ])
            ->sortByDesc('total')
            ->take(5);

        // ── Dados da empresa ──────────────────────────────────
        $user      = auth()->user();
        $storeName = $user->store_name ?? $user->name;
        $logoUrl   = $user->store_logo_url;

        $pdf = Pdf::loadView('reports.sales_pdf', compact(
            'sales', 'filters', 'storeName', 'logoUrl',
            'totalSum', 'totalCompleted', 'totalPending', 'totalCancelled',
            'countCompleted', 'countPending', 'countCancelled',
            'totalItems', 'avgTicket', 'productRanking'
        ));

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('relatorio_vendas.pdf');
    }

    public function create()
    {
        $products = Product::where('user_id', auth()->id())->where('active', true)->with('variations')->orderBy('name')->get();
        $customers = auth()->user()->customers()->orderBy('name')->get();
        return Inertia::render('Sales/Create', [
            'products' => $products,
            'customers' => $customers
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->planLimitReached('sales')) {
            $plan = $user->currentPlan();
            $limit = $plan ? $plan->max_sales_per_month : '?';
            return redirect()->route('plans.index')
                ->with('error', "Você atingiu o limite de {$limit} vendas por mês do seu plano. Faça um upgrade!");
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tracking_code' => 'nullable|string|max:255',
        ]);

        $total = collect($validated['items'])->sum(fn($item) => $item['quantity'] * $item['unit_price']);
        
        $customer = auth()->user()->customers()->findOrFail($validated['customer_id']);

        $sale = Sale::create([
            'user_id' => auth()->id(),
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'sale_date' => $validated['sale_date'],
            'notes' => $validated['notes'] ?? null,
            'total' => $total,
            'status' => 'completed',
            'tracking_code' => $validated['tracking_code'] ?? null,
            'shipping_status' => !empty($validated['tracking_code']) ? 'Pendente' : null,
        ]);

        foreach ($validated['items'] as $item) {
            $sale->items()->create($item);

            // Auto deduct stock
            StockMovement::create([
                'user_id' => auth()->id(),
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? null,
                'type' => 'out',
                'quantity' => $item['quantity'],
                'notes' => 'Venda #' . $sale->id,
            ]);
        }

        return redirect()->route('sales.index')
            ->with('success', 'Venda registrada com sucesso!');
    }

    public function show(Sale $sale)
    {
        if ($sale->user_id !== auth()->id()) abort(403);
        $sale->load('items.product', 'items.variation');
        return Inertia::render('Sales/Show', [
            'sale' => $sale
        ]);
    }

    public function destroy(Sale $sale)
    {
        if ($sale->user_id !== auth()->id()) abort(403);
        $sale->delete();
        return redirect()->route('sales.index')
            ->with('success', 'Venda removida com sucesso!');
    }

    public function updateTracking(Request $request, Sale $sale)
    {
        if ($sale->user_id !== auth()->id()) abort(403);

        $validated = $request->validate([
            'tracking_code' => 'nullable|string|max:255',
            'shipping_status' => 'nullable|string|in:Pendente,Em Trânsito,Entregue,Devolvido',
        ]);

        $sale->update([
            'tracking_code' => $validated['tracking_code'],
            'shipping_status' => $validated['shipping_status'],
        ]);

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Informações de rastreamento atualizadas com sucesso!');
    }
}
