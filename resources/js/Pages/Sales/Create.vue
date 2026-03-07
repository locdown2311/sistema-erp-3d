<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    products: Array,
    customers: Array,
});

const form = useForm({
    customer_id: '',
    sale_date: new Date().toISOString().split('T')[0],
    tracking_code: '',
    notes: '',
    items: [
        { product_id: '', quantity: 1, unit_price: 0.00 }
    ],
});

// Reactively calculate total whenever items change
const saleTotal = computed(() => {
    return form.items.reduce((total, item) => {
        const qty = parseFloat(item.quantity) || 0;
        const price = parseFloat(item.unit_price) || 0;
        return total + (qty * price);
    }, 0);
});

// Add a new empty item row
const addItem = () => {
    form.items.push({ product_id: '', quantity: 1, unit_price: 0.00 });
};

// Remove a specific row
const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

// When product changes, auto-fill its unit_price
const updatePrice = (index) => {
    const selectedProductId = form.items[index].product_id;
    const product = props.products.find(p => p.id === selectedProductId);
    if (product) {
        form.items[index].unit_price = parseFloat(product.base_price).toFixed(2);
    } else {
        form.items[index].unit_price = 0.00;
    }
};

const submitForm = () => {
    form.post('/vendas', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Nova Venda" />

        <div class="px-4 py-8 mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl mb-6">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
                    <h3 class="flex items-center gap-2 text-lg font-semibold text-zinc-900 dark:text-white">
                        <i class="fas fa-cart-plus text-emerald-500"></i> Registrar Venda
                    </h3>
                </div>

                <form @submit.prevent="submitForm" class="p-6">
                    <div class="grid grid-cols-1 gap-5 mb-5 md:grid-cols-3">
                        <div>
                            <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Cliente <span class="text-red-500">*</span></label>
                            <select v-model="form.customer_id" required 
                                class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none bg-white dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_16px_center] bg-no-repeat pr-10">
                                <option value="" disabled selected>Selecione um cliente...</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                    {{ customer.name }} {{ customer.document ? `(${customer.document})` : '' }}
                                </option>
                            </select>
                            <span v-if="form.errors.customer_id" class="mt-1 text-xs text-red-500">{{ form.errors.customer_id }}</span>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Data da Venda <span class="text-red-500">*</span></label>
                            <input type="date" v-model="form.sale_date" required 
                                class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <span v-if="form.errors.sale_date" class="mt-1 text-xs text-red-500">{{ form.errors.sale_date }}</span>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Cod. Rastreio <span class="font-normal text-zinc-500">(opcional)</span></label>
                            <input type="text" v-model="form.tracking_code" placeholder="Ex: XX123456789BR" 
                                class="w-full px-4 py-2 text-sm transition-shadow uppercase border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-zinc-400 dark:placeholder-zinc-600">
                            <span v-if="form.errors.tracking_code" class="mt-1 text-xs text-red-500">{{ form.errors.tracking_code }}</span>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Observações</label>
                        <textarea v-model="form.notes" rows="2" placeholder="Observações sobre a venda..." 
                                class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none resize-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-zinc-400 dark:placeholder-zinc-600"></textarea>
                        <span v-if="form.errors.notes" class="mt-1 text-xs text-red-500">{{ form.errors.notes }}</span>
                    </div>

                    <!-- Dynamic Items Section -->
                    <div class="pt-6 my-8 border-t border-zinc-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Itens da Venda</h4>
                            <button type="button" @click="addItem" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm">
                                <i class="fas fa-plus"></i> Adicionar Item
                            </button>
                        </div>
                        
                        <span v-if="form.errors.items" class="block mb-4 text-sm font-medium text-red-500">{{ form.errors.items }}</span>

                        <div class="space-y-4">
                            <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_auto] gap-4 items-center p-4 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl">
                                <div>
                                    <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Produto <span class="text-red-500">*</span></label>
                                    <select v-model="item.product_id" required @change="updatePrice(index)"
                                            class="w-full px-3 py-2 text-sm transition-shadow border rounded-lg outline-none appearance-none bg-white dark:bg-zinc-950 border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                                        <option value="" disabled>Selecione...</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">
                                            {{ product.name }} — R$ {{ parseFloat(product.base_price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                        </option>
                                    </select>
                                    <span v-if="form.errors[`items.${index}.product_id`]" class="mt-1 text-xs text-red-500">{{ form.errors[`items.${index}.product_id`] }}</span>
                                </div>
                                
                                <div>
                                    <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Qtd <span class="text-red-500">*</span></label>
                                    <input type="number" v-model="item.quantity" min="1" required 
                                           class="w-full px-3 py-2 text-sm transition-shadow bg-white border rounded-lg outline-none dark:bg-zinc-950 border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div>
                                    <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Preço Unit. <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <span class="mt-0.5 text-xs sm:text-xs text-zinc-500 dark:text-zinc-400">R$</span>
                                        </div>
                                        <input type="number" v-model="item.unit_price" step="0.01" min="0" required 
                                               class="w-full py-2 pl-8 pr-3 text-sm transition-shadow bg-white border rounded-lg outline-none dark:bg-zinc-950 border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>

                                <button type="button" @click="removeItem(index)" :disabled="form.items.length <= 1" class="inline-flex items-center justify-center w-10 h-10 mt-6 transition-colors rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Computed Total -->
                    <div class="flex flex-col items-end p-5 mb-6 border rounded-xl bg-gradient-to-br from-emerald-50 to-cyan-50 dark:from-emerald-950/30 dark:to-cyan-950/30 border-emerald-100 dark:border-emerald-900/50">
                        <span class="mb-1 text-xs font-medium tracking-wider uppercase text-zinc-500 dark:text-zinc-400">Total da Venda</span>
                        <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                            R$ {{ saleTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="flex items-center gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-zinc-900 disabled:opacity-75 disabled:cursor-wait">
                            <i class="fas" :class="form.processing ? 'fa-spinner fa-spin' : 'fa-check'"></i> 
                            Finalizar Venda
                        </button>
                        <Link href="/vendas" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm">
                            Cancelar
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
