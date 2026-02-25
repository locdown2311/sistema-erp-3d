<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Task;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $totalProducts = Product::where('user_id', $userId)->where('active', true)->count();
        $totalSales = Sale::where('user_id', $userId)->where('status', 'completed')->count();
        $totalRevenue = Sale::where('user_id', $userId)->where('status', 'completed')->sum('total');
        $pendingTasks = Task::where('user_id', $userId)->where('status', '!=', 'completed')->count();

        // Sales last 30 days for chart
        $salesChart = Sale::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('sale_date', '>=', now()->subDays(30))
            ->selectRaw("DATE(sale_date) as date, SUM(total) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent sales
        $recentSales = Sale::where('user_id', $userId)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Low stock products
        $products = Product::where('user_id', $userId)->where('active', true)->with('stockMovements')->get();
        $lowStock = $products->filter(fn($p) => $p->current_stock <= 5)->take(5);

        // Upcoming tasks
        $upcomingTasks = Task::where('user_id', $userId)
            ->where('status', '!=', 'completed')
            ->where('due_date', '>=', today())
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Modeler Requests for admins and users
        $modelerRequests = \App\Models\ModelerRequest::latest()->take(10)->get();

        // Active Shippings
        $activeShippings = \App\Models\Sale::where('user_id', $userId)
            ->whereNotNull('tracking_code')
            ->where('shipping_status', '!=', 'Entregue')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('dashboard.index', compact(
            'totalProducts', 'totalSales', 'totalRevenue', 'pendingTasks',
            'salesChart', 'recentSales', 'lowStock', 'upcomingTasks', 'modelerRequests', 'activeShippings'
        ));
    }
}
