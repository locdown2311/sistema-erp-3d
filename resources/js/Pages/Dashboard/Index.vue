<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    totalProducts: Number,
    totalSales: Number,
    totalRevenue: [Number, String],
    pendingTasks: Number,
    salesChart: Array,
    recentSales: Array,
    lowStock: Array,
    upcomingTasks: Array,
    modelerRequests: Array,
    activeShippings: Array,
    currentPlan: Object,
    planUsage: Object
});

// Format currency helper
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

// Format date helper
const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

// Tracking Modal Modal State
const showingTrackingModal = ref(false);
const trackingCode = ref('');
const trackingLoading = ref(false);
const trackingEvents = ref([]);
const trackingError = ref('');
const trackingLink = ref('');

const openTrackingModal = (code) => {
    trackingCode.value = code;
    trackingLoading.value = true;
    trackingEvents.value = [];
    trackingError.value = '';
    showingTrackingModal.value = true;

    fetch(`/api/rastreio/${code}`)
        .then(res => res.json())
        .then(data => {
            trackingLoading.value = false;
            if (data.success && data.events && data.events.length > 0) {
                trackingEvents.value = data.events;
                trackingLink.value = data.link;
            } else {
                trackingError.value = data.message || 'Código não encontrado nos Correios ou recém postado.';
            }
        })
        .catch(err => {
            trackingLoading.value = false;
            trackingError.value = 'Falha ao comunicar com os Correios. Tente mais tarde.';
        });
};

const closeTrackingModal = () => {
    showingTrackingModal.value = false;
};

// Chart Logic
const salesChartCanvas = ref(null);
let chartInstance = null;

const initChart = () => {
    if (!salesChartCanvas.value || !props.salesChart.length) return;

    if (chartInstance) {
        chartInstance.destroy();
    }

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#a1a1aa' : '#71717a'; 
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
    const lineColor = isDark ? '#e4e4e7' : '#18181b'; 
    const bgColor = isDark ? 'rgba(228, 228, 231, 0.1)' : 'rgba(24, 24, 27, 0.1)';

    chartInstance = new Chart(salesChartCanvas.value, {
        type: 'line',
        data: {
            labels: props.salesChart.map(s => {
                const parts = s.date.split('-'); // Fix timezone visual shifting
                return `${parts[2]}/${parts[1]}`;
            }),
            datasets: [{
                label: 'Vendas (R$)',
                data: props.salesChart.map(s => parseFloat(s.total)),
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
                                label += formatCurrency(context.parsed.y);
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
};

// Handle dark mode mutations
let observer = null;

onMounted(() => {
    initChart();
    
    observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === "class") {
                initChart(); // Re-initialize to apply correct theme colors
            }
        });
    });
    
    observer.observe(document.documentElement, { attributes: true });
});

