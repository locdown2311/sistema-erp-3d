@extends('layouts.app')

@section('page-title', 'Custos 3D')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 xl:gap-8">
    {{-- Calculator --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden h-fit">
        <div class="px-5 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
            <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-calculator text-blue-500"></i> Calculadora de Custos
            </h3>
        </div>

        <form id="costForm" class="p-5 sm:p-6">
            @csrf
            <div class="mb-5">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Produto (opcional)</label>
                <select name="product_id" id="costProduct" 
                        class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                    <option value="">Cálculo avulso</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                                data-weight="{{ $product->weight_grams }}"
                                data-time="{{ $product->print_time_hours }}">
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6 pb-6 border-b border-zinc-200 dark:border-zinc-800">
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome do Cálculo</label>
                <input type="text" name="name" id="costName" placeholder="Ex: Vaso Grande PLA" 
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
            </div>

            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-syringe text-indigo-500"></i> Filamento
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Peso (g)</label>
                    <input type="number" name="filament_weight_g" id="filWeight" step="0.01" min="0" value="0" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Preço / kg (R$)</label>
                    <input type="number" name="filament_price_kg" id="filPrice" step="0.01" min="0" value="{{ $defaults['filament_price_kg'] }}" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                </div>
            </div>

            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-bolt text-amber-500"></i> Energia
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Tempo de impressão (h)</label>
                    <input type="number" name="print_time_hours" id="printTime" step="0.01" min="0" value="0" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Potência (W)</label>
                    <input type="number" name="printer_wattage" id="wattage" step="0.01" min="0" value="{{ $defaults['printer_wattage'] }}" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                </div>
            </div>
            <div class="mb-8">
                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Tarifa kWh (R$)</label>
                <input type="number" name="kwh_rate" id="kwhRate" step="0.0001" min="0" value="{{ $defaults['kwh_rate'] }}" 
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
            </div>

            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-tools text-red-500"></i> Depreciação
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Preço da impressora (R$)</label>
                    <input type="number" name="printer_price" id="printerPrice" step="0.01" min="0" value="{{ $defaults['printer_price'] }}" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Vida útil (h)</label>
                    <input type="number" name="printer_lifespan_hours" id="lifespan" step="0.01" min="0.01" value="{{ $defaults['printer_lifespan_hours'] }}" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                </div>
            </div>

            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-hands text-emerald-500"></i> Mão de Obra
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Pós-processamento (h)</label>
                    <input type="number" name="post_processing_hours" id="postHours" step="0.01" min="0" value="0" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Valor/hora (R$)</label>
                    <input type="number" name="labor_rate" id="laborRate" step="0.01" min="0" value="{{ $defaults['labor_rate'] }}" 
                           class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
                </div>
            </div>

            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-percentage text-blue-500"></i> Margem
            </h4>
            <div class="mb-8">
                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-400 mb-1">Margem de lucro (%)</label>
                <input type="number" name="margin_percent" id="margin" step="0.01" min="0" value="50" 
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none text-right">
            </div>

            <button type="button" onclick="calculateCost()" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 text-sm font-semibold rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-sm">
                <i class="fas fa-calculator"></i> Calcular Custo
            </button>
        </form>
    </div>

    <div class="flex flex-col gap-6 xl:gap-8 overflow-hidden">
        {{-- Results --}}
        <div id="costResults" class="bg-zinc-900 dark:bg-black rounded-xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
            <!-- Decorative background gradient -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
            
            <h3 class="text-lg font-semibold text-white flex items-center gap-2 mb-6 relative z-10">
                <i class="fas fa-chart-pie text-zinc-400"></i> Resultado do Cálculo
            </h3>

            <div class="space-y-3 mb-8 relative z-10">
                <div class="flex items-center justify-between py-2 border-b border-zinc-800/80">
                    <span class="text-sm text-zinc-400 flex items-center gap-2"><i class="fas fa-syringe text-indigo-400 w-4 text-center"></i> Filamento</span>
                    <span id="resFilament" class="font-medium text-white">R$ 0,00</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-zinc-800/80">
                    <span class="text-sm text-zinc-400 flex items-center gap-2"><i class="fas fa-bolt text-amber-400 w-4 text-center"></i> Energia</span>
                    <span id="resEnergy" class="font-medium text-white">R$ 0,00</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-zinc-800/80">
                    <span class="text-sm text-zinc-400 flex items-center gap-2"><i class="fas fa-tools text-red-400 w-4 text-center"></i> Depreciação</span>
                    <span id="resDepreciation" class="font-medium text-white">R$ 0,00</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-zinc-800/80">
                    <span class="text-sm text-zinc-400 flex items-center gap-2"><i class="fas fa-hands text-emerald-400 w-4 text-center"></i> Mão de Obra</span>
                    <span id="resLabor" class="font-medium text-white">R$ 0,00</span>
                </div>
            </div>

            <div class="flex items-end justify-between mb-8 relative z-10">
                <span class="text-sm text-zinc-400">Custo Total de Produção</span>
                <span id="resTotal" class="text-2xl font-semibold text-white">R$ 0,00</span>
            </div>

            <div class="bg-gradient-to-br from-emerald-500/10 to-teal-500/10 border border-emerald-500/20 rounded-xl p-6 mb-6 relative z-10 text-center">
                <div class="text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-2">Preço de Venda Sugerido</div>
                <div id="resSuggested" class="text-4xl font-bold text-emerald-400 tracking-tight">R$ 0,00</div>
            </div>

            <button type="button" id="saveCostBtn" onclick="saveCost()" style="display: none;" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-500 text-white text-sm font-semibold rounded-lg hover:bg-emerald-600 transition-colors shadow-sm relative z-10">
                <i class="fas fa-save"></i> Salvar Este Cálculo
            </button>
        </div>

        {{-- Saved Costs --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex-1 h-fit">
            <div class="px-5 sm:px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-history text-zinc-400"></i> Cálculos Salvos
                </h3>
            </div>
            @if($costs->count() > 0)
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($costs as $cost)
                        <div class="flex items-center justify-between px-5 sm:px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <div class="flex-1 min-w-0 pr-4">
                                <div class="font-medium text-sm text-zinc-900 dark:text-white truncate mb-1">{{ $cost->name ?? ($cost->product->name ?? 'Cálculo #' . $cost->id) }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5 flex-wrap">
                                    <span>Custo: <strong class="text-zinc-600 dark:text-zinc-300 font-medium">R$ {{ number_format($cost->total_cost, 2, ',', '.') }}</strong></span>
                                    <i class="fas fa-arrow-right text-[10px] text-zinc-300 dark:text-zinc-600"></i>
                                    <span>Venda: <strong class="text-emerald-600 dark:text-emerald-400 font-semibold">R$ {{ number_format($cost->suggested_price, 2, ',', '.') }}</strong></span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('costs.destroy', $cost) }}" onsubmit="return confirm('Excluir este cálculo salvo?');">
                                @csrf @method('DELETE')
                                <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10 transition-colors" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center">
                    <p class="text-sm text-zinc-500">Nenhum cálculo salvo ainda</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const fmt = v => 'R$ ' + parseFloat(v).toFixed(2).replace('.', ',');
let lastResult = null;

// Auto-fill from product selection
document.getElementById('costProduct').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.weight) document.getElementById('filWeight').value = opt.dataset.weight;
    if (opt.dataset.time) document.getElementById('printTime').value = opt.dataset.time;
});

