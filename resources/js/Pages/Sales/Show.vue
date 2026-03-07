<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    sale: Object
});

const shippingForm = useForm({
    tracking_code: props.sale.tracking_code || '',
    shipping_status: props.sale.shipping_status || '',
});

const updateTracking = () => {
    shippingForm.put(`/vendas/${props.sale.id}/rastreio`, {
        preserveScroll: true
    });
};

const printPage = () => {
    window.print();
};
</script>

<template>
    <AppLayout>
        <Head :title="`Venda #${sale.id}`" />

        <div class="max-w-4xl mx-auto print:max-w-full print:m-0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6 print:border-none print:shadow-none print:mb-4">
                <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:border-b-2 print:border-black print:pb-2">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2 print:text-black">
                        <i class="fas fa-receipt text-emerald-500 print:text-black"></i> Detalhes da Venda #{{ sale.id }}
                    </h3>
                    <span :class="[
                        'inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full border print:border-black print:bg-transparent print:text-black',
                        sale.status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20' : 
                        (sale.status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200 dark:border-amber-500/20' : 
                        'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border-red-200 dark:border-red-500/20')
                    ]">
                        {{ sale.status === 'completed' ? 'Concluída' : (sale.status === 'pending' ? 'Pendente' : 'Cancelada') }}
                    </span>
                </div>

                <div class="p-6 print:p-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8 print:mb-4">
                        <div>
                            <span class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 print:text-black">Cliente</span>
                            <p class="font-medium text-zinc-900 dark:text-white text-base print:text-black">{{ sale.customer_name || 'Não informado' }}</p>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1 print:text-black">Data da Venda</span>
                            <p class="font-medium text-zinc-900 dark:text-white text-base print:text-black">{{ new Date(sale.sale_date).toLocaleDateString('pt-BR') }}</p>
                        </div>
                    </div>

                    <div v-if="sale.notes" class="mb-8 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700 print:border-black print:bg-transparent print:mb-4">
                        <span class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-2 print:text-black">Observações</span>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300 print:text-black">{{ sale.notes }}</p>
                    </div>

                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4 flex items-center gap-2 print:text-black">
                        <i class="fas fa-box-open text-zinc-400 print:text-black"></i> Itens da Venda
                    </h4>
                    
                    <div class="border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden mb-8 print:border-black print:mb-4">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap print:border-collapse">
                                <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800 print:bg-transparent print:text-black print:border-black">
                                    <tr>
                                        <th class="px-4 py-3 font-medium print:border print:border-black print:p-2">Produto</th>
                                        <th class="px-4 py-3 font-medium text-center print:border print:border-black print:p-2">Variação</th>
                                        <th class="px-4 py-3 font-medium text-center print:border print:border-black print:p-2">Qtd</th>
                                        <th class="px-4 py-3 font-medium text-right print:border print:border-black print:p-2">Preço Unit.</th>
                                        <th class="px-4 py-3 font-medium text-right print:border print:border-black print:p-2">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300 print:divide-black print:text-black">
                                    <tr v-for="item in sale.items" :key="item.id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white print:text-black print:border print:border-black print:p-2">
                                            {{ item.product ? item.product.name : 'Removido' }}
                                        </td>
                                        <td class="px-4 py-3 text-center print:border print:border-black print:p-2">
                                            <span v-if="item.variation" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300 print:bg-transparent print:border print:border-black print:text-black">
                                                {{ item.variation.name }}
                                            </span>
                                            <span v-else class="text-zinc-400 print:text-black">—</span>
                                        </td>
                                        <td class="px-4 py-3 text-center print:border print:border-black print:p-2">{{ item.quantity }}</td>
                                        <td class="px-4 py-3 text-right print:border print:border-black print:p-2">
                                            R$ {{ parseFloat(item.unit_price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-zinc-900 dark:text-white print:text-black print:border print:border-black print:p-2">
                                            R$ {{ (item.quantity * item.unit_price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1 p-5 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 print:bg-transparent print:border-none print:p-0 print:mt-4">
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider print:text-black print:font-bold">Total da Venda</span>
                        <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 print:text-black print:text-2xl">
                            R$ {{ parseFloat(sale.total).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping / Tracking Card (Hidden on Print) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6 print:hidden">
                <div class="px-6 py-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-truck text-indigo-500"></i> Informações de Entrega
                    </h3>
                    
                    <span v-if="sale.shipping_status" :class="[
                        'inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full border',
                        sale.shipping_status === 'Pendente' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border-amber-200 dark:border-amber-500/20' : '',
                        sale.shipping_status === 'Em Trânsito' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 border-blue-200 dark:border-blue-500/20' : '',
                        sale.shipping_status === 'Entregue' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20' : '',
                        sale.shipping_status === 'Devolvido' ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400 border-red-200 dark:border-red-500/20' : ''
                    ]">
                        <i v-if="sale.shipping_status === 'Pendente'" class="fas fa-clock text-[10px]"></i>
                        <i v-if="sale.shipping_status === 'Em Trânsito'" class="fas fa-shipping-fast text-[10px]"></i>
                        <i v-if="sale.shipping_status === 'Entregue'" class="fas fa-check-circle text-[10px]"></i>
                        <i v-if="sale.shipping_status === 'Devolvido'" class="fas fa-undo-alt text-[10px]"></i>
                        {{ sale.shipping_status }}
                    </span>
                    <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                        <i class="fas fa-minus-circle text-[10px]"></i> Não enviado
                    </span>
                </div>

                <div class="p-6">
                    <form @submit.prevent="updateTracking">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">Código de Rastreio</label>
                                <input type="text" v-model="shippingForm.tracking_code" placeholder="Ex: BR123456789BR"
                                    class="w-full px-4 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">Status do Frete</label>
                                <select v-model="shippingForm.shipping_status"
                                    class="w-full px-4 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                                    <option value="">— Não enviado —</option>
                                    <option value="Pendente">📦 Pendente</option>
                                    <option value="Em Trânsito">🚚 Em Trânsito</option>
                                    <option value="Entregue">✅ Entregue</option>
                                    <option value="Devolvido">↩️ Devolvido</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" :disabled="shippingForm.processing" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm disabled:opacity-50">
                            <i :class="shippingForm.processing ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i> Atualizar Rastreio
                        </button>
                    </form>
                </div>
            </div>

            <!-- Actions Row (Hidden on Print) -->
            <div class="flex items-center justify-between print:hidden">
                <Link href="/vendas" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <i class="fas fa-arrow-left"></i> Voltar para Vendas
                </Link>
                
                <button @click="printPage" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    body {
        background-color: white !important;
        color: black !important;
    }
}
</style>
