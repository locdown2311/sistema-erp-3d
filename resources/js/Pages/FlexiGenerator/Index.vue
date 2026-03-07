<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { TransformControls } from 'three/addons/controls/TransformControls.js';
import { STLLoader } from 'three/addons/loaders/STLLoader.js';
import { STLExporter } from 'three/addons/exporters/STLExporter.js';
import { mergeVertices } from 'three/addons/utils/BufferGeometryUtils.js';

const page = usePage();
const flexiCurrent = ref(page.props.flexi_current);
const flexiLimit = ref(page.props.flexi_limit);
const canUploadCutter = ref(page.props.canUploadCutter);

// UI State
const isLoaded = ref(false);
const isLoading = ref(false);
const loadingText = ref('Carregando modelo...');
const currentPattern = ref('straight');
const modelFileName = ref('Modelo 3D');
const modelInfo = ref('');
const transformMode = ref('translate');

// DOM Refs
const canvasContainer = ref(null);
const threeCanvas = ref(null);
const dropZone = ref(null);
const stlFileInput = ref(null);
const cutterDropZone = ref(null);
const cutterFileInput = ref(null);

// Transform Inputs
const trPosX = ref(0); const trPosY = ref(0); const trPosZ = ref(0);
const trRotX = ref(0); const trRotY = ref(0); const trRotZ = ref(0);
const scPosX = ref(1); const scPosY = ref(1); const scPosZ = ref(1);

// ThreeJS State (non-reactive for performance)
let scene, camera, renderer, orbit, transformControl, mainMesh;
let modelBBox = null;
let modelSize = new THREE.Vector3();
let cutterGroup = new THREE.Group();
let activeCutterMesh = null;
const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();
let CSG = null;
let animationId = null;

