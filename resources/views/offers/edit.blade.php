@extends('layouts.app')

@section('page-title', 'Editar Oferta')

@section('content')
<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-tags" style="margin-right: 8px; color: #ff6b35;"></i>
            Editar Oferta
        </h3>
    </div>

    <form method="POST" action="{{ route('offers.update', $offer) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">Nome do Produto *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $offer->name) }}" required>
            @error('name') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Descrição</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $offer->description) }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Preço Atual (R$) *</label>
                <input type="number" name="price" class="form-control" step="0.01" value="{{ old('price', $offer->price) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Preço Original (R$)</label>
                <input type="number" name="original_price" class="form-control" step="0.01" value="{{ old('original_price', $offer->original_price) }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Link de Afiliado *</label>
            <input type="url" name="affiliate_url" class="form-control" value="{{ old('affiliate_url', $offer->affiliate_url) }}" required>
            @error('affiliate_url') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Categoria</label>
                <select name="category" class="form-control">
                    @foreach(['Impressão 3D', 'Filamentos', 'Peças e Upgrades', 'Ferramentas', 'Resina', 'Eletrônica'] as $cat)
                        <option value="{{ $cat }}" {{ old('category', $offer->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="display:flex; align-items:center; padding-top:1.5rem;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="active" value="1" {{ old('active', $offer->active) ? 'checked' : '' }} style="width:18px; height:18px;">
                    Oferta ativa
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Imagem</label>
            <div class="image-upload-area" id="imageUploadArea" style="cursor:pointer; padding:var(--space-lg); text-align:center; border:2px dashed var(--border); border-radius:var(--radius-md);">
                @if($offer->image_path)
                    <img src="{{ asset('storage/' . $offer->image_path) }}" style="max-width:100%; max-height:200px; object-fit:contain;">
                @else
                    <i class="fas fa-cloud-upload-alt" style="font-size:2rem; color:var(--text-muted);"></i>
                    <p style="color:var(--text-muted); font-size:0.85rem;">Clique ou arraste uma imagem</p>
                @endif
            </div>
            <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;">
            <input type="hidden" name="cropped_image" id="croppedImage">
        </div>

        <div style="display:flex; gap:var(--space-sm); margin-top:var(--space-lg);">
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Atualizar</button>
            <a href="{{ route('offers.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
const area = document.getElementById('imageUploadArea');
const input = document.getElementById('imageInput');
const croppedInput = document.getElementById('croppedImage');

area.addEventListener('click', () => input.click());
input.addEventListener('change', e => {
    if (e.target.files[0]) handleFile(e.target.files[0]);
});

// Ctrl+V paste support
document.addEventListener('paste', e => {
    const items = e.clipboardData?.items;
    if (!items) return;
    for (const item of items) {
        if (item.type.startsWith('image/')) {
            e.preventDefault();
            handleFile(item.getAsFile());
            break;
        }
    }
});

function handleFile(file) {
    const reader = new FileReader();
    reader.onload = ev => {
        area.innerHTML = `<img src="${ev.target.result}" style="max-width:100%; max-height:200px; object-fit:contain;">`;
        croppedInput.value = ev.target.result;
    };
    reader.readAsDataURL(file);
}
</script>
@endsection
