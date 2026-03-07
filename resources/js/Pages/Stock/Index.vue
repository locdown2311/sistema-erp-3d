<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    products: Array,
    movements: Array,
});

const form = useForm({
    product_id: '',
    type: 'in',
    quantity: 1,
    notes: ''
});

const submitForm = () => {
    form.post('/estoque', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.type = 'in';
            form.quantity = 1;
        }
    });
};

const getStockBadge = (stock) => {
    if (stock <= 0) {
        return {
            class: 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20',
            text: 'Sem Estoque'
        };
    } else if (stock <= 5) {
        return {
            class: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20',
            text: 'Baixo'
        };
    } else {
        return {
            class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20',
            text: 'Normal'
        };
    }
};

const formatTimeAgo = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.round((now - date) / 1000);
    const minutes = Math.round(seconds / 60);
    const hours = Math.round(minutes / 60);
    const days = Math.round(hours / 24);

    if (seconds < 60) return 'agora mesmo';
    if (minutes < 60) return `há ${minutes} min`;
    if (hours < 24) return `há ${hours} h`;
    if (days === 1) return 'há 1 dia';
    return `há ${days} dias`;
};
</script>

<template>
    <AppLayout>
        <Head title="Estoque" />

        <template #header>Estoque</template>

        <div class="w-full">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:gap-8">
                
                <!-- Stock Overview Left Column -->
                <div class="flex flex-col overflow-hidden bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                    <div class="px-5 py-4 border-b sm:px-6 border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <h3 class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                            <i class="fas fa-warehouse text-amber-500"></i> Estoque Atual
                        </h3>
                    </div>

                    <div v-if="products.length > 0" class="flex-1 overflow-x-auto">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="text-xs uppercase tracking-wider bg-zinc-50 dark:bg-zinc-900 text-zinc-500 dark:text-zinc-400 border-b border-zinc-200 dark:border-zinc-800">
                                <tr>
                                    <th class="px-6 py-4 font-medium">Produto</th>
                                    <th class="px-6 py-4 font-medium text-center">Estoque</th>
                                    <th class="px-6 py-4 font-medium text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-zinc-700 dark:text-zinc-300 divide-zinc-200 dark:divide-zinc-800">
                                <tr v-for="product in products" :key="product.id" class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ product.name }}</div>
                                        <div v-if="product.variations && product.variations.length > 0" class="mt-0.5 text-xs text-zinc-500">
                                            {{ product.variations.length }} variação(ões)
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-center text-zinc-900 dark:text-white">{{ product.current_stock }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span :class="getStockBadge(product.current_stock).class" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full">
                                            {{ getStockBadge(product.current_stock).text }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center flex-1 p-12 text-center">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 class="mb-1 text-sm font-medium text-zinc-900 dark:text-white">Nenhum produto cadastrado</h3>
                        <p class="mb-4 text-sm text-zinc-500">Adicione produtos para visualizar o estoque</p>
                        <Link href="/produtos/create" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-zinc-900 dark:bg-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200">
                            <i class="fas fa-plus"></i> Cadastrar Produto
                        </Link>
                    </div>
                </div>

                <!-- Right Column: Add Movement & History -->
                <div class="flex flex-col gap-6 xl:gap-8">
                    
                    <!-- Add Movement Form -->
                    <div class="overflow-hidden bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                        <div class="px-5 py-4 border-b sm:px-6 border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                            <h3 class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                                <i class="fas fa-exchange-alt text-blue-500"></i> Nova Movimentação
                            </h3>
                        </div>

                        <form @submit.prevent="submitForm" class="p-5 sm:p-6">
                            <div class="mb-5">
                                <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Produto <span class="text-red-500">*</span></label>
                                <select v-model="form.product_id" required 
                                        class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none appearance-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                                    <option value="" disabled>Selecione...</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }}
                                    </option>
                                </select>
                                <span v-if="form.errors.product_id" class="text-xs text-red-500 mt-1">{{ form.errors.product_id }}</span>
                            </div>

                            <div class="grid grid-cols-1 gap-5 mb-5 sm:grid-cols-2">
                                <div>
                                    <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Tipo <span class="text-red-500">*</span></label>
                                    <select v-model="form.type" required 
                                            class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none appearance-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                                        <option value="in">Entrada (+)</option>
                                        <option value="out">Saída (-)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Quantidade <span class="text-red-500">*</span></label>
                                    <input type="number" v-model="form.quantity" min="1" required 
                                           class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Observação</label>
                                <input type="text" v-model="form.notes" placeholder="Ex: Produção do lote #12" 
                                       class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                            </div>

                            <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-zinc-900 dark:bg-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200">
                                <i class="fas" :class="form.processing ? 'fa-spinner fa-spin' : 'fa-check'"></i> 
                                Registrar Movimentação
                            </button>
                        </form>
                    </div>

                    <!-- Recent Movements -->
                    <div class="flex flex-col flex-1 overflow-hidden bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                        <div class="px-5 py-4 border-b sm:px-6 border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                            <h3 class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white">
                                <i class="fas fa-history text-zinc-400"></i> Movimentações Recentes
                            </h3>
                        </div>
                        
                        <div v-if="movements.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            <div v-for="mov in movements" :key="mov.id" class="flex items-center gap-4 px-5 py-3 transition-colors sm:px-6 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <span class="inline-flex items-center justify-center w-10 py-1.5 text-xs font-bold rounded-md shrink-0 border"
                                      :class="mov.type === 'in' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border-red-200 dark:border-red-500/20'">
                                    {{ mov.type === 'in' ? '+' : '-' }}{{ mov.quantity }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium truncate text-zinc-900 dark:text-white">{{ mov.product?.name }}</div>
                                    <div v-if="mov.notes" class="text-xs truncate text-zinc-500 mt-0.5">{{ mov.notes }}</div>
                                </div>
                                <span class="text-xs tracking-tight whitespace-nowrap shrink-0 text-zinc-400">{{ formatTimeAgo(mov.created_at) }}</span>
                            </div>
                        </div>
                        <div v-else class="p-8 text-center">
                            <p class="text-sm text-zinc-500">Nenhuma movimentação registrada</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
