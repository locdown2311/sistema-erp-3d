<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    filaments: Array,
    types: Array,
    filters: Object,
});

// --- SEARCH & FILTER ---
const search = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || '');

let searchTimeout = null;
watch([search, selectedType], ([newSearch, newType]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/filamentos', {
            search: newSearch,
            type: newType
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300);
});

// --- MODAL & FORM (CREATE/EDIT) ---
const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    brand: '',
    type: '',
    color: '',
    price_per_kg: 0,
    quantity: 1,
    diameter_mm: 1.75,
    weight_grams: 1000,
    remaining_grams: 1000,
    print_temp_min: '',
    print_temp_max: '',
    bed_temp_min: '',
    bed_temp_max: '',
    notes: '',
    active: true
});

const TEMP_DEFAULTS = {
    'PLA':    { printMin: 190, printMax: 220, bedMin: 50,  bedMax: 60 },
    'PLA+':   { printMin: 200, printMax: 230, bedMin: 50,  bedMax: 60 },
    'ABS':    { printMin: 230, printMax: 260, bedMin: 90,  bedMax: 110 },
    'PETG':   { printMin: 220, printMax: 250, bedMin: 70,  bedMax: 80 },
    'TPU':    { printMin: 210, printMax: 230, bedMin: 40,  bedMax: 60 },
    'Nylon':  { printMin: 240, printMax: 270, bedMin: 70,  bedMax: 90 },
    'ASA':    { printMin: 235, printMax: 260, bedMin: 90,  bedMax: 110 },
    'Resina': { printMin: 0,   printMax: 0,   bedMin: 0,   bedMax: 0 },
};

