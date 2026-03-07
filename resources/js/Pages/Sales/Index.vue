<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    sales: Object,
    filters: Object,
    canExportReports: Boolean
});

// Setup Reactive Filter Form
const filterForm = useForm({
    status: props.filters?.status || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

// Auto-submit filter on change or submit
const applyFilters = () => {
    filterForm.get('/vendas', {
        preserveState: true,
        preserveScroll: true,
    });
};

const showPremiumAlert = () => {
    window.Swal?.fire({
        icon: 'warning', 
        title: 'Recurso Premium', 
        text: 'A exportação de relatórios em PDF é exclusiva dos planos Basic e Pro. Faça upgrade para utilizar!', 
        confirmButtonText: 'Ver Planos', 
        confirmButtonColor: '#6366f1'
    }).then((result) => { 
        if(result.isConfirmed) router.visit('/planos');
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Vendas" />

        <template #header>Vendas</template>

        <template #top-actions>
            <a v-if="canExportReports" :href="`/vendas/relatorio/pdf?status=${filterForm.status}&date_from=${filterForm.date_from}&date_to=${filterForm.date_to}`" target="_blank" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-file-pdf text-red-500"></i> Relatório PDF
            </a>
            <button v-else @click="showPremiumAlert" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-sm font-medium rounded-lg text-zinc-400 dark:text-zinc-500 cursor-pointer overflow-hidden group relative transition-all">
                <i class="fas fa-file-pdf"></i> Relatório PDF
                <div class="absolute inset-0 bg-zinc-900/10 dark:bg-black/40 backdrop-blur-[1px] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg"><i class="fas fa-lock mr-0.5"></i> PRO</span>
                </div>
            </button>
            <Link href="/vendas/create" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
                <i class="fas fa-plus"></i> Nova Venda
            </Link>
        </template>

        <!-- Filters Form -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm mb-6">
            <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-4 items-end sm:items-center">
                <div class="w-full sm:w-auto flex-1">
                    <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Status</label>
                    <select v-model="filterForm.status" @change="applyFilters"
                            class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                        <option value="">Todos os status</option>
                        <option value="completed">Concluída</option>
                        <option value="pending">Pendente</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                </div>
                
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Data Inicial</label>
                    <input type="date" v-model="filterForm.date_from" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                </div>

                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Data Final</label>
                    <input type="date" v-model="filterForm.date_to" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                </div>

                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <i class="fas fa-filter text-zinc-400"></i> Filtrar
                </button>
            </form>
        </div>

        <!-- Sales Data -->
        <div v-if="sales.data && sales.data.length > 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-6 py-4 font-medium">#</th>
                                <th class="px-6 py-4 font-medium">Cliente</th>
                                <th class="px-6 py-4 font-medium text-center">Itens</th>
                                <th class="px-6 py-4 font-medium text-right">Total</th>
                                <th class="px-6 py-4 font-medium text-center">Status</th>
                                <th class="px-6 py-4 font-medium">Data</th>
                                <th class="px-6 py-4 font-medium text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                            <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="px-6 py-4 font-semibold text-zinc-900 dark:text-white">{{ sale.id }}</td>
                                <td class="px-6 py-4">{{ sale.customer_name || '—' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ sale.items ? sale.items.length : 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                    R$ {{ parseFloat(sale.total).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="sale.status === 'completed'" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">Concluída</span>
                                    <span v-else-if="sale.status === 'pending'" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20">Pendente</span>
                                    <span v-else class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20">Cancelada</span>
                                </td>
                                <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">
                                    {{ new Date(sale.sale_date).toLocaleDateString('pt-BR') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="`/vendas/${sale.id}`" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:text-white dark:hover:bg-zinc-800 transition-colors" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </Link>
                                        <Link :href="`/vendas/${sale.id}`" method="delete" as="button" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="sales.links && sales.links.length > 3" class="mt-6">
                <div class="flex flex-wrap -mb-1">
                    <template v-for="(link, key) in sales.links" :key="key">
                        <div v-if="link.url === null" class="px-4 py-3 mb-1 mr-1 text-sm leading-4 text-zinc-400 border border-zinc-200 dark:border-zinc-800 rounded bg-zinc-50 dark:bg-zinc-900" v-html="link.label"></div>
                        <Link v-else :href="link.url" class="px-4 py-3 mb-1 mr-1 text-sm leading-4 border rounded hover:bg-white dark:hover:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-white dark:bg-zinc-800 text-indigo-500 font-bold border-indigo-500': link.active }" v-html="link.label" preserve-scroll />
                    </template>
                </div>
            </div>
        </div>

        <div v-else class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-sm">
            <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400 text-2xl">
                <i class="fas fa-cash-register"></i>
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-1">Nenhuma venda encontrada</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6 max-w-sm mx-auto">Você ainda não registrou nenhuma venda ou os filtros não retornaram resultados.</p>
            <Link href="/vendas/create" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
                <i class="fas fa-plus"></i> Registrar Primeira Venda
            </Link>
        </div>
    </AppLayout>
</template>
