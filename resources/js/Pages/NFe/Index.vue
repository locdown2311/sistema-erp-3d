<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    products: {
        type: Array,
        required: true
    },
    sales: {
        type: Array,
        required: true
    },
    settings: {
        type: Object,
        required: true
    }
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
            ncm: '39269090',
            cfop: props.settings.default_cfop || '5101',
            qtd: 1.00,
            vlr_unit: 100.00,
            vlr_total: 100.00,
            autoFillProductId: ''
        }
    ]
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
                // The user still needs to fill the number
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
        descricao: '',
        ncm: '39269090',
        cfop: props.settings.default_cfop || '5101',
        qtd: 1.00,
        vlr_unit: 0.00,
        vlr_total: 0.00,
        autoFillProductId: ''
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
        // Se o produto tiver NCM, preenche. Adicional futuro.
        if (product.ncm) {
            item.ncm = product.ncm;
        }
        calculateItemTotal(item);
        // Reset the select visual
        item.autoFillProductId = '';
    }
};

// Watchers for Item Recalculation
form.items.forEach((item, index) => {
    watch([() => item.qtd, () => item.vlr_unit], () => {
        calculateItemTotal(item);
    });
});
// Need a deep watch on the array to catch new items
watch(() => form.items, (newItems) => {
    newItems.forEach(item => {
        calculateItemTotal(item);
    });
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
            // Se for venda avulsa, tenta pegar apenas o nome
            form.dest_nome = "Cliente Venda Avulsa #" + sale.id;
        }

        // Fill Items
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

        // Reset Select Visual
        autoFillSaleId.value = '';
    }
};

const submit = () => {
    // Transformer form data para o formato aceito pelo payload antigo do nfePHP backend
    // No backend eles esperam arrays: prod_descricao[], prod_ncm[], etc.
    const payload = {
        emit_nome: form.emit_nome,
        emit_cnpj: form.emit_cnpj,
        emit_ie: form.emit_ie,
        dest_nome: form.dest_nome,
        dest_cpf: form.dest_cpf,
        dest_ie: form.dest_ie,
        dest_cep: form.dest_cep,
        dest_logradouro: form.dest_logradouro,
        dest_numero: form.dest_numero,
        dest_bairro: form.dest_bairro,
        dest_municipio: form.dest_municipio,
        dest_uf: form.dest_uf,

        prod_descricao: form.items.map(i => i.descricao),
        prod_ncm: form.items.map(i => i.ncm),
        prod_cfop: form.items.map(i => i.cfop),
        prod_qtd: form.items.map(i => i.qtd),
        prod_vlr_unit: form.items.map(i => i.vlr_unit),
        prod_vlr_total: form.items.map(i => i.vlr_total),
    };

    // Submetendo a form com raw inertia post para exibir possiveis erros
    form.transform((data) => payload).post('/nfe/emitir', {
        preserveScroll: true,
        onError: (errors) => {
            console.error("Erros na emissão:", errors);
        }
    });
};
</script>

