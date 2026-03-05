@extends('layouts.app')

@section('page-title', 'Gerenciar Usuários')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Gerenciar Usuários</h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Altere planos, suspenda ou remova usuários da plataforma.</p>
        </div>
        <div class="text-sm text-zinc-500 dark:text-zinc-400">
            <i class="fas fa-users mr-1"></i> {{ $users->total() }} usuários
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-6">
        <div class="flex gap-2">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, email ou loja..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 rounded-xl text-sm font-medium hover:bg-zinc-800 dark:hover:bg-zinc-100 transition-colors">
                Buscar
            </button>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 rounded-xl text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    Limpar
                </a>
            @endif
        </div>
    </form>

    {{-- Users Table --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th class="px-5 py-4 font-medium">Usuário</th>
                        <th class="px-5 py-4 font-medium">Loja</th>
                        <th class="px-5 py-4 font-medium">Status</th>
                        <th class="px-5 py-4 font-medium">Plano</th>
                        <th class="px-5 py-4 font-medium">Alterar Plano</th>
                        <th class="px-5 py-4 font-medium text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($users as $user)
                        @php
                            $userPlan = $user->currentPlan();
                            $isSuspended = $user->suspended_at !== null;
                            $isCurrentUser = $user->id === auth()->id();
                        @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors {{ $isSuspended ? 'opacity-60' : '' }}">
                            {{-- User info --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($user->store_logo)
                                        <img src="{{ $user->store_logo_thumbnail_url }}" alt="" class="w-9 h-9 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-medium text-zinc-900 dark:text-white truncate flex items-center gap-2">
                                            {{ $user->name }}
                                            @if($user->is_admin)
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 uppercase">Admin</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Store name --}}
                            <td class="px-5 py-4 text-zinc-600 dark:text-zinc-400">
                                {{ $user->store_name ?? '—' }}
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @if($isSuspended)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/30" title="{{ $user->suspension_reason }}">
                                        <i class="fas fa-ban"></i> Suspenso
                                    </span>
                                    @if($user->suspension_reason)
                                        <div class="text-[11px] text-red-500 dark:text-red-400/70 mt-1 truncate max-w-[150px]" title="{{ $user->suspension_reason }}">{{ $user->suspension_reason }}</div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">
                                        <i class="fas fa-check-circle"></i> Ativo
                                    </span>
                                @endif
                            </td>

                            {{-- Current plan badge --}}
                            <td class="px-5 py-4">
                                @if($userPlan)
                                    @php
                                        $badgeClass = match($userPlan->slug) {
                                            'free' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700',
                                            'basic' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/30',
                                            'pro' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200 dark:border-amber-500/30',
                                            default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg border {{ $badgeClass }}">
                                        {{ $userPlan->name }}
                                    </span>
                                @else
                                    <span class="text-zinc-400 text-xs">Sem plano</span>
                                @endif
                            </td>

                            {{-- Plan changer --}}
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ route('admin.users.update-plan', $user) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="plan_id" class="text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white py-1.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" {{ $userPlan && $userPlan->id === $plan->id ? 'selected' : '' }}>
                                                {{ $plan->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm" onclick="return confirm('Alterar o plano de {{ $user->name }}?')">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!$user->is_admin && !$isCurrentUser)
                                        {{-- Suspend / Unsuspend --}}
                                        @if($isSuspended)
                                            <form method="POST" action="{{ route('admin.users.unsuspend', $user) }}" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20 flex items-center justify-center transition-colors shadow-sm" title="Reativar usuário" onclick="return confirm('Remover suspensão de {{ $user->name }}?')">
                                                    <i class="fas fa-unlock text-sm"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" onclick="openSuspendModal({{ $user->id }}, '{{ addslashes($user->name) }}')" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 flex items-center justify-center transition-colors shadow-sm" title="Suspender usuário">
                                                <i class="fas fa-ban text-sm"></i>
                                            </button>
                                        @endif

                                        {{-- Delete --}}
                                        <button type="button" onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 flex items-center justify-center transition-colors shadow-sm" title="Deletar usuário">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    @else
                                        <span class="text-zinc-300 dark:text-zinc-700 text-xs">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="w-14 h-14 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-3 text-zinc-400 text-xl">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <p class="text-sm text-zinc-500">Nenhum usuário encontrado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="px-5 py-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Suspend Modal --}}
<div id="suspendModal" class="fixed inset-0 z-50 flex items-center justify-center hidden" aria-modal="true">
    <div class="fixed inset-0 bg-zinc-900/80 backdrop-blur-sm" onclick="closeSuspendModal()"></div>
    <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden border border-zinc-200 dark:border-zinc-800">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-ban text-amber-500"></i> Suspender Usuário
            </h3>
        </div>
        <form id="suspendForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="p-6">
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                    Suspender <strong id="suspendUserName" class="text-zinc-900 dark:text-white"></strong>? O usuário não conseguirá fazer login enquanto estiver suspenso.
                </p>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Motivo (opcional)</label>
                <textarea name="reason" rows="3" placeholder="Ex: Violação dos termos de uso..." class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white text-sm p-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent outline-none resize-none"></textarea>
            </div>
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-3 bg-zinc-50 dark:bg-zinc-800/50">
                <button type="button" onclick="closeSuspendModal()" class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-ban mr-1"></i> Suspender
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Modal --}}
<div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center hidden" aria-modal="true">
    <div class="fixed inset-0 bg-zinc-900/80 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white dark:bg-zinc-900 rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden border border-zinc-200 dark:border-zinc-800">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-red-50 dark:bg-red-500/10">
            <h3 class="text-lg font-semibold text-red-700 dark:text-red-400 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> Deletar Usuário
            </h3>
        </div>
        <form id="deleteForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="p-6">
                <div class="p-3 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 mb-4">
                    <p class="text-sm text-red-700 dark:text-red-400 font-medium">
                        <i class="fas fa-exclamation-circle mr-1"></i> Esta ação é irreversível!
                    </p>
                    <p class="text-xs text-red-600 dark:text-red-400/70 mt-1">Todos os dados do usuário (produtos, vendas, etc.) serão perdidos permanentemente.</p>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                    Para confirmar a exclusão de <strong id="deleteUserName" class="text-zinc-900 dark:text-white"></strong>, digite sua senha de administrador:
                </p>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Sua senha</label>
                <input type="password" name="password" required autocomplete="current-password" placeholder="Digite sua senha..." class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white text-sm p-3 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none">
            </div>
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-3 bg-zinc-50 dark:bg-zinc-800/50">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-trash-alt mr-1"></i> Deletar Permanentemente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openSuspendModal(userId, userName) {
        document.getElementById('suspendForm').action = `/painel/usuarios/${userId}/suspender`;
        document.getElementById('suspendUserName').textContent = userName;
        document.getElementById('suspendModal').classList.remove('hidden');
    }

    function closeSuspendModal() {
        document.getElementById('suspendModal').classList.add('hidden');
    }

    function openDeleteModal(userId, userName) {
        document.getElementById('deleteForm').action = `/painel/usuarios/${userId}`;
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('deleteForm').querySelector('input[name=password]').value = '';
        document.getElementById('deleteModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('deleteForm').querySelector('input[name=password]').focus(), 100);
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>
@endsection
