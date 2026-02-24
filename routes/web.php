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

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Public store routes (anyone can view)
Route::get('loja/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::get('loja/{slug}/ofertas', [StoreController::class, 'offers'])->name('store.offers');
Route::get('loja/{slug}/produto/{product}', [StoreController::class, 'product'])->name('store.product');

// Authenticated ERP routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductController::class);
    Route::post('products/{product}/variations', [ProductController::class, 'addVariation'])->name('products.variations.store');
    Route::delete('products/{product}/variations/{variation}', [ProductController::class, 'destroyVariation'])->name('products.variations.destroy');

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
    Route::delete('costs/{cost}', [CostController::class, 'destroy'])->name('costs.destroy');

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
});
