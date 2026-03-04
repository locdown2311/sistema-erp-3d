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

<script type="module">
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { TransformControls } from 'three/addons/controls/TransformControls.js';
import { STLLoader } from 'three/addons/loaders/STLLoader.js';
import { STLExporter } from 'three/addons/exporters/STLExporter.js';
import { mergeVertices } from 'three/addons/utils/BufferGeometryUtils.js';

// ── State ──────────────────────────────────────────
let scene, camera, renderer, orbit, transformControl, mainMesh;
let modelBBox = null;
let modelSize = new THREE.Vector3();

let currentPattern = 'straight';
let cutterGroup = new THREE.Group(); // The interactive cutter rig
let activeCutterMesh = null; // The currently selected custom cutter mesh
const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();

// ── Init Three.js ──────────────────────────────────
function initThree() {
    const container = document.getElementById('viewerContainer');
    const canvas = document.getElementById('threeCanvas');

    scene = new THREE.Scene();
    const isDark = document.documentElement.classList.contains('dark');
    scene.background = new THREE.Color(isDark ? 0x0f0f11 : 0xf4f4f5);

    camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 10000);
    camera.position.set(0, 150, 150);

    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    // Soft shadow logic
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    // Lights
    scene.add(new THREE.AmbientLight(0xffffff, 0.7));
    const dirLight = new THREE.DirectionalLight(0xffffff, 1.2);
    dirLight.position.set(50, 150, 50);
    dirLight.castShadow = true;
    dirLight.shadow.mapSize.set(2048, 2048);
    // Soften shadows
    dirLight.shadow.camera.near = 0.5;
    dirLight.shadow.camera.far = 500;
    const d = 100;
    dirLight.shadow.camera.left = -d;
    dirLight.shadow.camera.right = d;
    dirLight.shadow.camera.top = d;
    dirLight.shadow.camera.bottom = -d;
    scene.add(dirLight);

    const fillLight = new THREE.DirectionalLight(0x90b0ff, 0.5);
    fillLight.position.set(-50, 50, -50);
    scene.add(fillLight);

    // Floor Grid
    const gridHelper = new THREE.GridHelper(400, 40, 0x888888, 0xcccccc);
    gridHelper.position.y = -0.1; // slightly below 0
    if (isDark) {
        gridHelper.material.color.setHex(0x333333); // center
        const colors = gridHelper.geometry.attributes.color;
        for (let i = 0; i < colors.count; i++) {
            if (colors.getX(i) > 0.5) colors.setXYZ(i, 0.2, 0.2, 0.2); // grid
        }
    }
    scene.add(gridHelper);

    // Controls
    orbit = new OrbitControls(camera, renderer.domElement);
    orbit.enableDamping = true;
    orbit.dampingFactor = 0.05;
    orbit.maxPolarAngle = Math.PI / 2 + 0.1; // Don't go below floor
    orbit.minDistance = 10;
    orbit.maxDistance = 600;

    // Transform Controls (For Cutter Group)
    transformControl = new TransformControls(camera, renderer.domElement);
    transformControl.addEventListener('dragging-changed', function (event) {
        orbit.enabled = !event.value;
    });
    transformControl.addEventListener('change', function () {
        updateInputsFromTransform();
    });

    scene.add(transformControl);

    scene.add(cutterGroup);

    // Raycaster selection logic for multiple custom cutters
    renderer.domElement.addEventListener('pointerdown', (event) => {
        if ((currentPattern !== 'custom' && currentPattern !== 'fixed' && currentPattern !== 'mini') || !cutterGroup) return;

        // Calculate mouse position in normalized device coordinates
        const rect = renderer.domElement.getBoundingClientRect();
        mouse.x = ( ( event.clientX - rect.left ) / rect.width ) * 2 - 1;
        mouse.y = - ( ( event.clientY - rect.top ) / rect.height ) * 2 + 1;

        raycaster.setFromCamera(mouse, camera);

        // Find intersections with cutters only
        const intersects = raycaster.intersectObjects(cutterGroup.children, false);

        if (intersects.length > 0) {
            // The first object is the closest one
            const clickedMesh = intersects[0].object;
            setActiveCutter(clickedMesh);
        }
    });

    window.addEventListener('resize', onResize);
    animate();
}

