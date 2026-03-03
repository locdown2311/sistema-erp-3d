@extends('layouts.app')

@section('page-title', 'Editar Oferta')

@section('content')
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden max-w-3xl mb-6">
    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-tags text-orange-500"></i> Editar Oferta
        </h3>
    </div>

    <form method="POST" action="{{ route('offers.update', $offer) }}" enctype="multipart/form-data" class="p-6">
        @csrf
        @method('PUT')

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome do Produto <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $offer->name) }}" required
                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
            @error('name') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Descrição</label>
            <textarea name="description" rows="3"
                      class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none resize-y placeholder-zinc-400 dark:placeholder-zinc-600">{{ old('description', $offer->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço Atual (R$) <span class="text-red-500">*</span></label>
                <input type="number" name="price" step="0.01" value="{{ old('price', $offer->price) }}" required
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Preço Original (R$)</label>
                <input type="number" name="original_price" step="0.01" value="{{ old('original_price', $offer->original_price) }}"
                       class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
            </div>
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Link de Afiliado <span class="text-red-500">*</span></label>
            <input type="url" name="affiliate_url" value="{{ old('affiliate_url', $offer->affiliate_url) }}" required
                   class="w-full px-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none placeholder-zinc-400 dark:placeholder-zinc-600">
            @error('affiliate_url') <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Categoria</label>
                <select name="category" class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow outline-none appearance-none pr-8 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                    @foreach(['Impressão 3D', 'Filamentos', 'Peças e Upgrades', 'Ferramentas', 'Resina', 'Eletrônica'] as $cat)
                        <option value="{{ $cat }}" {{ old('category', $offer->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center pt-2 md:pt-7">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative flex items-center">
                        <input type="checkbox" name="active" value="1" {{ old('active', $offer->active) ? 'checked' : '' }} 
                               class="peer sr-only">
                        <div class="w-11 h-6 bg-zinc-200 dark:bg-zinc-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
                    </div>
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">Oferta ativa</span>
                </label>
            </div>
        </div>

        {{-- Image Upload --}}
        <div class="mb-8">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Imagem</label>
            <div id="imageUploadArea" class="border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl p-6 text-center cursor-pointer transition-colors bg-zinc-50 dark:bg-zinc-950 hover:bg-zinc-100 dark:hover:bg-zinc-900 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 min-h-[160px] flex flex-col items-center justify-center">
                @if($offer->image_path)
                    <img src="{{ $offer->thumbnail_url }}" class="max-w-full max-h-48 rounded-lg object-contain shadow-sm border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                @else
                    <i class="fas fa-cloud-upload-alt text-3xl text-zinc-400 dark:text-zinc-500 mb-2"></i>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Clique ou arraste uma imagem<br><span class="text-xs mt-1 block">ou <strong class="text-zinc-700 dark:text-zinc-300">cole (Ctrl+V)</strong></span></p>
                @endif
            </div>
            <input type="file" name="image" id="imageInput" accept="image/*" class="sr-only">
            <input type="hidden" name="cropped_image" id="croppedImage">
        </div>

        <div class="flex items-center gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-900">
                <i class="fas fa-save"></i> Atualizar
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
        area.innerHTML = `<img src="${ev.target.result}" class="max-w-full max-h-48 rounded-lg object-contain shadow-sm border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">`;
        croppedInput.value = ev.target.result;
    };
    reader.readAsDataURL(file);
}
</script>
@endsection
