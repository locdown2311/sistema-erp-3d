@extends('layouts.app')

@section('page-title', isset($offer) ? 'Editar Oferta' : 'Nova Oferta')

@section('content')
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden max-w-3xl mb-6">
    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-tags text-orange-500"></i>
            {{ isset($offer) ? 'Editar Oferta' : 'Nova Oferta' }}
        </h3>
    </div>

    <form method="POST" action="{{ isset($offer) ? route('offers.update', $offer) : route('offers.store') }}" enctype="multipart/form-data" class="p-6">
        @csrf
        @if(isset($offer)) @method('PUT') @endif

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome do Produto <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $offer->name ?? '') }}" required placeholder="Ex: Filamento PLA 1kg Premium"
                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
            @error('name') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Descrição</label>
            <textarea name="description" rows="3" placeholder="Descrição breve da oferta..."
                      class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none resize-y placeholder-zinc-400 dark:placeholder-zinc-600">{{ old('description', $offer->description ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço Atual (R$) <span class="text-red-500">*</span></label>
                <input type="number" name="price" step="0.01" value="{{ old('price', $offer->price ?? '') }}" required placeholder="89.90"
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço Original (R$)</label>
                <input type="number" name="original_price" step="0.01" value="{{ old('original_price', $offer->original_price ?? '') }}" placeholder="129.90"
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none mb-1 placeholder-zinc-400 dark:placeholder-zinc-600">
                <span class="text-xs text-zinc-500 dark:text-zinc-400">Deixe vazio se não houver desconto</span>
            </div>
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Link de Afiliado (Shopee) <span class="text-red-500">*</span></label>
            <div class="flex flex-col sm:flex-row gap-3">
                <input type="url" name="affiliate_url" id="affiliateUrl" value="{{ old('affiliate_url', $offer->affiliate_url ?? '') }}" required placeholder="https://shope.ee/seu-link-afiliado"
                       class="flex-1 px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
                <button type="button" id="fetchMetaBtn" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm whitespace-nowrap" onclick="fetchMeta()">
                    <i class="fas fa-magic"></i> Buscar Info
                </button>
            </div>
            <div id="fetchStatus" class="mt-2 text-xs"></div>
            @error('affiliate_url') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Categoria</label>
                <select name="category" class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                    <option value="Impressão 3D" {{ old('category', $offer->category ?? '') == 'Impressão 3D' ? 'selected' : '' }}>Impressão 3D</option>
                    <option value="Filamentos" {{ old('category', $offer->category ?? '') == 'Filamentos' ? 'selected' : '' }}>Filamentos</option>
                    <option value="Peças e Upgrades" {{ old('category', $offer->category ?? '') == 'Peças e Upgrades' ? 'selected' : '' }}>Peças e Upgrades</option>
                    <option value="Ferramentas" {{ old('category', $offer->category ?? '') == 'Ferramentas' ? 'selected' : '' }}>Ferramentas</option>
                    <option value="Resina" {{ old('category', $offer->category ?? '') == 'Resina' ? 'selected' : '' }}>Resina</option>
                    <option value="Eletrônica" {{ old('category', $offer->category ?? '') == 'Eletrônica' ? 'selected' : '' }}>Eletrônica</option>
                </select>
            </div>
            <div class="flex items-center pt-2 md:pt-7">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input type="checkbox" name="active" value="1" {{ old('active', $offer->active ?? true) ? 'checked' : '' }} 
                               class="peer sr-only">
                        <div class="w-11 h-6 bg-zinc-200 dark:bg-zinc-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
                    </div>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">Oferta ativa</span>
                </label>
            </div>
        </div>

        {{-- Image Upload --}}
        <div class="mb-8">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Imagem do Produto</label>
            <div id="imageUploadContainer">
                <div id="imageUploadArea" class="border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl p-6 text-center cursor-pointer transition-colors bg-zinc-50 dark:bg-zinc-950 hover:bg-zinc-100 dark:hover:bg-zinc-900 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 min-h-[160px] flex flex-col items-center justify-center">
                    @if(isset($offer) && $offer->image_path)
                        <img src="{{ asset('storage/' . $offer->image_path) }}" id="imagePreview" class="max-w-full max-h-48 rounded-lg object-contain shadow-sm border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                    @else
                        <i class="fas fa-cloud-upload-alt text-3xl text-zinc-400 dark:text-zinc-500 mb-2"></i>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Clique ou arraste uma imagem<br><span class="text-xs mt-1 block">ou <strong class="text-zinc-700 dark:text-zinc-300">cole (Ctrl+V)</strong></span></p>
                    @endif
                </div>
                <input type="file" name="image" id="imageInput" accept="image/*" class="sr-only">
                <input type="hidden" name="cropped_image" id="croppedImage">
                <input type="hidden" name="og_image_url" id="og_image_url">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900">
                <i class="fas fa-save"></i> {{ isset($offer) ? 'Atualizar' : 'Criar Oferta' }}
            </button>
            <a href="{{ route('offers.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-sm font-medium rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-sm">
                Cancelar
            </a>
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
area.addEventListener('dragover', e => { 
    e.preventDefault(); 
    area.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10');
    area.classList.remove('border-zinc-300', 'dark:border-zinc-700', 'bg-zinc-50', 'dark:bg-zinc-950');
});
area.addEventListener('dragleave', () => {
    area.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10');
    area.classList.add('border-zinc-300', 'dark:border-zinc-700', 'bg-zinc-50', 'dark:bg-zinc-950');
});
area.addEventListener('drop', e => {
    e.preventDefault();
    area.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-500/10');
    area.classList.add('border-zinc-300', 'dark:border-zinc-700', 'bg-zinc-50', 'dark:bg-zinc-950');
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
        area.innerHTML = `<img src="${e.target.result}" class="max-w-full max-h-48 rounded-lg object-contain shadow-sm border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">`;
        croppedInput.value = e.target.result;
        // clear og_image_url so we use local file upload priority instead
        document.getElementById('og_image_url').value = '';
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
            filled.push('descrição');
        }
        if (data.image) {
            area.innerHTML = `<img src="${data.image}" class="max-w-full max-h-48 rounded-lg object-contain shadow-sm border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">`;
            // Store the remote URL to be downloaded on the backend
            document.getElementById('og_image_url').value = data.image;
            // Clear croppedImage so it won't conflict
            document.getElementById('croppedImage').value = '';
            filled.push('imagem');
        }

        status.innerHTML = filled.length
            ? `<span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5"><i class="fas fa-check"></i> Preenchido: ${filled.join(', ')}</span>`
            : `<span class="text-amber-500 dark:text-amber-400 flex items-center gap-1.5"><i class="fas fa-info-circle"></i> Nenhuma informação encontrada</span>`;
    } catch (e) {
        status.innerHTML = `<span class="text-red-500 flex items-center gap-1.5"><i class="fas fa-exclamation-triangle"></i> Erro ao buscar info</span>`;
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-magic"></i> Buscar Info';
}
</script>
@endsection
