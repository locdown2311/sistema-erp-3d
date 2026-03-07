<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    products: Array,
    costs: Array,
    defaults: Object,
});

// Main calculator form states
const form = useForm({
    product_id: '',
    name: '',
    filament_weight_g: 0,
    filament_price_kg: parseFloat(props.defaults.filament_price_kg) || 0,
    print_time_hours: 0,
    printer_wattage: parseFloat(props.defaults.printer_wattage) || 0,
    kwh_rate: parseFloat(props.defaults.kwh_rate) || 0,
    printer_price: parseFloat(props.defaults.printer_price) || 0,
    printer_lifespan_hours: parseFloat(props.defaults.printer_lifespan_hours) || 0.01,
    post_processing_hours: 0,
    labor_rate: parseFloat(props.defaults.labor_rate) || 0,
    margin_percent: 50,
    
    // Computed fields that will be sent to the backend
    filament_cost: 0,
    energy_cost: 0,
    depreciation_cost: 0,
    labor_cost: 0,
    total_cost: 0,
    suggested_price: 0,
});

// Watch for product changes to auto-fill weight/time based on selected product
watch(() => form.product_id, (newProductId) => {
    if (newProductId) {
        const p = props.products.find(x => x.id === newProductId);
        if (p) {
            form.filament_weight_g = Number(p.weight_grams) || 0;
            form.print_time_hours = Number(p.print_time_hours) || 0;
            form.name = p.name;
        }
    } else {
        form.filament_weight_g = 0;
        form.print_time_hours = 0;
        form.name = '';
    }
});

// Pure Vue Reactive Calculations (Updating the form inputs dynamically)
watch([
    () => form.filament_weight_g, () => form.filament_price_kg,
    () => form.print_time_hours, () => form.printer_wattage,
    () => form.kwh_rate, () => form.printer_price,
    () => form.printer_lifespan_hours, () => form.post_processing_hours,
    () => form.labor_rate, () => form.margin_percent
], () => {
    
    const weight = parseFloat(form.filament_weight_g) || 0;
    const price_kg = parseFloat(form.filament_price_kg) || 0;
    const time = parseFloat(form.print_time_hours) || 0;
    const wattage = parseFloat(form.printer_wattage) || 0;
    const kwh = parseFloat(form.kwh_rate) || 0;
    const pr_price = parseFloat(form.printer_price) || 0;
    const lifespan = parseFloat(form.printer_lifespan_hours) || 0.01;
    const post_h = parseFloat(form.post_processing_hours) || 0;
    const labor_r = parseFloat(form.labor_rate) || 0;
    const margin = parseFloat(form.margin_percent) || 0;

    // Formulas
    const fil_cost = weight * (price_kg / 1000);
    const eng_cost = (wattage / 1000) * time * kwh;
    const dep_cost = (pr_price / lifespan) * time;
    const lab_cost = post_h * labor_r;
    const tot_cost = fil_cost + eng_cost + dep_cost + lab_cost;
    const sugg_price = tot_cost * (1 + (margin / 100));

    // Update form properties for submittal and visual display
    form.filament_cost = fil_cost;
    form.energy_cost = eng_cost;
    form.depreciation_cost = dep_cost;
    form.labor_cost = lab_cost;
    form.total_cost = tot_cost;
    form.suggested_price = sugg_price;
}, { deep: true, immediate: true });

