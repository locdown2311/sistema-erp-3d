@extends('layouts.app')

@section('page-title', 'Filamentos')

@section('top-actions')
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
        <i class="fas fa-plus"></i> Novo Filamento
    </button>
@endsection

@section('content')
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
    <form method="GET" class="w-full sm:w-auto flex-1 max-w-md relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-zinc-400"></i>
        </div>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar filamento ou marca..." 
               class="w-full pl-10 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
    </form>

    @if($types->count() > 0)
        <form method="GET" class="w-full sm:w-auto">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="type" onchange="this.form.submit()" 
                    class="w-full sm:w-48 px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                <option value="">Todos os tipos</option>
                @foreach($types as $t)
                    <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </form>
    @endif
</div>

@if($filaments->count() > 0)
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-xs border-b border-zinc-200 dark:border-zinc-800">
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
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                    @foreach($filaments as $fil)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">{{ $fil->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                    {{ $fil->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($fil->price_per_kg, 2, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden min-w-[80px]">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $fil->remaining_percent > 30 ? 'bg-emerald-500' : ($fil->remaining_percent > 10 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $fil->remaining_percent }}%;"></div>
                                    </div>
                                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400 min-w-[40px]">{{ number_format($fil->remaining_grams, 0, ',', '.') }}g</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($fil->print_temp_min)
                                    <span class="inline-flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800/50 px-2 py-1 rounded border border-zinc-100 dark:border-zinc-800">
                                        <i class="fas fa-temperature-high text-red-400"></i> {{ $fil->print_temp_min }}–{{ $fil->print_temp_max }}°C
                                    </span>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-600">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($fil->bed_temp_min)
                                    <span class="inline-flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800/50 px-2 py-1 rounded border border-zinc-100 dark:border-zinc-800">
                                        <i class="fas fa-bed text-blue-400"></i> {{ $fil->bed_temp_min }}–{{ $fil->bed_temp_max }}°C
                                    </span>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-600">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openModal({{ json_encode($fil) }})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:text-white dark:hover:bg-zinc-800 transition-colors" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <form method="POST" action="{{ route('filaments.consume', $fil) }}" class="flex items-center gap-1" onsubmit="return this.grams.value > 0;">
                                        @csrf
                                        <input type="number" name="grams" step="0.01" min="0.01" placeholder="g" class="w-16 h-8 px-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-xs text-zinc-900 dark:text-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none text-center" title="Quantidade a consumir">
                                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:text-amber-500 dark:hover:text-amber-400 dark:hover:bg-amber-500/10 transition-colors" title="Consumir">
                                            <i class="fas fa-fire"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('filaments.destroy', $fil) }}" onsubmit="return confirm('Tem certeza que deseja remover este filamento?');">
                                        @csrf @method('DELETE')
                                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-sm">
        <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4 text-zinc-400 text-2xl">
            <i class="fas fa-fill-drip"></i>
        </div>
        <h3 class="text-base font-semibold text-zinc-900 dark:text-white mb-1">Nenhum filamento cadastrado</h3>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6 max-w-sm mx-auto">Mantenha o controle do seu estoque de filamentos para evitar que eles acabem no meio de uma impressão.</p>
        <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
            <i class="fas fa-plus"></i> Adicionar Primeiro Filamento
        </button>
    </div>
@endif