function onResize() {
    const container = document.getElementById('viewerContainer');
    if (!container || !camera || !renderer) return;
    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
}

function animate() {
    requestAnimationFrame(animate);
    if (orbit) orbit.update();
    if (renderer && scene && camera) renderer.render(scene, camera);
}

// ── Load STL ───────────────────────────────────────
function loadSTLFromArrayBuffer(buffer, filename) {
    const loader = new STLLoader();
    const geometry = loader.parse(buffer);
    geometry.computeVertexNormals();

    // Rotacionar Z-up para Y-up
    geometry.rotateX(-Math.PI / 2);

    // Center geometry around origin
    geometry.computeBoundingBox();
    const center = new THREE.Vector3();
    geometry.boundingBox.getCenter(center);
    geometry.translate(-center.x, -center.y, -center.z);

    // Sit exactly on ground (y = 0)
    geometry.computeBoundingBox();
    geometry.translate(0, -geometry.boundingBox.min.y, 0);

    const material = new THREE.MeshPhysicalMaterial({
        color: 0x6366f1, // Blue tint
        metalness: 0.1,
        roughness: 0.4,
        clearcoat: 0.3,
        side: THREE.DoubleSide,
    });

    if (mainMesh) scene.remove(mainMesh);
    mainMesh = new THREE.Mesh(geometry, material);
    mainMesh.castShadow = true;
    mainMesh.receiveShadow = true;
    scene.add(mainMesh);

    geometry.computeBoundingBox();
    modelBBox = geometry.boundingBox.clone();
    modelBBox.getSize(modelSize);

    // Auto camera to top down
    window.setTopView();

    // Info
    const triCount = geometry.attributes.position.count / 3;
    document.getElementById('modelFileName').textContent = filename;
    document.getElementById('modelInfo').textContent = `${triCount.toLocaleString('pt-BR')} triângulos`;

}

// ── File Upload ────────────────────────────────────
function handleFile(file) {
    if (!file) return;
    if (file.name.split('.').pop().toLowerCase() !== 'stl') {
        alert('Apenas arquivos .stl são aceitos.');
        return;
    }
    showLoading('Processando malha...');
    const reader = new FileReader();
    reader.onload = function(e) {
        if (!scene) initThree();
        loadSTLFromArrayBuffer(e.target.result, file.name);
        
        document.getElementById('uploadOverlay').style.display = 'none';
        document.getElementById('viewerHeader').style.display = 'flex';
        document.getElementById('canvasToolbar').style.display = 'flex';
        document.getElementById('viewerContainer').style.display = 'block';
        document.getElementById('controlsPanel').style.display = 'block';
        document.getElementById('downloadPanel').style.display = 'block';
        
        hideLoading();
        onResize();
    };
    reader.readAsArrayBuffer(file);
}

// Drag & Drop bindings
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('stlFileInput');
dropZone.addEventListener('click', () => fileInput.click());
fileInput.addEventListener('change', (e) => handleFile(e.target.files[0]));
dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('border-indigo-500','bg-indigo-50','dark:bg-indigo-500/10'); });
dropZone.addEventListener('dragleave', (e) => { e.preventDefault(); dropZone.classList.remove('border-indigo-500','bg-indigo-50','dark:bg-indigo-500/10'); });
dropZone.addEventListener('drop', (e) => { e.preventDefault(); dropZone.classList.remove('border-indigo-500','bg-indigo-50','dark:bg-indigo-500/10'); if (e.dataTransfer.files.length > 0) handleFile(e.dataTransfer.files[0]); });

