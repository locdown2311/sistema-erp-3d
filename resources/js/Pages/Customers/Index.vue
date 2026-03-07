<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    customers: {
        type: Array,
        required: true
    }
});

// Modal State
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    document: '',
    ie: '',
    cep: '',
    address: '',
    number: '',
    neighborhood: '',
    city: '',
    state: '',
    phone: '',
    email: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (customer) => {
    isEditing.value = true;
    editingId.value = customer.id;
    form.reset();
    form.clearErrors();
    Object.assign(form, customer);
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => {
        form.reset();
        form.clearErrors();
    }, 200);
};

const submit = () => {
    if (isEditing.value) {
        form.put(`/clientes/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/clientes', {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCustomer = (id) => {
    if (confirm('Tem certeza que deseja excluir este cliente?')) {
        router.delete(`/clientes/${id}`, {
            preserveScroll: true,
        });
    }
};

// Via CEP Logic
const loadingCep = ref(false);
const searchCep = async () => {
    const cepNumber = form.cep.replace(/\D/g, '');
    if (cepNumber.length === 8) {
        loadingCep.value = true;
        try {
            const response = await fetch(`https://brasilapi.com.br/api/cep/v2/${cepNumber}`);
            if (response.ok) {
                const data = await response.json();
                form.address = data.street || '';
                form.neighborhood = data.neighborhood || '';
                form.city = data.city || '';
                form.state = data.state || '';
                // form.number is intentionally left alone
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

// Auto format CEP
watch(() => form.cep, (newVal) => {
    if (newVal) {
        let value = newVal.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        form.cep = value;
    }
});

</script>

<template>
    <AppLayout>
        <Head title="Clientes" />
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="font-semibold text-xl text-zinc-800 dark:text-zinc-200 leading-tight">
                    Gestão de Clientes
                </h2>
                <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                    <i class="fas fa-plus mr-2"></i> Novo Cliente
                </button>
            </div>
        </template>

        <div class="w-full">
            <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-users text-indigo-500"></i> Seus Clientes
                    </h3>
                </div>

                <!-- Table View -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-700">
                                <th class="py-4 px-6 text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Cliente</th>
                                <th class="py-4 px-6 text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Documento</th>
                                <th class="py-4 px-6 text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider hidden sm:table-cell">Contato</th>
                                <th class="py-4 px-6 text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider hidden md:table-cell">Localização</th>
                                <th class="py-4 px-6 text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-right w-28">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800/60">
                            <tr v-for="customer in customers" :key="customer.id" class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors group">
                                <td class="py-5 px-6 align-middle">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/40 dark:to-purple-900/40 border border-indigo-200/50 dark:border-indigo-700/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
                                            {{ customer.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-sm text-zinc-900 dark:text-zinc-100">{{ customer.name }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">ID: #{{ String(customer.id).padStart(4, '0') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-5 px-6 align-middle">
                                    <div class="flex flex-col gap-1">
                                        <span v-if="customer.document" class="inline-flex items-center gap-1.5 text-sm text-zinc-700 dark:text-zinc-300 font-mono bg-zinc-100 dark:bg-zinc-800/60 px-2.5 py-1 rounded w-max border border-zinc-200 dark:border-zinc-700/50">
                                            <i class="fas fa-id-card text-xs text-zinc-400"></i> {{ customer.document }}
                                        </span>
                                        <span v-else class="text-sm text-zinc-400 dark:text-zinc-500 italic">Não informado</span>
                                        <span v-if="customer.ie" class="text-xs text-zinc-500 dark:text-zinc-400 ml-1">IE: {{ customer.ie }}</span>
                                    </div>
                                </td>
                                <td class="py-5 px-6 align-middle hidden sm:table-cell">
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400 flex flex-col gap-1.5">
                                        <span v-if="customer.phone" class="flex items-center gap-2 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors w-max"><i class="fas fa-phone-alt text-zinc-400 w-3 text-center"></i> {{ customer.phone }}</span>
                                        <span v-if="customer.email" class="flex items-center gap-2 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors w-max"><i class="fas fa-envelope text-zinc-400 w-3 text-center"></i> {{ customer.email }}</span>
                                        <span v-if="!customer.phone && !customer.email" class="text-zinc-400 italic">Sem contatos</span>
                                    </div>
                                </td>
                                <td class="py-5 px-6 align-middle hidden md:table-cell">
                                    <div v-if="customer.city" class="flex flex-col">
                                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                            {{ customer.city }} <span class="text-zinc-400 font-normal">/ {{ customer.state }}</span>
                                        </span>
                                        <span v-if="customer.neighborhood" class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 truncate max-w-[150px]" :title="customer.neighborhood">
                                            {{ customer.neighborhood }}
                                        </span>
                                    </div>
                                    <span v-else class="text-sm text-zinc-400 dark:text-zinc-500 italic">Sem endereço</span>
                                </td>
                                <td class="py-5 px-6 align-middle text-right">
                                    <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="openEditModal(customer)" class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-500/10 rounded-lg transition-colors border border-transparent hover:border-blue-200 dark:hover:border-blue-800/50" title="Editar">
                                            <i class="fas fa-pen text-sm"></i>
                                        </button>
                                        <button @click="deleteCustomer(customer.id)" class="inline-flex items-center justify-center w-8 h-8 text-red-500 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-500/10 rounded-lg transition-colors border border-transparent hover:border-red-200 dark:hover:border-red-800/50" title="Excluir">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="customers.length === 0">
                                <td colspan="5" class="py-12 px-4 text-center">
                                    <div class="text-zinc-400 dark:text-zinc-500 mb-2">
                                        <i class="fas fa-users-slash text-4xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Nenhum cliente cadastrado ainda.</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-500 mt-1">Clique em "Novo Cliente" para começar a cadastrar sua base.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal CRUD -->
        <Teleport to="body">
            <div v-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-opacity duration-300 bg-zinc-950/80" @click="closeModal">
                <div class="w-full max-w-2xl overflow-y-auto transition-transform duration-300 bg-white border shadow-2xl dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 rounded-xl max-h-[90vh] custom-scrollbar" @click.stop>
                    <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 border-b bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm border-zinc-200 dark:border-zinc-800">
                        <h3 class="flex items-center gap-2 text-lg font-semibold text-zinc-900 dark:text-white" id="modal-title">
                            <i class="fas" :class="isEditing ? 'fa-edit' : 'fa-user-plus'"></i>
                            {{ isEditing ? 'Editar Cliente' : 'Novo Cliente' }}
                        </h3>
                        <button @click="closeModal" class="flex items-center justify-center w-8 h-8 transition-colors rounded-lg text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                            <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nome / Razão Social <span class="text-red-500">*</span></label>
                                            <input type="text" v-model="form.name" class="w-full px-4 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none" required>
                                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Documento (CPF/CNPJ)</label>
                                            <input type="text" v-model="form.document" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none font-mono tracking-wide">
                                            <div v-if="form.errors.document" class="text-red-500 text-xs mt-1">{{ form.errors.document }}</div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">IE (Inscrição Estadual)</label>
                                            <input type="text" v-model="form.ie" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none font-mono">
                                            <div v-if="form.errors.ie" class="text-red-500 text-xs mt-1">{{ form.errors.ie }}</div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Telefone / WhatsApp</label>
                                            <input type="text" v-model="form.phone" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                                            <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">E-mail</label>
                                            <input type="email" v-model="form.email" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                                            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-8 mb-5 border-t border-zinc-200 dark:border-zinc-800 pt-5">
                                        <h4 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 mb-4 flex items-center gap-2">
                                            <i class="fas fa-map-marker-alt text-red-500"></i> Endereço
                                            <span v-if="loadingCep" class="ml-auto text-xs text-indigo-500 animate-pulse font-normal"><i class="fas fa-spinner fa-spin"></i> Buscando...</span>
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                            <div class="md:col-span-4">
                                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">CEP</label>
                                                <input type="text" v-model="form.cep" @blur="searchCep" @keyup.enter.prevent="searchCep" placeholder="00000-000" maxlength="9" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none font-mono">
                                                <div v-if="form.errors.cep" class="text-red-500 text-xs mt-1">{{ form.errors.cep }}</div>
                                            </div>

                                            <div class="md:col-span-8">
                                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Logradouro / Rua</label>
                                                <input type="text" v-model="form.address" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                                                <div v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</div>
                                            </div>
                                            
                                            <div class="md:col-span-3">
                                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Número</label>
                                                <input type="text" v-model="form.number" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none font-mono">
                                                <div v-if="form.errors.number" class="text-red-500 text-xs mt-1">{{ form.errors.number }}</div>
                                            </div>

                                            <div class="md:col-span-4">
                                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Bairro</label>
                                                <input type="text" v-model="form.neighborhood" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
                                                <div v-if="form.errors.neighborhood" class="text-red-500 text-xs mt-1">{{ form.errors.neighborhood }}</div>
                                            </div>

                                            <div class="md:col-span-5">
                                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Cidade / UF</label>
                                                <div class="flex flex-col sm:flex-row gap-2">
                                                    <input type="text" v-model="form.city" class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none min-w-0" placeholder="Cidade">
                                                    <input type="text" v-model="form.state" maxlength="2" class="w-16 px-2 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none text-center uppercase font-mono" placeholder="UF">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                            <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 focus:outline-none transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                                <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                                {{ isEditing ? 'Salvar Alterações' : 'Cadastrar Cliente' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