const openModal = (filament = null) => {
    form.clearErrors();
    if (filament) {
        isEditing.value = true;
        form.id = filament.id;
        form.brand = filament.brand || '';
        form.type = filament.type || '';
        form.color = filament.color || '';
        form.price_per_kg = filament.price_per_kg || 0;
        form.quantity = 1; // Hidden on edit usually, but keep a safe default
        form.diameter_mm = filament.diameter_mm || 1.75;
        form.weight_grams = filament.weight_grams || 1000;
        form.remaining_grams = filament.remaining_grams !== null ? filament.remaining_grams : 1000;
        form.print_temp_min = filament.print_temp_min || '';
        form.print_temp_max = filament.print_temp_max || '';
        form.bed_temp_min = filament.bed_temp_min || '';
        form.bed_temp_max = filament.bed_temp_max || '';
        form.notes = filament.notes || '';
    } else {
        isEditing.value = false;
        form.reset();
        form.weight_grams = 1000;
        form.remaining_grams = 1000;
        form.diameter_mm = 1.75;
        form.quantity = 1;
        form.price_per_kg = 0;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const suggestTemps = () => {
    const t = TEMP_DEFAULTS[form.type.trim()];
    if (t) {
        form.print_temp_min = t.printMin;
        form.print_temp_max = t.printMax;
        form.bed_temp_min = t.bedMin;
        form.bed_temp_max = t.bedMax;
    } else {
        alert('Tipo "' + form.type + '" não tem temperaturas pré-definidas. Tipos disponíveis: ' + Object.keys(TEMP_DEFAULTS).join(', '));
    }
};

// Auto-suggest on type change for new filaments
watch(() => form.type, (newType) => {
    if (!isEditing.value) {
        const t = TEMP_DEFAULTS[newType.trim()];
        if (t && t.printMin > 0) {
            form.print_temp_min = t.printMin;
            form.print_temp_max = t.printMax;
            form.bed_temp_min = t.bedMin;
            form.bed_temp_max = t.bedMax;
        }
    }
});

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/filamentos/${form.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/filamentos', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteFilament = (id) => {
    if (confirm('Tem certeza que deseja remover este filamento?')) {
        router.delete(`/filamentos/${id}`);
    }
};

// --- CONSUME ACTION ---
const consumeForms = ref({});

const initConsumeForm = (id) => {
    if (!consumeForms.value[id]) {
        consumeForms.value[id] = useForm({ grams: '' });
    }
    return consumeForms.value[id];
};

const consumeFilament = (filament) => {
    const cForm = consumeForms.value[filament.id];
    if (cForm && cForm.grams > 0) {
        cForm.post(`/filamentos/${filament.id}/consumir`, {
            preserveScroll: true,
            onSuccess: () => {
                cForm.reset();
            }
        });
    }
};

// --- HELPERS ---
const formatCurrency = (value) => {
    if (value === null || value === undefined) return '0,00';
    return Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatNumber = (value) => {
    if (value === null || value === undefined) return '0';
    return Number(value).toLocaleString('pt-BR', { maximumFractionDigits: 0 });
};

const getProgressBarColor = (percent) => {
    if (percent > 30) return 'bg-emerald-500';
    if (percent > 10) return 'bg-amber-500';
    return 'bg-red-500';
};
</script>

<template>
    <AppLayout>
        <Head title="Filamentos" />

        <template #header>Filamentos</template>

        <template #top-actions>
            <button @click="openModal()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-zinc-900 dark:bg-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200">
                <i class="fas fa-plus"></i> Novo Filamento
            </button>
        </template>

        <div class="w-full">
            <!-- Toolbar -->
            <div class="flex flex-col items-center justify-between gap-4 p-4 mb-6 transition-colors bg-white border shadow-sm sm:flex-row sm:p-5 dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                <div class="relative flex-1 w-full max-w-md sm:w-auto">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-zinc-400"></i>
                    </div>
                    <input type="text" v-model="search" placeholder="Buscar filamento ou marca..." 
                           class="w-full py-2 pl-10 pr-4 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                </div>

                <div v-if="types.length > 0" class="w-full sm:w-auto">
                    <select v-model="selectedType" 
                            class="w-full sm:w-48 px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                        <option value="">Todos os tipos</option>
                        <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                    </select>
                </div>
            </div>

            <!-- List -->
            <div v-if="filaments.length > 0" class="overflow-hidden bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap">
                        <thead class="text-xs uppercase tracking-wider bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="px-6 py-4 font-medium">Nome</th>
                                <th class="px-6 py-4 font-medium">Tipo</th>
                                <th class="px-6 py-4 font-medium">Preço/kg</th>
                                <th class="px-6 py-4 font-medium">Restante</th>
                                <th class="px-6 py-4 font-medium text-center">Temp. Impressão</th>
                                <th class="px-6 py-4 font-medium text-center">Temp. Mesa</th>
                                <th class="px-6 py-4 font-medium text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-zinc-700 dark:text-zinc-300 divide-zinc-200 dark:divide-zinc-800">
                            <tr v-for="fil in filaments" :key="fil.id" class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">{{ fil.name }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                        {{ fil.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-emerald-600 dark:text-emerald-400">R$ {{ formatCurrency(fil.price_per_kg) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden min-w-[80px]">
                                            <div class="h-full transition-all duration-500 rounded-full" :class="getProgressBarColor(fil.remaining_percent)" :style="{ width: `${fil.remaining_percent}%` }"></div>
                                        </div>
                                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 min-w-[40px]">{{ formatNumber(fil.remaining_grams) }}g</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="fil.print_temp_min" class="inline-flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800/50 px-2 py-1 rounded border border-zinc-100 dark:border-zinc-800">
                                        <i class="text-red-400 fas fa-temperature-high"></i> {{ fil.print_temp_min }}–{{ fil.print_temp_max }}°C
                                    </span>
                                    <span v-else class="text-zinc-400 dark:text-zinc-600">—</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="fil.bed_temp_min" class="inline-flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800/50 px-2 py-1 rounded border border-zinc-100 dark:border-zinc-800">
                                        <i class="text-blue-400 fas fa-bed"></i> {{ fil.bed_temp_min }}–{{ fil.bed_temp_max }}°C
                                    </span>
                                    <span v-else class="text-zinc-400 dark:text-zinc-600">—</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openModal(fil)" class="inline-flex items-center justify-center w-8 h-8 transition-colors rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:text-white dark:hover:bg-zinc-800" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <form @submit.prevent="consumeFilament(fil)" class="flex items-center gap-1">
                                            <input type="number" v-model="initConsumeForm(fil.id).grams" step="0.01" min="0.01" placeholder="g" class="w-16 h-8 px-2 text-xs transition-colors border rounded-lg outline-none bg-white/50 focus:bg-white dark:bg-zinc-950 border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500 text-center" title="Quantidade a consumir">
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 transition-colors rounded-lg text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:text-amber-500 dark:hover:text-amber-400 dark:hover:bg-amber-500/10" title="Consumir" :disabled="initConsumeForm(fil.id).processing">
                                                <i class="fas fa-fire"></i>
                                            </button>
                                        </form>

                                        <button @click="deleteFilament(fil.id)" class="inline-flex items-center justify-center w-8 h-8 transition-colors rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="p-12 text-center bg-white border shadow-sm dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-2xl rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                    <i class="fas fa-fill-drip"></i>
                </div>
                <h3 class="mb-1 text-base font-semibold text-zinc-900 dark:text-white">Nenhum filamento encontrado</h3>
                <p class="max-w-sm mx-auto mb-6 text-sm text-zinc-500 dark:text-zinc-400">Mantenha o controle do seu estoque de filamentos para evitar que eles acabem no meio de uma impressão.</p>
                <button v-if="!search && !selectedType" @click="openModal()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-zinc-900 dark:bg-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200">
                    <i class="fas fa-plus"></i> Adicionar Primeiro Filamento
                </button>
            </div>
        </div>

        <!-- Create/Edit Modal using Teleport -->
        <Teleport to="body">
            <div v-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-opacity duration-300 bg-zinc-950/80">
                <div class="w-full max-w-2xl overflow-y-auto transition-transform duration-300 bg-white border shadow-2xl dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl max-h-[90vh] custom-scrollbar" @click.stop>
                    <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 border-b bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm border-zinc-200 dark:border-zinc-800">
                        <h3 class="flex items-center gap-2 text-lg font-semibold text-zinc-900 dark:text-white">
                            <i class="fas" :class="isEditing ? 'fa-edit' : 'fa-cube'"></i> 
                            {{ isEditing ? 'Editar Filamento' : 'Novo Filamento' }}
                        </h3>
                        <button @click="closeModal" class="flex items-center justify-center w-8 h-8 transition-colors rounded-lg text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="p-6">
                        <div class="grid grid-cols-1 gap-5 mb-5 md:grid-cols-2">
                            <div>
                                <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Marca <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.brand" required placeholder="Ex: eSUN, 3D Fila" 
                                       class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                                <span v-if="form.errors.brand" class="text-xs text-red-500 mt-1">{{ form.errors.brand }}</span>
                            </div>
                            <div>
                                <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Tipo <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.type" required placeholder="PLA, ABS, PETG..." list="typeList" 
                                       class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                                <datalist id="typeList">
                                    <option value="PLA"></option>
                                    <option value="PLA+"></option>
                                    <option value="ABS"></option>
                                    <option value="PETG"></option>
                                    <option value="TPU"></option>
                                    <option value="Nylon"></option>
                                    <option value="Resina"></option>
                                    <option value="ASA"></option>
                                </datalist>
                                <span v-if="form.errors.type" class="text-xs text-red-500 mt-1">{{ form.errors.type }}</span>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Cor</label>
                            <input type="text" v-model="form.color" placeholder="Ex: Branco, Preto" 
                                   class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                        </div>

                        <div class="grid grid-cols-1 gap-5 mb-5 md:grid-cols-2">
                            <div>
                                <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Preço / kg (R$) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-zinc-500 sm:text-sm">R$</span>
                                    </div>
                                    <input type="number" v-model="form.price_per_kg" step="0.01" min="0" required 
                                           class="w-full py-2 pl-9 pr-4 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                                </div>
                                <span v-if="form.errors.price_per_kg" class="text-xs text-red-500 mt-1">{{ form.errors.price_per_kg }}</span>
                            </div>
                            <div v-show="!isEditing">
                                <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Quantidade de Rolos</label>
                                <input type="number" v-model="form.quantity" min="1" 
                                       class="w-full px-4 py-2 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Diâmetro (mm) <span class="text-red-500">*</span></label>
                            <input type="number" v-model="form.diameter_mm" step="0.01" required 
                                   class="w-full px-4 py-2 text-sm md:w-1/2 transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                        </div>

                        <div class="grid grid-cols-1 gap-5 mb-6 md:grid-cols-2">
                            <div>
                                <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Peso Total (g) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="number" v-model="form.weight_grams" step="0.01" min="0" required 
                                           class="w-full py-2 pl-4 pr-8 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-zinc-500 sm:text-sm">g</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Restante (g)</label>
                                <div class="relative">
                                    <input type="number" v-model="form.remaining_grams" step="0.01" min="0" 
                                           class="w-full py-2 pl-4 pr-8 text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-zinc-500 sm:text-sm">g</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 mb-6 border bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border-zinc-200 dark:border-zinc-800">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                                    <i class="fas fa-temperature-high text-amber-500"></i> Temperaturas
                                </h4>
                                <button type="button" @click="suggestTemps" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                    <i class="fas fa-magic text-amber-500"></i> Sugerir
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">Bico Mín (°C)</label>
                                    <input type="number" v-model="form.print_temp_min" placeholder="190" 
                                           class="w-full px-3 py-2 text-sm transition-shadow bg-white border rounded-lg outline-none dark:bg-zinc-950 border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">Bico Máx (°C)</label>
                                    <input type="number" v-model="form.print_temp_max" placeholder="220" 
                                           class="w-full px-3 py-2 text-sm transition-shadow bg-white border rounded-lg outline-none dark:bg-zinc-950 border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">Mesa Mín (°C)</label>
                                    <input type="number" v-model="form.bed_temp_min" placeholder="50" 
                                           class="w-full px-3 py-2 text-sm transition-shadow bg-white border rounded-lg outline-none dark:bg-zinc-950 border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">Mesa Máx (°C)</label>
                                    <input type="number" v-model="form.bed_temp_max" placeholder="60" 
                                           class="w-full px-3 py-2 text-sm transition-shadow bg-white border rounded-lg outline-none dark:bg-zinc-950 border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block mb-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">Observações</label>
                            <textarea v-model="form.notes" placeholder="Notas sobre o filamento..." 
                                      class="w-full px-4 py-2 min-h-[80px] resize-y text-sm transition-shadow border rounded-lg outline-none bg-zinc-50 dark:bg-zinc-950 border-zinc-300 dark:border-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                            <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium transition-colors border rounded-lg border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                Cancelar
                            </button>
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-zinc-900 dark:bg-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200" :disabled="form.processing">
                                <i class="fas" :class="form.processing ? 'fa-spinner fa-spin' : 'fa-check'"></i> 
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

    </AppLayout>
</template>
