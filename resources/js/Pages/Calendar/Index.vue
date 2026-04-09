<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    initialTasks: { type: Array, default: () => [] },
});

// State
const currentDate = new Date();
const selectedDate = ref(new Date());
const viewMonth = ref(new Date(currentDate.getFullYear(), currentDate.getMonth(), 1));

const tasks = ref([...props.initialTasks]);
const loading = ref(false);

const typeFilter = ref('todos');
const periodFilter = ref('30_dias');

// Computed
const currentMonthName = computed(() => {
    const month = viewMonth.value.toLocaleString('pt-BR', { month: 'long', year: 'numeric' });
    return month.charAt(0).toUpperCase() + month.slice(1);
});

const isToday = (date) => {
    return date.getDate() === currentDate.getDate() &&
        date.getMonth() === currentDate.getMonth() &&
        date.getFullYear() === currentDate.getFullYear();
};

const isSelected = (date) => {
    return date.getDate() === selectedDate.value.getDate() &&
        date.getMonth() === selectedDate.value.getMonth() &&
        date.getFullYear() === selectedDate.value.getFullYear();
};

const hasTask = (date) => {
    const dateString = date.toISOString().split('T')[0];
    return tasks.value.some(t => t.due_date.startsWith(dateString));
};

// Calendar Days Generation
const calendarDays = computed(() => {
    const year = viewMonth.value.getFullYear();
    const month = viewMonth.value.getMonth();

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    const days = [];

    // Fill empty slots for first week
    const firstDayIndex = firstDay.getDay();
    for (let i = 0; i < firstDayIndex; i++) {
        days.push({ empty: true });
    }

    // Fill month days
    for (let i = 1; i <= lastDay.getDate(); i++) {
        const date = new Date(year, month, i);
        days.push({
            date: date,
            day: i,
            isToday: isToday(date),
            isSelected: isSelected(date),
            hasTask: hasTask(date),
            empty: false
        });
    }

    // Fill remaining days to complete the grid (up to 42 cells)
    while (days.length % 7 !== 0) {
        days.push({ empty: true });
    }

    return days;
});

// Navigation
const prevMonth = () => {
    viewMonth.value = new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth() - 1, 1);
    fetchTasks();
};

const nextMonth = () => {
    viewMonth.value = new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth() + 1, 1);
    fetchTasks();
};

const selectDate = (dateInfo) => {
    if (dateInfo.empty) return;
    selectedDate.value = dateInfo.date;
};

// API
const fetchTasks = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/calendario/tarefas', {
            params: {
                month: viewMonth.value.getMonth() + 1,
                year: viewMonth.value.getFullYear()
            }
        });
        tasks.value = response.data;
    } catch (e) {
        console.error('Error fetching tasks', e);
    } finally {
        loading.value = false;
    }
};

// Ensure tasks are refetched if props change unexpectedly, though usually empty or handled by SSR first
onMounted(() => {
    if (tasks.value.length === 0) fetchTasks();
});

// View filtering (Main Area)
const selectedDateString = computed(() => selectedDate.value.toISOString().split('T')[0]);
const selectedDateTasks = computed(() => {
    return tasks.value.filter(t => t.due_date.startsWith(selectedDateString.value));
});

// Counters
const counters = computed(() => {
    const total = tasks.value.length;
    // Em andamento = tasks with status 'in_progress'
    const emAndamento = tasks.value.filter(t => t.status === 'in_progress').length;

    // Atrasado = tasks not completed where due_date is before today
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Reset time to start of day for fair comparison

    const atrasado = tasks.value.filter(t => {
        if (t.status === 'completed') return false;

        // Parse "YYYY-MM-DD" from DB into a Date object
        const [year, month, day] = t.due_date.split(' ')[0].split('-');
        const dueDate = new Date(year, month - 1, day);

        return dueDate < today;
    }).length;

    return { total, emAndamento, atrasado };
});

