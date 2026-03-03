@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm flex items-center gap-4 transition-all hover:border-zinc-300 dark:hover:border-zinc-700 hover:shadow-md relative overflow-hidden group">
        <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-300 dark:bg-zinc-700"></div>
        <div class="w-12 h-12 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xl text-zinc-600 dark:text-zinc-400 flex-shrink-0 group-hover:scale-110 transition-transform">
            <i class="fas fa-boxes-stacked"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider truncate">Produtos Ativos</div>
            <div class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5 truncate">{{ $totalProducts }}</div>
        </div>
    </div>
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm flex items-center gap-4 transition-all hover:border-zinc-300 dark:hover:border-zinc-700 hover:shadow-md relative overflow-hidden group">
        <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-300 dark:bg-zinc-700"></div>
        <div class="w-12 h-12 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xl text-zinc-600 dark:text-zinc-400 flex-shrink-0 group-hover:scale-110 transition-transform">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider truncate">Total de Vendas</div>
            <div class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5 truncate">{{ $totalSales }}</div>
        </div>
    </div>
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm flex items-center gap-4 transition-all hover:border-zinc-300 dark:hover:border-zinc-700 hover:shadow-md relative overflow-hidden group">
        <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-300 dark:bg-zinc-700"></div>
        <div class="w-12 h-12 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xl text-zinc-600 dark:text-zinc-400 flex-shrink-0 group-hover:scale-110 transition-transform">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider truncate">Receita Total</div>
            <div class="text-xl font-bold text-zinc-900 dark:text-white mt-0.5 truncate">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm flex items-center gap-4 transition-all hover:border-zinc-300 dark:hover:border-zinc-700 hover:shadow-md relative overflow-hidden group">
        <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-300 dark:bg-zinc-700"></div>
        <div class="w-12 h-12 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xl text-zinc-600 dark:text-zinc-400 flex-shrink-0 group-hover:scale-110 transition-transform">
            <i class="fas fa-list-check"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider truncate">Tarefas Pendentes</div>
            <div class="text-2xl font-bold text-zinc-900 dark:text-white mt-0.5 truncate">{{ $pendingTasks }}</div>
        </div>
    </div>
</div>

