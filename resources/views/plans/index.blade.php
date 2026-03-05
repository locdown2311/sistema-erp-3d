@extends('layouts.app')

@section('page-title', 'Planos e Assinaturas')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="text-center mb-10">
        <h2 class="text-3xl font-extrabold text-zinc-900 dark:text-white mb-3">Escolha seu Plano</h2>
        <p class="text-base text-zinc-500 dark:text-zinc-400 max-w-xl mx-auto">Comece grátis e evolua conforme seu negócio cresce. Sem surpresas, cancele quando quiser.</p>
    </div>

    {{-- Usage Stats (se tiver plano) --}}
    @if($currentPlan)
    <div class="mb-10 p-5 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
        <div class="flex items-center gap-2 mb-4">
            <i class="fas fa-chart-bar text-indigo-500"></i>
            <h3 class="font-semibold text-zinc-900 dark:text-white">Seu uso atual — <span class="text-indigo-500">{{ $currentPlan->name }}</span></h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach(['products' => ['Produtos', 'fa-boxes-stacked', 'emerald'], 'sales' => ['Vendas/mês', 'fa-cash-register', 'blue'], 'wishlists' => ['Wishlists', 'fa-heart', 'pink'], 'flexi_cuts' => ['Gerador Flexi', 'fa-cube', 'indigo']] as $key => [$label, $icon, $color])
                @php
                    $u = $usage[$key];
                    $pct = $u['limit'] ? min(round(($u['current'] / $u['limit']) * 100), 100) : 0;
                    $isNearLimit = $u['limit'] && $pct >= 80;
                @endphp
                <div class="p-4 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between mb-2">
                        <span class="flex items-center gap-2 text-sm font-medium text-zinc-600 dark:text-zinc-300">
                            <i class="fas {{ $icon }} text-{{ $color }}-500"></i> {{ $label }}
                        </span>
                        <span class="text-sm font-bold {{ $isNearLimit ? 'text-amber-500' : 'text-zinc-900 dark:text-white' }}">
                            {{ $u['current'] }}/{{ $u['limit'] ?? '∞' }}
                        </span>
                    </div>
                    @if($u['limit'])
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-500 {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-'.$color.'-500') }}" style="width: {{ $pct }}%"></div>
                        </div>
                    @else
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                            <div class="h-2 rounded-full bg-emerald-500 w-full opacity-30"></div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Plan Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        @foreach($plans as $plan)
            @php
                $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                $isPopular = $plan->slug === 'basic';
                $isPro = $plan->slug === 'pro';
                
                if ($plan->slug === 'free') { $gradient = 'from-zinc-500 to-zinc-700'; $badge = '🆓'; $ring = 'ring-zinc-300 dark:ring-zinc-600'; }
                elseif ($plan->slug === 'basic') { $gradient = 'from-indigo-500 to-purple-600'; $badge = '⭐'; $ring = 'ring-indigo-400'; }
                else { $gradient = 'from-amber-500 to-orange-600'; $badge = '🚀'; $ring = 'ring-amber-400'; }
            @endphp
            <div class="relative flex flex-col rounded-2xl border {{ $isCurrent ? 'ring-2 '.$ring : '' }} {{ $isPopular ? 'border-indigo-400 dark:border-indigo-500 shadow-lg shadow-indigo-500/10' : 'border-zinc-200 dark:border-zinc-700' }} bg-white dark:bg-zinc-900 overflow-hidden transition-all hover:shadow-xl hover:-translate-y-1 duration-300">
                {{-- Popular Badge --}}
                @if($isPopular)
                    <div class="absolute -top-0 left-1/2 -translate-x-1/2 px-4 py-1 bg-gradient-to-r {{ $gradient }} text-white text-xs font-bold rounded-b-lg tracking-wider uppercase shadow-md">
                        Mais Popular
                    </div>
                @endif

                {{-- Card Header --}}
                <div class="p-6 pb-4 text-center {{ $isPopular ? 'pt-10' : '' }}">
                    <span class="text-3xl mb-2 block">{{ $badge }}</span>
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-1">{{ $plan->name }}</h3>
                    <div class="flex items-baseline justify-center gap-1 mt-3">
                        @if($plan->price > 0)
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">R$</span>
                            <span class="text-4xl font-extrabold bg-gradient-to-r {{ $gradient }} bg-clip-text text-transparent">{{ number_format($plan->price, 2, ',', '.') }}</span>
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">/mês</span>
                        @else
                            <span class="text-4xl font-extrabold text-zinc-700 dark:text-zinc-300">Grátis</span>
                        @endif
                    </div>
                </div>

                {{-- Limits --}}
                <div class="px-6 pb-4">
                    <div class="grid grid-cols-2 gap-2 text-center">
                        <div class="p-2 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                            <div class="text-lg font-bold text-zinc-900 dark:text-white">{{ $plan->max_products ?? '∞' }}</div>
                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400">Produtos</div>
                        </div>
                        <div class="p-2 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                            <div class="text-lg font-bold text-zinc-900 dark:text-white">{{ $plan->max_sales_per_month ?? '∞' }}</div>
                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400">Vendas/mês</div>
                        </div>
                        <div class="p-2 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                            <div class="text-lg font-bold text-zinc-900 dark:text-white">{{ $plan->max_wishlists ?? '∞' }}</div>
                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400">Wishlists</div>
                        </div>
                        <div class="p-2 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                            <div class="text-lg font-bold text-zinc-900 dark:text-white">{{ $plan->max_flexi_cuts ?? '∞' }}</div>
                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400">Gerador Flexi</div>
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="mx-6 border-t border-zinc-200 dark:border-zinc-700/50"></div>

                {{-- Features List --}}
                <div class="p-6 flex-1">
                    <ul class="space-y-3">
                        @foreach($plan->features ?? [] as $feature)
                            <li class="flex items-start gap-2.5 text-sm">
                                <i class="fas fa-check mt-0.5 text-emerald-500 flex-shrink-0"></i>
                                <span class="text-zinc-700 dark:text-zinc-300">{{ $feature }}</span>
                            </li>
                        @endforeach

                        {{-- Boolean features --}}
                        @foreach([
                            ['can_export_reports', 'Exportar relatórios CSV/PDF'],
                            ['can_use_nfe', 'Notas Fiscais (NF-e)'],
                            ['can_customize_store', 'Personalização da loja'],
                            ['priority_store', 'Prioridade na vitrine'],
                        ] as [$field, $label])
                            @if(!in_array($label, $plan->features ?? []))
                                <li class="flex items-start gap-2.5 text-sm">
                                    @if($plan->$field)
                                        <i class="fas fa-check mt-0.5 text-emerald-500 flex-shrink-0"></i>
                                        <span class="text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                                    @else
                                        <i class="fas fa-times mt-0.5 text-zinc-300 dark:text-zinc-600 flex-shrink-0"></i>
                                        <span class="text-zinc-400 dark:text-zinc-600 line-through">{{ $label }}</span>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                {{-- CTA Button --}}
                <div class="p-6 pt-0">
                    @if($isCurrent)
                        <div class="w-full text-center py-3 px-4 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 font-semibold text-sm border border-zinc-200 dark:border-zinc-700 cursor-default">
                            <i class="fas fa-check-circle mr-1"></i> Plano Atual
                        </div>
                    @elseif($plan->price <= 0)
                        <form method="POST" action="{{ route('subscriptions.store') }}">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-semibold text-sm hover:bg-zinc-800 dark:hover:bg-zinc-100 transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer hover:scale-[1.02]">
                                Ativar Plano Gratuito
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('subscriptions.store') }}">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r {{ $gradient }} text-white font-semibold text-sm hover:brightness-110 transition-all duration-200 shadow-lg hover:shadow-xl cursor-pointer hover:scale-[1.02]">
                                <i class="fas fa-bolt mr-1"></i> Fazer Upgrade
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- FAQ Section --}}
    <div class="max-w-2xl mx-auto mb-12">
        <h3 class="text-xl font-bold text-zinc-900 dark:text-white text-center mb-6">Perguntas Frequentes</h3>
        <div class="space-y-3">
            @foreach([
                ['Posso cancelar a qualquer momento?', 'Sim! Não há contratos. Cancele quando quiser e você manterá os benefícios até o fim do período pago.'],
                ['O que acontece se eu ultrapassar o limite?', 'Você será redirecionado para a página de upgrade. Seus dados existentes nunca serão apagados.'],
                ['Posso fazer downgrade?', 'Sim. Se você tiver mais itens do que o permitido no novo plano, manterá os existentes mas não poderá criar novos até ficar dentro do limite.'],
            ] as [$question, $answer])
                <details class="group p-4 rounded-xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 cursor-pointer">
                    <summary class="flex items-center justify-between font-medium text-zinc-900 dark:text-white text-sm">
                        {{ $question }}
                        <i class="fas fa-chevron-down text-zinc-400 group-open:rotate-180 transition-transform duration-200"></i>
                    </summary>
                    <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</div>
@endsection