const priorityColor = (priority) => {
    switch (priority) {
        case 'high': return 'text-rose-400 bg-rose-500/10 border-rose-500/20';
        case 'medium': return 'text-amber-400 bg-amber-500/10 border-amber-500/20';
        case 'low': return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
        default: return 'text-zinc-400 bg-zinc-500/10 border-zinc-500/20';
    }
};

// CRUD Forms
const isModalOpen = ref(false);
const editingTask = ref(null);
const form = ref({
    title: '',
    description: '',
    due_date: '',
    priority: 'medium',
    status: 'pending',
    color: '#3B82F6'
});

const openModal = (task = null) => {
    if (task) {
        editingTask.value = task;
        form.value = { ...task, due_date: task.due_date.split(' ')[0] }; // Handle DB datetime formatting safely inside local date picker
    } else {
        editingTask.value = null;
        form.value = {
            title: '',
            description: '',
            due_date: selectedDateString.value,
            priority: 'medium',
            status: 'pending',
            color: '#3B82F6'
        };
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const saveTask = async () => {
    try {
        if (editingTask.value) {
            await axios.put(`/calendario/tarefas/${editingTask.value.id}`, form.value);
        } else {
            await axios.post('/calendario/tarefas', form.value);
        }
        closeModal();
        fetchTasks();
    } catch (e) {
        console.error('Error saving task', e);
        alert('Erro ao salvar tarefa. Verifique os campos.');
    }
};

const deleteTask = async (id) => {
    if (!confirm('Tem certeza que deseja excluir esta tarefa?')) return;
    try {
        await axios.delete(`/calendario/tarefas/${id}`);
        fetchTasks();
    } catch (e) {
        console.error('Error deleting task', e);
    }
};

const completeTask = async (task) => {
    try {
        await axios.put(`/calendario/tarefas/${task.id}`, { ...task, status: 'completed' });
        fetchTasks();
    } catch (e) {
        console.error('Error completing task', e);
    }
};

</script>

<template>
    <AppLayout>

        <Head title="Agenda" />

        <div
            class="min-h-screen bg-transparent dark:bg-[#09090b] text-slate-800 dark:text-zinc-200 p-6 font-sans transition-colors duration-200">

            <div class="max-w-7xl mx-auto flex flex-col md:flex-row gap-6">

                <div class="w-full md:w-64 lg:w-56 shrink-0 flex flex-col gap-4">

                    <div
                        class="bg-white dark:bg-[#18181b] border border-slate-200 dark:border-zinc-800 text-center rounded-xl p-3 shadow-sm transition-colors">
                        <div
                            class="flex items-center justify-between mb-3 text-sm font-medium text-slate-700 dark:text-zinc-300">
                            <button @click="prevMonth"
                                class="w-5 h-5 flex items-center justify-center rounded hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                                <i class="fas fa-chevron-left text-[10px]"></i>
                            </button>
                            <span class="capitalize text-xs font-semibold">{{ currentMonthName }}</span>
                            <button @click="nextMonth"
                                class="w-5 h-5 flex items-center justify-center rounded hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-7 text-center mb-2">
                            <span v-for="day in ['D', 'S', 'T', 'Q', 'Q', 'S', 'S']" :key="day"
                                class="text-[10px] text-slate-400 dark:text-zinc-500 font-medium tracking-wider">
                                {{ day }}
                            </span>
                        </div>

                        <div class="grid grid-cols-7 text-center text-xs font-medium gap-y-2 gap-x-1 relative">
                            <div v-if="loading"
                                class="absolute inset-0 bg-white/80 dark:bg-[#18181b]/80 flex items-center justify-center z-10 rounded">
                                <i class="fas fa-spinner fa-spin text-blue-500"></i>
                            </div>

                            <span v-for="(day, idx) in calendarDays" :key="idx" @click="selectDate(day)" :class="[
                                'w-6 h-6 mx-auto flex items-center justify-center rounded-full transition-all text-slate-600 dark:text-zinc-400 text-[11px]',
                                {
                                    'hover:bg-slate-100 dark:hover:bg-zinc-800 cursor-pointer': !day.empty,
                                    'opacity-0': day.empty,
                                    'bg-blue-600 text-white shadow-md shadow-blue-500/20 dark:shadow-blue-900/20': day.isSelected && !day.isToday,
                                    'text-emerald-600 dark:text-emerald-500 font-bold': day.isToday && !day.isSelected,
                                    'bg-emerald-600 text-white shadow-md shadow-emerald-500/20 dark:shadow-emerald-900/20': day.isToday && day.isSelected,
                                    'border border-blue-500/50': day.hasTask && !day.isSelected
                                }
                            ]">
                                {{ day.day || '' }}
                            </span>
                        </div>

                        <div
                            class="mt-3 pt-3 border-t border-slate-100 dark:border-zinc-800 flex flex-col gap-1.5 text-[10px] text-slate-500 dark:text-zinc-500">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Hoje
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                Selecionado
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full border border-blue-500/50"></span>
                                Com Tarefas
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-[#18181b] border border-slate-200 dark:border-zinc-800 shadow-sm sm:flex-row sm:p-5 rounded-xl p-3 shadow-sm transition-colors">
                        <h3
                            class="text-[9px] text-slate-400 dark:text-zinc-500 font-bold tracking-wider uppercase mb-3 text-center">
                            RESUMO</h3>
                        <div class="flex flex-col gap-2.5 text-xs">
                            <div
                                class="flex items-center justify-between font-medium text-slate-800 dark:text-zinc-300">
                                <span>Mês</span>
                                <span>{{ counters.total }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between text-slate-500 dark:text-zinc-400 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    <span>Em andamento</span>
                                </div>
                                <span class="text-slate-800 dark:text-zinc-300">{{ counters.emAndamento }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between text-slate-500 dark:text-zinc-400 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    <span>Atrasado</span>
                                </div>
                                <span class="text-slate-800 dark:text-zinc-300">{{ counters.atrasado }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="flex-1 flex flex-col">

                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800 dark:text-zinc-100 capitalize">{{
                                selectedDate.toLocaleString('pt-BR', {
                                    weekday: 'long', day: 'numeric', month: 'long'
                                }) }}</h2>
                            <p class="text-xs text-slate-500 dark:text-zinc-500 mt-1 font-medium"
                                v-if="isSelected(currentDate)">Hoje</p>
                            <p class="text-xs text-slate-500 dark:text-zinc-500 mt-1 font-medium" v-else>Selecionado no
                                calendário</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex gap-2">
                                <select v-model="typeFilter"
                                    class="bg-white dark:bg-[#09090b] border font-medium border-slate-200 dark:border-zinc-800 text-slate-700 dark:text-zinc-300 text-xs rounded px-3 py-1.5 outline-none focus:ring-1 focus:ring-blue-500/50 hover:bg-slate-50 dark:hover:bg-[#18181b] cursor-pointer shadow-sm transition-colors">
                                    <option value="todos">Todos</option>
                                </select>
                                <select v-model="periodFilter"
                                    class="bg-white dark:bg-[#09090b] border font-medium border-slate-200 dark:border-zinc-800 text-slate-700 dark:text-zinc-300 text-xs rounded px-3 py-1.5 outline-none focus:ring-1 focus:ring-blue-500/50 hover:bg-slate-50 dark:hover:bg-[#18181b] cursor-pointer shadow-sm transition-colors">
                                    <option value="30_dias">30 dias</option>
                                </select>
                            </div>
                            <button @click="openModal()"
                                class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold py-1.5 px-4 rounded shadow-md shadow-blue-500/20 dark:shadow-blue-900/20 transition-all flex items-center gap-2 border border-transparent dark:border-blue-500">
                                <i class="fas fa-plus"></i> Novo Evento
                            </button>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-[#18181b] border border-slate-200 dark:border-zinc-800 rounded-xl flex-1 p-8 shadow-sm flex flex-col min-h-[400px] transition-colors">

                        <div v-if="selectedDateTasks.length === 0"
                            class="flex-1 flex flex-col items-center justify-center text-center">
                            <div
                                class="text-4xl text-blue-500 mb-4 inline-block bg-blue-50/50 dark:bg-[#09090b] p-4 rounded-2xl border border-blue-100/50 dark:border-zinc-800 transition-colors">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-zinc-300 mb-1">Nenhum evento</h3>
                            <p class="text-sm text-slate-400 dark:text-zinc-500 font-medium">Você não tem compromissos
                                para esta data.</p>
                        </div>

                        <div v-else
                            class="w-full h-full relative pl-8 border-l-2 border-slate-100 dark:border-zinc-800 ml-4 flex flex-col gap-5 pt-2 pb-4">
                            <div v-for="task in selectedDateTasks" :key="task.id"
                                class="relative p-5 bg-white dark:bg-[#09090b] border border-slate-200 dark:border-zinc-800 rounded-xl flex items-center justify-between group hover:border-blue-400/30 dark:hover:border-zinc-600 hover:shadow-md hover:bg-slate-50/50 dark:hover:bg-[#121214] transition-all">

                                <div class="absolute -left-[35px] top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-white dark:bg-[#18181b] border-[3px] z-10 transition-colors"
                                    :style="{ borderColor: task.color || '#3B82F6' }"></div>

                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 dark:text-zinc-200 text-lg">{{ task.title
                                    }}</span>
                                    <span
                                        class="text-xs text-slate-500 dark:text-zinc-500 font-medium mt-1.5 line-clamp-2 pr-8 leading-relaxed">
                                        {{ task.description || 'Nenhum detalhe adicionado' }}
                                    </span>
                                </div>
                                <div class="flex flex-col items-end justify-center gap-4">
                                    <div class="flex gap-2">
                                        <span
                                            :class="['px-2 py-1 text-[10px] rounded uppercase font-bold border dark:border-zinc-800 dark:bg-[#18181b]', priorityColor(task.priority)]">
                                            {{ task.priority }}
                                        </span>
                                        <span :class="['px-2 py-1 text-[9px] rounded uppercase font-bold border',
                                            task.status === 'completed' ? 'text-emerald-600 dark:text-emerald-400 border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10' :
                                                task.status === 'in_progress' ? 'text-blue-600 dark:text-blue-400 border-blue-500/30 bg-blue-50 dark:bg-blue-500/10' :
                                                    'text-slate-500 dark:text-zinc-400 border-slate-300 dark:border-zinc-700 bg-slate-100 dark:bg-zinc-800'
                                        ]">
                                            {{ task.status === 'completed' ? 'CONCLUÍDO' : task.status === 'in_progress'
                                                ? 'EM ANDAMENTO' : 'PENDENTE' }}
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button v-if="task.status !== 'completed'" @click="completeTask(task)"
                                            title="Marcar como Concluído"
                                            class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-500 hover:bg-emerald-500 dark:hover:bg-emerald-500 hover:text-white dark:hover:text-white border border-emerald-100 dark:border-emerald-500/20 hover:border-emerald-500 transition-colors flex items-center justify-center">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button @click="openModal(task)" title="Editar"
                                            class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-500 hover:bg-blue-500 dark:hover:bg-blue-600 hover:text-white dark:hover:text-white border border-blue-100 dark:border-blue-500/20 hover:border-blue-500 transition-colors flex items-center justify-center">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deleteTask(task.id)" title="Excluir"
                                            class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-500 hover:bg-rose-500 dark:hover:bg-rose-600 hover:text-white dark:hover:text-white border border-rose-100 dark:border-rose-500/20 hover:border-rose-500 transition-colors flex items-center justify-center">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 dark:bg-black/70 backdrop-blur-sm p-4 font-sans transition-colors">
            <div
                class="bg-white dark:bg-[#18181b] border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-2xl w-full max-w-md flex flex-col overflow-hidden transition-colors">
                <div
                    class="px-6 py-4 flex justify-between items-center border-b border-slate-100 dark:border-zinc-800 bg-slate-50/50 dark:bg-[#09090b]">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-zinc-100">{{ editingTask ? 'Editar Evento' :
                        'Novo Evento' }}
                    </h3>
                    <button @click="closeModal"
                        class="text-slate-400 dark:text-zinc-500 hover:text-slate-600 dark:hover:text-zinc-300 p-1 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6 flex flex-col gap-4">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 dark:text-zinc-500 mb-1.5 uppercase tracking-wider">Título</label>
                        <input v-model="form.title" type="text" placeholder="Nome do evento"
                            class="w-full bg-white dark:bg-[#09090b] border border-slate-300 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-sm text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 shadow-sm transition-all" />
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 dark:text-zinc-500 mb-1.5 uppercase tracking-wider">Descrição</label>
                        <textarea v-model="form.description" rows="3" placeholder="Detalhes (opcional)"
                            class="w-full bg-white dark:bg-[#09090b] border border-slate-300 dark:border-zinc-800 rounded-lg px-4 py-2 text-sm text-slate-800 dark:text-zinc-200 placeholder-slate-400 dark:placeholder-zinc-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 shadow-sm transition-all"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-500 dark:text-zinc-500 mb-1.5 uppercase tracking-wider">Data</label>
                            <input v-model="form.due_date" type="date"
                                class="w-full bg-white dark:bg-[#09090b] border border-slate-300 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-sm text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 shadow-sm transition-all dark:[color-scheme:dark]" />
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-500 dark:text-zinc-500 mb-1.5 uppercase tracking-wider">Prioridade</label>
                            <select v-model="form.priority"
                                class="w-full bg-white dark:bg-[#09090b] border border-slate-300 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-sm text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 shadow-sm cursor-pointer transition-all">
                                <option value="low">Baixa</option>
                                <option value="medium">Média</option>
                                <option value="high">Alta</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-500 dark:text-zinc-500 mb-1.5 uppercase tracking-wider">Status</label>
                            <select v-model="form.status"
                                class="w-full bg-white dark:bg-[#09090b] border border-slate-300 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-sm text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 shadow-sm cursor-pointer transition-all">
                                <option value="pending">Pendente</option>
                                <option value="in_progress">Em Progresso</option>
                                <option value="completed">Concluída</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-500 dark:text-zinc-500 mb-1.5 uppercase tracking-wider">Cor
                                Base</label>
                            <input v-model="form.color" type="color"
                                class="w-full h-10 bg-white dark:bg-[#09090b] border border-slate-300 dark:border-zinc-800 rounded-lg p-1 cursor-pointer focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 shadow-sm transition-all" />
                        </div>
                    </div>
                </div>
                <div
                    class="px-6 py-4 bg-slate-50 dark:bg-[#09090b] border-t border-slate-100 dark:border-zinc-800 flex justify-end gap-3 rounded-b-2xl transition-colors">
                    <button @click="closeModal"
                        class="px-5 py-2 text-xs font-semibold text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200 hover:bg-slate-200 dark:hover:bg-zinc-800 bg-slate-100 dark:bg-[#18181b] border border-slate-200 dark:border-zinc-800 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button @click="saveTask"
                        class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 dark:border dark:border-blue-500 shadow-md shadow-blue-500/30 dark:shadow-blue-900/30 rounded-lg transition-all focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-[#09090b]">
                        <i class="fas fa-save mr-1.5"></i> Salvar Evento
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
