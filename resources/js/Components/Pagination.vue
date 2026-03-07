<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div v-if="link.url === null"
                     class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-zinc-400 border border-zinc-200 dark:border-zinc-800 rounded bg-zinc-50 dark:bg-zinc-900/50"
                     v-html="link.label" />
                <Link v-else
                      class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 focus:border-indigo-500 focus:text-indigo-500 transition-colors"
                      :class="{ 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 border-zinc-900 dark:border-white': link.active, 'border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300': !link.active }"
                      :href="link.url"
                      v-html="link.label" />
            </template>
        </div>
    </div>
</template>
