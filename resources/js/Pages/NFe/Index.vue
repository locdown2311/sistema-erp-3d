<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    products: { type: Array, required: true },
    sales: { type: Array, required: true },
    settings: { type: Object, required: true }
});

const user = usePage().props.auth.user;

const loadingCep = ref(false);
const autoFillSaleId = ref('');

// Helper to calculate total for an item
const calculateItemTotal = (item) => {
    const qtd = parseFloat(item.qtd) || 0;
    const vlr = parseFloat(item.vlr_unit) || 0;
    item.vlr_total = (qtd * vlr).toFixed(2);
};

// Form Setup
const form = useForm({
    emit_nome: props.settings.emit_nome || '',
    emit_cnpj: props.settings.emit_cnpj || '',
    emit_ie: props.settings.emit_ie || '',

    tipo_operacao: '1', // 1=Saída, 0=Entrada
    regime_tributario: '1', // 1=Simples Nacional, 2=MEI, 3=Regime Normal

    dest_nome: '',
    dest_cpf: '',
    dest_ie: '',
    dest_cep: '',
    dest_logradouro: '',
    dest_numero: '',
    dest_bairro: '',
    dest_municipio: '',
    dest_uf: '',

    items: [
        {
            descricao: 'Peça em PLA - Impressão 3D',
            ncm: '',
            cfop: '',
            qtd: 1.00,
            vlr_unit: 100.00,
            vlr_total: 100.00,
            autoFillProductId: ''
        }
    ]
});

// UI State for Accordions
const sections = ref({
    gerais: true,
    destinatario: true,
    produtos: true,
    totais: true,
    servicos: false,
    impostos: false,
    fatura: false,
    pagamento: false,
    transporte: false,
    compras: false,
    agropecuaria: false,
    livre: false,
    outras: false
});

const toggleSection = (section) => {
    sections.value[section] = !sections.value[section];
};

const totalNfe = computed(() => {
    return (parseFloat(totalProdutos.value) + parseFloat(totalIpi.value)).toFixed(2);
});

const totalProdutos = computed(() => {
    return form.items.reduce((acc, item) => acc + (parseFloat(item.vlr_total) || 0), 0).toFixed(2);
});

const totalIcms = computed(() => {
    if (form.regime_tributario === '3') {
        const total = parseFloat(totalProdutos.value);
        return (total * 0.18).toFixed(2); // 18% padrão
    }
    return "0.00";
});

const totalIpi = computed(() => {
    if (form.regime_tributario === '3') {
        const total = parseFloat(totalProdutos.value);
        return (total * 0.05).toFixed(2); // 5% padrão
    }
    return "0.00";
});

const totalPis = computed(() => {
    if (form.regime_tributario === '3') {
        const total = parseFloat(totalProdutos.value);
        return (total * 0.0165).toFixed(2); // 1.65% padrão
    }
    return "0.00";
});

const totalCofins = computed(() => {
    if (form.regime_tributario === '3') {
        const total = parseFloat(totalProdutos.value);
        return (total * 0.076).toFixed(2); // 7.6% padrão
    }
    return "0.00";
});

// Auto Formatting CEP
watch(() => form.dest_cep, (newVal) => {
    if (newVal) {
        let value = newVal.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        form.dest_cep = value;
    }
});

// Via CEP Logic
const searchCep = async () => {
    const cep = form.dest_cep.replace(/\D/g, '');
    if (cep.length === 8) {
        loadingCep.value = true;
        try {
            const response = await fetch(`https://brasilapi.com.br/api/cep/v2/${cep}`);
            if (response.ok) {
                const data = await response.json();
                form.dest_logradouro = data.street || '';
                form.dest_bairro = data.neighborhood || '';
                form.dest_municipio = data.city || '';
                form.dest_uf = data.state || '';
                document.getElementById('dest_numero')?.focus();
            } else {
                alert('CEP não encontrado na BrasilAPI.');
            }
        } catch (error) {
            console.error('Erro ao buscar o CEP:', error);
            alert('Erro ao comunicar com o servidor de CEP.');
        } finally {
            loadingCep.value = false;
        }
    }
};