<template>
    <AppLayout>

        <Head title="Emissão de NF-e" />
        <template #header>Notas Fiscais Eletrônicas</template>

        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full">

            <!-- Aviso de Ambiente e Info -->
            <div
                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 md:p-5 mb-6 shadow-sm flex flex-col sm:flex-row items-start gap-4 justify-between">
                <div class="flex items-start gap-4">
                    <div
                        class="bg-blue-100 dark:bg-blue-800/50 text-blue-600 dark:text-blue-400 p-2 md:p-3 rounded-lg mt-0.5 shrink-0">
                        <i class="fas fa-info-circle text-lg md:text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-blue-800 dark:text-blue-300 font-semibold mb-1">Protótipo de Emissão de NF-e
                        </h3>
                        <p class="text-sm text-blue-700 dark:text-blue-400 leading-relaxed max-w-2xl">
                            Preencha os dados abaixo para gerar um XML estrutural de teste de uma Nota Fiscal Eletrônica
                            (Layout
                            4.00).
                            A NF-e gerada <strong
                                class="font-medium underline decoration-blue-300 dark:decoration-blue-600 underline-offset-2">não
                                será</strong> transmitida para a SEFAZ de forma oficial neste protótipo.
                        </p>
                    </div>
                </div>

                <div v-if="sales.length > 0"
                    class="w-full sm:w-auto mt-4 sm:mt-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 p-3 rounded-lg flex items-center gap-3 shadow-sm min-w-64">
                    <i class="fas fa-magic text-emerald-500"></i>
                    <div class="flex-1">
                        <label class="block text-xs text-zinc-500 mb-1 font-semibold">Importar Venda</label>
                        <select v-model="autoFillSaleId" @change="applySaleData"
                            class="w-full bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-md text-xs py-1.5 focus:ring-emerald-500 outline-none">
                            <option value="">-- Autopreencher Dados --</option>
                            <option v-for="s in sales" :key="s.id" :value="s.id">
                                {{ s.reference }} ({{ s.customer ? s.customer.name : 'Avulsa' }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">

                <div class="space-y-6">

                    <!-- Emitente Card -->
                    <div
                        class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                        <div
                            class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
                            <h3 class="font-semibold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                                <i class="fas fa-store text-indigo-500"></i> Dados do Emitente
                            </h3>
                        </div>

                        <div class="p-5 space-y-5">
                            <div
                                class="bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 text-xs px-3 py-2 rounded-md mb-4 flex items-center gap-2">
                                <i class="fas fa-lock"></i> Dados importados automaticamente das Configurações
                            </div>

                            <div class="cursor-not-allowed opacity-80"
                                title="Altere estes dados nas Configurações Gerais.">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">Razão
                                        Social</label>
                                    <input type="text" v-model="form.emit_nome"
                                        class="w-full px-4 py-2 bg-zinc-100 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm text-zinc-500 dark:text-zinc-500 pointer-events-none"
                                        readonly>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">CNPJ</label>
                                        <input type="text" v-model="form.emit_cnpj"
                                            class="w-full px-4 py-2 bg-zinc-100 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm text-zinc-500 dark:text-zinc-500 pointer-events-none"
                                            readonly>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1.5">Inscrição
                                            Estadual</label>
                                        <input type="text" v-model="form.emit_ie"
                                            class="w-full px-4 py-2 bg-zinc-100 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm text-zinc-500 dark:text-zinc-500 pointer-events-none"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Destinatário Card -->
                    <div
                        class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                        <div
                            class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50 flex justify-between items-center">
                            <h3 class="font-semibold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                                <i class="fas fa-user-tag text-emerald-500"></i> Dados do Destinatário
                            </h3>
                            <span v-if="loadingCep"
                                class="text-xs text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-md">
                                <i class="fas fa-spinner fa-spin"></i> Buscando CEP...
                            </span>
                        </div>

                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-1">
                                    <label
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome /
                                        Razão Social <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.dest_nome"
                                        class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none"
                                        required>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">CPF
                                        / CNPJ
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.dest_cpf"
                                        class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none tracking-wide"
                                        required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Inscrição
                                        Estadual (IE)</label>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-1.5">Apenas se for
                                        <strong>contribuinte do ICMS</strong>. Deixe em branco se pessoa física/isento.
                                    </p>
                                    <input type="text" v-model="form.dest_ie" placeholder="Ex: 123456789012 (opcional)"
                                        class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none tracking-wide font-mono">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-1">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">CEP
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.dest_cep" @blur="searchCep"
                                        @keyup.enter.prevent="searchCep" placeholder="00000-000" maxlength="9"
                                        class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none tracking-wider font-mono"
                                        required :disabled="loadingCep" :class="{ 'opacity-50': loadingCep }">
                                </div>
                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Logradouro
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.dest_logradouro"
                                        class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none"
                                        required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                <div class="sm:col-span-1">
                                    <label
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Número
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" id="dest_numero" v-model="form.dest_numero"
                                        class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none font-mono"
                                        required>
                                </div>
                                <div class="sm:col-span-1">
                                    <label
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Bairro
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.dest_bairro"
                                        class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none"
                                        required>
                                </div>
                                <div class="sm:col-span-1">
                                    <label
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Município
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.dest_municipio"
                                        class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none"
                                        required>
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">UF
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.dest_uf" maxlength="2"
                                        class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none uppercase font-mono text-center"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- End Emitente/Destinatario Sections -->

                <!-- Produtos Card Container -->
                <div
                    class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                    <div
                        class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <h3 class="font-semibold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                            <i class="fas fa-boxes text-orange-500"></i> Itens da Nota
                        </h3>

                        <button type="button" @click="addItem"
                            class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg transition-colors border border-zinc-300 dark:border-zinc-700 shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-plus text-xs"></i> <span>Adicionar Produto</span>
                        </button>
                    </div>

                    <div class="p-5 space-y-5">
                        <TransitionGroup name="list" tag="div" class="space-y-5">
                            <div v-for="(item, index) in form.items" :key="'item-' + index"
                                class="bg-zinc-50/50 dark:bg-zinc-800/20 p-5 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 relative group transition-all duration-200 hover:border-zinc-400 dark:hover:border-zinc-500">

                                <button v-show="form.items.length > 1" @click="removeItem(index)" type="button"
                                    class="absolute -top-3 -right-3 bg-white dark:bg-zinc-800 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 border border-zinc-200 dark:border-zinc-700 rounded-full w-8 h-8 flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition-all duration-200 z-10"
                                    title="Remover Produto">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>

                                <div v-if="products.length > 0"
                                    class="mb-5 flex flex-col sm:flex-row gap-2 sm:items-center bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-2.5 rounded-lg shadow-sm">
                                    <div
                                        class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-500 font-medium px-2 whitespace-nowrap">
                                        <i class="fas fa-wand-magic-sparkles"></i> Auto-preencher:
                                    </div>
                                    <select v-model="item.autoFillProductId"
                                        @change="handleProductSelect(index, $event.target.value)"
                                        class="w-full rounded-md border-0 bg-transparent text-sm text-zinc-700 dark:text-zinc-300 focus:ring-0 cursor-pointer outline-none">
                                        <option value=""
                                            class="bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">--
                                            Selecione um produto do catálogo --</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id"
                                            class="bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                                            {{ product.name }} — R$ {{ product.base_price }}
                                        </option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 mb-5">
                                    <div class="md:col-span-6">
                                        <label
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Descrição
                                            do Produto <span class="text-red-500">*</span></label>
                                        <input type="text" v-model="item.descricao"
                                            class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none"
                                            required>
                                    </div>

                                    <div class="md:col-span-3">
                                        <label
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Cód.
                                            NCM <span class="text-red-500">*</span></label>
                                        <select v-model="item.ncm"
                                            class="w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none"
                                            required>
                                            <optgroup label="Impressão 3D (Plásticos)">
                                                <option value="39269090">3926.90.90 - Outras Obras (Geral / Protótipos)
                                                </option>
                                                <option value="39264000">3926.40.00 - Estatuetas e Ornamentação</option>
                                                <option value="39249000">3924.90.00 - Artigos de Uso Doméstico
                                                    (Organizadores,
                                                    suportes de mesa)</option>
                                                <option value="39263000">3926.30.00 - Guarnições para Móveis/Carroçarias
                                                    (Puxadores, peças técnicas)</option>
                                                <option value="39259090">3925.90.90 - Artigos para Construção (Suportes
                                                    de
                                                    parede, buchas)</option>
                                                <option value="95030099">9503.00.99 - Brinquedos, Quebra-cabeças e
                                                    Modelos em
                                                    Escala</option>
                                                <option value="39235000">3923.50.00 - Tampas, Cápsulas e Fechos</option>
                                            </optgroup>
                                        </select>
                                    </div>

                                    <div class="md:col-span-3">
                                        <label
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">CFOP
                                            <span class="text-red-500">*</span></label>
                                        <select v-model="item.cfop"
                                            class="w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none"
                                            required>
                                            <optgroup label="Estadual">
                                                <option value="5101">5101 - Venda Própria</option>
                                                <option value="5102">5102 - Revenda</option>
                                            </optgroup>
                                            <optgroup label="Interestadual">
                                                <option value="6101">6101 - Venda Própria</option>
                                                <option value="6102">6102 - Revenda</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Quantidade
                                            <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.01" min="0.01" v-model="item.qtd"
                                            class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none text-right font-mono"
                                            required>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Valor
                                            Unitário (R$) <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-sm">R$</span>
                                            <input type="number" step="0.01" min="0.01" v-model="item.vlr_unit"
                                                class="w-full pl-9 pr-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none text-right font-mono"
                                                required>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Valor
                                            Total Bruto (R$)</label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-600 dark:text-emerald-500 text-sm font-medium">R$</span>
                                            <input type="number" step="0.01" v-model="item.vlr_total"
                                                class="bg-emerald-50/50 dark:bg-emerald-900/10 w-full pl-9 pr-4 py-2 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm text-emerald-800 dark:text-emerald-200 font-semibold focus:ring-0 outline-none text-right font-mono"
                                                readonly required tabindex="-1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TransitionGroup>
                    </div>

                    <div
                        class="p-5 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                            Carga e envio processados via backend para segurança da Chave/XML.
                        </div>
                        <button type="submit" :disabled="form.processing"
                            class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 focus:ring-4 focus:ring-emerald-500/30 outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <i :class="form.processing ? 'fas fa-spinner fa-spin' : 'fas fa-file-code'"
                                class="text-lg"></i>
                            Gerar XML e Autorizar (Teste)
                        </button>
                    </div>
                </div>
            </form>

            <div v-if="$page.props.flash.error"
                class="mt-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg flex items-start gap-3">
                <i class="fas fa-exclamation-triangle mt-1"></i>
                <div v-html="$page.props.flash.error"></div>
            </div>

            <div v-if="$page.props.flash.success"
                class="mt-6 p-4 bg-emerald-100 border border-emerald-300 text-emerald-700 rounded-lg flex items-start gap-3">
                <i class="fas fa-check-circle mt-1"></i>
                <div v-html="$page.props.flash.success"></div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}
</style>