{{-- Modal --}}
<div id="filModal" class="fixed inset-0 z-50 hidden bg-zinc-950/80 flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl scale-95 transition-transform duration-300 custom-scrollbar">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between sticky top-0 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm z-10">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2" id="modalTitle">
                <i class="fas fa-cube text-zinc-400"></i> Novo Filamento
            </h3>
            <button onclick="closeModal()" class="text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="filForm" method="POST" action="{{ route('filaments.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="_method" id="filMethod" value="POST">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Marca <span class="text-red-500">*</span></label>
                    <input type="text" name="brand" id="fBrand" required placeholder="Ex: eSUN, 3D Fila" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Tipo <span class="text-red-500">*</span></label>
                    <input type="text" name="type" id="fType" required placeholder="PLA, ABS, PETG..." list="typeList" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                    <datalist id="typeList">
                        <option value="PLA">
                        <option value="PLA+">
                        <option value="ABS">
                        <option value="PETG">
                        <option value="TPU">
                        <option value="Nylon">
                        <option value="Resina">
                        <option value="ASA">
                    </datalist>
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Cor</label>
                <input type="text" name="color" id="fColor" placeholder="Ex: Branco, Preto" 
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço / kg (R$) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-zinc-500 sm:text-sm">R$</span>
                        </div>
                        <input type="number" name="price_per_kg" id="fPrice" step="0.01" min="0" value="0" required 
                               class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                    </div>
                </div>
                <div id="qtyGroup">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Quantidade de Rolos</label>
                    <input type="number" name="quantity" id="fQty" min="1" value="1" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Diâmetro (mm) <span class="text-red-500">*</span></label>
                <input type="number" name="diameter_mm" id="fDiameter" step="0.01" value="1.75" required 
                       class="w-full md:w-1/2 px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Peso Total (g) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="weight_grams" id="fWeight" step="0.01" min="0" value="1000" required 
                               class="w-full pr-8 pl-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-zinc-500 sm:text-sm">g</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Restante (g)</label>
                    <div class="relative">
                        <input type="number" name="remaining_grams" id="fRemaining" step="0.01" min="0" value="1000" 
                               class="w-full pr-8 pl-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-zinc-500 sm:text-sm">g</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-temperature-high text-amber-500"></i> Temperaturas
                    </h4>
                    <button type="button" onclick="suggestTemps()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        <i class="fas fa-magic text-amber-500"></i> Sugerir
                    </button>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Bico Mín (°C)</label>
                        <input type="number" name="print_temp_min" id="fPrintMin" placeholder="190" 
                               class="w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Bico Máx (°C)</label>
                        <input type="number" name="print_temp_max" id="fPrintMax" placeholder="220" 
                               class="w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Mesa Mín (°C)</label>
                        <input type="number" name="bed_temp_min" id="fBedMin" placeholder="50" 
                               class="w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Mesa Máx (°C)</label>
                        <input type="number" name="bed_temp_max" id="fBedMax" placeholder="60" 
                               class="w-full px-3 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Observações</label>
                <textarea name="notes" id="fNotes" placeholder="Notas sobre o filamento..." 
                          class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none min-h-[80px] resize-y"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-medium rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
                    <i class="fas fa-check"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openModal(filament = null) {
    const modal = document.getElementById('filModal');
    const modalContent = modal.querySelector('div.bg-white');
    const form = document.getElementById('filForm');
    const methodField = document.getElementById('filMethod');

    if (filament) {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit text-zinc-400"></i> Editar Filamento';
        form.action = `/filaments/${filament.id}`;
        methodField.value = 'PUT';
        document.getElementById('fType').value = filament.type;
        document.getElementById('fColor').value = filament.color || '';
        document.getElementById('fBrand').value = filament.brand || '';
        document.getElementById('fPrice').value = filament.price_per_kg;
        document.getElementById('fDiameter').value = filament.diameter_mm;
        document.getElementById('qtyGroup').classList.add('hidden');
        document.getElementById('fWeight').value = filament.weight_grams;
        document.getElementById('fRemaining').value = filament.remaining_grams;
        document.getElementById('fPrintMin').value = filament.print_temp_min || '';
        document.getElementById('fPrintMax').value = filament.print_temp_max || '';
        document.getElementById('fBedMin').value = filament.bed_temp_min || '';
        document.getElementById('fBedMax').value = filament.bed_temp_max || '';
        document.getElementById('fNotes').value = filament.notes || '';
    } else {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-cube text-zinc-400"></i> Novo Filamento';
        form.action = '{{ route("filaments.store") }}';
        methodField.value = 'POST';
        form.reset();
        document.getElementById('fWeight').value = 1000;
        document.getElementById('fRemaining').value = 1000;
        document.getElementById('fDiameter').value = 1.75;
        document.getElementById('fQty').value = 1;
        document.getElementById('qtyGroup').classList.remove('hidden');
    }

    modal.classList.remove('hidden');
    // slight delay for transition
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modalContent.classList.remove('scale-95');
    }, 10);
}

function closeModal() {
    const modal = document.getElementById('filModal');
    const modalContent = modal.querySelector('div.bg-white');
    
    modal.classList.add('opacity-0');
    modalContent.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

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

function suggestTemps() {
    const type = document.getElementById('fType').value.trim();
    const t = TEMP_DEFAULTS[type];
    if (t) {
        document.getElementById('fPrintMin').value = t.printMin;
        document.getElementById('fPrintMax').value = t.printMax;
        document.getElementById('fBedMin').value = t.bedMin;
        document.getElementById('fBedMax').value = t.bedMax;
    } else {
        alert('Tipo "' + type + '" não tem temperaturas pré-definidas. Tipos disponíveis: ' + Object.keys(TEMP_DEFAULTS).join(', '));
    }
}

// Auto-suggest on type change for new filaments
document.getElementById('fType').addEventListener('change', function() {
    if (document.getElementById('filMethod').value === 'POST') {
        const t = TEMP_DEFAULTS[this.value.trim()];
        if (t && t.printMin > 0) {
            document.getElementById('fPrintMin').value = t.printMin;
            document.getElementById('fPrintMax').value = t.printMax;
            document.getElementById('fBedMin').value = t.bedMin;
            document.getElementById('fBedMax').value = t.bedMax;
        }
    }
});
</script>
@endsection
