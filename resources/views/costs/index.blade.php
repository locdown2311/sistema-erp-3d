@extends('layouts.app')

@section('page-title', 'Custos 3D')

@section('content')
<div class="cost-grid">
    {{-- Calculator --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calculator" style="margin-right: 8px; color: var(--primary-light);"></i>Calculadora de Custos</h3>
        </div>

        <form id="costForm">
            @csrf
            <div class="form-group">
                <label class="form-label">Produto (opcional)</label>
                <select name="product_id" id="costProduct" class="form-control">
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

            <div class="form-group">
                <label class="form-label">Nome do Cálculo</label>
                <input type="text" name="name" id="costName" class="form-control" placeholder="Ex: Vaso Grande PLA">
            </div>

            <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--accent); margin: var(--space-lg) 0 var(--space-md);">
                <i class="fas fa-syringe"></i> Filamento
            </h4>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Peso (g)</label>
                    <input type="number" name="filament_weight_g" id="filWeight" class="form-control cost-input" step="0.01" min="0" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Preço / kg (R$)</label>
                    <input type="number" name="filament_price_kg" id="filPrice" class="form-control cost-input" step="0.01" min="0" value="{{ $defaults['filament_price_kg'] }}">
                </div>
            </div>

            <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--warning); margin: var(--space-lg) 0 var(--space-md);">
                <i class="fas fa-bolt"></i> Energia
            </h4>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tempo de impressão (h)</label>
                    <input type="number" name="print_time_hours" id="printTime" class="form-control cost-input" step="0.01" min="0" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Potência (W)</label>
                    <input type="number" name="printer_wattage" id="wattage" class="form-control cost-input" step="0.01" min="0" value="{{ $defaults['printer_wattage'] }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Tarifa kWh (R$)</label>
                <input type="number" name="kwh_rate" id="kwhRate" class="form-control cost-input" step="0.0001" min="0" value="{{ $defaults['kwh_rate'] }}">
            </div>

            <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--danger-light); margin: var(--space-lg) 0 var(--space-md);">
                <i class="fas fa-tools"></i> Depreciação
            </h4>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Preço da impressora (R$)</label>
                    <input type="number" name="printer_price" id="printerPrice" class="form-control cost-input" step="0.01" min="0" value="{{ $defaults['printer_price'] }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Vida útil (h)</label>
                    <input type="number" name="printer_lifespan_hours" id="lifespan" class="form-control cost-input" step="0.01" min="0.01" value="{{ $defaults['printer_lifespan_hours'] }}">
                </div>
            </div>

            <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--success); margin: var(--space-lg) 0 var(--space-md);">
                <i class="fas fa-hands"></i> Mão de Obra
            </h4>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pós-processamento (h)</label>
                    <input type="number" name="post_processing_hours" id="postHours" class="form-control cost-input" step="0.01" min="0" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Valor/hora (R$)</label>
                    <input type="number" name="labor_rate" id="laborRate" class="form-control cost-input" step="0.01" min="0" value="{{ $defaults['labor_rate'] }}">
                </div>
            </div>

            <h4 style="font-size: 0.9rem; font-weight: 600; color: var(--primary-light); margin: var(--space-lg) 0 var(--space-md);">
                <i class="fas fa-percentage"></i> Margem
            </h4>
            <div class="form-group">
                <label class="form-label">Margem de lucro (%)</label>
                <input type="number" name="margin_percent" id="margin" class="form-control cost-input" step="0.01" min="0" value="50">
            </div>

            <button type="button" class="btn btn-primary" onclick="calculateCost()" style="width: 100%; justify-content: center;">
                <i class="fas fa-calculator"></i> Calcular
            </button>
        </form>
    </div>

    <div>
        {{-- Results --}}
        <div class="cost-result" id="costResults" style="margin-bottom: var(--space-lg);">
            <h3 class="card-title" style="margin-bottom: var(--space-lg);"><i class="fas fa-chart-bar" style="margin-right: 8px;"></i>Resultado</h3>

            <div class="cost-line">
                <span><i class="fas fa-syringe" style="color: var(--accent); margin-right: 6px;"></i>Filamento</span>
                <span id="resFilament">R$ 0,00</span>
            </div>
            <div class="cost-line">
                <span><i class="fas fa-bolt" style="color: var(--warning); margin-right: 6px;"></i>Energia</span>
                <span id="resEnergy">R$ 0,00</span>
            </div>
            <div class="cost-line">
                <span><i class="fas fa-tools" style="color: var(--danger-light); margin-right: 6px;"></i>Depreciação</span>
                <span id="resDepreciation">R$ 0,00</span>
            </div>
            <div class="cost-line">
                <span><i class="fas fa-hands" style="color: var(--success); margin-right: 6px;"></i>Mão de Obra</span>
                <span id="resLabor">R$ 0,00</span>
            </div>

            <div class="cost-total">
                <span>Custo Total</span>
                <span id="resTotal">R$ 0,00</span>
            </div>

            <div class="cost-suggested">
                <div style="font-size: 0.8rem; opacity: 0.8;">Preço Sugerido</div>
                <div class="price" id="resSuggested">R$ 0,00</div>
            </div>

            <button type="button" class="btn btn-success mt-1" id="saveCostBtn" onclick="saveCost()" style="width: 100%; justify-content: center; display: none;">
                <i class="fas fa-save"></i> Salvar Cálculo
            </button>
        </div>

        {{-- Saved Costs --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history" style="margin-right: 8px; color: var(--text-muted);"></i>Cálculos Salvos</h3>
            </div>
            @if($costs->count() > 0)
                @foreach($costs as $cost)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border);">
                        <div>
                            <div style="font-weight: 500; font-size: 0.9rem;">{{ $cost->name ?? ($cost->product->name ?? 'Cálculo #' . $cost->id) }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                Custo: R$ {{ number_format($cost->total_cost, 2, ',', '.') }} →
                                <span class="text-success">R$ {{ number_format($cost->suggested_price, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('costs.destroy', $cost) }}" onsubmit="return confirm('Remover?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm btn-icon"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                @endforeach
            @else
                <p class="text-muted" style="font-size: 0.85rem;">Nenhum cálculo salvo</p>
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
