<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\FilamentController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModelerRequestController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AdminUserController;

// Public Landing Page (Marketplace Hub)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Modeler Request routes
Route::get('procurar-modelador', [ModelerRequestController::class, 'create'])->name('modeler-requests.create');
Route::post('procurar-modelador', [ModelerRequestController::class, 'store'])->name('modeler-requests.store');

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Public store routes (anyone can view)
Route::get('loja/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::get('loja/{slug}/ofertas', [StoreController::class, 'offers'])->name('store.offers');
Route::get('loja/{slug}/produto/{product}', [StoreController::class, 'product'])->name('store.product');
Route::get('loja/{slug}/latest-offer', [StoreController::class, 'latestOffer'])->name('store.latest-offer');

// Authenticated ERP routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // NFe Prototype routes
    Route::get('nfe', [\App\Http\Controllers\NFeController::class, 'index'])->name('nfe.index');
    Route::post('nfe/emit', [\App\Http\Controllers\NFeController::class, 'emit'])->name('nfe.emit');

    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');

    Route::resource('products', ProductController::class);
    Route::post('products/{product}/variations', [ProductController::class, 'addVariation'])->name('products.variations.store');
    Route::delete('products/{product}/variations/{variation}', [ProductController::class, 'destroyVariation'])->name('products.variations.destroy');
    Route::get('products/{product}/image-status', [ProductController::class, 'imageStatus'])->name('products.image-status');

    Route::get('filaments', [FilamentController::class, 'index'])->name('filaments.index');
    Route::post('filaments', [FilamentController::class, 'store'])->name('filaments.store');
    Route::put('filaments/{filament}', [FilamentController::class, 'update'])->name('filaments.update');
    Route::delete('filaments/{filament}', [FilamentController::class, 'destroy'])->name('filaments.destroy');
    Route::post('filaments/{filament}/consume', [FilamentController::class, 'consume'])->name('filaments.consume');

    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('stock', [StockController::class, 'store'])->name('stock.store');

    Route::resource('sales', SaleController::class);

    Route::get('costs', [CostController::class, 'index'])->name('costs.index');
    Route::post('costs/calculate', [CostController::class, 'calculate'])->name('costs.calculate');
    Route::post('costs', [CostController::class, 'store'])->name('costs.store');
    Route::get('pedidos-modelagem', [ModelerRequestController::class, 'index'])->name('modeler-requests.index');

    // Tracking SeuRastreio API interno da view
    Route::get('api/tracking/{code}', [\App\Http\Controllers\TrackingController::class, 'track'])->name('api.tracking');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/tasks', [CalendarController::class, 'tasks'])->name('calendar.tasks');
    Route::post('calendar/tasks', [CalendarController::class, 'storeTask'])->name('calendar.tasks.store');
    Route::put('calendar/tasks/{task}', [CalendarController::class, 'updateTask'])->name('calendar.tasks.update');
    Route::delete('calendar/tasks/{task}', [CalendarController::class, 'destroyTask'])->name('calendar.tasks.destroy');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

    // Admin: Offers management
    Route::resource('offers', OfferController::class);
    Route::post('offers/fetch-meta', [OfferController::class, 'fetchMeta'])->name('offers.fetch-meta');

    // Admin: Modeler Requests
    Route::delete('modeler-requests/{modelerRequest}', [ModelerRequestController::class, 'destroy'])->name('modeler-requests.destroy');

    // Admin: User Management
    Route::get('panel/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::put('panel/users/{user}/plan', [AdminUserController::class, 'updatePlan'])->name('admin.users.update-plan');
    Route::put('panel/users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('admin.users.suspend');
    Route::put('panel/users/{user}/unsuspend', [AdminUserController::class, 'unsuspend'])->name('admin.users.unsuspend');
    Route::delete('panel/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

    // Wishlists (User specific)
    Route::post('wishlists/refresh-all', [WishlistController::class, 'refreshAll'])->name('wishlists.refresh-all');
    Route::resource('wishlists', WishlistController::class)->only(['index', 'store', 'destroy']);

    // Plans & Subscriptions
    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('subscriptions/callback', [SubscriptionController::class, 'callback'])->name('subscriptions.callback');
});

// Webhook do Mercado Pago (sem autenticação)
Route::post('webhooks/mercadopago', [SubscriptionController::class, 'webhook'])->name('subscriptions.webhook');
