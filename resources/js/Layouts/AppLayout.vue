<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// The auth user and global variables are typically passed via Inertia shared props.
// Assuming we'll share auth.user and setting.company_name in HandleInertiaRequests middleware.
const page = usePage();
const user = page.props.auth?.user;
const companyName = page.props.setting?.company_name || 'Central 3D';

// --- FLASH MESSAGES ---
const flashMessage = ref('');
const flashType = ref('success');
let flashTimeout;

const showFlash = () => {
    clearTimeout(flashTimeout);
    flashTimeout = setTimeout(() => {
        flashMessage.value = '';
    }, 4000);
};

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        flashMessage.value = flash.success;
        flashType.value = 'success';
        showFlash();
    } else if (flash?.error) {
        flashMessage.value = flash.error;
        flashType.value = 'error';
        showFlash();
    }
}, { deep: true, immediate: true });

const isSidebarOpen = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
    if (isSidebarOpen.value) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
};

const closeSidebar = () => {
    if (isSidebarOpen.value) {
        toggleSidebar();
    }
};

// Theme Toggle
const isDark = ref(false);

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark');
});

const toggleTheme = () => {
    document.documentElement.classList.toggle('dark');
    isDark.value = document.documentElement.classList.contains('dark');
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

const showPremiumAlert = () => {
    window.Swal?.fire({
        icon: 'warning', 
        title: 'Recurso Premium', 
        text: 'A emissão de Notas Fiscais (NF-e) é exclusiva dos planos que suportam este recurso. Faça upgrade!', 
        confirmButtonText: 'Ver Planos', 
        confirmButtonColor: '#6366f1'
    }).then((result) => { 
        if(result.isConfirmed) window.location.href = '/planos';
    });
};
</script>

<template>
    <!-- Overlay for mobile sidebar -->
    <div v-if="user" 
         id="sidebarOverlay" 
         class="fixed inset-0 bg-zinc-950/50 dark:bg-zinc-950/80 z-40 md:hidden transition-opacity" 
         :class="isSidebarOpen ? 'opacity-100' : 'opacity-0 hidden'"
         aria-hidden="true"
         @click="closeSidebar"></div>

    <!-- Sidebar -->
    <aside v-if="user && !$page.props.hide_sidebar" 
           id="sidebar" 
           class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800 transition-transform md:translate-x-0 md:static md:flex-shrink-0 flex flex-col"
           :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="h-16 flex items-center justify-between px-6 border-b border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center gap-3">
                <img v-if="user.store_logo_thumbnail_url || user.store_logo_url" :src="user.store_logo_thumbnail_url || user.store_logo_url" alt="Logo" class="w-7 h-7 rounded object-cover">
                <i v-else class="fas fa-cube text-xl text-zinc-900 dark:text-zinc-100"></i>
                <span class="font-bold text-zinc-900 dark:text-white truncate" style="max-width: 140px;">
                    {{ user.store_name || 'Central 3D' }}
                </span>
            </div>
            <button @click="closeSidebar" class="md:hidden text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 p-2">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            <a href="/painel" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url === '/painel' }">
                <i class="fas fa-chart-pie w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            <a href="/produtos" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/produtos') }">
                <i class="fas fa-boxes-stacked w-5 text-center"></i>
                <span>Produtos</span>
            </a>
            <a href="/filamentos" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/filamentos') }">
                <i class="fas fa-fill-drip w-5 text-center"></i>
                <span>Filamentos</span>
            </a>
            <a href="/estoque" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/estoque') }">
                <i class="fas fa-warehouse w-5 text-center"></i>
                <span>Estoque</span>
            </a>
            <a href="/vendas" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/vendas') }">
                <i class="fas fa-cash-register w-5 text-center"></i>
                <span>Vendas</span>
            </a>
            <a href="/custos-3d" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/custos-3d') }">
                <i class="fas fa-calculator w-5 text-center"></i>
                <span>Custos 3D</span>
            </a>
            
            <Link href="/gerador-flexi" class="group relative flex items-center justify-between px-3 py-2.5 rounded-md overflow-hidden transition-all duration-300 hover:bg-zinc-100 dark:hover:bg-zinc-800" :class="{ 'bg-indigo-50 dark:bg-indigo-500/10': $page.url.startsWith('/gerador-flexi') }">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500 rounded-l-md opacity-0 group-hover:opacity-100 transition-opacity" :class="{ 'opacity-100': $page.url.startsWith('/gerador-flexi') }"></div>
                <div class="flex items-center gap-3 relative z-10">
                    <i class="fas fa-cube w-5 text-center transition-transform group-hover:scale-110" :class="$page.url.startsWith('/gerador-flexi') ? 'text-indigo-500' : 'text-zinc-600 dark:text-zinc-400 group-hover:text-indigo-500'"></i>
                    <span class="font-semibold" :class="$page.url.startsWith('/gerador-flexi') ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-600 dark:text-zinc-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400'">Gerador Flexi</span>
                </div>
                <span class="bg-indigo-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full relative z-10 flex items-center gap-1">
                    NOVO
                </span>
            </Link>

            <a v-if="user.can_use_nfe" href="/nfe" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/nfe') }">
                <i class="fas fa-file-invoice w-5 text-center"></i>
                <span>Notas Fiscais</span>
            </a>
            <button v-else type="button" @click="showPremiumAlert" class="w-full flex items-center justify-between px-3 py-2.5 rounded-md text-zinc-400 dark:text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors group">
                <div class="flex items-center gap-3">
                    <i class="fas fa-file-invoice w-5 text-center"></i>
                    <span>Notas Fiscais</span>
                </div>
                <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm opacity-80 group-hover:opacity-100"><i class="fas fa-lock text-[8px] mr-0.5"></i> PRO</span>
            </button>
            <a href="/calendario" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/calendario') }">
                <i class="fas fa-calendar-alt w-5 text-center"></i>
                <span>Calendário</span>
            </a>

            <template v-if="user.is_admin">
                <div class="my-4 border-t border-zinc-200 dark:border-zinc-800/50"></div>
                <a href="/ofertas" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/ofertas') }">
                    <i class="fas fa-tags w-5 text-center"></i>
                    <span>Ofertas</span>
                </a>
                <a href="/painel/usuarios" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/painel/usuarios') }">
                    <i class="fas fa-users-cog w-5 text-center"></i>
                    <span>Gerenciar Usuários</span>
                </a>
            </template>

            <div class="my-4 border-t border-zinc-200 dark:border-zinc-800/50"></div>
            
            <a href="/pedidos-modelagem" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'border-l-2 border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold': $page.url.startsWith('/pedidos-modelagem') }">
                <i class="fas fa-magic w-5 text-center text-emerald-500"></i>
                <span>Pedidos de Modelagem</span>
            </a>

            <div class="my-4 border-t border-zinc-200 dark:border-zinc-800/50"></div>

            <a :href="`/loja/${user.slug}`" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white">
                <i class="fas fa-store w-5 text-center"></i>
                <span>Minha Loja</span>
            </a>
            <a href="/configuracoes" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/configuracoes') }">
                <i class="fas fa-cog w-5 text-center"></i>
                <span>Configurações</span>
            </a>
            
            <a href="/planos" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/planos') }">
                <i class="fas fa-star w-5 text-center text-amber-500"></i>
                <span>Meu Plano</span>
            </a>
            
            <a href="/wishlists" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white" :class="{ 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-medium': $page.url.startsWith('/wishlists') }">
                <i class="fas fa-heart w-5 text-center text-pink-500"></i>
                <span>Lista de Desejos</span>
            </a>
        </nav>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            <div class="flex items-center gap-3 px-3 py-2 mb-2">
                <i class="fas fa-user-circle text-zinc-400 w-5 text-center text-lg"></i>
                <span class="text-sm text-zinc-500 dark:text-zinc-400 truncate">{{ user.name }}</span>
            </div>
            <form method="POST" action="/logout">
                <!-- Assuming standard laravel logout route uses POST. We need to add csrf token if doing native form submission -->
                <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white text-left transition-colors">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 bg-transparent">
        <header v-if="!$page.props.hide_header" class="h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 md:bg-transparent md:dark:bg-transparent sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <button v-if="user" @click="toggleSidebar" class="md:hidden p-2 -ml-2 text-zinc-500 hover:text-zinc-900 dark:hover:text-white rounded-md">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="text-lg font-semibold text-zinc-900 dark:text-white truncate">
                    <slot name="header">Dashboard</slot>
                </h1>
            </div>
            
            <div class="flex items-center gap-3">
                <button @click="toggleTheme" class="p-2 text-zinc-500 hover:text-zinc-900 dark:hover:text-white rounded-md transition-colors" title="Alternar tema">
                    <i :class="isDark ? 'fas fa-moon' : 'fas fa-sun'"></i>
                </button>
                <slot name="top-actions"></slot>
            </div>
        </header>

        <div class="p-4 sm:p-6 lg:p-8 flex-1 flex flex-col overflow-x-hidden">
            <slot />
        </div>
    </main>

    <!-- Flash Messages -->
    <Transition
        enter-active-class="transform transition duration-300 ease-out"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="flashMessage" class="fixed z-50 flex items-start gap-3 px-4 py-3 bg-white border shadow-xl bot-4 right-4 sm:bottom-6 sm:right-6 sm:top-auto dark:bg-zinc-800 rounded-xl border-zinc-200 dark:border-zinc-700 max-w-sm w-full">
            <div class="flex-shrink-0 mt-0.5">
                <i v-if="flashType === 'success'" class="text-xl text-emerald-500 fas fa-check-circle"></i>
                <i v-else class="text-xl text-red-500 fas fa-exclamation-circle"></i>
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ flashMessage }}</p>
            </div>
            <button @click="flashMessage = ''" class="flex-shrink-0 pt-1 ml-4 transition-colors text-zinc-400 hover:text-zinc-500 dark:hover:text-zinc-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </Transition>
</template>
