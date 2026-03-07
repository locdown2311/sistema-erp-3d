<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ImageUpload from '@/Components/ImageUpload.vue';

defineProps({
    categories: Array,
});

const form = useForm({
    name: '',
    category: '',
    description: '',
    base_price: '0.00',
    base_cost: '0.00',
    print_time_hours: '',
    weight_grams: '',
    cropped_image: null,
    image: null,
    extra_images: [],
});

const handleImageUpdate = (dataUrl) => {
    form.cropped_image = dataUrl;
};

const handleExtraImages = (e) => {
    form.extra_images = Array.from(e.target.files);
};

const submit = () => {
    form.post('/produtos', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Novo Produto" />
        <template #header>Novo Produto</template>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden max-w-3xl mb-6">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-plus-circle text-indigo-500"></i> Cadastrar Produto
                </h3>
            </div>

            <form @submit.prevent="submit" class="p-6">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome do Produto <span class="text-red-500">*</span></label>
                    <input type="text" v-model="form.name" required placeholder="Ex: Vaso Geométrico" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600"
                           :class="{ 'border-red-500': form.errors.name }">
                    <div v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Categoria</label>
                    <input type="text" v-model="form.category" placeholder="Ex: Decoração" list="categories" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600"
                           :class="{ 'border-red-500': form.errors.category }">
                    <datalist id="categories">
                        <option v-for="cat in categories" :key="cat" :value="cat"></option>
                    </datalist>
                    <div v-if="form.errors.category" class="mt-1 text-xs text-red-500">{{ form.errors.category }}</div>
                </div>

                <ImageUpload @update:croppedData="handleImageUpdate" />
                <div v-if="form.errors.image_path || form.errors.cropped_image" class="mt-1 text-xs text-red-500">{{ form.errors.image_path || form.errors.cropped_image }}</div>
                <div v-if="form.errors.image" class="mt-1 text-xs text-red-500">{{ form.errors.image }}</div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Imagens Adicionais <span class="text-zinc-400 text-xs font-normal">(até 5, opcional)</span></label>
                    <input type="file" @change="handleExtraImages" multiple accept="image/*" class="w-full text-sm text-zinc-500 dark:text-zinc-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-l-lg file:border-0
                        file:text-sm file:font-medium
                        file:bg-indigo-50 file:text-indigo-700
                        dark:file:bg-indigo-500/10 dark:file:text-indigo-400
                        hover:file:bg-indigo-100 dark:hover:file:bg-indigo-500/20
                        border border-zinc-300 dark:border-zinc-800 rounded-lg bg-zinc-50 dark:bg-zinc-950 cursor-pointer transition-colors"
                        :class="{ 'border-red-500': form.errors.extra_images }">
                    <p class="mt-1 text-xs text-zinc-500">Fotos extras do produto que serão exibidas no carrossel da loja.</p>
                    <div v-if="form.errors.extra_images" class="mt-1 text-xs text-red-500">{{ form.errors.extra_images }}</div>
                    <div v-for="(error, key) in form.errors" :key="key">
                        <div v-if="key.startsWith('extra_images.')" class="mt-1 text-xs text-red-500">{{ error }}</div>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Descrição</label>
                    <textarea v-model="form.description" rows="3" placeholder="Descreva o produto..." 
                              class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none resize-none placeholder-zinc-400 dark:placeholder-zinc-600"
                              :class="{ 'border-red-500': form.errors.description }"></textarea>
                    <div v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description }}</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço de Venda <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">R$</span>
                            </div>
                            <input type="number" v-model="form.base_price" step="0.01" min="0" required 
                                   class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none"
                                   :class="{ 'border-red-500': form.errors.base_price }">
                        </div>
                        <div v-if="form.errors.base_price" class="mt-1 text-xs text-red-500">{{ form.errors.base_price }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Custo Base <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">R$</span>
                            </div>
                            <input type="number" v-model="form.base_cost" step="0.01" min="0" required 
                                   class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none"
                                   :class="{ 'border-red-500': form.errors.base_cost }">
                        </div>
                        <div v-if="form.errors.base_cost" class="mt-1 text-xs text-red-500">{{ form.errors.base_cost }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Tempo de Impressão</label>
                        <div class="relative">
                            <input type="number" v-model="form.print_time_hours" step="0.01" min="0" placeholder="Ex: 4.5" 
                                   class="w-full pr-14 pl-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600"
                                   :class="{ 'border-red-500': form.errors.print_time_hours }">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">horas</span>
                            </div>
                        </div>
                        <div v-if="form.errors.print_time_hours" class="mt-1 text-xs text-red-500">{{ form.errors.print_time_hours }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Peso do Filamento</label>
                        <div class="relative">
                            <input type="number" v-model="form.weight_grams" step="0.01" min="0" placeholder="Ex: 120" 
                                   class="w-full pr-8 pl-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600"
                                   :class="{ 'border-red-500': form.errors.weight_grams }">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">g</span>
                            </div>
                        </div>
                        <div v-if="form.errors.weight_grams" class="mt-1 text-xs text-red-500">{{ form.errors.weight_grams }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-zinc-900 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-check" :class="{ 'fa-spinner fa-spin': form.processing }"></i> Salvar Produto
                    </button>
                    <Link href="/produtos" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