const fmt = (v) => {
    return 'R$ ' + parseFloat(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const hasCalculated = computed(() => form.total_cost > 0);

const saveCost = () => {
    form.post('/custos', {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: reset just the name and specific identifiers while preserving configurations.
            form.product_id = '';
            form.name = '';
        }
    });
};

const deleteCost = (id) => {
    if (confirm('Tem certeza que deseja excluir este cálculo salvo?')) {
        router.delete(`/custos/${id}`, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Custos 3D" />
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 xl:gap-8">
            <!-- Calculator -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden h-fit">
                <div class="px-5 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-calculator text-blue-500"></i> Calculadora Automática
                    </h3>
                </div>

                <div class="p-5 sm:p-6">
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Produto (opcional)</label>
                        <select v-model="form.product_id" 
                                class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                            <option value="">Cálculo avulso</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </option>
                        </select>
                        <span v-if="form.errors.product_id" class="text-xs text-red-500 mt-1 block">{{ form.errors.product_id }}</span>
                    </div>

                    <div class="mb-6 pb-6 border-b border-zinc-200 dark:border-zinc-800">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome do Cálculo</label>
                        <input type="text" v-model="form.name" placeholder="Ex: Vaso Grande PLA" 
                               class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                        <span v-if="form.errors.name" class="text-xs text-red-500 mt-1 block">{{ form.errors.name }}</span>
                    </div>

                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                        <i class="fas fa-syringe text-indigo-500"></i> Filamento
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Peso (g)</label>
                            <input type="number" v-model="form.filament_weight_g" step="0.01" min="0" 
                                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Preço / kg (R$)</label>
                            <input type="number" v-model="form.filament_price_kg" step="0.01" min="0" 
                                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                        </div>
                    </div>

                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                        <i class="fas fa-bolt text-amber-500"></i> Energia
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Tempo de impressão (h)</label>
                            <input type="number" v-model="form.print_time_hours" step="0.01" min="0" 
                                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Potência (W)</label>
                            <input type="number" v-model="form.printer_wattage" step="0.01" min="0" 
                                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                        </div>
                    </div>
                    <div class="mb-8">
                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Tarifa kWh (R$)</label>
                        <input type="number" v-model="form.kwh_rate" step="0.0001" min="0" 
                               class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                    </div>

                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                        <i class="fas fa-tools text-red-500"></i> Depreciação
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Preço da impressora (R$)</label>
                            <input type="number" v-model="form.printer_price" step="0.01" min="0" 
                                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Vida útil (h)</label>
                            <input type="number" v-model="form.printer_lifespan_hours" step="0.01" min="0.01" 
                                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                        </div>
                    </div>

                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                        <i class="fas fa-hands text-emerald-500"></i> Mão de Obra
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Pós-processamento (h)</label>
                            <input type="number" v-model="form.post_processing_hours" step="0.01" min="0" 
                                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Valor/hora (R$)</label>
                            <input type="number" v-model="form.labor_rate" step="0.01" min="0" 
                                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                        </div>
                    </div>

                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                        <i class="fas fa-percentage text-blue-500"></i> Margem
                    </h4>
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Margem de lucro (%)</label>
                        <input type="number" v-model="form.margin_percent" step="0.01" min="0" 
                               class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6 xl:gap-8 overflow-hidden">
                <!-- Results -->
                <div class="bg-zinc-900 dark:bg-black rounded-xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2 mb-6 relative z-10 transition-opacity" :class="{'opacity-50' : !hasCalculated}">
                        <i class="fas" :class="hasCalculated ? 'fa-chart-pie text-emerald-400' : 'fa-hourglass-half text-zinc-400'"></i> 
                        {{ hasCalculated ? 'Resultado do Cálculo' : 'Aguardando valores...' }}
                    </h3>

                    <div class="space-y-3 mb-8 relative z-10">
                        <div class="flex items-center justify-between py-2 border-b border-zinc-800/80">
                            <span class="text-sm text-zinc-400 flex items-center gap-2"><i class="fas fa-syringe text-indigo-400 w-4 text-center"></i> Filamento</span>
                            <span class="font-medium text-white transition-all">{{ fmt(form.filament_cost) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-zinc-800/80">
                            <span class="text-sm text-zinc-400 flex items-center gap-2"><i class="fas fa-bolt text-amber-400 w-4 text-center"></i> Energia</span>
                            <span class="font-medium text-white transition-all">{{ fmt(form.energy_cost) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-zinc-800/80">
                            <span class="text-sm text-zinc-400 flex items-center gap-2"><i class="fas fa-tools text-red-400 w-4 text-center"></i> Depreciação</span>
                            <span class="font-medium text-white transition-all">{{ fmt(form.depreciation_cost) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-zinc-800/80">
                            <span class="text-sm text-zinc-400 flex items-center gap-2"><i class="fas fa-hands text-emerald-400 w-4 text-center"></i> Mão de Obra</span>
                            <span class="font-medium text-white transition-all">{{ fmt(form.labor_cost) }}</span>
                        </div>
                    </div>

                    <div class="flex items-end justify-between mb-8 relative z-10">
                        <span class="text-sm text-zinc-400">Custo Total de Produção</span>
                        <span class="text-2xl font-semibold text-white transition-all">{{ fmt(form.total_cost) }}</span>
                    </div>

                    <div class="bg-gradient-to-br from-emerald-500/10 to-teal-500/10 border border-emerald-500/20 rounded-xl p-6 mb-6 relative z-10 text-center">
                        <div class="text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-2">Preço de Venda Sugerido</div>
                        <div class="text-4xl font-bold text-emerald-400 tracking-tight transition-all">{{ fmt(form.suggested_price) }}</div>
                    </div>

                    <button v-if="hasCalculated" type="button" @click="saveCost" :disabled="form.processing" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-500 text-white text-sm font-semibold rounded-lg hover:bg-emerald-600 transition-colors shadow-sm relative z-10 disabled:opacity-50">
                        <i :class="form.processing ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i> Salvar Este Cálculo
                    </button>
                </div>
            </div>

            <!-- Saved Costs -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex-1 h-fit">
                    <div class="px-5 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-history text-zinc-400"></i> Cálculos Salvos
                        </h3>
                    </div>
                    <div v-if="costs.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div v-for="cost in costs" :key="cost.id" class="flex items-center justify-between px-5 sm:px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <div class="flex-1 min-w-0 pr-4">
                                <div class="font-medium text-sm text-zinc-900 dark:text-white truncate mb-1">
                                    {{ cost.name || (cost.product ? cost.product.name : 'Cálculo #' + cost.id) }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5 flex-wrap">
                                    <span>Custo: <strong class="text-zinc-600 dark:text-zinc-300 font-medium">{{ fmt(cost.total_cost) }}</strong></span>
                                    <i class="fas fa-arrow-right text-[10px] text-zinc-300 dark:text-zinc-600"></i>
                                    <span>Venda: <strong class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ fmt(cost.suggested_price) }}</strong></span>
                                </div>
                            </div>
                            <button @click="deleteCost(cost.id)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors" title="Excluir">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div v-else class="p-8 text-center">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Nenhum cálculo salvo ainda</p>
                    </div>
                </div>
            </div>
    </AppLayout>
</template>