onMounted(() => {
    initThree();

    // Drag and Drop Events for Main File
    if (dropZone.value) {
        dropZone.value.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.value.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10'); });
        dropZone.value.addEventListener('dragleave', (e) => { e.preventDefault(); dropZone.value.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10'); });
        dropZone.value.addEventListener('drop', (e) => { e.preventDefault(); dropZone.value.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10'); if (e.dataTransfer.files.length > 0) handleFile(e.dataTransfer.files[0]); });
    }

    if (cutterDropZone.value) {
        cutterDropZone.value.addEventListener('dragover', (e) => { e.preventDefault(); cutterDropZone.value.classList.add('border-indigo-500', 'bg-indigo-100', 'dark:bg-indigo-500/20'); });
        cutterDropZone.value.addEventListener('dragleave', (e) => { e.preventDefault(); cutterDropZone.value.classList.remove('border-indigo-500', 'bg-indigo-100', 'dark:bg-indigo-500/20'); });
        cutterDropZone.value.addEventListener('drop', (e) => { e.preventDefault(); cutterDropZone.value.classList.remove('border-indigo-500', 'bg-indigo-100', 'dark:bg-indigo-500/20'); if (e.dataTransfer.files.length > 0) handleCutterFile(e.dataTransfer.files[0]); });
    }

    window.addEventListener('resize', onResize);
    setTransformMode('translate');
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', onResize);
    if (animationId) cancelAnimationFrame(animationId);
    if (renderer) renderer.dispose();
});

function initThree() {
    scene = new THREE.Scene();
    const isDark = document.documentElement.classList.contains('dark');
    scene.background = new THREE.Color(isDark ? 0x0f0f11 : 0xf4f4f5);

    camera = new THREE.PerspectiveCamera(45, canvasContainer.value.clientWidth / canvasContainer.value.clientHeight, 0.1, 10000);
    camera.position.set(0, 150, 150);

    renderer = new THREE.WebGLRenderer({ canvas: threeCanvas.value, antialias: true, alpha: true });
    renderer.setSize(canvasContainer.value.clientWidth, canvasContainer.value.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    scene.add(new THREE.AmbientLight(0xffffff, 0.7));
    const dirLight = new THREE.DirectionalLight(0xffffff, 1.2);
    dirLight.position.set(50, 150, 50);
    dirLight.castShadow = true;
    dirLight.shadow.mapSize.set(2048, 2048);
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

    const gridHelper = new THREE.GridHelper(400, 40, 0x888888, 0xcccccc);
    gridHelper.position.y = -0.1;
    if (isDark) {
        gridHelper.material.color.setHex(0x333333);
        const colors = gridHelper.geometry.attributes.color;
        for (let i = 0; i < colors.count; i++) {
            if (colors.getX(i) > 0.5) colors.setXYZ(i, 0.2, 0.2, 0.2);
        }
    }
    scene.add(gridHelper);

    orbit = new OrbitControls(camera, renderer.domElement);
    orbit.enableDamping = true;
    orbit.dampingFactor = 0.05;
    orbit.maxPolarAngle = Math.PI / 2 + 0.1;
    orbit.minDistance = 10;
    orbit.maxDistance = 600;

    transformControl = new TransformControls(camera, renderer.domElement);
    transformControl.addEventListener('dragging-changed', (event) => orbit.enabled = !event.value);
    transformControl.addEventListener('change', updateInputsFromTransform);
    scene.add(transformControl.getHelper());
    scene.add(cutterGroup);

    renderer.domElement.addEventListener('pointerdown', (event) => {
        if (!['custom', 'fixed', 'mini'].includes(currentPattern.value) || !cutterGroup) return;
        const rect = renderer.domElement.getBoundingClientRect();
        mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        mouse.y = - ((event.clientY - rect.top) / rect.height) * 2 + 1;
        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(cutterGroup.children, false);
        if (intersects.length > 0) setActiveCutter(intersects[0].object);
    });

    animate();
}

function onResize() {
    if (!canvasContainer.value || !camera || !renderer) return;
    camera.aspect = canvasContainer.value.clientWidth / canvasContainer.value.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(canvasContainer.value.clientWidth, canvasContainer.value.clientHeight);
}

function animate() {
    animationId = requestAnimationFrame(animate);
    if (orbit) orbit.update();
    if (renderer && scene && camera) renderer.render(scene, camera);
}

// ── Files Handling ──────────────
function handleFile(file) {
    if (!file) return;
    if (file.name.split('.').pop().toLowerCase() !== 'stl') return window.alert('Apenas arquivos .stl são aceitos.');
    
    showLoading('Processando malha...');
    const reader = new FileReader();
    reader.onload = (e) => {
        loadSTLFromArrayBuffer(e.target.result, file.name);
        isLoaded.value = true;
        hideLoading();
        nextTick(() => onResize());
    };
    reader.readAsArrayBuffer(file);
}

function handleCutterFile(file) {
    if (!file) return;
    if (file.name.split('.').pop().toLowerCase() !== 'stl') return window.alert('Apenas .stl para o molde.');
    
    showLoading('Carregando cortador...');
    const reader = new FileReader();
    reader.onload = (e) => {
        modelFileName.value = file.name;
        loadCutterSTLFromArrayBuffer(e.target.result, file.name);
        hideLoading();
    };
    reader.readAsArrayBuffer(file);
}

function loadSTLFromArrayBuffer(buffer, filename) {
    const loader = new STLLoader();
    const geometry = loader.parse(buffer);
    geometry.computeVertexNormals();
    geometry.rotateX(-Math.PI / 2);
    
    geometry.computeBoundingBox();
    const center = new THREE.Vector3();
    geometry.boundingBox.getCenter(center);
    geometry.translate(-center.x, -center.y, -center.z);
    
    geometry.computeBoundingBox();
    geometry.translate(0, -geometry.boundingBox.min.y, 0);

    const material = new THREE.MeshPhysicalMaterial({
        color: 0x6366f1,
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

    setTopView();

    const triCount = geometry.attributes.position.count / 3;
    modelFileName.value = filename;
    modelInfo.value = `${triCount.toLocaleString('pt-BR')} triângulos`;
}

function loadCutterSTLFromArrayBuffer(buffer, filename) {
    const loader = new STLLoader();
    let geometry = loader.parse(buffer);
    geometry = mergeVertices(geometry);
    geometry.rotateX(-Math.PI / 2);
    
    geometry.computeBoundingBox();
    const center = new THREE.Vector3();
    geometry.boundingBox.getCenter(center);
    geometry.translate(-center.x, -center.y, -center.z);
    
    geometry = ensureIndexedGeometry(geometry);

    const mat = new THREE.MeshBasicMaterial({ color: 0x10b981, transparent: true, opacity: 0.50, depthTest: true, side: THREE.DoubleSide });
    const mesh = new THREE.Mesh(geometry, mat);

    const edges = new THREE.EdgesGeometry(geometry);
    const edgeMat = new THREE.LineBasicMaterial({ color: 0x059669, opacity: 0.8, transparent: true });
    mesh.add(new THREE.LineSegments(edges, edgeMat));

    while (cutterGroup.children.length > 0) cutterGroup.remove(cutterGroup.children[0]);
    cutterGroup.add(mesh);
    setActiveCutter(mesh);
    centerCutter();
}

function loadFixedCutterSTL() {
    showLoading('Carregando Cortador Padrão...');
    fetch('/assets/models/cortador_padrao.stl')
        .then(res => { if(!res.ok) throw new Error(); return res.arrayBuffer(); })
        .then(buffer => { loadCutterSTLFromArrayBuffer(buffer, 'cortador_padrao.stl'); hideLoading(); })
        .catch(err => { hideLoading(); window.alert('Erro ao carregar cortador fixo.'); selectPattern('custom'); });
}

function loadMiniCutterSTL() {
    showLoading('Carregando Cortador Mini...');
    fetch('/assets/models/cortador_mini.stl')
        .then(res => { if(!res.ok) throw new Error(); return res.arrayBuffer(); })
        .then(buffer => { loadCutterSTLFromArrayBuffer(buffer, 'cortador_mini.stl'); hideLoading(); })
        .catch(err => { hideLoading(); window.alert('Erro ao carregar cortador mini.'); selectPattern('custom'); });
}

function setActiveCutter(mesh) {
    if (!mesh) return;
    cutterGroup.children.forEach(c => {
        if (c.material) { c.material.opacity = 0.25; c.material.color.setHex(0x059669); }
    });
    mesh.material.opacity = 0.65;
    mesh.material.color.setHex(0x10b981);
    activeCutterMesh = mesh;
    transformControl.attach(activeCutterMesh);
    updateInputsFromTransform();
}

function cloneCutter() {
    if (!activeCutterMesh) return;
    const cloneGeo = activeCutterMesh.geometry.clone();
    const cloneMat = activeCutterMesh.material.clone();
    const clone = new THREE.Mesh(ensureIndexedGeometry(cloneGeo), cloneMat);
    clone.position.copy(activeCutterMesh.position);
    clone.rotation.copy(activeCutterMesh.rotation);
    clone.scale.copy(activeCutterMesh.scale);
    clone.position.x += 10;
    clone.position.z += 10;
    
    const edges = new THREE.EdgesGeometry(clone.geometry);
    const edgeMat = new THREE.LineBasicMaterial({ color: 0x059669, opacity: 0.8, transparent: true });
    clone.add(new THREE.LineSegments(edges, edgeMat));
    
    cutterGroup.add(clone);
    setActiveCutter(clone);
}

// ── Transforms ──────────────
function setTransformMode(mode) {
    if (!transformControl) return;
    try {
        if (typeof transformControl.setMode === 'function') {
            transformControl.setMode(mode);
        } else {
            transformControl.mode = mode;
        }
    } catch (e) {
        console.warn('Fallback transform mode setting', e);
        transformControl.mode = mode;
    }
    transformMode.value = mode;
    transformControl.showX = true;
    transformControl.showY = true;
    transformControl.showZ = true;
}

function centerCutter() {
    const target = ['custom', 'fixed', 'mini'].includes(currentPattern.value) ? activeCutterMesh : cutterGroup;
    if (!target || !modelSize) return;
    target.position.set(0, modelSize.y / 2, 0);
    target.rotation.set(0, 0, 0);
    updateInputsFromTransform();
}

function updateInputsFromTransform() {
    const target = ['custom', 'fixed', 'mini'].includes(currentPattern.value) ? activeCutterMesh : cutterGroup;
    if (!target) return;
    trPosX.value = target.position.x.toFixed(1);
    trPosY.value = target.position.z.toFixed(1);
    trPosZ.value = target.position.y.toFixed(1);
    trRotX.value = THREE.MathUtils.radToDeg(target.rotation.x).toFixed(1);
    trRotY.value = THREE.MathUtils.radToDeg(target.rotation.y).toFixed(1);
    trRotZ.value = THREE.MathUtils.radToDeg(target.rotation.z).toFixed(1);
    scPosX.value = target.scale.x.toFixed(2);
    scPosY.value = target.scale.y.toFixed(2);
    scPosZ.value = target.scale.z.toFixed(2);
}

function updateTransformFromInputs() {
    const target = ['custom', 'fixed', 'mini'].includes(currentPattern.value) ? activeCutterMesh : cutterGroup;
    if (!target) return;
    target.position.set(parseFloat(trPosX.value) || 0, parseFloat(trPosZ.value) || 0, parseFloat(trPosY.value) || 0);
    target.rotation.set(
        THREE.MathUtils.degToRad(parseFloat(trRotX.value) || 0),
        THREE.MathUtils.degToRad(parseFloat(trRotY.value) || 0),
        THREE.MathUtils.degToRad(parseFloat(trRotZ.value) || 0)
    );
    target.scale.set(
        Math.max(0.01, parseFloat(scPosX.value) || 1),
        Math.max(0.01, parseFloat(scPosY.value) || 1),
        Math.max(0.01, parseFloat(scPosZ.value) || 1)
    );
}

// ── Utils ──────────────
function setTopView() {
    if (!camera || !orbit || !modelSize) return;
    const max = Math.max(modelSize.x, modelSize.z) * 1.5;
    camera.position.set(0, max, 0);
    orbit.target.set(0, 0, 0);
    orbit.update();
}

function resetCamera() {
    if (!camera || !orbit || !modelSize) return;
    const max = Math.max(modelSize.x, modelSize.z) * 1.5;
    camera.position.set(max * 0.8, max * 0.8, max * 0.8);
    orbit.target.set(0, modelSize.y / 2, 0);
    orbit.update();
}

function ensureIndexedGeometry(geometry) {
    let geo = geometry.clone();
    if (!geo.index) geo = mergeVertices(geo);
    if (!geo.index && geo.attributes.position) {
        const count = geo.attributes.position.count;
        const indexArray = count > 65535 ? new Uint32Array(count) : new Uint16Array(count);
        for (let i = 0; i < count; i++) indexArray[i] = i;
        geo.setIndex(new THREE.BufferAttribute(indexArray, 1));
    }
    const allowed = ['position', 'normal'];
    for (const key in geo.attributes) if (!allowed.includes(key)) geo.deleteAttribute(key);
    if (geo.hasAttribute('uv')) geo.deleteAttribute('uv');
    if (geo.hasAttribute('color')) geo.deleteAttribute('color');
    geo.computeVertexNormals();
    return geo;
}

function selectPattern(type) {
    currentPattern.value = type;
    if (!cutterGroup) return;
    if (type === 'custom') {
        if (cutterGroup.children.length > 0) setActiveCutter(cutterGroup.children[cutterGroup.children.length - 1]);
    } else if (type === 'fixed') {
        while (cutterGroup.children.length > 0) cutterGroup.remove(cutterGroup.children[0]);
        loadFixedCutterSTL();
    } else if (type === 'mini') {
        while (cutterGroup.children.length > 0) cutterGroup.remove(cutterGroup.children[0]);
        loadMiniCutterSTL();
    }
}

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

async function downloadModifiedSTL() {
    if (!mainMesh || cutterGroup.children.length === 0) return;
    showLoading('Preparando CSG...');

    try {
        const response = await fetch('/gerador-flexi/rastrear-uso', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': page.props.csrf_token,
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();

        if (!data.allowed) {
            hideLoading();
            window.alert(data.message || 'Limite de uso alcançado. Faça upgrade para continuar!');
            if (window.confirm('Deseja ver os planos de assinatura agora?')) window.location.href = '/planos';
            return;
        }

        if (data.current !== undefined) {
            flexiCurrent.value = data.current;
            flexiLimit.value = data.limit;
        }

        const csg = await loadCSG();
        if (!csg || !csg.Evaluator) {
            window.alert('Não foi possível carregar o manipulador CSG. Baixando modelo original.');
            exportMesh(mainMesh.geometry, '_original');
            hideLoading();
            return;
        }

        const { Evaluator, Brush, SUBTRACTION } = csg;
        cutterGroup.updateMatrixWorld(true);
        const slitMeshes = cutterGroup.children.filter(c => c.isMesh);

        if (slitMeshes.length === 0) { window.alert('Nenhum corte para aplicar.'); hideLoading(); return; }

        const evaluator = new Evaluator();
        evaluator.attributes = ['position', 'normal'];
        const baseMat = new THREE.MeshBasicMaterial();

        loadingText.value = 'Indexando STL...';
        await new Promise(r => setTimeout(r, 10));

        let currentBrush = new Brush(ensureIndexedGeometry(mainMesh.geometry), baseMat);
        currentBrush.updateMatrixWorld();

        for (let i = 0; i < slitMeshes.length; i++) {
            loadingText.value = `Aplicando corte ${i + 1}/${slitMeshes.length}...`;
            await new Promise(r => setTimeout(r, 0));
            const localMesh = slitMeshes[i];
            let slitGeo = ensureIndexedGeometry(localMesh.geometry.clone());
            localMesh.updateMatrixWorld(true);
            slitGeo.applyMatrix4(localMesh.matrixWorld);
            slitGeo.computeVertexNormals();
            const brush = new Brush(slitGeo, baseMat);
            brush.updateMatrixWorld();
            currentBrush = evaluator.evaluate(currentBrush, brush, SUBTRACTION);
        }

        loadingText.value = 'Exportando STL...';
        await new Promise(r => setTimeout(r, 50));
        exportMesh(currentBrush.geometry, `_flexi`);

    } catch (err) {
        console.error('Erro CSG:', err);
        window.alert('Erro ao aplicar junções: ' + err.message);
    }
    hideLoading();
}

function downloadOriginalSTL() {
    if (!mainMesh) return;
    exportMesh(mainMesh.geometry, '_original');
}

function exportMesh(geometry, suffix) {
    const mesh = new THREE.Mesh(geometry, new THREE.MeshBasicMaterial());
    const exporter = new STLExporter();
    const stl = exporter.parse(mesh, { binary: true });
    const blob = new Blob([stl], { type: 'model/stl' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${modelFileName.value.replace('.stl', '')}${suffix}.stl`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function loadNewFile() {
    if (mainMesh) { scene.remove(mainMesh); mainMesh = null; }
    while (cutterGroup.children.length > 0) cutterGroup.remove(cutterGroup.children[0]);
    modelBBox = null;
    transformControl.detach();
    isLoaded.value = false;
}

function showLoading(text) { loadingText.value = text || 'Carregando...'; isLoading.value = true; }
function hideLoading() { isLoading.value = false; }
</script>

<template>
    <AppLayout>
        <template #header>Gerador Flexi</template>

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_360px] gap-6 xl:gap-8 flex-1 w-full min-h-[500px]">
            
            <!-- 3D Viewer -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col relative w-full h-full min-h-[400px]">
                
                <!-- Upload overlay -->
                <div v-show="!isLoaded" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white dark:bg-zinc-900 transition-opacity">
                    <div ref="dropZone" @click="$refs.stlFileInput.click()" class="w-full h-full flex flex-col items-center justify-center cursor-pointer border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl m-4 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5 transition-all group">
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
                        <input type="file" ref="stlFileInput" @change="handleFile($event.target.files[0])" accept=".stl" class="hidden">
                    </div>
                </div>

                <!-- 3D Canvas header -->
                <div v-show="isLoaded" class="px-5 sm:px-6 py-3 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2 max-w-[150px] sm:max-w-[300px] truncate">
                            <i class="fas fa-cube text-indigo-500"></i>
                            <span>{{ modelFileName }}</span>
                        </h3>
                        <span class="text-xs text-zinc-400 dark:text-zinc-500 hidden sm:inline">{{ modelInfo }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <button @click="setTopView" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg transition-colors border border-indigo-200 dark:border-indigo-500/20" title="Vista Superior 2D">
                            <i class="fas fa-square"></i> Topo
                        </button>
                        <button @click="resetCamera" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white bg-zinc-100 dark:bg-zinc-800 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors" title="Reset câmera livre">
                            <i class="fas fa-video"></i>
                        </button>
                        <button @click="loadNewFile" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white bg-zinc-100 dark:bg-zinc-800 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors" title="Carregar novo arquivo">
                            <i class="fas fa-folder-open"></i> Novo
                        </button>
                    </div>
                </div>

                <!-- Tool Panel Over Canvas (Bottom Center) -->
                <div v-show="isLoaded" class="absolute bottom-6 left-1/2 -translate-x-1/2 z-10 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-full shadow-lg p-1.5 flex items-center gap-1">
                    <button @click="setTransformMode('translate')" :class="transformMode === 'translate' ? 'bg-indigo-500 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800'" class="px-4 py-2 rounded-full text-sm font-semibold transition-all flex items-center gap-2">
                        <i class="fas fa-arrows-alt"></i> Mover
                    </button>
                    <button @click="setTransformMode('rotate')" :class="transformMode === 'rotate' ? 'bg-indigo-500 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800'" class="px-4 py-2 rounded-full text-sm font-semibold transition-all flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i> Girar
                    </button>
                    <button @click="setTransformMode('scale')" :class="transformMode === 'scale' ? 'bg-indigo-500 text-white shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800'" class="px-4 py-2 rounded-full text-sm font-semibold transition-all flex items-center gap-2">
                        <i class="fas fa-compress-arrows-alt"></i> Escalar
                    </button>
                </div>

                <!-- Three.js canvas -->
                <div ref="canvasContainer" class="flex-1 relative bg-zinc-100 dark:bg-zinc-950 w-full h-full" :class="!isLoaded ? 'opacity-0' : 'opacity-100'">
                    <canvas ref="threeCanvas" class="w-full h-full outline-none"></canvas>
                </div>
            </div>

            <!-- Controls Panel -->
            <div class="flex flex-col gap-4 overflow-y-auto pb-4 custom-scrollbar">

                <div v-show="isLoaded" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden shrink-0">
                    <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <h3 class="font-semibold text-sm text-zinc-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-tools text-indigo-500"></i> Ferramenta de Corte
                        </h3>
                    </div>
                    
                    <div class="p-4 space-y-5 shadow-inner bg-zinc-50/30 dark:bg-zinc-950/30">
                        <div class="grid grid-cols-2 gap-2">
                            <!-- Custom Cutters (Premium Only) -->
                            <template v-if="canUploadCutter">
                                <button @click="selectPattern('custom')" :class="currentPattern === 'custom' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500'" class="pattern-card relative flex flex-col justify-center items-center gap-1.5 p-2 rounded-lg border-2 transition-all">
                                    <i class="fas fa-upload text-xl mb-1 mt-1" :class="currentPattern === 'custom' ? 'text-indigo-500' : 'text-zinc-400'"></i>
                                    <span class="text-[10px] font-semibold text-center leading-tight" :class="currentPattern === 'custom' ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-500 dark:text-zinc-400'">Cortador<br>Upload</span>
                                </button>
                            </template>
                            <template v-else>
                                <div @click="() => { window.Swal?.fire({icon: 'warning', title: 'Recurso Premium', text: 'O upload de cortadores personalizados é exclusivo.', confirmButtonText: 'Ver Planos'}).then((r) => r.isConfirmed && (window.location.href = '/planos')) }" class="pattern-card relative flex flex-col justify-center items-center gap-1.5 p-2 rounded-lg border-2 border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 cursor-pointer overflow-hidden group">
                                    <i class="fas fa-upload text-xl text-zinc-400 mb-1 mt-1"></i>
                                    <span class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-600 text-center leading-tight">Cortador<br>Upload</span>
                                    <div class="absolute inset-0 bg-zinc-900/10 dark:bg-black/40 backdrop-blur-[1px] flex items-center justify-center transition-all group-hover:bg-zinc-900/20 dark:group-hover:bg-black/60">
                                        <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg"><i class="fas fa-lock mr-0.5"></i> PRO</span>
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Fixed Pattern -->
                            <button @click="selectPattern('fixed')" :class="currentPattern === 'fixed' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500'" class="pattern-card relative flex flex-col justify-center items-center gap-1.5 p-2 rounded-lg border-2 transition-all">
                                <i class="fas fa-plug text-xl mb-1 mt-1" :class="currentPattern === 'fixed' ? 'text-indigo-500' : 'text-zinc-400'"></i>
                                <span class="text-[10px] font-semibold text-center leading-tight" :class="currentPattern === 'fixed' ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-500 dark:text-zinc-400'">Encaixe<br>Base</span>
                            </button>

                            <!-- Mini Pattern -->
                            <button @click="selectPattern('mini')" :class="currentPattern === 'mini' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500'" class="pattern-card relative flex flex-col justify-center items-center gap-1.5 p-2 rounded-lg border-2 transition-all">
                                <i class="fas fa-compress text-xl mb-1 mt-1" :class="currentPattern === 'mini' ? 'text-indigo-500' : 'text-zinc-400'"></i>
                                <span class="text-[10px] font-semibold text-center leading-tight" :class="currentPattern === 'mini' ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-500 dark:text-zinc-400'">Encaixe<br>Mini</span>
                            </button>
                        </div>

                        <!-- Custom STL Cutter Controls -->
                        <div v-show="currentPattern === 'custom'" class="pt-3 border-t border-zinc-200 dark:border-zinc-800">
                            <span class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Molde de Corte (STL)</span>
                            <div ref="cutterDropZone" @click="$refs.cutterFileInput.click()" class="w-full h-24 flex flex-col items-center justify-center cursor-pointer border-2 border-dashed border-indigo-300 dark:border-indigo-700/50 bg-indigo-50/50 dark:bg-indigo-500/5 rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-500/10 transition-colors">
                                <i class="fas fa-upload text-xl text-indigo-500 mb-2"></i>
                                <span class="text-xs text-indigo-700 dark:text-indigo-400 font-medium text-center px-4">Clique para carregar (.stl)</span>
                                <input type="file" ref="cutterFileInput" @change="handleCutterFile($event.target.files[0])" accept=".stl" class="hidden">
                            </div>
                        </div>

                        <!-- Action Panel (Clone) -->
                        <div v-show="['custom', 'fixed', 'mini'].includes(currentPattern)" class="pt-3 border-t border-zinc-200 dark:border-zinc-800">
                            <button @click="cloneCutter" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-xs font-semibold rounded outline-none transition-colors border border-indigo-200 dark:border-indigo-800/50">
                                <i class="fas fa-copy"></i> Duplicar Cortador Selecionado
                            </button>
                            <p class="text-[10px] text-zinc-400 mt-2 text-center leading-tight">Dica: Clique em uma peça verde no 3D para selecioná-la.</p>
                        </div>

                        <!-- Transform Sync -->
                        <div class="grid grid-cols-3 gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-100 dark:bg-zinc-900/80 p-3 rounded-lg border border-zinc-200 dark:border-zinc-800 shadow-inner">
                            <div class="col-span-3 mb-1 flex items-center justify-between">
                                <span class="text-[10px] uppercase font-bold text-zinc-500 tracking-wider">Mundo</span>
                                <button @click="centerCutter" class="text-[10px] text-indigo-500 hover:text-indigo-600 font-semibold uppercase"><i class="fas fa-crosshairs"></i> Center</button>
                            </div>
                            <div>
                                <label class="block text-[10px] text-zinc-500">Pos X</label>
                                <input type="number" v-model="trPosX" @change="updateTransformFromInputs" step="1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-[10px] text-zinc-500">Pos Y</label>
                                <input type="number" v-model="trPosY" @change="updateTransformFromInputs" step="1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-[10px] text-zinc-500">Pos Z</label>
                                <input type="number" v-model="trPosZ" @change="updateTransformFromInputs" step="1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-[10px] text-zinc-500">Rot X°</label>
                                <input type="number" v-model="trRotX" @change="updateTransformFromInputs" step="5" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-[10px] text-zinc-500">Rot Y°</label>
                                <input type="number" v-model="trRotY" @change="updateTransformFromInputs" step="5" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-[10px] text-zinc-500">Rot Z°</label>
                                <input type="number" v-model="trRotZ" @change="updateTransformFromInputs" step="5" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-[10px] text-zinc-500">Scale X</label>
                                <input type="number" v-model="scPosX" @change="updateTransformFromInputs" step="0.1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-[10px] text-zinc-500">Scale Y</label>
                                <input type="number" v-model="scPosY" @change="updateTransformFromInputs" step="0.1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300">
                            </div>
                            <div>
                                <label class="block text-[10px] text-zinc-500">Scale Z</label>
                                <input type="number" v-model="scPosZ" @change="updateTransformFromInputs" step="0.1" class="w-full px-1.5 py-1 text-xs border border-zinc-300 dark:border-zinc-700 rounded bg-white dark:bg-zinc-950 font-mono text-zinc-700 dark:text-zinc-300">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Download -->
                <div v-show="isLoaded" class="bg-gradient-to-br from-emerald-500/5 to-teal-500/5 dark:from-emerald-500/10 dark:to-teal-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl shadow-sm overflow-hidden shrink-0">
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between text-xs mb-3 px-1">
                            <span class="font-medium text-zinc-600 dark:text-zinc-400">Uso do Gerador</span>
                            <span class="font-bold" :class="(flexiLimit && flexiCurrent >= flexiLimit) ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400'">
                                {{ flexiCurrent }} / {{ flexiLimit || '∞' }}
                            </span>
                        </div>
                        
                        <div v-if="flexiLimit && flexiCurrent >= flexiLimit" class="px-3 py-2 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded text-[10px] text-red-600 dark:text-red-400 text-center uppercase tracking-wide font-bold mb-2">
                            Limite Atingido
                        </div>
                        
                        <button @click="downloadModifiedSTL" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-lg hover:from-emerald-600 hover:to-teal-700 transition-all shadow-sm">
                            <i class="fas fa-download"></i> Baixar Modificado (CSG)
                        </button>
                        <button @click="downloadOriginalSTL" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-zinc-500 dark:text-zinc-400 text-xs font-medium hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">
                            <i class="fas fa-file-export"></i> Baixar Original
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div v-show="isLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-white/80 dark:bg-zinc-950/80 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-indigo-200 dark:border-indigo-800 border-t-indigo-500 rounded-full animate-spin"></div>
                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ loadingText }}</span>
            </div>
        </div>
    </AppLayout>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #d4d4d8; border-radius: 4px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #3f3f46; }
</style>