{{-- Plan Usage Card --}}
@if(isset($currentPlan))
<div class="mb-8 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <i class="fas fa-gauge-high text-indigo-500"></i>
            <h3 class="font-semibold text-zinc-900 dark:text-white text-sm">Uso do Plano — <span class="text-indigo-500">{{ $currentPlan->name }}</span></h3>
        </div>
        <a href="{{ route('plans.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
            @if($currentPlan->slug === 'free') <i class="fas fa-bolt mr-1"></i>Fazer Upgrade @else Ver planos @endif
        </a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @foreach(['products' => ['Produtos', 'fa-boxes-stacked', 'emerald'], 'sales' => ['Vendas/mês', 'fa-cash-register', 'blue'], 'wishlists' => ['Wishlists', 'fa-heart', 'pink']] as $key => [$label, $icon, $color])
            @php
                $u = $planUsage[$key];
                $pct = $u['limit'] ? min(round(($u['current'] / $u['limit']) * 100), 100) : 0;
                $isNearLimit = $u['limit'] && $pct >= 80;
            @endphp
            <div class="flex items-center gap-3 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800">
                <i class="fas {{ $icon }} text-{{ $color }}-500 text-lg"></i>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-zinc-500 dark:text-zinc-400">{{ $label }}</span>
                        <span class="font-bold {{ $isNearLimit ? 'text-amber-500' : 'text-zinc-700 dark:text-zinc-300' }}">{{ $u['current'] }}/{{ $u['limit'] ?? '∞' }}</span>
                    </div>
                    @if($u['limit'])
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full transition-all duration-500 {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-400' : 'bg-'.$color.'-500') }}" style="width: {{ $pct }}%"></div>
                        </div>
                    @else
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-emerald-500/30 w-full"></div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Sales Chart --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 sm:p-6 shadow-sm lg:col-span-2 flex flex-col">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-zinc-100 dark:border-zinc-800">
            <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-chart-line text-zinc-400"></i> Vendas — Últimos 30 dias
            </h3>
        </div>
        <div class="flex-1 min-h-[300px] w-full relative">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Low Stock --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-amber-500"></i> Estoque Baixo
                </h3>
            </div>
            @if($lowStock->count() > 0)
                <div class="space-y-3">
                @foreach($lowStock as $product)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-zinc-700 dark:text-zinc-300 truncate pr-2">{{ $product->name }}</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full whitespace-nowrap {{ $product->current_stock <= 0 ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">
                            {{ $product->current_stock }} un.
                        </span>
                    </div>
                @endforeach
                </div>
            @else
                <p class="text-sm text-zinc-500 py-2">Nenhum produto com estoque baixo</p>
            @endif
        </div>

        {{-- Upcoming Tasks --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-calendar-check text-blue-500"></i> Próximas Tarefas
                </h3>
            </div>
            @if($upcomingTasks->count() > 0)
                <div class="space-y-3">
                @foreach($upcomingTasks as $task)
                    <div class="flex justify-between items-center bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg border border-zinc-100 dark:border-zinc-800">
                        <div class="min-w-0 pr-2">
                            <div class="font-medium text-sm text-zinc-900 dark:text-white truncate">{{ $task->title }}</div>
                            <div class="text-xs text-zinc-500 mt-0.5">{{ $task->due_date->format('d/m/Y') }}</div>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full flex-shrink-0 
                            {{ $task->priority === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' : 
                               ($task->priority === 'medium' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' : 
                               'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400') }}">
                            {{ $task->priority === 'high' ? 'Alta' : ($task->priority === 'medium' ? 'Média' : 'Baixa') }}
                        </span>
                    </div>
                @endforeach
                </div>
            @else
                <p class="text-sm text-zinc-500 py-2">Nenhuma tarefa pendente</p>
            @endif
        </div>
    </div>
</div>

{{-- Recent Sales --}}
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="p-5 sm:p-6 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-receipt text-zinc-400"></i> Vendas Recentes
        </h3>
        <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors w-full sm:w-auto justify-center">
            <i class="fas fa-plus"></i> Nova Venda
        </a>
    </div>
    
    @if($recentSales->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="px-6 py-4 font-medium">#</th>
                        <th class="px-6 py-4 font-medium">Cliente</th>
                        <th class="px-6 py-4 font-medium">Itens</th>
                        <th class="px-6 py-4 font-medium text-right">Total</th>
                        <th class="px-6 py-4 font-medium text-right">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                    @foreach($recentSales as $sale)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4">{{ $sale->id }}</td>
                            <td class="px-6 py-4 font-medium">{{ $sale->customer_name ?? '—' }}</td>
                            <td class="px-6 py-4 text-zinc-500">{{ $sale->items->count() }} produto(s)</td>
                            <td class="px-6 py-4 font-medium text-right">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-zinc-500 text-right">{{ $sale->sale_date ? $sale->sale_date->format('d/m/Y') : $sale->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-6 py-12 text-center">
            <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400 text-2xl">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h3 class="text-sm font-medium text-zinc-900 dark:text-white mb-1">Nenhuma venda registrada ainda</h3>
            <p class="text-sm text-zinc-500 mb-4">Comece adicionando sua primeira venda ao sistema.</p>
            <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
                <i class="fas fa-plus"></i> Registrar Primeira Venda
            </a>
        </div>
    @endif
</div>

{{-- Active Shippings --}}
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="p-5 sm:p-6 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-truck-fast text-sky-500"></i> Fretes em Andamento
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Acompanhe pelo Correio pacotes que foram registrados com rastreamento.</p>
        </div>
    </div>
    
    @if(isset($activeShippings) && $activeShippings->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="px-6 py-4 font-medium">Cliente</th>
                        <th class="px-6 py-4 font-medium">Código</th>
                        <th class="px-6 py-4 font-medium text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                    @foreach($activeShippings as $shipping)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4 font-medium">{{ $shipping->customer_name ?: 'Venda #'.$shipping->id }}</td>
                            <td class="px-6 py-4 font-mono text-zinc-600 dark:text-zinc-400 font-semibold">{{ $shipping->tracking_code }}</td>
                            <td class="px-6 py-4 text-right cursor-pointer">
                                <button type="button" onclick="trackCode('{{ $shipping->tracking_code }}')" class="inline-flex items-center gap-2 px-3 py-1.5 bg-sky-50 dark:bg-sky-500/10 text-sky-700 dark:text-sky-400 rounded-lg text-xs font-semibold border border-sky-200 dark:border-sky-500/20 hover:bg-sky-100 dark:hover:bg-sky-500/20 transition-colors">
                                    <i class="fas fa-search-location"></i> Rastrear
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-6 py-12 text-center">
            <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400 text-2xl">
                <i class="fas fa-box-open"></i>
            </div>
            <h3 class="text-sm font-medium text-zinc-900 dark:text-white mb-1">Nenhum frete com rastreio</h3>
            <p class="text-sm text-zinc-500 mb-4">Adicione o Código de Rastreio nas suas vendas e ele aparecerá aqui.</p>
        </div>
    @endif
</div>

{{-- Modeler Requests --}}
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="p-5 sm:p-6 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-pencil-ruler text-emerald-500"></i> Solicitações de Modelagem da Comunidade
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Últimos pedidos abertos por clientes buscando modeladores.</p>
        </div>
    </div>
    
    @if(isset($modelerRequests) && $modelerRequests->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="px-6 py-4 font-medium">Cliente</th>
                        <th class="px-6 py-4 font-medium">Contato</th>
                        <th class="px-6 py-4 font-medium">Descrição</th>
                        <th class="px-6 py-4 font-medium text-right">Orçamento</th>
                        <th class="px-6 py-4 font-medium text-right">Anexo</th>
                        @if(auth()->user()->is_admin)
                        <th class="px-6 py-4 font-medium text-right">Ação</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                    @foreach($modelerRequests as $request)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4 font-medium">{{ $request->name }}</td>
                            <td class="px-6 py-4 text-zinc-500">
                                @if($request->whatsapp)
                                    <div class="flex flex-col">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $request->whatsapp) }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5"><i class="fab fa-whatsapp"></i> {{ $request->whatsapp }}</a>
                                        <span class="text-xs">{{ $request->email }}</span>
                                    </div>
                                @else
                                    <a href="mailto:{{ $request->email }}" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"><i class="fas fa-envelope"></i> {{ $request->email }}</a>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-zinc-500 truncate max-w-[250px]" title="{{ $request->description }}">
                                {{ $request->description }}
                            </td>
                            <td class="px-6 py-4 font-medium text-right">
                                <span class="px-3 py-1 bg-zinc-100 dark:bg-zinc-800 rounded-full text-xs border border-zinc-200 dark:border-zinc-700">{{ $request->budget_range }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($request->image_path)
                                    <a href="{{ $request->image_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 rounded-lg text-xs font-semibold border border-emerald-200 dark:border-emerald-500/20 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                                        <i class="fas fa-image"></i> Ver Imagem
                                    </a>
                                @else
                                    <span class="text-zinc-400 text-xs">—</span>
                                @endif
                            </td>
                            @if(auth()->user()->is_admin)
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('modeler-requests.destroy', $request) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja apagar essa solicitação?')" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 flex items-center justify-center transition-colors shadow-sm" title="Excluir">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-6 py-12 text-center">
            <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400 text-2xl">
                <i class="fas fa-inbox"></i>
            </div>
            <h3 class="text-sm font-medium text-zinc-900 dark:text-white mb-1">Nenhuma solicitação no momento</h3>
            <p class="text-sm text-zinc-500 mb-4">Os clientes ainda não fizeram novos pedidos de modelagem hoje.</p>
        </div>
    @endif
</div>

{{-- Modal Rastreio --}}
<div id="trackingModal" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Fundo escuro -->
    <div class="fixed inset-0 bg-zinc-900/80 backdrop-blur-sm transition-opacity" onclick="closeTrackingModal()"></div>
    
    <!-- Container do modal -->
    <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden border border-zinc-200 dark:border-zinc-800 transform scale-95 transition-transform duration-300" id="trackingModalContent">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50 dark:bg-zinc-800/50">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-route text-sky-500"></i> Rastreamento
                <span id="tCodeTitle" class="text-sm px-2 py-0.5 ml-2 bg-sky-100 text-sky-700 dark:bg-sky-900 dark:text-sky-300 rounded-md font-mono"></span>
            </h3>
            <button onclick="closeTrackingModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6 max-h-[60vh] overflow-y-auto" id="trackingTimelineContainer">
            <!-- Loading -->
            <div id="tLoading" class="flex flex-col items-center justify-center py-8 text-zinc-500">
                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-sky-500"></i>
                <p>Buscando atualizações nos Correios...</p>
            </div>
            
            <!-- Result Timeline -->
            <div id="tResult" class="hidden">
                <div class="relative border-l-2 border-sky-100 dark:border-sky-900/50 ml-3 space-y-6" id="tEvents">
                    <!-- Eventos Injetados pelo JS -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        const salesData = @json($salesChart);
        
        // Define theme-aware colors
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#a1a1aa' : '#71717a'; // zinc-400 : zinc-500
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
        const lineColor = isDark ? '#e4e4e7' : '#18181b'; // zinc-200 : zinc-900
        const bgColor = isDark ? 'rgba(228, 228, 231, 0.1)' : 'rgba(24, 24, 27, 0.1)';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.map(s => {
                    const d = new Date(s.date + 'T00:00:00');
                    return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
                }),
                datasets: [{
                    label: 'Vendas (R$)',
                    data: salesData.map(s => parseFloat(s.total)),
                    borderColor: lineColor,
                    backgroundColor: bgColor,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: lineColor,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#27272a' : '#ffffff',
                        titleColor: isDark ? '#ffffff' : '#18181b',
                        bodyColor: isDark ? '#d4d4d8' : '#3f3f46',
                        borderColor: isDark ? '#3f3f46' : '#e4e4e7',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { color: textColor, font: { size: 11, family: "'Inter', sans-serif" } },
                        border: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor, drawBorder: false },
                        ticks: {
                            color: textColor,
                            font: { size: 11, family: "'Inter', sans-serif" },
                            callback: v => 'R$ ' + v.toFixed(0),
                            maxTicksLimit: 6
                        },
                        border: { display: false }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
        
        // Listen to theme change
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "class") {
                    // Check if chart exists and update its colors
                    const chart = Chart.getChart(ctx);
                    if (chart) {
                        const isDarkNow = document.documentElement.classList.contains('dark');
                        const newTextColor = isDarkNow ? '#a1a1aa' : '#71717a';
                        const newGridColor = isDarkNow ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
                        const newLineColor = isDarkNow ? '#e4e4e7' : '#18181b';
                        const newBgColor = isDarkNow ? 'rgba(228, 228, 231, 0.1)' : 'rgba(24, 24, 27, 0.1)';
                        
                        chart.options.scales.x.ticks.color = newTextColor;
                        chart.options.scales.x.grid.color = newGridColor;
                        chart.options.scales.y.ticks.color = newTextColor;
                        chart.options.scales.y.grid.color = newGridColor;
                        
                        chart.data.datasets[0].borderColor = newLineColor;
                        chart.data.datasets[0].backgroundColor = newBgColor;
                        chart.data.datasets[0].pointBorderColor = newLineColor;
                        
                        chart.options.plugins.tooltip.backgroundColor = isDarkNow ? '#27272a' : '#ffffff';
                        chart.options.plugins.tooltip.titleColor = isDarkNow ? '#ffffff' : '#18181b';
                        chart.options.plugins.tooltip.bodyColor = isDarkNow ? '#d4d4d8' : '#3f3f46';
                        chart.options.plugins.tooltip.borderColor = isDarkNow ? '#3f3f46' : '#e4e4e7';
                        
                        chart.update();
                    }
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });
    }
    
    // Tracking Modal Logic
    const tModal = document.getElementById('trackingModal');
    const tModalContent = document.getElementById('trackingModalContent');
    const tLoading = document.getElementById('tLoading');
    const tResult = document.getElementById('tResult');
    const tEvents = document.getElementById('tEvents');
    const tCodeTitle = document.getElementById('tCodeTitle');

    function trackCode(code) {
        // Show modal
        tCodeTitle.textContent = code;
        tResult.classList.add('hidden');
        tLoading.classList.remove('hidden');
        
        tModal.classList.remove('hidden');
        // Trigger animations
        setTimeout(() => {
            tModal.classList.remove('opacity-0');
            tModalContent.classList.remove('scale-95');
        }, 10);
        
        // Fetch API
        fetch(`/api/tracking/${code}`)
            .then(res => res.json())
            .then(data => {
                tLoading.classList.add('hidden');
                
                if (data.success && data.events && data.events.length > 0) {
                    renderTimeline(data.events, data.link);
                    tResult.classList.remove('hidden');
                } else {
                    tResult.classList.remove('hidden');
                    
                    // Extrai mensagem da API raw ou default se não existir
                    let errorMessage = data.message || 'Código não encontrado nos Correios ou recém postado.';
                    
                    tEvents.innerHTML = `
                        <div class="ml-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            <i class="fas fa-box-open text-4xl mb-3 text-zinc-300 dark:text-zinc-700"></i>
                            <p>${errorMessage}</p>
                            ${data.raw ? `<pre class="text-left mt-4 text-xs bg-zinc-100 dark:bg-zinc-800 p-2 rounded overflow-auto hidden">${JSON.stringify(data.raw, null, 2)}</pre>` : ''}
                        </div>
                    `;
                }
            })
            .catch(err => {
                tLoading.classList.add('hidden');
                tResult.classList.remove('hidden');
                tEvents.innerHTML = `
                    <div class="ml-6 py-4 text-center text-red-500">
                        <i class="fas fa-exclamation-triangle text-4xl mb-3"></i>
                        <p>Falha ao comunicar com os Correios. Tente mais tarde.</p>
                    </div>
                `;
            });
    }

    function renderTimeline(events, link = null) {
        let html = '';
        events.forEach((ev, index) => {
            const isLatest = index === 0;
            const dotColor = isLatest ? 'bg-sky-500 border-white dark:border-zinc-900' : 'bg-zinc-300 dark:bg-zinc-600 border-white dark:border-zinc-900';
            const icon = isLatest ? '<i class="fas fa-truck text-xs text-white"></i>' : '';
            
            // Tratamento caso a API retorne algo inesperado ou formato estranho
            const evStatus = ev.status || ev.descricao || ev.description || 'Evento Registrado';
            const evData = ev.data || ev.date || '--/--/----';
            const evHora = ev.hora || ev.time || '--:--';
            const evLocal = ev.local || ev.location || 'Local Não Informado';

            html += `
                <div class="relative pl-6">
                    <div class="absolute w-6 h-6 rounded-full border-4 ${dotColor} -left-[14px] top-1 flex items-center justify-center">
                        ${icon}
                    </div>
                    <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-100 dark:border-zinc-800 shadow-sm relative ${isLatest ? 'ring-1 ring-sky-500/30' : ''}">
                        <div class="font-semibold text-zinc-900 dark:text-white leading-tight mb-1">
                            ${evStatus}
                        </div>
                        <div class="flex items-center gap-3 text-xs text-zinc-500 mb-2">
                            <span class="flex items-center gap-1.5"><i class="far fa-clock"></i> ${evData} às ${evHora}</span>
                        </div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                            <i class="fas fa-map-marker-alt text-zinc-400 w-4"></i> ${evLocal}
                        </div>
                    </div>
                </div>
            `;
        });
        
        if (link) {
            html += `
                <div class="mt-8 text-center border-t border-zinc-200 dark:border-zinc-800 pt-4">
                    <a href="${link}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors w-full sm:w-auto justify-center">
                        <i class="fas fa-external-link-alt"></i> Ver Histórico Completo no Site
                    </a>
                </div>
            `;
        }
        
        tEvents.innerHTML = html;
    }

    function closeTrackingModal() {
        tModal.classList.add('opacity-0');
        tModalContent.classList.add('scale-95');
        setTimeout(() => {
            tModal.classList.add('hidden');
        }, 300);
    }
</script>
@endsection