// Cutter Custom STL Drag & Drop bindings
const cutterDropZone = document.getElementById('cutterDropZone');
const cutterFileInput = document.getElementById('cutterFileInput');
if (cutterDropZone && cutterFileInput) {
    cutterDropZone.addEventListener('click', () => cutterFileInput.click());
    cutterFileInput.addEventListener('change', (e) => handleCutterFile(e.target.files[0]));
    cutterDropZone.addEventListener('dragover', (e) => { e.preventDefault(); cutterDropZone.classList.add('border-indigo-500','bg-indigo-100','dark:bg-indigo-500/20'); });
    cutterDropZone.addEventListener('dragleave', (e) => { e.preventDefault(); cutterDropZone.classList.remove('border-indigo-500','bg-indigo-100','dark:bg-indigo-500/20'); });
    cutterDropZone.addEventListener('drop', (e) => { e.preventDefault(); cutterDropZone.classList.remove('border-indigo-500','bg-indigo-100','dark:bg-indigo-500/20'); if (e.dataTransfer.files.length > 0) handleCutterFile(e.dataTransfer.files[0]); });
}

function handleCutterFile(file) {
    if (!file) return;
    if (file.name.split('.').pop().toLowerCase() !== 'stl') {
        alert('Apenas arquivos .stl são aceitos para o molde.');
        return;
    }
    showLoading('Carregando cortador...');
    const reader = new FileReader();
    reader.onload = function(e) {
        loadCutterSTLFromArrayBuffer(e.target.result, file.name);
        hideLoading();
    };
    reader.readAsArrayBuffer(file);
}

function loadCutterSTLFromArrayBuffer(buffer, filename) {
    document.getElementById('cutterFileLabel').textContent = filename;
    
    const loader = new STLLoader();
    let geometry = loader.parse(buffer);
    
    // Convert to indexed geometry (REQUIRED for three-bvh-csg)
    geometry = mergeVertices(geometry);
    
    // Default STL rotation Z to Y up
    geometry.rotateX(-Math.PI / 2);

    // Center geometry around its own origin
    geometry.computeBoundingBox();
    const center = new THREE.Vector3();
    geometry.boundingBox.getCenter(center);
    geometry.translate(-center.x, -center.y, -center.z);
    
    // Ensure matching attributes, compute normals
    geometry = ensureIndexedGeometry(geometry);

    const mat = new THREE.MeshBasicMaterial({
        color: 0x10b981, // Emerald Green
        transparent: true,
        opacity: 0.50,
        depthTest: true,
        side: THREE.DoubleSide
    });

    const mesh = new THREE.Mesh(geometry, mat);
    
    // Add wireframe to custom mold too
    const edges = new THREE.EdgesGeometry(geometry);
    const edgeMat = new THREE.LineBasicMaterial({ color: 0x059669, opacity: 0.8, transparent: true });
    const wireframe = new THREE.LineSegments(edges, edgeMat);
    mesh.add(wireframe);

    // Clear previous splits
    while(cutterGroup.children.length > 0){ 
        cutterGroup.remove(cutterGroup.children[0]); 
    }
    
    cutterGroup.add(mesh);
    setActiveCutter(mesh);
    window.centerCutter(); // Bring the new cutter to center of the model
    
    document.getElementById('actionControls').style.display = 'block';
}

function setActiveCutter(mesh) {
    if (!mesh) return;
    
    // De-highlight previous
    cutterGroup.children.forEach(c => {
        if (c.material) {
            c.material.opacity = 0.25;
            c.material.color.setHex(0x059669); 
        }
    });
    
    // Highlight new
    mesh.material.opacity = 0.65;
    mesh.material.color.setHex(0x10b981); // Bright Emerald
    
    activeCutterMesh = mesh;
    transformControl.attach(activeCutterMesh);
    updateInputsFromTransform();
}

window.cloneCutter = function() {
    if (!activeCutterMesh) return;
    
    // Deep clone mesh and geometry to prevent buffer sharing issues in CSG
    const cloneGeo = activeCutterMesh.geometry.clone();
    
    // Create new material instance so opacity can be controlled
    const cloneMat = activeCutterMesh.material.clone();
    
    const clone = new THREE.Mesh(ensureIndexedGeometry(cloneGeo), cloneMat);
    
    clone.position.copy(activeCutterMesh.position);
    clone.rotation.copy(activeCutterMesh.rotation);
    clone.scale.copy(activeCutterMesh.scale);
    
    // Shift slightly so user sees the duplicate
    clone.position.x += 10;
    clone.position.z += 10;
    
    // Re-add wireframe to the clone
    const edges = new THREE.EdgesGeometry(clone.geometry);
    const edgeMat = new THREE.LineBasicMaterial({ color: 0x059669, opacity: 0.8, transparent: true });
    const wireframe = new THREE.LineSegments(edges, edgeMat);
    clone.add(wireframe);
    
    cutterGroup.add(clone);
    setActiveCutter(clone);
};

