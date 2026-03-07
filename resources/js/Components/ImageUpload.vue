<script setup>
import { ref, onMounted, watch } from 'vue';
import 'cropperjs/dist/cropper.css';
import Cropper from 'cropperjs';

const props = defineProps({
    currentImage: {
        type: String,
        default: null,
    },
    imageUrl: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:croppedData', 'update:file']);

const fileInput = ref(null);
const dropZone = ref(null);
const imagePreview = ref(null);
const cropImageRef = ref(null);

const isDragging = ref(false);
const previewUrl = ref(props.imageUrl || (props.currentImage ? (props.currentImage.startsWith('http') ? props.currentImage : `/storage/${props.currentImage}`) : null));
const showModal = ref(false);

let cropper = null;
let originalFile = null;

onMounted(() => {
    document.addEventListener('paste', handlePaste);
});

const handlePaste = (e) => {
    if (!showModal.value) {
        const items = e.clipboardData?.items;
        if (!items) return;
        for (const item of items) {
            if (item.type.startsWith('image/')) {
                e.preventDefault();
                openCropModal(item.getAsFile());
                break;
            }
        }
    }
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileSelect = (e) => {
    if (e.target.files && e.target.files[0]) {
        openCropModal(e.target.files[0]);
    }
};

const handleDragOver = (e) => {
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (e) => {
    e.preventDefault();
    isDragging.value = false;
};

const handleDrop = (e) => {
    e.preventDefault();
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        openCropModal(file);
    }
};

const openCropModal = (file) => {
    originalFile = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        showModal.value = true;
        // Wait for next tick so the modal img is in DOM
        setTimeout(() => {
            if (cropImageRef.value) {
                cropImageRef.value.src = e.target.result;
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(cropImageRef.value, {
                    viewMode: 1,
                    aspectRatio: NaN, // Free crop
                    autoCropArea: 0.9,
                    responsive: true,
                    background: false,
                    guides: true,
                });
            }
        }, 50);
    };
    reader.readAsDataURL(file);
};

const closeCropModal = () => {
    showModal.value = false;
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
};

const applyCrop = () => {
    if (!cropper) return;
    const canvas = cropper.getCroppedCanvas({
        maxWidth: 1200,
        maxHeight: 1200,
        imageSmoothingQuality: 'high',
    });

    previewUrl.value = canvas.toDataURL('image/jpeg', 0.9);
    emit('update:croppedData', previewUrl.value);
    
    // Create a new File object from the Blob if needed by parent
    canvas.toBlob((blob) => {
        const newFile = new File([blob], originalFile.name, { type: 'image/jpeg' });
        emit('update:file', newFile);
    }, 'image/jpeg', 0.9);

    closeCropModal();
};

const skipCrop = () => {
    if (originalFile) {
        const url = URL.createObjectURL(originalFile);
        previewUrl.value = url;
        emit('update:file', originalFile);
    }
    closeCropModal();
};

const rotate = (degree) => {
    if (cropper) cropper.rotate(degree);
};

const flipH = () => {
    if (cropper) {
        const data = cropper.getImageData();
        cropper.scaleX(data.scaleX === -1 ? 1 : -1);
    }
};
</script>

<template>
    <div class="mb-5 relative">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Imagem</label>

        <!-- Drop/Paste Zone -->
        <div ref="dropZone" 
             @click="triggerFileInput"
             @dragover="handleDragOver"
             @dragleave="handleDragLeave"
             @drop="handleDrop"
             class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-colors relative"
             :class="isDragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 hover:bg-zinc-100 dark:hover:bg-zinc-900'">
            
            <div v-if="previewUrl" class="block">
                <img :src="previewUrl" ref="imagePreview" class="max-w-full max-h-48 mx-auto rounded-lg mb-2 object-contain bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">Clique ou cole (Ctrl+V) para trocar</div>
            </div>

            <div v-else class="block">
                <i class="fas fa-cloud-upload-alt text-3xl text-zinc-400 dark:text-zinc-500 mb-2"></i>
                <div class="text-sm text-zinc-600 dark:text-zinc-400">Clique para selecionar, arraste ou <strong class="text-zinc-900 dark:text-white font-medium">cole (Ctrl+V)</strong></div>
                <div class="text-xs text-zinc-500 dark:text-zinc-500 mt-1">PNG, JPG, WEBP</div>
            </div>
        </div>

        <input type="file" ref="fileInput" @change="handleFileSelect" accept="image/*" class="sr-only">
        
        <!-- Crop Modal -->
        <div v-show="showModal" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-[100] w-full p-4 overflow-x-hidden overflow-y-auto bg-zinc-900/50 dark:bg-zinc-900/80 backdrop-blur-sm shadow-sm flex items-center justify-center h-[100vh]">
            <div class="relative w-full max-w-2xl max-h-full" @click.stop>
                <div class="relative bg-white dark:bg-zinc-900 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-800 flex flex-col max-h-[90vh]">
                    <!-- Header -->
                    <div class="flex items-start justify-between p-5 border-b border-zinc-200 dark:border-zinc-800 rounded-t-xl bg-zinc-50/50 dark:bg-zinc-800/50">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-crop-alt text-indigo-500"></i> Recortar Imagem
                        </h3>
                        <button type="button" @click="closeCropModal" class="text-zinc-400 bg-transparent hover:bg-zinc-200 hover:text-zinc-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-zinc-700 dark:hover:text-white transition-colors">
                            <i class="fas fa-times"></i>
                            <span class="sr-only">Fechar</span>
                        </button>
                    </div>
                    <!-- Body -->
                    <div class="p-4 sm:p-5 flex-1 overflow-hidden flex items-center justify-center bg-zinc-100 dark:bg-zinc-950 py-6 min-h-[300px]">
                        <div class="w-full max-h-[50vh] flex items-center justify-center">
                            <img ref="cropImageRef" class="max-w-full max-h-[50vh] block">
                        </div>
                    </div>
                    <!-- Footer -->
                    <div class="flex flex-col sm:flex-row items-center justify-between p-5 border-t border-zinc-200 dark:border-zinc-800 rounded-b-xl gap-4 bg-white dark:bg-zinc-900">
                        <div class="flex gap-2">
                            <button type="button" @click="rotate(-90)" class="inline-flex items-center justify-center w-10 h-10 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900" title="Girar esquerda">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button type="button" @click="rotate(90)" class="inline-flex items-center justify-center w-10 h-10 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900" title="Girar direita">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button type="button" @click="flipH" class="inline-flex items-center justify-center w-10 h-10 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900" title="Espelhar Horizontalmente">
                                <i class="fas fa-arrows-alt-h"></i>
                            </button>
                        </div>
                        <div class="flex gap-3 w-full sm:w-auto">
                            <button type="button" @click="skipCrop" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-sm">
                                <i class="fas fa-forward"></i> Usar Original
                            </button>
                            <button type="button" @click="applyCrop" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-zinc-900">
                                <i class="fas fa-check"></i> Recortar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
