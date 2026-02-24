@extends('layouts.app')

@section('page-title', 'Filamentos')

@section('top-actions')
    <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Novo Filamento</button>
@endsection

@section('content')
<div class="filters-bar">
    <div class="search-input">
        <i class="fas fa-search"></i>
        <form method="GET" style="display:contents;">
            <input type="text" name="search" class="form-control" placeholder="Buscar filamento ou marca..."
                   value="{{ request('search') }}" style="padding-left: 38px;">
        </form>
    </div>
    @if($types->count() > 0)
        <form method="GET" style="display:contents;">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <select name="type" class="form-control" style="width: auto; min-width: 140px;" onchange="this.form.submit()">
                <option value="">Todos os tipos</option>
                @foreach($types as $t)
                    <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </form>
    @endif
</div>

@if($filaments->count() > 0)
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Preço/kg</th>
                        <th>Restante</th>
                        <th>Temp. Impressão</th>
                        <th>Temp. Mesa</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filaments as $fil)
                        <tr>
                            <td style="font-weight: 500;">{{ $fil->name }}</td>
                            <td><span class="badge badge-primary">{{ $fil->type }}</span></td>
                            <td class="text-success" style="font-weight:600;">R$ {{ number_format($fil->price_per_kg, 2, ',', '.') }}</td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="flex:1; height:6px; background:rgba(255,255,255,0.06); border-radius:3px; overflow:hidden; min-width:60px;">
                                        <div style="height:100%; width:{{ $fil->remaining_percent }}%; background: {{ $fil->remaining_percent > 30 ? 'var(--success)' : ($fil->remaining_percent > 10 ? 'var(--warning)' : 'var(--danger)') }}; border-radius:3px;"></div>
                                    </div>
                                    <span style="font-size:0.78rem; color:var(--text-secondary);">{{ number_format($fil->remaining_grams, 0, ',', '.') }}g</span>
                                </div>
                            </td>
                            <td style="font-size:0.82rem; white-space:nowrap;">
                                @if($fil->print_temp_min)
                                    🌡️ {{ $fil->print_temp_min }}–{{ $fil->print_temp_max }}°C
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="font-size:0.82rem; white-space:nowrap;">
                                @if($fil->bed_temp_min)
                                    🛏️ {{ $fil->bed_temp_min }}–{{ $fil->bed_temp_max }}°C
                                @else
                                    <span style="color:var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; gap:4px;">
                                    <button class="btn btn-outline btn-sm btn-icon" title="Editar"
                                            onclick="openModal({{ json_encode($fil) }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('filaments.consume', $fil) }}" style="display:flex; gap:3px; align-items:center;" onsubmit="return this.grams.value > 0;">
                                        @csrf
                                        <input type="number" name="grams" step="0.01" min="0.01" placeholder="g" class="form-control" style="width:65px; height:30px; font-size:0.75rem; padding:2px 6px; text-align:center;">
                                        <button class="btn btn-warning btn-sm btn-icon" title="Consumir"><i class="fas fa-fire"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('filaments.destroy', $fil) }}" onsubmit="return confirm('Remover este filamento?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm btn-icon"><i class="fas fa-trash"></i></button>
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
    <div class="empty-state">
        <i class="fas fa-fill-drip"></i>
        <p>Nenhum filamento cadastrado</p>
        <button class="btn btn-primary" onclick="openModal()">Adicionar Primeiro Filamento</button>
    </div>
@endif

{{-- Modal --}}
<div class="modal-overlay" id="filModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Novo Filamento</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="filForm" method="POST" action="{{ route('filaments.store') }}">
            @csrf
            <input type="hidden" name="_method" id="filMethod" value="POST">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Marca *</label>
                    <input type="text" name="brand" id="fBrand" class="form-control" required placeholder="Ex: eSUN, 3D Fila">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo *</label>
                    <input type="text" name="type" id="fType" class="form-control" required placeholder="PLA, ABS, PETG, TPU..." list="typeList">
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

            <div class="form-group">
                <label class="form-label">Cor</label>
                <input type="text" name="color" id="fColor" class="form-control" placeholder="Ex: Branco, Preto">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Preço / kg (R$) *</label>
                    <input type="number" name="price_per_kg" id="fPrice" class="form-control" step="0.01" min="0" value="0" required>
                </div>
                <div class="form-group" id="qtyGroup">
                    <label class="form-label">Quantidade de Rolos</label>
                    <input type="number" name="quantity" id="fQty" class="form-control" min="1" value="1">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Diâmetro (mm) *</label>
                    <input type="number" name="diameter_mm" id="fDiameter" class="form-control" step="0.01" value="1.75" required>
                </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Peso Total (g) *</label>
                    <input type="number" name="weight_grams" id="fWeight" class="form-control" step="0.01" min="0" value="1000" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Restante (g)</label>
                    <input type="number" name="remaining_grams" id="fRemaining" class="form-control" step="0.01" min="0" value="1000">
                </div>
            </div>

            <div style="margin: var(--space-lg) 0 var(--space-sm); padding-top: var(--space-md); border-top: 1px solid var(--border);">
                <div style="display:flex; align-items:center; gap: var(--space-sm); margin-bottom: var(--space-md);">
                    <h4 style="font-size: 0.85rem; font-weight: 600; color: var(--warning); margin:0;">
                        <i class="fas fa-temperature-high"></i> Temperaturas
                    </h4>
                    <button type="button" class="btn btn-outline btn-sm" onclick="suggestTemps()" style="font-size:0.72rem; padding:2px 8px; margin-left:auto;">
                        <i class="fas fa-magic"></i> Sugerir
                    </button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bico Mín (°C)</label>
                        <input type="number" name="print_temp_min" id="fPrintMin" class="form-control" placeholder="190">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bico Máx (°C)</label>
                        <input type="number" name="print_temp_max" id="fPrintMax" class="form-control" placeholder="220">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Mesa Mín (°C)</label>
                        <input type="number" name="bed_temp_min" id="fBedMin" class="form-control" placeholder="50">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mesa Máx (°C)</label>
                        <input type="number" name="bed_temp_max" id="fBedMax" class="form-control" placeholder="60">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Observações</label>
                <textarea name="notes" id="fNotes" class="form-control" style="min-height:60px;" placeholder="Notas sobre o filamento..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openModal(filament = null) {
    const modal = document.getElementById('filModal');
    const form = document.getElementById('filForm');
    const methodField = document.getElementById('filMethod');

    if (filament) {
        document.getElementById('modalTitle').textContent = 'Editar Filamento';
        form.action = `/filaments/${filament.id}`;
        methodField.value = 'PUT';
        document.getElementById('fType').value = filament.type;
        document.getElementById('fColor').value = filament.color || '';
        document.getElementById('fBrand').value = filament.brand || '';
        document.getElementById('fPrice').value = filament.price_per_kg;
        document.getElementById('fDiameter').value = filament.diameter_mm;
        document.getElementById('qtyGroup').style.display = 'none';
        document.getElementById('fWeight').value = filament.weight_grams;
        document.getElementById('fRemaining').value = filament.remaining_grams;
        document.getElementById('fPrintMin').value = filament.print_temp_min || '';
        document.getElementById('fPrintMax').value = filament.print_temp_max || '';
        document.getElementById('fBedMin').value = filament.bed_temp_min || '';
        document.getElementById('fBedMax').value = filament.bed_temp_max || '';
        document.getElementById('fNotes').value = filament.notes || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Novo Filamento';
        form.action = '{{ route("filaments.store") }}';
        methodField.value = 'POST';
        form.reset();
        document.getElementById('fWeight').value = 1000;
        document.getElementById('fRemaining').value = 1000;
        document.getElementById('fDiameter').value = 1.75;
        document.getElementById('fQty').value = 1;
        document.getElementById('qtyGroup').style.display = 'block';
    }

    modal.classList.add('active');
}

function closeModal() {
    document.getElementById('filModal').classList.remove('active');
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