// ── Tool Logic (Transform Controls) ────────────────
window.setTransformMode = function(mode) {
    if(!transformControl) return;
    // mode = 'translate', 'rotate', or 'scale'
    transformControl.setMode(mode);
    
    // UI active state
    document.querySelectorAll('.transform-btn').forEach(btn => {
        btn.classList.remove('bg-indigo-500', 'text-white', 'shadow-sm');
        btn.classList.add('text-zinc-600', 'dark:text-zinc-400', 'hover:bg-zinc-100', 'dark:hover:bg-zinc-800');
    });
    
    const active = document.getElementById('btn-' + mode);
    if(active) {
        active.classList.add('bg-indigo-500', 'text-white', 'shadow-sm');
        active.classList.remove('text-zinc-600', 'dark:text-zinc-400', 'hover:bg-zinc-100', 'dark:hover:bg-zinc-800');
    }
    
    transformControl.showX = true;
    transformControl.showY = true;
    transformControl.showZ = true;
};

window.centerCutter = function() {
    const target = (currentPattern === 'custom' || currentPattern === 'fixed' || currentPattern === 'mini') ? activeCutterMesh : cutterGroup;
    if(!target || !modelSize) return;
    
    target.position.set(0, modelSize.y / 2, 0);
    target.rotation.set(0, 0, 0);
    // Note: Do not reset scale automatically to preserve user intentions.
    
    updateInputsFromTransform();
};

function updateInputsFromTransform() {
    const target = (currentPattern === 'custom' || currentPattern === 'fixed' || currentPattern === 'mini') ? activeCutterMesh : cutterGroup;
    if(!target) return;
    
    document.getElementById('trPosX').value = target.position.x.toFixed(1);
    document.getElementById('trPosY').value = target.position.z.toFixed(1);
    document.getElementById('trPosZ').value = target.position.y.toFixed(1);
    
    document.getElementById('trRotX').value = THREE.MathUtils.radToDeg(target.rotation.x).toFixed(1);
    document.getElementById('trRotY').value = THREE.MathUtils.radToDeg(target.rotation.y).toFixed(1);
    document.getElementById('trRotZ').value = THREE.MathUtils.radToDeg(target.rotation.z).toFixed(1);
    
    document.getElementById('scPosX').value = target.scale.x.toFixed(2);
    document.getElementById('scPosY').value = target.scale.y.toFixed(2);
    document.getElementById('scPosZ').value = target.scale.z.toFixed(2);
}

window.updateTransformFromInputs = function() {
    const target = (currentPattern === 'custom' || currentPattern === 'fixed' || currentPattern === 'mini') ? activeCutterMesh : cutterGroup;
    if(!target) return;
    
    const px = parseFloat(document.getElementById('trPosX').value) || 0;
    const pz = parseFloat(document.getElementById('trPosY').value) || 0;
    const py = parseFloat(document.getElementById('trPosZ').value) || 0;
    const rx = parseFloat(document.getElementById('trRotX').value) || 0;
    const ry = parseFloat(document.getElementById('trRotY').value) || 0;
    const rz = parseFloat(document.getElementById('trRotZ').value) || 0;
    const sx = parseFloat(document.getElementById('scPosX').value) || 1;
    const sy = parseFloat(document.getElementById('scPosY').value) || 1;
    const sz = parseFloat(document.getElementById('scPosZ').value) || 1;
    
    target.position.set(px, py, pz);
    target.rotation.set(
        THREE.MathUtils.degToRad(rx),
        THREE.MathUtils.degToRad(ry),
        THREE.MathUtils.degToRad(rz)
    );
    // Don't allow 0 scale to prevent singular matrix errors
    target.scale.set(
        Math.max(0.01, sx),
        Math.max(0.01, sy),
        Math.max(0.01, sz)
    );
};