function calculateCost() {
    const data = {
        filament_weight_g: parseFloat(document.getElementById('filWeight').value) || 0,
        filament_price_kg: parseFloat(document.getElementById('filPrice').value) || 0,
        print_time_hours: parseFloat(document.getElementById('printTime').value) || 0,
        printer_wattage: parseFloat(document.getElementById('wattage').value) || 0,
        kwh_rate: parseFloat(document.getElementById('kwhRate').value) || 0,
        printer_price: parseFloat(document.getElementById('printerPrice').value) || 0,
        printer_lifespan_hours: parseFloat(document.getElementById('lifespan').value) || 0.01,
        post_processing_hours: parseFloat(document.getElementById('postHours').value) || 0,
        labor_rate: parseFloat(document.getElementById('laborRate').value) || 0,
        margin_percent: parseFloat(document.getElementById('margin').value) || 0,
    };

    fetch('{{ route("costs.calculate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(res => {
        lastResult = { ...data, ...res };
        document.getElementById('resFilament').textContent = fmt(res.filament_cost);
        document.getElementById('resEnergy').textContent = fmt(res.energy_cost);
        document.getElementById('resDepreciation').textContent = fmt(res.depreciation_cost);
        document.getElementById('resLabor').textContent = fmt(res.labor_cost);
        document.getElementById('resTotal').textContent = fmt(res.total_cost);
        document.getElementById('resSuggested').textContent = fmt(res.suggested_price);
        document.getElementById('saveCostBtn').style.display = 'flex';
    });
}

function saveCost() {
    if (!lastResult) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("costs.store") }}';

    const fields = {
        _token: '{{ csrf_token() }}',
        product_id: document.getElementById('costProduct').value || '',
        name: document.getElementById('costName').value || '',
        filament_weight_g: lastResult.filament_weight_g,
        filament_price_kg: lastResult.filament_price_kg,
        filament_cost: lastResult.filament_cost,
        print_time_hours: lastResult.print_time_hours,
        printer_wattage: lastResult.printer_wattage,
        kwh_rate: lastResult.kwh_rate,
        energy_cost: lastResult.energy_cost,
        printer_price: lastResult.printer_price,
        printer_lifespan_hours: lastResult.printer_lifespan_hours,
        depreciation_cost: lastResult.depreciation_cost,
        post_processing_hours: lastResult.post_processing_hours,
        labor_rate: lastResult.labor_rate,
        labor_cost: lastResult.labor_cost,
        total_cost: lastResult.total_cost,
        margin_percent: lastResult.margin_percent,
        suggested_price: lastResult.suggested_price,
    };

    for (const [key, val] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = val;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
