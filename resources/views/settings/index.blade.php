@extends('layouts.app')

@section('page-title', 'Configurações')

@section('content')
<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cog" style="margin-right: 8px; color: var(--primary-light);"></i>Configurações Gerais</h3>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group" style="margin-bottom: var(--space-lg); padding-bottom: var(--space-md); border-bottom: 1px solid var(--border);">
            <label class="form-label">Logo da Loja</label>
            <div style="display: flex; align-items: center; gap: var(--space-md);">
                <div style="width: 80px; height: 80px; border-radius: var(--radius-md); border: 1px dashed var(--border); display: flex; align-items: center; justify-content: center; overflow: hidden; background: var(--bg-body);">
                    @if($user->store_logo)
                        <img src="{{ asset('storage/' . $user->store_logo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="fas fa-store" style="font-size: 2rem; color: var(--text-muted);"></i>
                    @endif
                </div>
                <div style="flex: 1;">
                    <input type="file" name="store_logo" class="form-control" accept="image/*">
                    <small style="color: var(--text-muted); display: block; margin-top: var(--space-xs);">Tamanho recomendado: 500x500px (JPG ou PNG). Enviar uma nova imagem substituirá a atual.</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Nome da Empresa</label>
            <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] }}">
        </div>

        <div class="form-group">
            <label class="form-label">Moeda</label>
            <select name="currency" class="form-control">
                <option value="BRL" {{ $settings['currency'] == 'BRL' ? 'selected' : '' }}>R$ — Real Brasileiro</option>
                <option value="USD" {{ $settings['currency'] == 'USD' ? 'selected' : '' }}>$ — Dólar</option>
                <option value="EUR" {{ $settings['currency'] == 'EUR' ? 'selected' : '' }}>€ — Euro</option>
            </select>
        </div>

        <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--accent); margin: var(--space-xl) 0 var(--space-md); padding-bottom: var(--space-sm); border-bottom: 1px solid var(--border);">
            <i class="fas fa-print"></i> Padrões da Impressora 3D
        </h4>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tarifa kWh (R$)</label>
                <input type="number" name="kwh_rate" class="form-control" step="0.0001" value="{{ $settings['kwh_rate'] }}">
            </div>
            <div class="form-group">
                <label class="form-label">Preço Filamento / kg (R$)</label>
                <input type="number" name="filament_price_kg" class="form-control" step="0.01" value="{{ $settings['filament_price_kg'] }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Potência da Impressora (W)</label>
                <input type="number" name="printer_wattage" class="form-control" step="1" value="{{ $settings['printer_wattage'] }}">
            </div>
            <div class="form-group">
                <label class="form-label">Valor da Impressora (R$)</label>
                <input type="number" name="printer_price" class="form-control" step="0.01" value="{{ $settings['printer_price'] }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Vida Útil (horas)</label>
                <input type="number" name="printer_lifespan_hours" class="form-control" step="1" value="{{ $settings['printer_lifespan_hours'] }}">
            </div>
            <div class="form-group">
                <label class="form-label">Valor Mão de Obra / hora (R$)</label>
                <input type="number" name="labor_rate" class="form-control" step="0.01" value="{{ $settings['labor_rate'] }}">
            </div>
        </div>
        <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--accent); margin: var(--space-xl) 0 var(--space-md); padding-bottom: var(--space-sm); border-bottom: 1px solid var(--border);">
            <i class="fas fa-palette"></i> Paleta de Cores da Loja
        </h4>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: var(--space-md);">Estas cores serão aplicadas na sidebar do painel e na vitrine pública da sua loja.</p>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Cor Primária</label>
                <div style="display:flex; align-items:center; gap:var(--space-sm);">
                    <input type="color" name="store_color_primary" id="colorPrimary" value="{{ $user->store_color_primary ?? '#16a34a' }}" style="width:48px; height:40px; border:1px solid var(--border); border-radius:var(--radius-md); cursor:pointer; background:transparent; padding:2px;">
                    <input type="text" id="colorPrimaryHex" class="form-control" value="{{ $user->store_color_primary ?? '#16a34a' }}" style="width:100px; font-family:monospace;" maxlength="7">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Cor Secundária</label>
                <div style="display:flex; align-items:center; gap:var(--space-sm);">
                    <input type="color" name="store_color_accent" id="colorAccent" value="{{ $user->store_color_accent ?? '#86efac' }}" style="width:48px; height:40px; border:1px solid var(--border); border-radius:var(--radius-md); cursor:pointer; background:transparent; padding:2px;">
                    <input type="text" id="colorAccentHex" class="form-control" value="{{ $user->store_color_accent ?? '#86efac' }}" style="width:100px; font-family:monospace;" maxlength="7">
                </div>
            </div>
        </div>

        <div id="colorPreview" style="margin: var(--space-md) 0 var(--space-lg); border-radius: var(--radius-md); overflow: hidden;">
            <div id="previewBar" style="height: 8px; background: linear-gradient(90deg, {{ $user->store_color_primary ?? '#16a34a' }}, {{ $user->store_color_accent ?? '#86efac' }});"></div>
            <div style="display:flex; gap:var(--space-sm); padding: var(--space-md); background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-top:none; border-radius: 0 0 var(--radius-md) var(--radius-md);">
                <div id="previewBtn1" style="padding:6px 16px; border-radius:var(--radius-md); background:{{ $user->store_color_primary ?? '#16a34a' }}; color:white; font-size:0.8rem; font-weight:600;">Botão Primário</div>
                <div id="previewBtn2" style="padding:6px 16px; border-radius:var(--radius-md); background:{{ $user->store_color_accent ?? '#86efac' }}; color:white; font-size:0.8rem; font-weight:600;">Botão Secundário</div>
            </div>
        </div>

        <button type="submit" class="btn btn-success mt-1"><i class="fas fa-save"></i> Salvar Configurações</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
const cp = document.getElementById('colorPrimary');
const cpHex = document.getElementById('colorPrimaryHex');
const ca = document.getElementById('colorAccent');
const caHex = document.getElementById('colorAccentHex');

function updatePreview() {
    const p = cp.value, a = ca.value;
    document.getElementById('previewBar').style.background = `linear-gradient(90deg, ${p}, ${a})`;
    document.getElementById('previewBtn1').style.background = p;
    document.getElementById('previewBtn2').style.background = a;
}

cp.addEventListener('input', () => { cpHex.value = cp.value; updatePreview(); });
cpHex.addEventListener('input', () => { if (/^#[0-9a-f]{6}$/i.test(cpHex.value)) { cp.value = cpHex.value; updatePreview(); }});
ca.addEventListener('input', () => { caHex.value = ca.value; updatePreview(); });
caHex.addEventListener('input', () => { if (/^#[0-9a-f]{6}$/i.test(caHex.value)) { ca.value = caHex.value; updatePreview(); }});
</script>
@endsection
