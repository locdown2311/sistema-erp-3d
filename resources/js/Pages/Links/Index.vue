<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    links: Array,
});

const form = useForm({
    title: '',
    url: '',
    icon: 'link', // default icon
});

const commonIcons = [
    { value: 'link', label: 'Link Padrão', icon: 'fas fa-link' },
    { value: 'instagram', label: 'Instagram', icon: 'fab fa-instagram' },
    { value: 'tiktok', label: 'TikTok', icon: 'fab fa-tiktok' },
    { value: 'whatsapp', label: 'WhatsApp', icon: 'fab fa-whatsapp' },
    { value: 'youtube', label: 'YouTube', icon: 'fab fa-youtube' },
    { value: 'facebook', label: 'Facebook', icon: 'fab fa-facebook' },
    { value: 'x-twitter', label: 'X (Twitter)', icon: 'fab fa-x-twitter' },
    { value: 'globe', label: 'Site', icon: 'fas fa-globe' },
    { value: 'envelope', label: 'E-mail', icon: 'fas fa-envelope' },
    { value: 'map-marker-alt', label: 'Localização', icon: 'fas fa-map-marker-alt' },
];

const saveLink = () => {
    form.post('/links', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
};

const deleteLink = (id) => {
    if (confirm('Tem certeza que deseja excluir este link?')) {
        router.delete(`/links/${id}`, {
            preserveScroll: true
        });
    }
};

const toggleStatus = (link) => {
    router.put(`/links/${link.id}`, {
        title: link.title,
        url: link.url,
        icon: link.icon,
        is_active: !link.is_active,
        order: link.order
    }, {
        preserveScroll: true
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Árvore de Links" />
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 xl:gap-8">
            <!-- Add Link Form -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden h-fit">
                <div class="px-5 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex justify-between items-center">
                    <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-plus text-blue-500"></i> Adicionar Novo Link
                    </h3>
                </div>

                <div class="p-5 sm:p-6 text-sm">
                    <form @submit.prevent="saveLink">
                        <div class="mb-4">
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Título do Link</label>
                            <input type="text" v-model="form.title" placeholder="Ex: Meu Instagram" required
                                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <span v-if="form.errors.title" class="text-xs text-red-500 mt-1 block">{{ form.errors.title }}</span>
                        </div>

                        <div class="mb-4">
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">URL / Link</label>
                            <input type="url" v-model="form.url" placeholder="https://..." required
                                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <span v-if="form.errors.url" class="text-xs text-red-500 mt-1 block">{{ form.errors.url }}</span>
                        </div>

                        <div class="mb-6">
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Ícone</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <label v-for="iconOpt in commonIcons" :key="iconOpt.value"
                                       class="flex flex-col items-center justify-center p-3 border rounded-lg cursor-pointer transition-colors"
                                       :class="form.icon === iconOpt.value ? 'bg-blue-50 dark:bg-blue-500/10 border-blue-500 text-blue-600 dark:text-blue-400' : 'bg-white dark:bg-zinc-950 border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-900'">
                                    <input type="radio" v-model="form.icon" :value="iconOpt.value" class="hidden">
                                    <i :class="iconOpt.icon" class="text-xl mb-1"></i>
                                    <span class="text-[10px] font-medium text-center">{{ iconOpt.label }}</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" :disabled="form.processing" 
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50">
                            <i :class="form.processing ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i> Adicionar Link
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Links -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex-1 h-fit">
                <div class="px-5 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                    <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-list text-zinc-400"></i> Meus Links
                    </h3>
                </div>
                
                <div v-if="links.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <div v-for="link in links" :key="link.id" class="flex items-center justify-between px-5 sm:px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <div class="flex items-center gap-4 flex-1 min-w-0 pr-4">
                            <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 rounded-full text-zinc-600 dark:text-zinc-300">
                                <i :class="(commonIcons.find(i => i.value === link.icon) || {icon: 'fas fa-link'}).icon"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm text-zinc-900 dark:text-white truncate mb-0.5" :class="{'opacity-50 line-through': !link.is_active}">
                                    {{ link.title }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                    {{ link.url }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="toggleStatus(link)" class="p-2 text-zinc-400 hover:text-blue-500 transition-colors outline-none" :title="link.is_active ? 'Ocultar Link' : 'Ativar Link'">
                                <i :class="link.is_active ? 'fas fa-eye' : 'fas fa-eye-slash'"></i>
                            </button>
                            <button @click="deleteLink(link.id)" class="p-2 text-zinc-400 hover:text-red-500 transition-colors outline-none" title="Excluir">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="p-8 text-center text-zinc-500 dark:text-zinc-400">
                    Nenhum link adicionado ainda.
                </div>
            </div>
            
            <div class="lg:col-span-2">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-500/10 dark:to-indigo-500/10 border border-blue-100 dark:border-blue-500/20 rounded-xl p-5 flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h4 class="font-semibold text-blue-800 dark:text-blue-300 mb-1 flex items-center gap-2">
                            <i class="fas fa-external-link-alt"></i> Visualizar Árvore de Links
                        </h4>
                        <p class="text-sm text-blue-600/80 dark:text-blue-400/80">Esta é a página pública que você pode colocar na bio do seu perfil.</p>
                    </div>
                    <a :href="'/loja/' + $page.props.auth.user.slug + '/links'" target="_blank" class="px-5 py-2 bg-white dark:bg-zinc-800 text-blue-600 dark:text-blue-400 font-medium rounded-lg shadow-sm border border-blue-200 dark:border-zinc-700 hover:bg-blue-50 dark:hover:bg-zinc-700 transition-colors">
                        Abrir Minha Página
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
