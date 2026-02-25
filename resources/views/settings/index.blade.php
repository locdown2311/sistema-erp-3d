@extends('layouts.app')

@section('page-title', 'Configurações')

@section('content')
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden max-w-3xl mb-6">
    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-cog text-indigo-500"></i> Configurações Gerais
        </h3>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="p-6">
        @csrf

        <div class="mb-8 pb-6 border-b border-zinc-200 dark:border-zinc-800">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Logo da Loja</label>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="w-20 h-20 rounded-xl border-2 border-dashed border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 flex items-center justify-center overflow-hidden flex-shrink-0">
                    @if($user->store_logo)
                        <img src="{{ asset('storage/' . $user->store_logo) }}" alt="Logo" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-store text-3xl text-zinc-400 dark:text-zinc-600"></i>
                    @endif
                </div>
                <div class="flex-1 w-full">
                    <input type="file" name="store_logo" accept="image/*" 
                           class="block w-full text-sm text-zinc-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-500/10 dark:file:text-indigo-400 dark:hover:file:bg-indigo-500/20 transition-colors cursor-pointer border border-zinc-200 dark:border-zinc-800 rounded-lg bg-zinc-50 dark:bg-zinc-950 p-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Tamanho recomendado: 500x500px (JPG ou PNG). Enviar uma nova imagem substituirá a atual.</p>
                </div>
            </div>
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome da Empresa</label>
            <input type="text" name="company_name" value="{{ $settings['company_name'] }}" 
                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none">
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Moeda padrão</label>
            <select name="currency" 
                    class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                <option value="BRL" {{ $settings['currency'] == 'BRL' ? 'selected' : '' }}>R$ — Real Brasileiro</option>
                <option value="USD" {{ $settings['currency'] == 'USD' ? 'selected' : '' }}>$ — Dólar Americano</option>
                <option value="EUR" {{ $settings['currency'] == 'EUR' ? 'selected' : '' }}>€ — Euro</option>
            </select>
        </div>

        <h4 class="text-base font-semibold text-emerald-600 dark:text-emerald-500 mt-10 mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-2">
            <i class="fas fa-print"></i> Padrões da Impressora 3D
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Tarifa kWh (R$)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">R$</span>
                    </div>
                    <input type="number" name="kwh_rate" step="0.0001" value="{{ $settings['kwh_rate'] }}" 
                           class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço Filamento / kg (R$)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">R$</span>
                    </div>
                    <input type="number" name="filament_price_kg" step="0.01" value="{{ $settings['filament_price_kg'] }}" 
                           class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Potência da Impressora (W)</label>
                <div class="relative">
                    <input type="number" name="printer_wattage" step="1" value="{{ $settings['printer_wattage'] }}" 
                           class="w-full pr-8 pl-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">W</span>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Valor da Impressora (R$)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">R$</span>
                    </div>
                    <input type="number" name="printer_price" step="0.01" value="{{ $settings['printer_price'] }}" 
                           class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Vida Útil Estimada</label>
                <div class="relative">
                    <input type="number" name="printer_lifespan_hours" step="1" value="{{ $settings['printer_lifespan_hours'] }}" 
                           class="w-full pr-12 pl-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">horas</span>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Valor Mão de Obra / hora (R$)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm">R$</span>
                    </div>
                    <input type="number" name="labor_rate" step="0.01" value="{{ $settings['labor_rate'] }}" 
                           class="w-full pl-9 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none">
                </div>
            </div>
        </div>

        <h4 class="text-base font-semibold text-orange-500 dark:text-orange-400 mt-10 mb-2 pb-2 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-2">
            <i class="fas fa-palette"></i> Paleta de Cores da Loja
        </h4>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-5">Estas cores serão aplicadas na sidebar do painel e na vitrine pública da sua loja.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Cor Primária</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="store_color_primary" id="colorPrimary" value="{{ $user->store_color_primary ?? '#10b981' }}" 
                           class="w-12 h-10 p-0.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg cursor-pointer">
                    <input type="text" id="colorPrimaryHex" value="{{ $user->store_color_primary ?? '#10b981' }}" maxlength="7" 
                           class="w-28 px-3 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm font-mono text-zinc-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow outline-none uppercase">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Cor Secundária (Accent)</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="store_color_accent" id="colorAccent" value="{{ $user->store_color_accent ?? '#34d399' }}" 
                           class="w-12 h-10 p-0.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg cursor-pointer">
                    <input type="text" id="colorAccentHex" value="{{ $user->store_color_accent ?? '#34d399' }}" maxlength="7" 
                           class="w-28 px-3 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm font-mono text-zinc-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow outline-none uppercase">
                </div>
            </div>
        </div>

        <div class="mb-8 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div id="previewBar" class="h-2 w-full transition-all duration-300" style="background: linear-gradient(90deg, {{ $user->store_color_primary ?? '#10b981' }}, {{ $user->store_color_accent ?? '#34d399' }});"></div>
            <div class="flex gap-3 p-4 sm:p-5 bg-zinc-50 dark:bg-zinc-950 items-center justify-center sm:justify-start">
                <div id="previewBtn1" class="px-4 py-2 rounded-lg text-white text-sm font-medium shadow-sm transition-colors duration-300" style="background: {{ $user->store_color_primary ?? '#10b981' }};">
                    Botão Primário
                </div>
                <div id="previewBtn2" class="px-4 py-2 rounded-lg text-white text-sm font-medium shadow-sm transition-colors duration-300" style="background: {{ $user->store_color_accent ?? '#34d399' }};">
                    Botão Secundário
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800 flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-zinc-900">
                <i class="fas fa-save"></i> Salvar Configurações
            </button>
        </div>
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

cp.addEventListener('input', () => { cpHex.value = cp.value.toUpperCase(); updatePreview(); });
cpHex.addEventListener('input', () => { 
    if (/^#[0-9A-Fa-f]{6}$/i.test(cpHex.value)) { 
        cp.value = cpHex.value; 
        updatePreview(); 
    }
});
ca.addEventListener('input', () => { caHex.value = ca.value.toUpperCase(); updatePreview(); });
caHex.addEventListener('input', () => { 
    if (/^#[0-9A-Fa-f]{6}$/i.test(caHex.value)) { 
        ca.value = caHex.value; 
        updatePreview(); 
    }
});
</script>
@endsection