onUnmounted(() => {
    if (observer) observer.disconnect();
    if (chartInstance) chartInstance.destroy();
});
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <div class="px-6 py-8">
            <!-- KPIs -->
            <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-4 sm:gap-6">
                <div class="relative flex items-center gap-4 p-5 overflow-hidden transition-all bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl hover:border-zinc-300 dark:hover:border-zinc-700 hover:shadow-md group">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-300 dark:bg-zinc-700"></div>
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 text-xl transition-transform rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 group-hover:scale-110">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-medium tracking-wider uppercase truncate text-zinc-500 dark:text-zinc-400">Produtos Ativos</div>
                        <div class="text-2xl font-bold truncate text-zinc-900 dark:text-white mt-0.5">{{ totalProducts }}</div>
                    </div>
                </div>
                <div class="relative flex items-center gap-4 p-5 overflow-hidden transition-all bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl hover:border-zinc-300 dark:hover:border-zinc-700 hover:shadow-md group">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-300 dark:bg-zinc-700"></div>
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 text-xl transition-transform rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 group-hover:scale-110">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-medium tracking-wider uppercase truncate text-zinc-500 dark:text-zinc-400">Total de Vendas</div>
                        <div class="text-2xl font-bold truncate text-zinc-900 dark:text-white mt-0.5">{{ totalSales }}</div>
                    </div>
                </div>
                <div class="relative flex items-center gap-4 p-5 overflow-hidden transition-all bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl hover:border-zinc-300 dark:hover:border-zinc-700 hover:shadow-md group">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-300 dark:bg-zinc-700"></div>
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 text-xl transition-transform rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 group-hover:scale-110">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-medium tracking-wider uppercase truncate text-zinc-500 dark:text-zinc-400">Receita Total</div>
                        <div class="text-xl font-bold truncate text-zinc-900 dark:text-white mt-0.5">{{ formatCurrency(totalRevenue) }}</div>
                    </div>
                </div>
                <div class="relative flex items-center gap-4 p-5 overflow-hidden transition-all bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl hover:border-zinc-300 dark:hover:border-zinc-700 hover:shadow-md group">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-300 dark:bg-zinc-700"></div>
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 text-xl transition-transform rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 group-hover:scale-110">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-medium tracking-wider uppercase truncate text-zinc-500 dark:text-zinc-400">Tarefas Pendentes</div>
                        <div class="text-2xl font-bold truncate text-zinc-900 dark:text-white mt-0.5">{{ pendingTasks }}</div>
                    </div>
                </div>
            </div>

            <!-- Plan Usage Card -->
            <div v-if="currentPlan" class="p-5 mb-8 bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-gauge-high text-indigo-500"></i>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Uso do Plano — <span class="text-indigo-500">{{ currentPlan.name }}</span></h3>
                    </div>
                    <Link href="/painel/assinatura" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                        <span v-if="currentPlan.slug === 'free'"><i class="mr-1 fas fa-bolt"></i>Fazer Upgrade</span>
                        <span v-else>Ver planos</span>
                    </Link>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <!-- Products -->
                    <div class="flex items-center gap-3 p-3 border rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border-zinc-100 dark:border-zinc-800">
                        <i class="text-lg fas fa-boxes-stacked text-emerald-500"></i>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1 text-xs">
                                <span class="text-zinc-500 dark:text-zinc-400">Produtos</span>
                                <span class="font-bold text-zinc-700 dark:text-zinc-300" :class="{ 'text-amber-500': planUsage.products && planUsage.products.limit && (planUsage.products.current / planUsage.products.limit) >= 0.8 }">
                                    {{ planUsage.products.current }}/{{ planUsage.products.limit || '∞' }}
                                </span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700">
                                <div v-if="planUsage.products.limit" 
                                     class="h-1.5 transition-all duration-500 rounded-full" 
                                     :class="{ 'bg-red-500': (planUsage.products.current / planUsage.products.limit) >= 0.9, 'bg-amber-400': (planUsage.products.current / planUsage.products.limit) >= 0.7 && (planUsage.products.current / planUsage.products.limit) < 0.9, 'bg-emerald-500': (planUsage.products.current / planUsage.products.limit) < 0.7 }"
                                     :style="{ width: Math.min((planUsage.products.current / planUsage.products.limit) * 100, 100) + '%' }"></div>
                                <div v-else class="w-full h-1.5 rounded-full bg-emerald-500/30"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Sales -->
                    <div class="flex items-center gap-3 p-3 border rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border-zinc-100 dark:border-zinc-800">
                        <i class="text-lg text-blue-500 fas fa-cash-register"></i>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1 text-xs">
                                <span class="text-zinc-500 dark:text-zinc-400">Vendas/mês</span>
                                <span class="font-bold text-zinc-700 dark:text-zinc-300" :class="{ 'text-amber-500': planUsage.sales && planUsage.sales.limit && (planUsage.sales.current / planUsage.sales.limit) >= 0.8 }">
                                    {{ planUsage.sales.current }}/{{ planUsage.sales.limit || '∞' }}
                                </span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700">
                                <div v-if="planUsage.sales.limit" 
                                     class="h-1.5 transition-all duration-500 rounded-full" 
                                     :class="{ 'bg-red-500': (planUsage.sales.current / planUsage.sales.limit) >= 0.9, 'bg-amber-400': (planUsage.sales.current / planUsage.sales.limit) >= 0.7 && (planUsage.sales.current / planUsage.sales.limit) < 0.9, 'bg-blue-500': (planUsage.sales.current / planUsage.sales.limit) < 0.7 }"
                                     :style="{ width: Math.min((planUsage.sales.current / planUsage.sales.limit) * 100, 100) + '%' }"></div>
                                <div v-else class="w-full h-1.5 rounded-full bg-blue-500/30"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Wishlists -->
                    <div class="flex items-center gap-3 p-3 border rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border-zinc-100 dark:border-zinc-800">
                        <i class="text-lg text-pink-500 fas fa-heart"></i>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1 text-xs">
                                <span class="text-zinc-500 dark:text-zinc-400">Wishlists</span>
                                <span class="font-bold text-zinc-700 dark:text-zinc-300" :class="{ 'text-amber-500': planUsage.wishlists && planUsage.wishlists.limit && (planUsage.wishlists.current / planUsage.wishlists.limit) >= 0.8 }">
                                    {{ planUsage.wishlists.current }}/{{ planUsage.wishlists.limit || '∞' }}
                                </span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700">
                                <div v-if="planUsage.wishlists.limit" 
                                     class="h-1.5 transition-all duration-500 rounded-full" 
                                     :class="{ 'bg-red-500': (planUsage.wishlists.current / planUsage.wishlists.limit) >= 0.9, 'bg-amber-400': (planUsage.wishlists.current / planUsage.wishlists.limit) >= 0.7 && (planUsage.wishlists.current / planUsage.wishlists.limit) < 0.9, 'bg-pink-500': (planUsage.wishlists.current / planUsage.wishlists.limit) < 0.7 }"
                                     :style="{ width: Math.min((planUsage.wishlists.current / planUsage.wishlists.limit) * 100, 100) + '%' }"></div>
                                <div v-else class="w-full h-1.5 rounded-full bg-pink-500/30"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-3">
                <!-- Sales Chart -->
                <div class="flex flex-col p-5 bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl sm:p-6 lg:col-span-2">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                            <i class="fas fa-chart-line text-zinc-400"></i> Vendas — Últimos 30 dias
                        </h3>
                    </div>
                    <div class="flex-1 min-h-[300px] w-full relative">
                        <canvas ref="salesChartCanvas"></canvas>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Low Stock -->
                    <div class="p-5 bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <h3 class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                                <i class="fas fa-exclamation-triangle text-amber-500"></i> Estoque Baixo
                            </h3>
                        </div>
                        <div v-if="lowStock.length > 0" class="space-y-3">
                            <div v-for="product in lowStock" :key="product.id" class="flex items-center justify-between text-sm">
                                <span class="pr-2 truncate text-zinc-700 dark:text-zinc-300">{{ product.name }}</span>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full whitespace-nowrap" :class="product.current_stock <= 0 ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400'">
                                    {{ product.current_stock }} un.
                                </span>
                            </div>
                        </div>
                        <p v-else class="py-2 text-sm text-zinc-500">Nenhum produto com estoque baixo</p>
                    </div>

                    <!-- Upcoming Tasks -->
                    <div class="p-5 bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <h3 class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                                <i class="text-blue-500 fas fa-calendar-check"></i> Próximas Tarefas
                            </h3>
                        </div>
                        <div v-if="upcomingTasks.length > 0" class="space-y-3">
                            <div v-for="task in upcomingTasks" :key="task.id" class="flex items-center justify-between p-3 border rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border-zinc-100 dark:border-zinc-800">
                                <div class="min-w-0 pr-2">
                                    <div class="text-sm font-medium truncate text-zinc-900 dark:text-white">{{ task.title }}</div>
                                    <div class="text-xs text-zinc-500 mt-0.5">{{ formatDate(task.due_date) }}</div>
                                </div>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full flex-shrink-0" 
                                    :class="task.priority === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' : 
                                       (task.priority === 'medium' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' : 
                                       'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400')">
                                    {{ task.priority === 'high' ? 'Alta' : (task.priority === 'medium' ? 'Média' : 'Baixa') }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="py-2 text-sm text-zinc-500">Nenhuma tarefa pendente</p>
                    </div>
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="overflow-hidden mb-6 bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                <div class="flex flex-col items-center justify-between gap-4 p-5 border-b sm:flex-row sm:p-6 border-zinc-200 dark:border-zinc-800">
                    <h3 class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                        <i class="fas fa-receipt text-zinc-400"></i> Vendas Recentes
                    </h3>
                    <a href="/painel/vendas/create" class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-medium text-white transition-colors bg-zinc-900 dark:bg-white dark:text-zinc-900 rounded-lg sm:w-auto hover:bg-zinc-800 dark:hover:bg-zinc-200">
                        <i class="fas fa-plus"></i> Nova Venda
                    </a>
                </div>
                
                <div v-if="recentSales.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap">
                        <thead class="text-xs tracking-wider uppercase border-b bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-6 py-4 font-medium">#</th>
                                <th class="px-6 py-4 font-medium">Cliente</th>
                                <th class="px-6 py-4 font-medium">Itens</th>
                                <th class="px-6 py-4 font-medium text-right">Total</th>
                                <th class="px-6 py-4 font-medium text-right">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-zinc-700 dark:text-zinc-300 divide-zinc-200 dark:divide-zinc-800">
                            <tr v-for="sale in recentSales" :key="sale.id" class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-6 py-4">{{ sale.id }}</td>
                                <td class="px-6 py-4 font-medium">{{ sale.customer_name || '—' }}</td>
                                <td class="px-6 py-4 text-zinc-500">{{ sale.items?.length || 0 }} produto(s)</td>
                                <td class="px-6 py-4 font-medium text-right">{{ formatCurrency(sale.total) }}</td>
                                <td class="px-6 py-4 text-right text-zinc-500">{{ formatDate(sale.sale_date || sale.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-6 py-12 text-center">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3 class="mb-1 text-sm font-medium text-zinc-900 dark:text-white">Nenhuma venda registrada ainda</h3>
                    <p class="mb-4 text-sm text-zinc-500">Comece adicionando sua primeira venda ao sistema.</p>
                    <a href="/painel/vendas/create" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors bg-zinc-900 dark:bg-white dark:text-zinc-900 rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200">
                        <i class="fas fa-plus"></i> Registrar Primeira Venda
                    </a>
                </div>
            </div>

            <!-- Active Shippings -->
            <div class="overflow-hidden mb-6 bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                <div class="flex flex-col items-center justify-between gap-4 p-5 border-b sm:flex-row sm:p-6 border-zinc-200 dark:border-zinc-800">
                    <div>
                        <h3 class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                            <i class="fas fa-truck-fast text-sky-500"></i> Fretes em Andamento
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Acompanhe pelo Correio pacotes que foram registrados com rastreamento.</p>
                    </div>
                </div>
                
                <div v-if="activeShippings && activeShippings.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap">
                        <thead class="text-xs tracking-wider uppercase border-b bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-6 py-4 font-medium">Cliente</th>
                                <th class="px-6 py-4 font-medium">Código</th>
                                <th class="px-6 py-4 font-medium text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-zinc-700 dark:text-zinc-300 divide-zinc-200 dark:divide-zinc-800">
                            <tr v-for="shipping in activeShippings" :key="shipping.id" class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-6 py-4 font-medium">{{ shipping.customer_name || 'Venda #' + shipping.id }}</td>
                                <td class="px-6 py-4 font-mono font-semibold text-zinc-600 dark:text-zinc-400">{{ shipping.tracking_code }}</td>
                                <td class="px-6 py-4 text-right cursor-pointer">
                                    <button type="button" @click="openTrackingModal(shipping.tracking_code)" class="inline-flex items-center gap-2 px-3 py-1.5 bg-sky-50 dark:bg-sky-500/10 text-sky-700 dark:text-sky-400 rounded-lg text-xs font-semibold border border-sky-200 dark:border-sky-500/20 hover:bg-sky-100 dark:hover:bg-sky-500/20 transition-colors">
                                        <i class="fas fa-search-location"></i> Rastrear
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-6 py-12 text-center">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="mb-1 text-sm font-medium text-zinc-900 dark:text-white">Nenhum frete com rastreio</h3>
                    <p class="mb-4 text-sm text-zinc-500">Adicione o Código de Rastreio nas suas vendas e ele aparecerá aqui.</p>
                </div>
            </div>

            <!-- Modeler Requests -->
            <div class="overflow-hidden mb-6 bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                <div class="flex flex-col items-center justify-between gap-4 p-5 border-b sm:flex-row sm:p-6 border-zinc-200 dark:border-zinc-800">
                    <div>
                        <h3 class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                            <i class="fas fa-pencil-ruler text-emerald-500"></i> Solicitações de Modelagem da Comunidade
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Últimos pedidos abertos por clientes buscando modeladores.</p>
                    </div>
                </div>
                
                <div v-if="modelerRequests && modelerRequests.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap">
                        <thead class="text-xs tracking-wider uppercase border-b bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-6 py-4 font-medium">Cliente</th>
                                <th class="px-6 py-4 font-medium">Contato</th>
                                <th class="px-6 py-4 font-medium">Descrição</th>
                                <th class="px-6 py-4 font-medium text-right">Orçamento</th>
                                <th class="px-6 py-4 font-medium text-right">Anexo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-zinc-700 dark:text-zinc-300 divide-zinc-200 dark:divide-zinc-800">
                            <tr v-for="request in modelerRequests" :key="request.id" class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-6 py-4 font-medium">{{ request.name }}</td>
                                <td class="px-6 py-4 text-zinc-500">
                                    <div v-if="request.whatsapp" class="flex flex-col">
                                        <a :href="'https://wa.me/' + request.whatsapp.replace(/[^0-9]/g, '')" target="_blank" class="flex items-center gap-1.5 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                                            <i class="fab fa-whatsapp"></i> {{ request.whatsapp }}
                                        </a>
                                        <span class="text-xs">{{ request.email }}</span>
                                    </div>
                                    <a v-else :href="'mailto:' + request.email" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                        <i class="fas fa-envelope"></i> {{ request.email }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-zinc-500 truncate max-w-[250px]" :title="request.description">
                                    {{ request.description }}
                                </td>
                                <td class="px-6 py-4 font-medium text-right">
                                    <span class="px-3 py-1 text-xs border rounded-full bg-zinc-100 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700">{{ request.budget_range }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a v-if="request.image_path" :href="request.image_url" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 rounded-lg text-xs font-semibold border border-emerald-200 dark:border-emerald-500/20 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                                        <i class="fas fa-image"></i> Ver Imagem
                                    </a>
                                    <span v-else class="text-xs text-zinc-400">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-6 py-12 text-center">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3 class="mb-1 text-sm font-medium text-zinc-900 dark:text-white">Nenhuma solicitação no momento</h3>
                    <p class="mb-4 text-sm text-zinc-500">Os clientes ainda não fizeram novos pedidos de modelagem hoje.</p>
                </div>
            </div>
            
        </div>

        <!-- Tracking Modal -->
        <Teleport to="body">
            <div v-if="showingTrackingModal" class="fixed inset-0 z-50 flex items-center justify-center" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div class="fixed inset-0 transition-opacity bg-zinc-900/80 backdrop-blur-sm" @click="closeTrackingModal"></div>
                
                <!-- Modal Panel -->
                <div class="relative w-full max-w-lg mx-4 overflow-hidden transition-all transform bg-white border border-zinc-200 dark:border-zinc-800 dark:bg-zinc-900 rounded-2xl shadow-xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50">
                        <h3 class="flex items-center gap-2 text-lg font-semibold text-zinc-900 dark:text-white">
                            <i class="fas fa-route text-sky-500"></i> Rastreamento
                            <span class="px-2 py-0.5 ml-2 text-sm font-mono text-sky-700 bg-sky-100 dark:bg-sky-900 dark:text-sky-300 rounded-md">{{ trackingCode }}</span>
                        </h3>
                        <button @click="closeTrackingModal" class="transition-colors text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                            <i class="text-xl fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="p-6 overflow-y-auto max-h-[60vh]">
                        <!-- Loading State -->
                        <div v-if="trackingLoading" class="flex flex-col items-center justify-center py-8 text-zinc-500">
                            <i class="mb-3 text-3xl fas fa-spinner fa-spin text-sky-500"></i>
                            <p>Buscando atualizações nos Correios...</p>
                        </div>
                        
                        <!-- Result State -->
                        <div v-else>
                            <!-- Error or Notice -->
                            <div v-if="trackingError" class="py-4 ml-6 text-center text-zinc-500 dark:text-zinc-400">
                                <i class="mb-3 text-4xl fas" :class="trackingError.includes('Falha') ? 'fa-exclamation-triangle text-red-500' : 'fa-box-open text-zinc-300 dark:text-zinc-700'"></i>
                                <p :class="{'text-red-500': trackingError.includes('Falha')}">{{ trackingError }}</p>
                            </div>
                            
                            <!-- Timeline -->
                            <div v-if="trackingEvents.length > 0" class="relative pl-3 space-y-6 border-l-2 border-sky-100 dark:border-sky-900/50">
                                <div v-for="(ev, index) in trackingEvents" :key="index" class="relative pl-6">
                                    <div class="absolute -left-[14px] top-1 flex items-center justify-center w-6 h-6 border-4 rounded-full"
                                         :class="index === 0 ? 'bg-sky-500 border-white dark:border-zinc-900' : 'bg-zinc-300 dark:bg-zinc-600 border-white dark:border-zinc-900'">
                                        <i v-if="index === 0" class="text-xs text-white fas fa-truck"></i>
                                    </div>
                                    <div class="relative p-4 border shadow-sm bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border-zinc-100 dark:border-zinc-800"
                                         :class="{'ring-1 ring-sky-500/30': index === 0}">
                                        <div class="mb-1 font-semibold leading-tight text-zinc-900 dark:text-white">
                                            {{ ev.status || ev.descricao || ev.description || 'Evento Registrado' }}
                                        </div>
                                        <div class="flex items-center gap-3 mb-2 text-xs text-zinc-500">
                                            <span class="flex items-center gap-1.5"><i class="far fa-clock"></i> {{ ev.data || ev.date || '--/--/----' }} às {{ ev.hora || ev.time || '--:--' }}</span>
                                        </div>
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                            <i class="w-4 fas fa-map-marker-alt text-zinc-400"></i> {{ ev.local || ev.location || 'Local Não Informado' }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="trackingLink" class="pt-4 mt-8 text-center border-t border-zinc-200 dark:border-zinc-800">
                                    <a :href="trackingLink" target="_blank" class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-medium text-white transition-colors bg-zinc-900 dark:bg-white dark:text-zinc-900 rounded-lg sm:w-auto hover:bg-zinc-800 dark:hover:bg-zinc-200">
                                        <i class="fas fa-external-link-alt"></i> Ver Histórico Completo no Site
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