// ── Camera Modes ───────────────────────────────────
window.setTopView = function() {
    if(!camera || !orbit || !modelSize) return;
    const max = Math.max(modelSize.x, modelSize.z) * 1.5;
    camera.position.set(0, max, 0);
    orbit.target.set(0, 0, 0);
    orbit.update();
};

window.resetCamera = function() {
    if(!camera || !orbit || !modelSize) return;
    const max = Math.max(modelSize.x, modelSize.z) * 1.5;
    camera.position.set(max * 0.8, max * 0.8, max * 0.8);
    orbit.target.set(0, modelSize.y / 2, 0);
    orbit.update();
};

// ── Geometry Helpers ─────────────────────────────
function ensureIndexedGeometry(geometry) {
    let geo = geometry.clone();
    
    // 1. Try standard mergeVertices
    if (!geo.index) {
        geo = mergeVertices(geo);
    }
    
    // 2. If it STILL has no index (edge case for some STLs), build one manually
    if (!geo.index && geo.attributes.position) {
        const count = geo.attributes.position.count;
        const indexArray = count > 65535 ? new Uint32Array(count) : new Uint16Array(count);
        for (let i = 0; i < count; i++) {
            indexArray[i] = i; // Sequential non-indexed fallback
        }
        geo.setIndex(new THREE.BufferAttribute(indexArray, 1));
    }

    // 3. Ensure exact attribute match (CSG strictly requires matching buffers)
    const allowed = ['position', 'normal'];
    const toDelete = [];
    for (const key in geo.attributes) {
        if (!allowed.includes(key)) toDelete.push(key);
    }
    toDelete.forEach(key => geo.deleteAttribute(key));
    if (geo.hasAttribute('uv')) geo.deleteAttribute('uv');
    if (geo.hasAttribute('color')) geo.deleteAttribute('color');

    // 4. Force normal calculation
    geo.computeVertexNormals();
    
    return geo;
}

// ── Interactive Grid Generation ────────────────────
window.selectPattern = function(type) {
    currentPattern = type;
    
    // Change Border Colors
    ['straight', 'alternating', 'custom', 'fixed'].forEach(p => {
        const btn = document.getElementById('pat-' + p);
        if(!btn) return;
        
        if(p === type) {
            btn.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10');
            btn.classList.remove('border-zinc-200', 'dark:border-zinc-700', 'hover:border-zinc-400', 'dark:hover:border-zinc-500');
            if(btn.querySelector('svg')) { btn.querySelector('svg').classList.add('text-indigo-500'); btn.querySelector('svg').classList.remove('text-zinc-400'); }
            if(btn.querySelector('i')) { btn.querySelector('i').classList.add('text-indigo-500'); btn.querySelector('i').classList.remove('text-zinc-400'); }
            if(btn.querySelector('span')) { btn.querySelector('span').classList.add('text-indigo-600', 'dark:text-indigo-400'); btn.querySelector('span').classList.remove('text-zinc-500', 'dark:text-zinc-400'); }
        } else {
            btn.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10');
            btn.classList.add('border-zinc-200', 'dark:border-zinc-700', 'hover:border-zinc-400', 'dark:hover:border-zinc-500');
            if(btn.querySelector('svg')) { btn.querySelector('svg').classList.remove('text-indigo-500'); btn.querySelector('svg').classList.add('text-zinc-400'); }
            if(btn.querySelector('i')) { btn.querySelector('i').classList.remove('text-indigo-500'); btn.querySelector('i').classList.add('text-zinc-400'); }
            if(btn.querySelector('span')) { btn.querySelector('span').classList.remove('text-indigo-600', 'dark:text-indigo-400'); btn.querySelector('span').classList.add('text-zinc-500', 'dark:text-zinc-400'); }
        }
    });

    if(!cutterGroup) return;

    // Toggle UI panels
    if (type === 'custom') {
        document.getElementById('customCutterControls').style.display = 'block';
        if (cutterGroup && cutterGroup.children.length > 0) {
            document.getElementById('actionControls').style.display = 'block';
            setActiveCutter(cutterGroup.children[cutterGroup.children.length - 1]);
        } else {
            document.getElementById('actionControls').style.display = 'none';
        }
    } else if (type === 'fixed') {
        document.getElementById('customCutterControls').style.display = 'none';
        document.getElementById('actionControls').style.display = 'none';
        
        // Clear all previous cuts completely so user sees only the new fixed one
        while(cutterGroup.children.length > 0) { 
            cutterGroup.remove(cutterGroup.children[0]); 
        }
        
        loadFixedCutterSTL();
    } else if (type === 'mini') {
        document.getElementById('customCutterControls').style.display = 'none';
        document.getElementById('actionControls').style.display = 'none';
        
        // Clear all previous cuts completely so user sees only the new fixed one
        while(cutterGroup.children.length > 0) { 
            cutterGroup.remove(cutterGroup.children[0]); 
        }
        
        loadMiniCutterSTL();
    }
};