// Item Management
const addItem = () => {
    form.items.push({
        descricao: '', ncm: '', cfop: '',
        qtd: 1.00, vlr_unit: 0.00, vlr_total: 0.00, autoFillProductId: ''
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    } else {
        alert('A nota fiscal deve ter pelo menos um produto.');
    }
};

// Auto Fill Product Data
const handleProductSelect = (index, productId) => {
    const item = form.items[index];
    if (!productId) return;

    const product = props.products.find(p => p.id === parseInt(productId));
    if (product) {
        item.descricao = product.name;
        item.vlr_unit = product.base_price;
        item.ncm = '';
        item.cfop = '';
        calculateItemTotal(item);
        item.autoFillProductId = '';
    }
};

// Watchers for Item Recalculation
watch(() => form.items, (newItems) => {
    newItems.forEach(item => { calculateItemTotal(item); });
}, { deep: true });

// Auto Fill from Sale
const applySaleData = () => {
    if (!autoFillSaleId.value) return;

    const sale = props.sales.find(s => s.id === autoFillSaleId.value);
    if (sale) {
        if (sale.customer) {
            form.dest_nome = sale.customer.name;
            form.dest_cpf = sale.customer.document || '';
            form.dest_ie = sale.customer.ie || '';
            form.dest_cep = sale.customer.cep || '';
            form.dest_logradouro = sale.customer.address || '';
            form.dest_numero = sale.customer.number || '';
            form.dest_bairro = sale.customer.neighborhood || '';
            form.dest_municipio = sale.customer.city || '';
            form.dest_uf = sale.customer.state || '';
        } else {
            form.dest_nome = "Cliente Venda Avulsa #" + sale.id;
        }

        if (sale.items && sale.items.length > 0) {
            form.items = sale.items.map(item => ({
                descricao: item.name,
                ncm: item.ncm || '39269090',
                cfop: props.settings.default_cfop || '5101',
                qtd: item.quantity,
                vlr_unit: item.price,
                vlr_total: item.total,
                autoFillProductId: ''
            }));
        }
        autoFillSaleId.value = '';
        autoFillSaleId.value = '';
    }
};

const submit = (isDraft = false) => {
    const payload = {
        emit_nome: form.emit_nome, emit_cnpj: form.emit_cnpj, emit_ie: form.emit_ie,
        dest_nome: form.dest_nome, dest_cpf: form.dest_cpf, dest_ie: form.dest_ie, dest_cep: form.dest_cep,
        dest_logradouro: form.dest_logradouro, dest_numero: form.dest_numero, dest_bairro: form.dest_bairro,
        dest_municipio: form.dest_municipio, dest_uf: form.dest_uf,

        tipo_operacao: form.tipo_operacao,
        regime_tributario: form.regime_tributario,

        prod_descricao: form.items.map(i => i.descricao),
        prod_ncm: form.items.map(i => i.ncm),
        prod_cfop: form.items.map(i => i.cfop),
        prod_qtd: form.items.map(i => i.qtd),
        prod_vlr_unit: form.items.map(i => i.vlr_unit),
        prod_vlr_total: form.items.map(i => i.vlr_total),
        is_draft: isDraft,
    };

    form.transform(() => payload).post('/nfe/emitir', {
        preserveScroll: true,
    });
};

// Listen for file download trigger from backend
watch(() => usePage().props.flash.download_xml, (downloadUrl) => {
    if (downloadUrl) {
        window.location.href = downloadUrl;
    }
});
</script>

<template>
    <AppLayout>

        <Head title="Criando nova NF-e" />

        <template #header>
            Criando nova NF-e
        </template>

        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 w-full bg-white dark:bg-zinc-950 min-h-screen">

            <!-- Top Header Actions -->
            <div class="flex justify-between items-center mb-6">
                <!-- Emitente Info Box -->
                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                    <div class="font-bold text-zinc-800 dark:text-zinc-200 text-lg mb-1">{{ form.emit_nome }}</div>
                    <div>CNPJ: {{ form.emit_cnpj }} IE: {{ form.emit_ie }}</div>
                    <div class="mt-2 space-x-4">
                        <a href="/configuracoes"
                            class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 text-xs font-medium"><i
                                class="fas fa-edit"></i> Editar dados do emitente</a>
                    </div>
                </div>

                <!-- Number Box -->
                <div
                    class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-4 text-center min-w-[200px] shadow-sm">
                    <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-1">Número da NF-e</div>
                    <div class="text-zinc-400 italic text-sm mb-3">{automático}</div>
                    <div class="grid grid-cols-2 gap-4 divide-x divide-zinc-200 dark:divide-zinc-800">
                        <div>
                            <div class="text-xs text-zinc-500 mb-1">Série</div>
                            <div class="font-semibold text-zinc-700 dark:text-zinc-300">001</div>
                        </div>
                        <div>
                            <div class="text-xs text-zinc-500 mb-1">Ambiente</div>
                            <div class="font-semibold text-zinc-700 dark:text-zinc-300">Teste</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Sale Banner -->
            <div v-if="sales.length > 0"
                class="bg-indigo-50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30 p-4 rounded-lg mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-indigo-800 dark:text-indigo-300">
                    <i class="fas fa-magic text-xl"></i>
                    <div>
                        <div class="font-medium">Facilite o preenchimento</div>
                        <div class="text-sm opacity-80">Importe dados de uma venda existente</div>
                    </div>
                </div>
                <select v-model="autoFillSaleId" @change="applySaleData"
                    class="w-full sm:w-auto bg-white dark:bg-zinc-900 border border-indigo-200 dark:border-indigo-800/50 rounded-md py-2 px-3 text-sm focus:ring-indigo-500 outline-none min-w-[250px] shadow-sm">
                    <option value="">-- Autopreencher Dados --</option>
                    <option v-for="s in sales" :key="s.id" :value="s.id">{{ s.reference }} ({{ s.customer ?
                        s.customer.name :
                        'Avulsa' }})</option>
                </select>
            </div>

            <form @submit.prevent="submit" class="space-y-6">

                <!-- Dados Gerais -->
                <div class="border-b-2 border-emerald-500/20 pb-4 mb-2">
                    <div class="flex justify-between items-center cursor-pointer" @click="toggleSection('gerais')">
                        <h2 class="text-xl font-medium text-emerald-600 dark:text-emerald-500">Dados gerais</h2>
                        <button type="button"
                            class="text-emerald-600 dark:text-emerald-500 text-sm flex items-center gap-1 hover:underline">
                            <i :class="sections.gerais ? 'fas fa-eye-slash' : 'fas fa-eye'"></i> {{ sections.gerais ?
                                'ocultar'
                                : 'mostrar' }}
                        </button>
                    </div>

                    <div v-show="sections.gerais" class="mt-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="col-span-1 lg:col-span-2 space-y-4">
                                <div>
                                    <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Natureza da
                                        operação</label>
                                    <input type="text" value="Venda de mercadorias"
                                        class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                                        readonly>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Tipo de
                                            operação</label>
                                        <select v-model="form.tipo_operacao"
                                            class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none">
                                            <option value="1">1 - Saída</option>
                                            <option value="0">0 - Entrada</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Regime
                                            Tributário</label>
                                        <select v-model="form.regime_tributario"
                                            class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none">
                                            <option value="1">Simples Nacional</option>
                                            <option value="2">MEI</option>
                                            <option value="3">Regime Normal</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="text-xs text-emerald-600 dark:text-emerald-500 mb-1 block font-medium">Destino
                                            da operação</label>
                                        <select
                                            class="w-full border border-emerald-500/50 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none">
                                            <option>Operação interna</option>
                                            <option>Operação interestadual</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Presença do
                                            comprador</label>
                                        <select
                                            class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none">
                                            <option>Operação não presencial (Internet)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="col-span-1 lg:col-span-1 border-l border-zinc-200 dark:border-zinc-800 pl-6 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Data emissão
                                            (atual)</label>
                                        <input type="date"
                                            class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Hora emissão
                                            (atual)</label>
                                        <input type="time"
                                            class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Destinatário -->
                <div class="border-b-2 border-emerald-500/20 pb-4 mb-2 mt-8">
                    <div class="flex justify-between items-center cursor-pointer"
                        @click="toggleSection('destinatario')">
                        <h2 class="text-xl font-medium text-emerald-600 dark:text-emerald-500">Dados do destinatário
                        </h2>
                        <button type="button"
                            class="text-emerald-600 dark:text-emerald-500 text-sm flex items-center gap-1 hover:underline">
                            <i :class="sections.destinatario ? 'fas fa-eye-slash' : 'fas fa-eye'"></i> {{
                                sections.destinatario
                                    ? 'ocultar' : 'mostrar' }}
                        </button>
                    </div>

                    <div v-show="sections.destinatario" class="mt-6">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                            <div class="md:col-span-5">
                                <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Razão/Nome
                                    destinatário <span class="text-red-500">*</span></label>
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-500">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" v-model="form.dest_nome"
                                        class="flex-1 w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-r-md p-2 text-sm focus:ring-emerald-500 outline-none"
                                        required>
                                </div>
                            </div>
                            <div class="md:col-span-4">
                                <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">CPF/CNPJ <span
                                        class="text-red-500">*</span></label>
                                <input type="text" v-model="form.dest_cpf"
                                    class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none"
                                    required>
                            </div>
                            <div class="md:col-span-3">
                                <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Inscrição
                                    Estadual</label>
                                <input type="text" v-model="form.dest_ie"
                                    class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none">
                            </div>

                            <!-- Endereço row 1 -->
                            <div class="md:col-span-3">
                                <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">CEP <span
                                        class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="text" v-model="form.dest_cep" @blur="searchCep"
                                        class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none"
                                        placeholder="00000-000" maxlength="9" required>
                                    <i v-if="loadingCep"
                                        class="fas fa-spinner fa-spin absolute right-3 top-2.5 text-emerald-500"></i>
                                </div>
                            </div>
                            <div class="md:col-span-7">
                                <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Logradouro <span
                                        class="text-red-500">*</span></label>
                                <input type="text" v-model="form.dest_logradouro"
                                    class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none"
                                    required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Número <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="dest_numero" v-model="form.dest_numero"
                                    class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none"
                                    required>
                            </div>

                            <!-- Endereço row 2 -->
                            <div class="md:col-span-5">
                                <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Bairro <span
                                        class="text-red-500">*</span></label>
                                <input type="text" v-model="form.dest_bairro"
                                    class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none"
                                    required>
                            </div>
                            <div class="md:col-span-5">
                                <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Município <span
                                        class="text-red-500">*</span></label>
                                <input type="text" v-model="form.dest_municipio"
                                    class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none"
                                    required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">UF <span
                                        class="text-red-500">*</span></label>
                                <input type="text" v-model="form.dest_uf"
                                    class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none uppercase text-center"
                                    maxlength="2" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Produtos -->
                <div class="border-b-2 border-emerald-500/20 pb-4 mb-2 mt-8">
                    <div class="flex justify-between items-center cursor-pointer" @click="toggleSection('produtos')">
                        <h2 class="text-xl font-medium text-emerald-600 dark:text-emerald-500">Lista de
                            produtos/serviços</h2>
                        <button type="button"
                            class="text-emerald-600 dark:text-emerald-500 text-sm flex items-center gap-1 hover:underline">
                            <i :class="sections.produtos ? 'fas fa-eye-slash' : 'fas fa-eye'"></i> {{ sections.produtos
                                ?
                                'ocultar' : 'mostrar' }}
                        </button>
                    </div>

                    <div v-show="sections.produtos" class="mt-6 space-y-4">
                        <div v-for="(item, index) in form.items" :key="'item-' + index"
                            class="border border-zinc-200 dark:border-zinc-800 rounded-lg p-4 bg-zinc-50/30 dark:bg-zinc-900/40 relative">

                            <button v-show="form.items.length > 1" @click="removeItem(index)" type="button"
                                class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-2">
                                <i class="fas fa-times"></i>
                            </button>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <!-- Produto/Descricao -->
                                <div class="md:col-span-6">
                                    <label
                                        class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block font-medium">Produto
                                        /
                                        Serviço</label>
                                    <input type="text" v-model="item.descricao"
                                        class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none"
                                        required>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">NCM</label>
                                    <input type="text" v-model="item.ncm" list="ncm-options"
                                        :class="{ 'border-red-500': form.errors['prod_ncm.' + index] }"
                                        class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none"
                                        required>
                                    <div v-if="form.errors['prod_ncm.' + index]" class="text-red-500 text-xs mt-1">{{
                                        form.errors['prod_ncm.' + index] }}</div>
                                    <datalist id="ncm-options">
                                        <!-- 3D Printing & Plastics -->
                                        <option value="39269090">3926.90.90 - Outras Obras de Plástico (Geral /
                                            Protótipos 3D)
                                        </option>
                                        <option value="39264000">3926.40.00 - Estatuetas e Artigos de Ornamentação
                                            (Plástico)
                                        </option>
                                        <option value="39249000">3924.90.00 - Artigos de Uso Doméstico (Plástico)
                                        </option>
                                        <option value="39235000">3923.50.00 - Tampas, Cápsulas e Fechos (Plástico)
                                        </option>
                                        <option value="39263000">3926.30.00 - Guarnições para Móveis/Carroçarias
                                            (Plástico)
                                        </option>
                                        <option value="39259090">3925.90.90 - Artigos para Construção Civil (Plástico)
                                        </option>
                                        <!-- Others (Toys, Electronics, Models) -->
                                        <option value="95030099">9503.00.99 - Brinquedos, Quebra-cabeças e Modelos em
                                            Escala
                                        </option>
                                        <option value="84733041">8473.30.41 - Placas de Circuito Impresso com
                                            Componentes
                                            Montados</option>
                                        <option value="85389090">8538.90.90 - Partes P/ Quadros, Painéis e Consoles P/
                                            Controle
                                            Elétrico</option>
                                        <option value="90230000">9023.00.00 - Instrumentos e modelos concebidos para
                                            demonstração</option>
                                    </datalist>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">CFOP</label>
                                    <input type="text" v-model="item.cfop" list="cfop-options"
                                        :class="{ 'border-red-500': form.errors['prod_cfop.' + index] }"
                                        class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none"
                                        required>
                                    <div v-if="form.errors['prod_cfop.' + index]" class="text-red-500 text-xs mt-1">{{
                                        form.errors['prod_cfop.' + index] }}</div>
                                    <datalist id="cfop-options">
                                        <!-- Vendas de Produção (Ex: Peças Impressas Reais) -->
                                        <option value="5101">5101 - Venda de produção do estabelecimento (Dentro do
                                            Estado)
                                        </option>
                                        <option value="6101">6101 - Venda de produção do estabelecimento (Fora do
                                            Estado)
                                        </option>
                                        <option value="6107">6107 - Venda de produção do estabelecimento a não
                                            contribuinte
                                            (Fora do Estado)</option>
                                        <!-- Revendas (Ex: Venda de Filamentos) -->
                                        <option value="5102">5102 - Venda de mercadoria de terceiros (Dentro do Estado)
                                        </option>
                                        <option value="6102">6102 - Venda de mercadoria de terceiros (Fora do Estado)
                                        </option>
                                        <option value="6108">6108 - Venda de mercadoria de terceiros a não contribuinte
                                            (Fora do
                                            Estado)</option>
                                        <option value="5405">5405 - Venda de mercadoria com ICMS ST (Dentro do Estado)
                                        </option>
                                        <!-- Outros / Serviços / Devoluções / Brindes -->
                                        <option value="5910">5910 - Remessa em bonificação, doação ou brinde</option>
                                        <option value="5915">5915 - Remessa de mercadoria ou bem para conserto ou reparo
                                        </option>
                                        <option value="5916">5916 - Retorno de mercadoria ou bem remetido p/ conserto ou
                                            reparo
                                        </option>
                                        <option value="5949">5949 - Outra saída de mercadoria ou prestação de serviço
                                            não
                                            especificado</option>
                                        <option value="1202">1202 - Devolução de venda de mercadoria adquirida de
                                            terceiros
                                        </option>
                                        <option value="2202">2202 - Devolução de mercadoria de terceiros (Fora do
                                            Estado)
                                        </option>
                                    </datalist>
                                </div>

                                <!-- Valores -->
                                <div class="md:col-span-3">
                                    <label
                                        class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Quantidade</label>
                                    <input type="number" step="0.01" min="0.01" v-model="item.qtd"
                                        :class="{ 'border-red-500': form.errors['prod_qtd.' + index] }"
                                        class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none text-right"
                                        required>
                                    <div v-if="form.errors['prod_qtd.' + index]" class="text-red-500 text-xs mt-1">{{
                                        form.errors['prod_qtd.' + index] }}</div>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block">Valor
                                        Unitário</label>
                                    <input type="number" step="0.01" min="0.01" v-model="item.vlr_unit"
                                        :class="{ 'border-red-500': form.errors['prod_vlr_unit.' + index] }"
                                        class="w-full border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 rounded-md p-2 text-sm focus:ring-emerald-500 outline-none text-right"
                                        required>
                                    <div v-if="form.errors['prod_vlr_unit.' + index]" class="text-red-500 text-xs mt-1">
                                        {{
                                            form.errors['prod_vlr_unit.' + index] }}</div>
                                </div>
                                <div class="md:col-span-3">
                                    <label
                                        class="text-xs text-zinc-600 dark:text-zinc-400 mb-1 block font-semibold text-emerald-600">Total
                                        (R$)</label>
                                    <input type="number" step="0.01" v-model="item.vlr_total"
                                        class="w-full border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 rounded-md p-2 text-sm font-semibold text-emerald-800 dark:text-emerald-200 outline-none text-right"
                                        readonly tabindex="-1">
                                </div>
                                <div class="md:col-span-3 flex items-end">
                                    <select v-model="item.autoFillProductId"
                                        @change="handleProductSelect(index, $event.target.value)"
                                        class="w-full text-xs border border-zinc-200 bg-zinc-50 dark:bg-zinc-800 dark:border-zinc-700 rounded-md p-2 cursor-pointer">
                                        <option value="">+ Preencher de catálogo</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">{{
                                            product.name }} (R$ {{ product.base_price }})</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Add Button Row -->
                        <div class="flex gap-2">
                            <button type="button" @click="addItem"
                                class="bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-4 py-2 rounded flex-1 sm:flex-none text-sm shadow-sm transition-colors text-center cursor-pointer">
                                Adicionar produto
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Totais -->
                <div class="border-b border-indigo-200 dark:border-indigo-800 pb-4 mb-2 mt-8">
                    <div class="flex justify-between items-center cursor-pointer" @click="toggleSection('totais')">
                        <h2 class="text-xl font-medium text-indigo-700 dark:text-indigo-400">Totais <span
                                class="text-xs font-normal text-zinc-500 ml-2">(valores calculados
                                automaticamente)</span></h2>
                        <button type="button"
                            class="text-indigo-700 dark:text-indigo-400 text-sm flex items-center gap-1 hover:underline">
                            <i :class="sections.totais ? 'fas fa-eye-slash' : 'fas fa-eye'"></i> {{ sections.totais ?
                                'ocultar'
                                : 'mostrar' }}
                        </button>
                    </div>

                    <div v-show="sections.totais"
                        class="mt-4 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-5 flex flex-col sm:flex-row gap-6">
                        <div class="flex-1">
                            <div v-if="form.regime_tributario === '3'" class="space-y-1 mb-3">
                                <div
                                    class="flex justify-between text-xs py-1 border-b border-zinc-200 dark:border-zinc-700">
                                    <span class="text-zinc-500">Base de Cálculo ICMS</span>
                                    <span class="font-medium">R$ {{ totalProdutos }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-xs py-1 border-b border-zinc-200 dark:border-zinc-700">
                                    <span class="text-zinc-500">Valor Total do ICMS</span>
                                    <span class="font-medium">R$ {{ totalIcms }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-xs py-1 border-b border-zinc-200 dark:border-zinc-700">
                                    <span class="text-zinc-500">Valor Total do IPI</span>
                                    <span class="font-medium text-amber-600">R$ {{ totalIpi }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-xs py-1 border-b border-zinc-200 dark:border-zinc-700">
                                    <span class="text-zinc-500">Valor do PIS</span>
                                    <span class="font-medium">R$ {{ totalPis }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-xs py-1 border-b border-zinc-200 dark:border-zinc-700">
                                    <span class="text-zinc-500">Valor do COFINS</span>
                                    <span class="font-medium">R$ {{ totalCofins }}</span>
                                </div>
                            </div>
                            <div v-else class="text-xs text-zinc-500 mb-3 italic">
                                Regime Tributário atual não destaca impostos nos totais.
                            </div>

                            <div
                                class="flex justify-between text-sm py-1 border-b border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500">Total valor produtos</span>
                                <span class="font-medium">R$ {{ totalProdutos }}</span>
                            </div>
                            <div
                                class="flex justify-between text-base py-2 mt-2 font-bold text-indigo-700 dark:text-indigo-400">
                                <span>TOTAL DA NF</span>
                                <span>R$ {{ totalNfe }}</span>
                            </div>
                        </div>
                        <div class="flex-1 hidden sm:block"></div> <!-- Spacer -->
                    </div>
                </div>

                <!-- Other Sections (Placeholders based on screenshot) -->
                <div v-for="sec in [
                    { id: 'fatura', title: 'Fatura e duplicatas' },
                    { id: 'transporte', title: 'Dados do transporte' },
                    { id: 'compras', title: 'Informações de compras' },
                    { id: 'outras', title: 'Outras informações' }
                ]" :key="sec.id" class="border-b border-indigo-200 dark:border-indigo-800/30 pb-4 mb-2 mt-6">
                    <div class="flex justify-between items-center cursor-pointer" @click="toggleSection(sec.id)">
                        <h2 class="text-lg font-medium text-indigo-600 dark:text-indigo-400">{{ sec.title }}</h2>
                        <button type="button"
                            class="text-indigo-600 dark:text-indigo-400 text-sm flex items-center gap-1 hover:underline">
                            <i :class="sections[sec.id] ? 'fas fa-eye-slash' : 'fas fa-eye'"></i> {{ sections[sec.id] ?
                                'ocultar' : 'mostrar' }}
                        </button>
                    </div>
                    <div v-show="sections[sec.id]">
                        <div
                            class="mt-4 p-4 border border-zinc-200 text-zinc-500 dark:border-zinc-800 rounded-md text-sm text-center bg-zinc-50/50 dark:bg-zinc-900/50">
                            Campos de {{ sec.title.toLowerCase() }} serão implementados conforme necessidade do sistema.
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div
                    class="sticky bottom-0 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md p-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-3 mt-10">
                    <button type="button" @click="submit(true)" :disabled="form.processing"
                        class="px-5 py-2.5 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 font-medium text-sm rounded transition-colors flex items-center gap-2">
                        <i v-if="form.processing && form.is_draft" class="fas fa-spinner fa-spin"></i>
                        <span>Salvar Rascunho (Baixar XML)</span>
                    </button>
                    <button type="button" @click="submit(false)" :disabled="form.processing"
                        class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-medium text-sm rounded shadow-sm transition-colors flex items-center gap-2">
                        <i v-if="form.processing && !form.is_draft" class="fas fa-spinner fa-spin"></i>
                        <span>Salvar e Autenticar na SEFAZ</span>
                    </button>
                </div>

            </form>
        </div>
    </AppLayout>
</template>
