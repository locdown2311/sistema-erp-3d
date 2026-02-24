{{-- Image Upload Zone with Paste (Ctrl+V) and Crop --}}
@props(['name' => 'image', 'currentImage' => null])

<div class="form-group">
    <label class="form-label">Imagem</label>

    {{-- Drop/Paste Zone --}}
    <div id="imageDropZone" style="border: 2px dashed var(--border); border-radius: var(--radius-md); padding: var(--space-lg); text-align: center; cursor: pointer; transition: all 0.2s; background: var(--bg-input); position: relative;"
         onclick="document.getElementById('imageFileInput').click()">

        <div id="imagePreviewArea" style="{{ $currentImage ? '' : 'display:none;' }}">
            <img id="imagePreview" src="{{ $currentImage ? asset('storage/' . $currentImage) : '' }}"
                 style="max-width: 100%; max-height: 200px; border-radius: var(--radius-md); margin-bottom: 8px;">
            <div style="font-size: 0.75rem; color: var(--text-muted);">Clique ou cole (Ctrl+V) para trocar</div>
        </div>

        <div id="imagePlaceholder" style="{{ $currentImage ? 'display:none;' : '' }}">
            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 8px;"></i>
            <div style="font-size: 0.85rem; color: var(--text-secondary);">Clique para selecionar, arraste ou <strong>cole (Ctrl+V)</strong></div>
            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">PNG, JPG, WEBP</div>
        </div>
    </div>

    <input type="file" id="imageFileInput" name="{{ $name }}" accept="image/*" style="display: none;" onchange="handleImageSelected(this)">
    <input type="hidden" id="croppedImageData" name="cropped_image">
</div>

{{-- Crop Modal --}}
<div class="modal-overlay" id="cropModal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-crop-alt" style="margin-right: 8px; color: var(--primary-light);"></i>Recortar Imagem</h3>
            <button class="modal-close" onclick="cancelCrop()"><i class="fas fa-times"></i></button>
        </div>
        <div style="max-height: 400px; overflow: hidden;">
            <img id="cropImage" style="max-width: 100%; display: block;">
        </div>
        <div class="modal-footer" style="justify-content: space-between;">
            <div style="display: flex; gap: 6px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="cropperRotate(-90)" title="Girar esquerda"><i class="fas fa-undo"></i></button>
                <button type="button" class="btn btn-outline btn-sm" onclick="cropperRotate(90)" title="Girar direita"><i class="fas fa-redo"></i></button>
                <button type="button" class="btn btn-outline btn-sm" onclick="cropperFlipH()" title="Espelhar H"><i class="fas fa-arrows-alt-h"></i></button>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="skipCrop()"><i class="fas fa-forward"></i> Usar Original</button>
                <button type="button" class="btn btn-success" onclick="applyCrop()"><i class="fas fa-check"></i> Recortar</button>
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
        dropZone.style.borderColor = 'var(--primary-light)';
        dropZone.style.background = 'rgba(22, 163, 74, 0.05)';
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = 'var(--border)';
        dropZone.style.background = 'var(--bg-input)';
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = 'var(--border)';
        dropZone.style.background = 'var(--bg-input)';
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

        document.getElementById('cropModal').classList.add('active');

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
    document.getElementById('imagePreviewArea').style.display = 'block';
    document.getElementById('imagePlaceholder').style.display = 'none';

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
    document.getElementById('cropModal').classList.remove('active');
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