function loadFixedCutterSTL() {
    showLoading('Carregando Cortador Padrão...');
    
    // Attempt to download the fixed STL from the server
    const fixedUrl = '/assets/models/cortador_padrao.stl';
    
    fetch(fixedUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Arquivo não encontrado no servidor (${response.status})`);
            }
            return response.arrayBuffer();
        })
        .then(buffer => {
            loadCutterSTLFromArrayBuffer(buffer, 'cortador_padrao.stl');
            hideLoading();
            // In Fixed/Mini mode, the Custom Cutter Controls are hidden, so we only unhide the Action Panel (duplication)
            document.getElementById('actionControls').style.display = 'block';
        })
        .catch(err => {
            hideLoading();
            alert('Não foi possível carregar o cortador padrão fixo.\n\nDetalhes do erro: ' + err.message + '\n\nCertifique-se de que o arquivo "cortador_padrao.stl" existe na pasta public/assets/models/ da sua aplicação Laravel.');
            // Fallback to empty custom mode
            selectPattern('custom');
        });
}

function loadMiniCutterSTL() {
    showLoading('Carregando Cortador Mini...');
    
    // Attempt to download the fixed STL from the server
    const miniUrl = '/assets/models/cortador_mini.stl';
    
    fetch(miniUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Arquivo não encontrado no servidor (${response.status})`);
            }
            return response.arrayBuffer();
        })
        .then(buffer => {
            loadCutterSTLFromArrayBuffer(buffer, 'cortador_mini.stl');
            hideLoading();
            document.getElementById('actionControls').style.display = 'block';
        })
        .catch(err => {
            hideLoading();
            alert('Não foi possível carregar o cortador mini fixo.\n\nDetalhes do erro: ' + err.message + '\n\nCertifique-se de que o arquivo "cortador_mini.stl" existe na pasta public/assets/models/ da sua aplicação Laravel.');
            // Fallback to empty custom mode
            selectPattern('custom');
        });
}

// ── CSG Lazy Load ──────────────────────────────────
let CSG = null;
async function loadCSG() {
    if (CSG) return CSG;
    try {
        CSG = await import('https://esm.sh/three-bvh-csg@0.0.16?external=three');
        return CSG;
    } catch (e) {
        try {
            CSG = await import('https://cdn.jsdelivr.net/npm/three-bvh-csg@0.0.16/+esm');
            return CSG;
        } catch (e2) {
            console.error('All CSG CDN sources failed:', e2);
            return null;
        }
    }
}

