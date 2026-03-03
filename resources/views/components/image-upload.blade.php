{{-- Image Upload Zone with Paste (Ctrl+V) and Crop --}}
@props(['name' => 'image', 'currentImage' => null, 'imageUrl' => null])

<div class="mb-5 relative">
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Imagem</label>

    {{-- Drop/Paste Zone --}}
    <div id="imageDropZone" class="border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl p-6 text-center cursor-pointer transition-colors bg-zinc-50 dark:bg-zinc-950 hover:bg-zinc-100 dark:hover:bg-zinc-900 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 relative"
         onclick="document.getElementById('imageFileInput').click()">

        <div id="imagePreviewArea" class="{{ $currentImage || $imageUrl ? 'block' : 'hidden' }}">
            <img id="imagePreview" src="{{ $imageUrl ?: ($currentImage ? (str_starts_with($currentImage, 'http') ? $currentImage : asset('storage/' . $currentImage)) : '') }}"
                 class="max-w-full max-h-48 mx-auto rounded-lg mb-2 object-contain bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Clique ou cole (Ctrl+V) para trocar</div>
        </div>

        <div id="imagePlaceholder" class="{{ $currentImage || $imageUrl ? 'hidden' : 'block' }}">
            <i class="fas fa-cloud-upload-alt text-3xl text-zinc-400 dark:text-zinc-500 mb-2"></i>
            <div class="text-sm text-zinc-600 dark:text-zinc-400">Clique para selecionar, arraste ou <strong class="text-zinc-900 dark:text-white font-medium">cole (Ctrl+V)</strong></div>
            <div class="text-xs text-zinc-500 dark:text-zinc-500 mt-1">PNG, JPG, WEBP</div>
        </div>
    </div>

    <input type="file" id="imageFileInput" name="{{ $name }}" accept="image/*" class="sr-only" onchange="handleImageSelected(this)">
    <input type="hidden" id="croppedImageData" name="cropped_image">
</div>

{{-- Crop Modal --}}
<div id="cropModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-[100] w-full p-4 overflow-x-hidden overflow-y-auto bg-zinc-900/50 dark:bg-zinc-900/80 backdrop-blur-sm shadow-sm md:inset-0 h-[calc(100%-1rem)] max-h-full flex items-center justify-center">
    <div class="relative w-full max-w-2xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white dark:bg-zinc-900 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-800 flex flex-col max-h-[90vh]">
            <!-- Modal header -->
            <div class="flex items-start justify-between p-5 border-b border-zinc-200 dark:border-zinc-800 rounded-t-xl bg-zinc-50/50 dark:bg-zinc-800/50">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-crop-alt text-indigo-500"></i> Recortar Imagem
                </h3>
                <button type="button" onclick="cancelCrop()" class="text-zinc-400 bg-transparent hover:bg-zinc-200 hover:text-zinc-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-zinc-700 dark:hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                    <span class="sr-only">Fechar</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 sm:p-5 flex-1 overflow-hidden flex items-center justify-center bg-zinc-100 dark:bg-zinc-950 py-6 min-h-[300px]">
                <div class="w-full max-h-[50vh] flex items-center justify-center">
                    <img id="cropImage" class="max-w-full max-h-[50vh] block">
                </div>
            </div>
            <!-- Modal footer -->
            <div class="flex flex-col sm:flex-row items-center justify-between p-5 border-t border-zinc-200 dark:border-zinc-800 rounded-b-xl gap-4 bg-white dark:bg-zinc-900">
                <div class="flex gap-2">
                    <button type="button" class="inline-flex items-center justify-center w-10 h-10 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900" onclick="cropperRotate(-90)" title="Girar esquerda">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" class="inline-flex items-center justify-center w-10 h-10 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900" onclick="cropperRotate(90)" title="Girar direita">
                        <i class="fas fa-redo"></i>
                    </button>
                    <button type="button" class="inline-flex items-center justify-center w-10 h-10 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900" onclick="cropperFlipH()" title="Espelhar Horizontalmente">
                        <i class="fas fa-arrows-alt-h"></i>
                    </button>
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <button type="button" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-sm" onclick="skipCrop()">
                        <i class="fas fa-forward"></i> Usar Original
                    </button>
                    <button type="button" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-zinc-900" onclick="applyCrop()">
                        <i class="fas fa-check"></i> Recortar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('image-crop-scripts')
<script>
let cropper = null;
let originalFile = null;

// Paste handler
document.addEventListener('paste', function(e) {
    const items = e.clipboardData?.items;
    if (!items) return;

    for (const item of items) {
        if (item.type.startsWith('image/')) {
            e.preventDefault();
            const blob = item.getAsFile();
            openCropModal(blob);
            break;
        }
    }
});

// Drag & drop
const dropZone = document.getElementById('imageDropZone');
if (dropZone) {
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10');
        dropZone.classList.remove('border-zinc-300', 'dark:border-zinc-700', 'bg-zinc-50', 'dark:bg-zinc-950');
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10');
        dropZone.classList.add('border-zinc-300', 'dark:border-zinc-700', 'bg-zinc-50', 'dark:bg-zinc-950');
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10');
        dropZone.classList.add('border-zinc-300', 'dark:border-zinc-700', 'bg-zinc-50', 'dark:bg-zinc-950');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            openCropModal(file);
        }
    });
}

function handleImageSelected(input) {
    if (input.files && input.files[0]) {
        openCropModal(input.files[0]);
    }
}

function openCropModal(file) {
    originalFile = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        const img = document.getElementById('cropImage');
        img.src = e.target.result;

        const modal = document.getElementById('cropModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Destroy previous cropper
        if (cropper) { cropper.destroy(); cropper = null; }

        // Init after image loads
        img.onload = () => {
            cropper = new Cropper(img, {
                viewMode: 1,
                aspectRatio: NaN, // Free crop
                autoCropArea: 0.9,
                responsive: true,
                background: false,
                guides: true,
            });
        };
    };
    reader.readAsDataURL(file);
}

function applyCrop() {
    if (!cropper) return;

    const canvas = cropper.getCroppedCanvas({
        maxWidth: 1200,
        maxHeight: 1200,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob((blob) => {
        setImagePreview(blob);
        closeCropModal();
    }, 'image/jpeg', 0.9);
}

function skipCrop() {
    if (originalFile) {
        setImagePreview(originalFile);
    }
    closeCropModal();
}

function setImagePreview(blob) {
    // Set preview
    const url = URL.createObjectURL(blob);
    document.getElementById('imagePreview').src = url;
    document.getElementById('imagePreviewArea').classList.remove('hidden');
    document.getElementById('imagePreviewArea').classList.add('block');
    document.getElementById('imagePlaceholder').classList.add('hidden');
    document.getElementById('imagePlaceholder').classList.remove('block');

    // Convert to base64 and store in hidden input
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('croppedImageData').value = e.target.result;
    };
    reader.readAsDataURL(blob);

    // Clear the file input so it doesn't conflict
    document.getElementById('imageFileInput').value = '';
}

function cancelCrop() {
    closeCropModal();
}

function closeCropModal() {
    const modal = document.getElementById('cropModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    
    // Restore body scroll
    document.body.style.overflow = '';
    
    if (cropper) { cropper.destroy(); cropper = null; }
}

function cropperRotate(deg) { if (cropper) cropper.rotate(deg); }
function cropperFlipH() {
    if (cropper) {
        const data = cropper.getImageData();
        cropper.scaleX(data.scaleX === -1 ? 1 : -1);
    }
}
</script>
@endpush
@endonce
