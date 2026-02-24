@extends('layouts.app')

@section('page-title', 'Calendário')

@section('top-actions')
    <button class="btn btn-primary" onclick="openTaskModal()"><i class="fas fa-plus"></i> Nova Tarefa</button>
@endsection

@section('content')
<div class="card">
    <div class="calendar-header">
        <div class="calendar-nav">
            <button class="btn btn-outline btn-sm" onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
            <span class="calendar-title" id="calendarTitle"></span>
            <button class="btn btn-outline btn-sm" onclick="changeMonth(1)"><i class="fas fa-chevron-right"></i></button>
        </div>
        <button class="btn btn-outline btn-sm" onclick="goToday()"><i class="fas fa-calendar-day"></i> Hoje</button>
    </div>

    <div class="calendar-grid" id="calendarGrid"></div>
</div>

{{-- Task Modal --}}
<div class="modal-overlay" id="taskModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Nova Tarefa</h3>
            <button class="modal-close" onclick="closeTaskModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="taskForm">
            <input type="hidden" id="taskId" value="">

            <div class="form-group">
                <label class="form-label">Título *</label>
                <input type="text" id="taskTitle" class="form-control" required placeholder="Ex: Imprimir lote de vasos">
            </div>

            <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea id="taskDesc" class="form-control" style="min-height: 70px;" placeholder="Detalhes da tarefa..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Data *</label>
                    <input type="date" id="taskDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Prioridade</label>
                    <select id="taskPriority" class="form-control">
                        <option value="low">🟢 Baixa</option>
                        <option value="medium" selected>🟡 Média</option>
                        <option value="high">🔴 Alta</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="taskStatus" class="form-control">
                        <option value="pending">Pendente</option>
                        <option value="in_progress">Em Andamento</option>
                        <option value="completed">Concluída</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Cor</label>
                    <input type="color" id="taskColor" class="form-control" value="#6366f1" style="height: 38px; padding: 4px;">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="deleteTaskBtn" style="margin-right: auto; display: none;" onclick="deleteTask()">
                    <i class="fas fa-trash"></i> Excluir
                </button>
                <button type="button" class="btn btn-outline" onclick="closeTaskModal()">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const MONTHS_PT = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
const DAYS_PT = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let currentYear, currentMonth;
let tasks = [];

function init() {
    const now = new Date();
    currentYear = now.getFullYear();
    currentMonth = now.getMonth();
    renderCalendar();
}

function changeMonth(delta) {
    currentMonth += delta;
    if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    renderCalendar();
}

function goToday() {
    const now = new Date();
    currentYear = now.getFullYear();
    currentMonth = now.getMonth();
    renderCalendar();
}

async function renderCalendar() {
    document.getElementById('calendarTitle').textContent = `${MONTHS_PT[currentMonth]} ${currentYear}`;

    // Fetch tasks
    const res = await fetch(`{{ url('calendar/tasks') }}?month=${currentMonth + 1}&year=${currentYear}`);
    tasks = await res.json();

    const grid = document.getElementById('calendarGrid');
    grid.innerHTML = '';

    // Day headers
    DAYS_PT.forEach(d => {
        const el = document.createElement('div');
        el.className = 'calendar-day-header';
        el.textContent = d;
        grid.appendChild(el);
    });

    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    const daysInPrev = new Date(currentYear, currentMonth, 0).getDate();
    const today = new Date();

    // Previous month padding
    for (let i = firstDay - 1; i >= 0; i--) {
        const el = createDayCell(daysInPrev - i, true);
        grid.appendChild(el);
    }

    // Current month
    for (let d = 1; d <= daysInMonth; d++) {
        const isToday = today.getFullYear() === currentYear && today.getMonth() === currentMonth && today.getDate() === d;
        const el = createDayCell(d, false, isToday);
        grid.appendChild(el);
    }

    // Next month padding
    const totalCells = grid.children.length;
    const remaining = (Math.ceil(totalCells / 7) * 7) - totalCells;
    for (let i = 1; i <= remaining; i++) {
        const el = createDayCell(i, true);
        grid.appendChild(el);
    }
}

function createDayCell(day, otherMonth, isToday = false) {
    const cell = document.createElement('div');
    cell.className = 'calendar-day' + (otherMonth ? ' other-month' : '') + (isToday ? ' today' : '');

    const num = document.createElement('div');
    num.className = 'day-number';
    num.textContent = day;
    cell.appendChild(num);

    if (!otherMonth) {
        // Add tasks for this day
        const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayTasks = tasks.filter(t => (t.due_date || '').substring(0, 10) === dateStr);

        const tasksDiv = document.createElement('div');
        tasksDiv.className = 'day-tasks';

        dayTasks.forEach(task => {
            const t = document.createElement('div');
            t.className = `day-task priority-${task.priority}${task.status === 'completed' ? ' completed' : ''}`;
            t.textContent = task.title;
            t.style.borderLeft = `3px solid ${task.color || '#6366f1'}`;
            t.addEventListener('click', (e) => {
                e.stopPropagation();
                openTaskModal(task);
            });
            tasksDiv.appendChild(t);
        });

        cell.appendChild(tasksDiv);

        // Click to create new task
        cell.addEventListener('click', () => {
            openTaskModal(null, dateStr);
        });
    }

    return cell;
}

function openTaskModal(task = null, date = null) {
    const modal = document.getElementById('taskModal');
    const deleteBtn = document.getElementById('deleteTaskBtn');

    if (task) {
        document.getElementById('modalTitle').textContent = 'Editar Tarefa';
        document.getElementById('taskId').value = task.id;
        document.getElementById('taskTitle').value = task.title;
        document.getElementById('taskDesc').value = task.description || '';
        document.getElementById('taskDate').value = (task.due_date || '').substring(0, 10);
        document.getElementById('taskPriority').value = task.priority;
        document.getElementById('taskStatus').value = task.status;
        document.getElementById('taskColor').value = task.color || '#6366f1';
        deleteBtn.style.display = 'inline-flex';
    } else {
        document.getElementById('modalTitle').textContent = 'Nova Tarefa';
        document.getElementById('taskId').value = '';
        document.getElementById('taskTitle').value = '';
        document.getElementById('taskDesc').value = '';
        document.getElementById('taskDate').value = date || new Date().toISOString().split('T')[0];
        document.getElementById('taskPriority').value = 'medium';
        document.getElementById('taskStatus').value = 'pending';
        document.getElementById('taskColor').value = '#6366f1';
        deleteBtn.style.display = 'none';
    }

    modal.classList.add('active');
}

function closeTaskModal() {
    document.getElementById('taskModal').classList.remove('active');
}

document.getElementById('taskForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = document.getElementById('taskId').value;
    const data = {
        title: document.getElementById('taskTitle').value,
        description: document.getElementById('taskDesc').value,
        due_date: document.getElementById('taskDate').value,
        priority: document.getElementById('taskPriority').value,
        status: document.getElementById('taskStatus').value,
        color: document.getElementById('taskColor').value,
    };

    const url = id ? `{{ url('calendar/tasks') }}/${id}` : '{{ route("calendar.tasks.store") }}';
    const method = id ? 'PUT' : 'POST';

    await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(data),
    });

    closeTaskModal();
    renderCalendar();
});

async function deleteTask() {
    const id = document.getElementById('taskId').value;
    if (!id || !confirm('Excluir esta tarefa?')) return;

    await fetch(`{{ url('calendar/tasks') }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    });

    closeTaskModal();
    renderCalendar();
}

init();
</script>
@endsection
