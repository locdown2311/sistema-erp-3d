@extends('layouts.app')

@section('page-title', 'Pedidos de Modelagem')

@section('content')
<div class="mb-8">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 rounded-2xl shadow-sm relative overflow-hidden">
        <div class="absolute inset-y-0 left-0 w-2 bg-emerald-500"></div>
        <div>
            <h2 class="text-2xl font-black text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-pencil-ruler text-emerald-500"></i> Solicitações da Comunidade
            </h2>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1 font-medium">Os clientes precisam da sua expertise. Entre em contato e feche negócios!</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('modeler-requests.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors border border-emerald-200 dark:border-emerald-500/20">
                <i class="fas fa-plus"></i> Novo Pedido
            </a>
        </div>
    </div>
</div>

@if($modelerRequests->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($modelerRequests as $request)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm flex flex-col hover:border-emerald-500/50 hover:shadow-md transition-all group overflow-hidden relative">
                
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-zinc-900 dark:text-white leading-tight truncate max-w-[150px] sm:max-w-[200px]" title="{{ $request->name }}">{{ $request->name }}</h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $request->created_at->diffForHumans() }}
                                    <span class="mx-1">&bull;</span>
                                    @php
                                        $expiresAt = $request->created_at->copy()->addDays(7);
                                        $isExpired = $expiresAt->isPast();
                                        $diff = now()->diff($expiresAt);
                                        $daysRemaining = $diff->days;
                                        $hoursRemaining = $diff->h;
                                    @endphp
                                    @if($isExpired)
                                        <span class="text-red-500 font-medium">Expirado</span>
                                    @else
                                        <span class="{{ $daysRemaining <= 2 ? 'text-red-500' : 'text-amber-500' }} font-medium" title="{{ $expiresAt->format('d/m/Y H:i') }}">Expira em {{ $daysRemaining }}d e {{ $hoursRemaining }}h</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="inline-block px-3 py-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold rounded-full whitespace-nowrap border border-zinc-200 dark:border-zinc-700">
                            {{ $request->budget_range }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Descrição do Projeto</h4>
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-100 dark:border-zinc-700/50 text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-line max-h-48 overflow-y-auto custom-scrollbar">
                            {{ $request->description }}
                        </div>
                    </div>

                    @php
                        $thumbnails = $request->thumbnail_urls;
                        $imageUrls = $request->image_urls;
                        $imageCount = count($thumbnails);
                    @endphp

                    @if($imageCount > 0)
                        <div class="mb-4">
                            <h4 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">
                                <i class="fas fa-images mr-1"></i> {{ $imageCount }} {{ $imageCount === 1 ? 'imagem' : 'imagens' }} de referência
                            </h4>
                            <div class="grid {{ $imageCount === 1 ? 'grid-cols-1' : ($imageCount === 2 ? 'grid-cols-2' : 'grid-cols-3') }} gap-2">
                                @foreach($thumbnails as $i => $thumb)
                                    <a href="{{ $imageUrls[$i] ?? $thumb }}" target="_blank" class="block rounded-xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden relative group/img border border-zinc-200 dark:border-zinc-700 {{ $imageCount === 1 ? 'h-32' : 'aspect-square' }}">
                                        <div class="absolute inset-0 bg-zinc-200 dark:bg-zinc-700 animate-pulse flex items-center justify-center transition-opacity duration-300 z-10" id="loader-{{ $request->id }}-{{ $i }}">
                                            <i class="fas fa-spinner fa-spin text-zinc-400 dark:text-zinc-500 text-lg"></i>
                                        </div>
                                        <img src="{{ $thumb }}" 
                                             onload="document.getElementById('loader-{{ $request->id }}-{{ $i }}').classList.add('opacity-0'); setTimeout(() => document.getElementById('loader-{{ $request->id }}-{{ $i }}').remove(), 300)"
                                             loading="lazy"
                                             referrerpolicy="no-referrer"
                                             class="w-full h-full object-cover group-hover/img:scale-105 transition-transform duration-500 relative z-0" 
                                             alt="Referência {{ $i + 1 }}">
                                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/img:opacity-100 transition-opacity z-20 pointer-events-none">
                                            <span class="bg-white/20 backdrop-blur-md text-white px-2 py-1 rounded-lg text-xs font-bold flex items-center gap-1">
                                                <i class="fas fa-expand-arrows-alt"></i> Ver
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="mt-auto mb-4 border border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl p-3 text-center text-xs text-zinc-400">
                            Sem imagem de referência
                        </div>
                    @endif

                    <div class="mt-auto pt-4 border-t border-zinc-100 dark:border-zinc-800/80">
                        <h4 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3">Contatar Solicitante</h4>
                        <div class="flex gap-2">
                            @if($request->whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $request->whatsapp) }}" target="_blank" class="flex-1 flex items-center justify-center gap-2 py-2 px-3 bg-[#25D366]/10 hover:bg-[#25D366]/20 text-[#25D366] text-sm font-bold rounded-lg transition-colors border border-[#25D366]/20">
                                    <i class="fab fa-whatsapp text-lg"></i> WhatsApp
                                </a>
                            @endif
                            <a href="mailto:{{ $request->email }}" class="flex-1 flex items-center justify-center gap-2 py-2 px-3 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-sm font-bold rounded-lg transition-colors border border-indigo-200 dark:border-indigo-500/20">
                                <i class="fas fa-envelope"></i> E-mail
                            </a>
                            @if(auth()->user()->is_admin)
                                <form action="{{ route('modeler-requests.destroy', $request) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja apagar essa solicitação?')" class="flex items-stretch">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 text-sm font-bold rounded-lg transition-colors border border-red-200 dark:border-red-500/20 cursor-pointer" title="Excluir Pedido">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
    
    <div class="mt-8">
        {{ $modelerRequests->links() }}
    </div>
@else
    <div class="flex flex-col items-center justify-center p-16 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl text-center shadow-sm">
        <div class="w-20 h-20 rounded-full bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center text-emerald-500 mb-6 border-8 border-emerald-50 dark:border-emerald-500/10">
            <i class="fas fa-inbox text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">Nenhum pedido no momento</h3>
        <p class="text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto mb-6">Ainda não há solicitações de modelagem cadastradas pela comunidade.</p>
        <a href="{{ route('modeler-requests.create') }}" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-bold rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
            Ser o primeiro a pedir
        </a>
    </div>
@endif
@endsection
