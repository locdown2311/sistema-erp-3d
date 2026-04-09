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
use App\Http\Controllers\FlexiGeneratorController;

// Página Inicial Pública (Marketplace Hub)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Páginas Legais
Route::view('termos-de-uso', 'legal.terms')->name('legal.terms');
Route::view('politica-de-privacidade', 'legal.privacy')->name('legal.privacy');

// Rotas de Pedido de Modelagem (públicas)
Route::get('procurar-modelador', [ModelerRequestController::class, 'create'])->name('modeler-requests.create');
Route::post('procurar-modelador', [ModelerRequestController::class, 'store'])->name('modeler-requests.store');

// Rotas de autenticação (somente visitantes)
Route::middleware('guest')->group(function () {
    Route::get('entrar', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('entrar', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('cadastro', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('cadastro', [RegisterController::class, 'register']);
});

Route::post('sair', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Rotas públicas da loja (qualquer visitante)
Route::get('loja/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::get('loja/{slug}/links', [StoreController::class, 'links'])->name('store.links');
Route::get('loja/{slug}/ofertas', [StoreController::class, 'offers'])->name('store.offers');
Route::get('loja/{slug}/produto/{product}', [StoreController::class, 'product'])->name('store.product');
Route::get('loja/{slug}/oferta-recente', [StoreController::class, 'latestOffer'])->name('store.latest-offer');

// Rotas autenticadas do ERP
Route::middleware('auth')->group(function () {
    Route::get('/painel', [DashboardController::class, 'index'])->name('dashboard');

    // Clientes
    Route::resource('clientes', \App\Http\Controllers\CustomerController::class)
        ->names('customers')
        ->parameters(['clientes' => 'customer'])
        ->except(['create', 'edit', 'show']);

    // NFe - Protótipo
    Route::get('nfe', [\App\Http\Controllers\NFeController::class, 'index'])->name('nfe.index');
    Route::post('nfe/emitir', [\App\Http\Controllers\NFeController::class, 'emit'])->name('nfe.emit');
    Route::get('nfe/download/{file}', [\App\Http\Controllers\NFeController::class, 'download'])->name('nfe.download');

    // Planos e Assinaturas
    Route::get('planos', [PlanController::class, 'index'])->name('plans.index');
    Route::post('assinaturas', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('assinaturas/sucesso', [SubscriptionController::class, 'success'])->name('subscriptions.success');

    // Produtos
    Route::resource('produtos', ProductController::class)->names('products')->parameters(['produtos' => 'product']);
    Route::post('produtos/{product}/variacoes', [ProductController::class, 'addVariation'])->name('products.variations.store');
    Route::delete('produtos/{product}/variacoes/{variation}', [ProductController::class, 'destroyVariation'])->name('products.variations.destroy');
    Route::get('produtos/{product}/status-imagem', [ProductController::class, 'imageStatus'])->name('products.image-status');

    // Filamentos
    Route::get('filamentos', [FilamentController::class, 'index'])->name('filaments.index');
    Route::post('filamentos', [FilamentController::class, 'store'])->name('filaments.store');
    Route::put('filamentos/{filament}', [FilamentController::class, 'update'])->name('filaments.update');
    Route::delete('filamentos/{filament}', [FilamentController::class, 'destroy'])->name('filaments.destroy');
    Route::post('filamentos/{filament}/consumir', [FilamentController::class, 'consume'])->name('filaments.consume');

    // Estoque
    Route::get('estoque', [StockController::class, 'index'])->name('stock.index');
    Route::post('estoque', [StockController::class, 'store'])->name('stock.store');

    // Vendas
    Route::get('vendas/relatorio/pdf', [SaleController::class, 'reportPdf'])->name('sales.report.pdf');
    Route::put('vendas/{sale}/rastreio', [SaleController::class, 'updateTracking'])->name('sales.tracking.update');
    Route::resource('vendas', SaleController::class)->names('sales')->parameters(['vendas' => 'sale']);

    // Custos
    Route::get('custos', [CostController::class, 'index'])->name('costs.index');
    Route::post('custos/calcular', [CostController::class, 'calculate'])->name('costs.calculate');
    Route::post('custos', [CostController::class, 'store'])->name('costs.store');
    Route::delete('custos/{cost}', [CostController::class, 'destroy'])->name('costs.destroy');

    // Pedidos de Modelagem (autenticado)
    Route::get('pedidos-modelagem', [ModelerRequestController::class, 'index'])->name('modeler-requests.index');
    Route::delete('pedidos-modelagem/{modelerRequest}', [ModelerRequestController::class, 'destroy'])->name('modeler-requests.destroy');

    // Rastreio interno (API AJAX)
    Route::get('api/rastreio/{code}', [\App\Http\Controllers\TrackingController::class, 'track'])->name('api.tracking');

    // Calendário
    Route::get('calendario', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendario/tarefas', [CalendarController::class, 'tasks'])->name('calendar.tasks');
    Route::post('calendario/tarefas', [CalendarController::class, 'storeTask'])->name('calendar.tasks.store');
    Route::put('calendario/tarefas/{task}', [CalendarController::class, 'updateTask'])->name('calendar.tasks.update');
    Route::delete('calendario/tarefas/{task}', [CalendarController::class, 'destroyTask'])->name('calendar.tasks.destroy');

    // Configurações
    Route::get('configuracoes', [SettingController::class, 'index'])->name('settings.index');
    Route::post('configuracoes', [SettingController::class, 'update'])->name('settings.update');

    // Links (Árvore de Links)
    Route::post('links/reorder', [\App\Http\Controllers\StoreLinkController::class, 'updateOrder'])->name('links.reorder');
    Route::resource('links', \App\Http\Controllers\StoreLinkController::class)->except(['create', 'edit', 'show']);

    // Admin: Gestão de Ofertas
    Route::resource('ofertas', OfferController::class)->names('offers')->parameters(['ofertas' => 'offer']);
    Route::post('ofertas/buscar-meta', [OfferController::class, 'fetchMeta'])->name('offers.fetch-meta');

    // Admin: Gestão de Usuários
    Route::get('painel/usuarios', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::put('painel/usuarios/{user}/plano', [AdminUserController::class, 'updatePlan'])->name('admin.users.update-plan');
    Route::put('painel/usuarios/{user}/flexi-cuts', [AdminUserController::class, 'updateFlexiCuts'])->name('admin.users.update-flexi-cuts');
    Route::put('painel/usuarios/{user}/suspender', [AdminUserController::class, 'suspend'])->name('admin.users.suspend');
    Route::put('painel/usuarios/{user}/reativar', [AdminUserController::class, 'unsuspend'])->name('admin.users.unsuspend');
    Route::delete('painel/usuarios/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

    // Lista de Desejos
    Route::post('lista-desejos/atualizar-todos', [WishlistController::class, 'refreshAll'])->name('wishlists.refresh-all');
    Route::resource('lista-desejos', WishlistController::class)->names('wishlists')->parameters(['lista-desejos' => 'wishlist'])->only(['index', 'store', 'destroy']);

    // Gerador Flexi
    Route::get('gerador-flexi', [FlexiGeneratorController::class, 'index'])->name('flexi-generator.index');
    Route::post('gerador-flexi/enviar', [FlexiGeneratorController::class, 'upload'])->name('flexi-generator.upload');
    Route::post('gerador-flexi/rastrear-uso', [FlexiGeneratorController::class, 'trackUsage'])->name('flexi-generator.track-usage');
});

// Webhook do Stripe (sem autenticação)
Route::post('webhooks/stripe', [SubscriptionController::class, 'webhook'])->name('subscriptions.webhook');