// ── Download Modified STL ──────────────────────────
window.downloadModifiedSTL = async function() {
    if (!mainMesh || cutterGroup.children.length === 0) return;

    showLoading('Preparando CSG...');

    try {
        const csg = await loadCSG();

        if (!csg || !csg.Evaluator) {
            alert('Não foi possível carregar o manipulador CSG.\n\nBaixando modelo original.');
            exportMesh(mainMesh.geometry, '_original');
            hideLoading();
            return;
        }

        const { Evaluator, Brush, SUBTRACTION } = csg;

        // Force matrix update on cutter group
        cutterGroup.updateMatrixWorld(true);

        // Collect all meshes inside the cutter group (skipping the bounds lines)
        const slitMeshes = cutterGroup.children.filter(c => c.isMesh);

        if (slitMeshes.length === 0) {
            alert('Nenhum corte para aplicar.');
            hideLoading();
            return;
        }

        const evaluator = new Evaluator();
        evaluator.attributes = ['position', 'normal']; // Override defaults (which included 'uv')
        
        const baseMat = new THREE.MeshBasicMaterial();

        document.getElementById('loadingText').textContent = 'Indexando STL...';
        await new Promise(r => setTimeout(r, 10));

        // Use our robust indexing helper
        let indexedGeo = ensureIndexedGeometry(mainMesh.geometry);

        let currentBrush = new Brush(indexedGeo, baseMat);
        currentBrush.updateMatrixWorld();

        // Recursively subtract each slit
        for (let i = 0; i < slitMeshes.length; i++) {
            document.getElementById('loadingText').textContent = `Aplicando corte ${i + 1}/${slitMeshes.length}...`;
            await new Promise(r => setTimeout(r, 0));

            const localMesh = slitMeshes[i];
            
            // localMesh.geometry might have been cloned/shared. Ensure it is rigorously indexed.
            let slitGeo = ensureIndexedGeometry(localMesh.geometry.clone());

            // To get the absolute position of the slit in the world:
            // Apply the Mesh's global Matrix directly to the Geometry vertices.
            localMesh.updateMatrixWorld(true);
            slitGeo.applyMatrix4(localMesh.matrixWorld);
            slitGeo.computeVertexNormals();

            const brush = new Brush(slitGeo, baseMat);
            brush.updateMatrixWorld();

            const result = evaluator.evaluate(currentBrush, brush, SUBTRACTION);
            currentBrush = result;
        }

        document.getElementById('loadingText').textContent = 'Exportando STL...';
        await new Promise(r => setTimeout(r, 50));

        exportMesh(currentBrush.geometry, `_flexi`);

    } catch (err) {
        console.error('Erro CSG:', err);
        alert('Erro ao aplicar junções: ' + err.message);
    }

    hideLoading();
};

window.downloadOriginalSTL = function() {
    if (!mainMesh) return;
    exportMesh(mainMesh.geometry, '_original');
};

function exportMesh(geometry, suffix) {
    const mesh = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial());
    const exporter = new STLExporter();
    const stl = exporter.parse(mesh, { binary: true });

    const blob = new Blob([stl], { type: 'model/stl' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    const name = document.getElementById('modelFileName').textContent.replace('.stl', '');
    a.download = `${name}${suffix}.stl`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// ── Load New File ──────────────────────────────────
window.loadNewFile = function() {
    if (mainMesh) { scene.remove(mainMesh); mainMesh = null; }
    while(cutterGroup.children.length > 0){ cutterGroup.remove(cutterGroup.children[0]); }
    modelBBox = null;
    
    transformControl.detach();

    document.getElementById('uploadOverlay').style.display = 'flex';
    document.getElementById('viewerHeader').style.display = 'none';
    document.getElementById('canvasToolbar').style.display = 'none';
    document.getElementById('viewerContainer').style.display = 'none';
    document.getElementById('controlsPanel').style.display = 'none';
    document.getElementById('downloadPanel').style.display = 'none';
};

// ── Theme observer ─────────────────────────────────
const themeObserver = new MutationObserver(() => {
    if (scene) {
        const isDark = document.documentElement.classList.contains('dark');
        scene.background = new THREE.Color(isDark ? 0x0f0f11 : 0xf4f4f5);
        if(scene.children.length > 0) {
            scene.children.forEach(child => {
                if(child.type === 'GridHelper') {
                     child.material.color.setHex(isDark ? 0x333333 : 0x888888);
                }
            });
        }
    }
});
themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

// ── Loading Utilities ──────────────────────────────
function showLoading(text) {
    document.getElementById('loadingText').textContent = text || 'Carregando...';
    document.getElementById('loadingOverlay').style.display = 'flex';
}
function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

// Ensure Transform Mode starts cleanly
setTimeout(() => window.setTransformMode('translate'), 500);

</script>
@endsection
