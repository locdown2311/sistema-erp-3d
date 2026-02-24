@extends('layouts.app')

@section('page-title', isset($offer) ? 'Editar Oferta' : 'Nova Oferta')

@section('content')
<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-tags" style="margin-right: 8px; color: #ff6b35;"></i>
            {{ isset($offer) ? 'Editar Oferta' : 'Nova Oferta' }}
        </h3>
    </div>

    <form method="POST" action="{{ isset($offer) ? route('offers.update', $offer) : route('offers.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($offer)) @method('PUT') @endif

        <div class="form-group">
            <label class="form-label">Nome do Produto *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $offer->name ?? '') }}" required placeholder="Ex: Filamento PLA 1kg Premium">
            @error('name') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Descrição</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Descrição breve da oferta...">{{ old('description', $offer->description ?? '') }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Preço Atual (R$) *</label>
                <input type="number" name="price" class="form-control" step="0.01" value="{{ old('price', $offer->price ?? '') }}" required placeholder="89.90">
            </div>
            <div class="form-group">
                <label class="form-label">Preço Original (R$)</label>
                <input type="number" name="original_price" class="form-control" step="0.01" value="{{ old('original_price', $offer->original_price ?? '') }}" placeholder="129.90">
                <span style="font-size:0.72rem; color:var(--text-muted);">Deixe vazio se não houver desconto</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Link de Afiliado (Shopee) *</label>
            <div style="display:flex; gap:var(--space-sm);">
                <input type="url" name="affiliate_url" id="affiliateUrl" class="form-control" value="{{ old('affiliate_url', $offer->affiliate_url ?? '') }}" required placeholder="https://shope.ee/seu-link-afiliado" style="flex:1;">
                <button type="button" id="fetchMetaBtn" class="btn btn-outline" style="white-space:nowrap;" onclick="fetchMeta()">
                    <i class="fas fa-magic"></i> Buscar Info
                </button>
            </div>
            <div id="fetchStatus" style="font-size:0.75rem; margin-top:4px;"></div>
            @error('affiliate_url') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Categoria</label>
                <select name="category" class="form-control">
                    <option value="Impressão 3D" {{ old('category', $offer->category ?? '') == 'Impressão 3D' ? 'selected' : '' }}>Impressão 3D</option>
                    <option value="Filamentos" {{ old('category', $offer->category ?? '') == 'Filamentos' ? 'selected' : '' }}>Filamentos</option>
                    <option value="Peças e Upgrades" {{ old('category', $offer->category ?? '') == 'Peças e Upgrades' ? 'selected' : '' }}>Peças e Upgrades</option>
                    <option value="Ferramentas" {{ old('category', $offer->category ?? '') == 'Ferramentas' ? 'selected' : '' }}>Ferramentas</option>
                    <option value="Resina" {{ old('category', $offer->category ?? '') == 'Resina' ? 'selected' : '' }}>Resina</option>
                    <option value="Eletrônica" {{ old('category', $offer->category ?? '') == 'Eletrônica' ? 'selected' : '' }}>Eletrônica</option>
                </select>
            </div>
            <div class="form-group" style="display:flex; align-items:center; padding-top:1.5rem;">
                <label class="form-label" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="active" value="1" {{ old('active', $offer->active ?? true) ? 'checked' : '' }} style="width:18px; height:18px;">
                    Oferta ativa
                </label>
            </div>
        </div>

        {{-- Image Upload --}}
        <div class="form-group">
            <label class="form-label">Imagem do Produto</label>
            <div class="image-upload-container" id="imageUploadContainer">
                <div class="image-upload-area" id="imageUploadArea">
                    @if(isset($offer) && $offer->image_path)
                        <img src="{{ asset('storage/' . $offer->image_path) }}" id="imagePreview" style="max-width:100%; max-height:200px; object-fit:contain;">
                    @else
                        <i class="fas fa-cloud-upload-alt" style="font-size:2rem; color:var(--text-muted); margin-bottom:8px;"></i>
                        <p style="color:var(--text-muted); font-size:0.85rem;">Clique ou arraste uma imagem</p>
                    @endif
                </div>
                <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;">
                <input type="hidden" name="cropped_image" id="croppedImage">
                <input type="hidden" name="og_image_url" id="og_image_url">
            </div>
        </div>

        <div style="display:flex; gap:var(--space-sm); margin-top:var(--space-lg);">
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> {{ isset($offer) ? 'Atualizar' : 'Criar Oferta' }}</button>
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
area.addEventListener('dragover', e => { e.preventDefault(); area.style.borderColor = 'var(--primary)'; });
area.addEventListener('dragleave', () => area.style.borderColor = '');
area.addEventListener('drop', e => {
    e.preventDefault();
    area.style.borderColor = '';
    if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
});
input.addEventListener('change', e => { if (e.target.files[0]) handleFile(e.target.files[0]); });

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
    reader.onload = e => {
        area.innerHTML = `<img src="${e.target.result}" style="max-width:100%; max-height:200px; object-fit:contain;">`;
        croppedInput.value = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Auto-fetch og:image/title/description from URL
async function fetchMeta() {
    const url = document.getElementById('affiliateUrl').value.trim();
    if (!url) return;

    const status = document.getElementById('fetchStatus');
    const btn = document.getElementById('fetchMetaBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
    status.textContent = '';

    try {
        const res = await fetch('{{ route("offers.fetch-meta") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            },
            body: JSON.stringify({ url })
        });

        const data = await res.json();
        let filled = [];

        if (data.title && !document.querySelector('[name=name]').value) {
            document.querySelector('[name=name]').value = data.title;
            filled.push('nome');
        }
        if (data.description && !document.querySelector('[name=description]').value) {
            document.querySelector('[name=description]').value = data.description;
            filled.push('descri\u00e7\u00e3o');
        }
        if (data.image) {
            area.innerHTML = `<img src="${data.image}" style="max-width:100%; max-height:200px; object-fit:contain;">`;
            // Store the remote URL to be downloaded on the backend
            document.getElementById('og_image_url').value = data.image;
            filled.push('imagem');
        }

        status.innerHTML = filled.length
            ? `<span style="color:var(--success-light);"><i class="fas fa-check"></i> Preenchido: ${filled.join(', ')}</span>`
            : `<span style="color:var(--warning-light);"><i class="fas fa-info-circle"></i> Nenhuma informa\u00e7\u00e3o encontrada</span>`;
    } catch (e) {
        status.innerHTML = `<span style="color:var(--danger-light);"><i class="fas fa-exclamation-triangle"></i> Erro ao buscar</span>`;
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-magic"></i> Buscar Info';
}
</script>
@endsection
