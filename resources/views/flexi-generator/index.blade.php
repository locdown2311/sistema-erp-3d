@extends('layouts.app')

@section('page-title', 'Gerador Flexi')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-[1fr_360px] gap-6 xl:gap-8" style="height: calc(100vh - 140px);">

    {{-- 3D Viewer --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col relative">
        {{-- Upload overlay --}}
        <div id="uploadOverlay" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white dark:bg-zinc-900 transition-opacity">
            <div id="dropZone" class="w-full h-full flex flex-col items-center justify-center cursor-pointer border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl m-4 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5 transition-all group">
                <div class="relative mb-6">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/20 dark:to-purple-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-cube text-4xl text-indigo-500 dark:text-indigo-400"></i>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-lg">
                        <i class="fas fa-plus text-white text-xs"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Arraste seu arquivo STL aqui</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">ou clique para selecionar</p>
                <div class="flex items-center gap-2 text-xs text-zinc-400 dark:text-zinc-500">
                    <i class="fas fa-info-circle"></i>
                    <span>Máximo 50MB &middot; Formato .stl (binário ou ASCII)</span>
                </div>
                <input type="file" id="stlFileInput" accept=".stl" class="hidden">
            </div>
        </div>

        {{-- 3D Canvas header --}}
        <div id="viewerHeader" class="px-5 sm:px-6 py-3 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex items-center justify-between" style="display:none;">
            <div class="flex items-center gap-3">
                <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-cube text-indigo-500"></i>
                    <span id="modelFileName">Modelo 3D</span>
                </h3>
                <span id="modelInfo" class="text-xs text-zinc-400 dark:text-zinc-500"></span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="setTopView()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg transition-colors border border-indigo-200 dark:border-indigo-500/20" title="Vista Superior 2D">
                    <i class="fas fa-square"></i> Topo
                </button>
                <button onclick="resetCamera()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white bg-zinc-100 dark:bg-zinc-800 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors" title="Reset câmera livre">
                    <i class="fas fa-video"></i> Perspectiva
                </button>
                <button onclick="loadNewFile()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white bg-zinc-100 dark:bg-zinc-800 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors" title="Carregar novo arquivo">
                    <i class="fas fa-folder-open"></i> Novo
                </button>
            </div>
        </div>

        {{-- Tool Panel Over Canvas (Bottom Center) --}}
        <div id="canvasToolbar" class="absolute bottom-6 left-1/2 -translate-x-1/2 z-10 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-full shadow-lg p-1.5 flex items-center gap-1" style="display:none;">
            <button onclick="setTransformMode('translate')" id="btn-translate" class="transform-btn active px-4 py-2 rounded-full text-sm font-semibold transition-all bg-indigo-500 text-white shadow-sm flex items-center gap-2">
                <i class="fas fa-arrows-alt"></i> Mover
            </button>
            <button onclick="setTransformMode('rotate')" id="btn-rotate" class="transform-btn px-4 py-2 rounded-full text-sm font-semibold transition-all text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center gap-2">
                <i class="fas fa-sync-alt"></i> Girar
            </button>
            <button onclick="setTransformMode('scale')" id="btn-scale" class="transform-btn px-4 py-2 rounded-full text-sm font-semibold transition-all text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center gap-2">
                <i class="fas fa-compress-arrows-alt"></i> Escalar
            </button>
        </div>

        {{-- Three.js canvas --}}
        <div id="viewerContainer" class="flex-1 relative bg-zinc-100 dark:bg-zinc-950" style="display:none;">
            <canvas id="threeCanvas"></canvas>
        </div>
    </div>

    {{-- Controls Panel --}}
    <div class="flex flex-col gap-4 overflow-y-auto pb-4 custom-scrollbar">

        {{-- Pattern Tool Settings --}}
        <div id="controlsPanel" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden shrink-0" style="display:none;">
            <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                <h3 class="font-semibold text-sm text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-tools text-indigo-500"></i> Ferramenta de Corte
                </h3>
            </div>
            
            <div class="p-4 space-y-5 shadow-inner bg-zinc-50/30 dark:bg-zinc-950/30">
                {{-- Pattern Type --}}
                <div class="grid grid-cols-2 gap-2">
                    <button id="pat-custom" class="pattern-card relative flex flex-col justify-center items-center gap-1.5 p-2 rounded-lg border-2 border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 opacity-50 cursor-not-allowed transition-all" title="Upload Desativado Temporariamente">
                        <i class="fas fa-cube text-xl text-zinc-400 mb-1 mt-1"></i>
                        <span class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-600 text-center leading-tight">Cortador<br>Upload</span>
                    </button>
                    <button onclick="selectPattern('fixed')" id="pat-fixed" class="pattern-card relative flex flex-col justify-center items-center gap-1.5 p-2 rounded-lg border-2 border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 transition-all">
                        <i class="fas fa-plug text-xl text-zinc-400 mb-1 mt-1"></i>
                        <span class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400 text-center leading-tight">Encaixe<br>Base</span>
                    </button>
                    <button onclick="selectPattern('mini')" id="pat-mini" class="pattern-card relative flex flex-col justify-center items-center gap-1.5 p-2 rounded-lg border-2 border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500 transition-all">
                        <i class="fas fa-compress text-xl text-zinc-400 mb-1 mt-1"></i>
                        <span class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400 text-center leading-tight">Encaixe<br>Mini</span>
                    </button>
                </div>



                {{-- Custom STL Cutter Controls --}}
                <div id="customCutterControls" class="pt-3 border-t border-zinc-200 dark:border-zinc-800" style="display:none;">
                    <span class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Molde de Corte (STL)</span>
                    <div id="cutterDropZone" class="w-full h-24 flex flex-col items-center justify-center cursor-pointer border-2 border-dashed border-indigo-300 dark:border-indigo-700/50 bg-indigo-50/50 dark:bg-indigo-500/5 rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-500/10 transition-colors">
                        <i class="fas fa-upload text-xl text-indigo-500 mb-2"></i>
                        <span class="text-xs text-indigo-700 dark:text-indigo-400 font-medium text-center px-4" id="cutterFileLabel">Clique para carregar o modelo de recorte (.stl)</span>
                        <input type="file" id="cutterFileInput" accept=".stl" class="hidden">
                    </div>
                </div>

                {{-- Action Panel (Clone) --}}
                <div id="actionControls" class="pt-3 border-t border-zinc-200 dark:border-zinc-800" style="display:none;">
                    <button onclick="cloneCutter()" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-xs font-semibold rounded outline-none transition-colors border border-indigo-200 dark:border-indigo-800/50">
                        <i class="fas fa-copy"></i> Duplicar Cortador Selecionado
                    </button>
                    <p class="text-[10px] text-zinc-400 mt-2 text-center leading-tight">Dica: Clique em uma peça verde no 3D para selecioná-la.</p>
                </div>

                {{-- Transform Sync --}}
                <div class="grid grid-cols-3 gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-100 dark:bg-zinc-900/80 p-3 rounded-lg border border-zinc-200 dark:border-zinc-800 shadow-inner">
                    <div class="col-span-3 mb-1 flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-zinc-500 tracking-wider">Transformação (Mundo)</span>
                        <button onclick="centerCutter()" class="text-[10px] text-indigo-500 hover:text-indigo-600 font-semibold uppercase"><i class="fas fa-crosshairs"></i> Centralizar</button>
                    </div>
                    <div>
                        <label class="block text-[10px] text-zinc-500">Pos X</label>
                        <input type="number" id="trPosX" value="0" step="1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300" onchange="updateTransformFromInputs()">
                    </div>
                    <div>
                        <label class="block text-[10px] text-zinc-500">Pos Y</label>
                        <input type="number" id="trPosY" value="0" step="1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300" onchange="updateTransformFromInputs()">
                    </div>
                    <div>
                        <label class="block text-[10px] text-zinc-500">Pos Z</label>
                        <input type="number" id="trPosZ" value="0" step="1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300" onchange="updateTransformFromInputs()">
                    </div>
                    <div>
                        <label class="block text-[10px] text-zinc-500">Rot X°</label>
                        <input type="number" id="trRotX" value="0" step="5" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300" onchange="updateTransformFromInputs()">
                    </div>
                    <div>
                        <label class="block text-[10px] text-zinc-500">Rot Y°</label>
                        <input type="number" id="trRotY" value="0" step="5" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300" onchange="updateTransformFromInputs()">
                    </div>
                    <div>
                        <label class="block text-[10px] text-zinc-500">Rot Z°</label>
                        <input type="number" id="trRotZ" value="0" step="5" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300" onchange="updateTransformFromInputs()">
                    </div>
                    <div>
                        <label class="block text-[10px] text-zinc-500">Scale X</label>
                        <input type="number" id="scPosX" value="1.0" step="0.1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300" onchange="updateTransformFromInputs()">
                    </div>
                    <div>
                        <label class="block text-[10px] text-zinc-500">Scale Y</label>
                        <input type="number" id="scPosY" value="1.0" step="0.1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300" onchange="updateTransformFromInputs()">
                    </div>
                    <div>
                        <label class="block text-[10px] text-zinc-500">Scale Z</label>
                        <input type="number" id="scPosZ" value="1.0" step="0.1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300" onchange="updateTransformFromInputs()">
                    </div>
                </div>

            </div>
        </div>

        {{-- Download --}}
        <div id="downloadPanel" class="bg-gradient-to-br from-emerald-500/5 to-teal-500/5 dark:from-emerald-500/10 dark:to-teal-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl shadow-sm overflow-hidden shrink-0" style="display:none;">
            <div class="p-5 space-y-3">
                
                {{-- Usage Indicator --}}
                <div class="flex items-center justify-between text-xs mb-3 px-1">
                    <span class="font-medium text-zinc-600 dark:text-zinc-400">Uso do Gerador</span>
                    <span class="font-bold @if($flexi_limit && $flexi_current >= $flexi_limit) text-red-500 @else text-emerald-600 dark:text-emerald-400 @endif">
                        {{ $flexi_current }} / {{ $flexi_limit ?: '∞' }}
                    </span>
                </div>
                
                @if($flexi_limit && $flexi_current >= $flexi_limit)
                    <div class="px-3 py-2 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded text-[10px] text-red-600 dark:text-red-400 text-center uppercase tracking-wide font-bold mb-2">
                        Limite Gratuito Atingido
                    </div>
                @endif
                
                <button onclick="downloadModifiedSTL()" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-lg hover:from-emerald-600 hover:to-teal-700 transition-all shadow-sm">
                    <i class="fas fa-download"></i> Baixar Modificado (CSG)
                </button>
                <button onclick="downloadOriginalSTL()" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-zinc-500 dark:text-zinc-400 text-xs font-medium hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">
                    <i class="fas fa-file-export"></i> Baixar Original
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div id="loadingOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-white/80 dark:bg-zinc-950/80 backdrop-blur-sm" style="display:none;">
    <div class="flex flex-col items-center gap-4">
        <div class="w-12 h-12 border-4 border-indigo-200 dark:border-indigo-800 border-t-indigo-500 rounded-full animate-spin"></div>
        <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400" id="loadingText">Carregando modelo...</span>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d4d4d8; border-radius: 4px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #3f3f46; }
</style>

<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.164.1/build/three.module.js",
        "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.164.1/examples/jsm/"
    }
}
</script>

<script type="module" src="{{ asset('js/flexi-generator.min.js') }}"></script>
@endsection
