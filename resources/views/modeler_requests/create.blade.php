@extends('layouts.app')

@section('page-title', 'Procurar um Modelador 3D')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden">
        
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-500 p-8 sm:p-12 text-center relative overflow-hidden text-white">
            <div class="relative z-10">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="fas fa-pencil-ruler drop-shadow-md"></i>
                </div>
                <h1 class="text-3xl sm:text-4xl font-black mb-3 text-white drop-shadow-md tracking-tight">Precisa de algo único?</h1>
                <p class="text-emerald-50 max-w-2xl mx-auto text-sm sm:text-base font-medium">Descreva o que você quer imprimir em 3D e receba propostas dos melhores modeladores da nossa comunidade.</p>
            </div>
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-40 h-40 rounded-full bg-black/10 blur-2xl"></div>
        </div>

        <div class="p-6 sm:p-10">
            <form action="{{ route('modeler-requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Contato -->
                <div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-user-circle text-emerald-500"></i> Seus Dados
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-zinc-50 dark:bg-zinc-800/50 p-6 rounded-xl border border-zinc-100 dark:border-zinc-700/50">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nome Completo <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" required value="{{ old('name', auth()->user()->name ?? '') }}" class="w-full rounded-xl border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors py-2.5">
                            @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-1">E-mail <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" required value="{{ old('email', auth()->user()->email ?? '') }}" class="w-full rounded-xl border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors py-2.5">
                            @error('email')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="whatsapp" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-1">WhatsApp <span class="text-zinc-400 text-xs font-normal">(opcional, mas recomendado)</span></label>
                            <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp') }}" placeholder="(99) 99999-9999" class="w-full md:w-1/2 rounded-xl border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors py-2.5">
                            @error('whatsapp')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Detalhes do Projeto -->
                <div class="pt-2 border-t border-zinc-200 dark:border-zinc-800">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4 mt-6 flex items-center gap-2">
                        <i class="fas fa-layer-group text-emerald-500"></i> Sobre o Projeto
                    </h3>
                    
                    <div class="space-y-6">
                        <div>
                            <label for="description" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Descreva com detalhes o que você deseja <span class="text-red-500">*</span></label>
                            <p class="text-xs text-zinc-500 mb-2">Tente incluir tamanho aproximado, se é uma peça mecânica ou algo decorativo, grau de dificuldade e para qual utilidade.</p>
                            <textarea name="description" id="description" rows="5" required class="w-full rounded-xl border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors py-3">{{ old('description') }}</textarea>
                            @error('description')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="budget_range" class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Faixa de Orçamento Previsto <span class="text-red-500">*</span></label>
                                <select name="budget_range" id="budget_range" required class="w-full rounded-xl border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 shadow-sm py-2.5">
                                    <option value="" disabled selected>Selecione uma faixa...</option>
                                    <option value="Até R$ 50" {{ old('budget_range') == 'Até R$ 50' ? 'selected' : '' }}>Peças pequenas/simples (Até R$ 50)</option>
                                    <option value="R$ 50 - R$ 150" {{ old('budget_range') == 'R$ 50 - R$ 150' ? 'selected' : '' }}>Média complexidade (R$ 50 - R$ 150)</option>
                                    <option value="R$ 150 - R$ 500" {{ old('budget_range') == 'R$ 150 - R$ 500' ? 'selected' : '' }}>Peças grandes/complexas (R$ 150 - R$ 500)</option>
                                    <option value="Acima de R$ 500" {{ old('budget_range') == 'Acima de R$ 500' ? 'selected' : '' }}>Projetos avançados (Acima de R$ 500)</option>
                                    <option value="A combinar" {{ old('budget_range') == 'A combinar' ? 'selected' : '' }}>Não sei / A combinar</option>
                                </select>
                                @error('budget_range')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Imagens de Referência <span class="text-zinc-400 text-xs font-normal">(até 5, max 20MB cada)</span></label>
                                <div class="relative w-full">
                                    <input type="file" name="images[]" id="images" multiple accept="image/*,.pdf,.heic,.heif,.tiff" class="w-full text-sm text-zinc-500 dark:text-zinc-400
                                    file:mr-4 file:py-2.5 file:px-4
                                    file:rounded-l-xl file:border-0
                                    file:text-sm file:font-bold
                                    file:bg-emerald-50 file:text-emerald-700
                                    dark:file:bg-emerald-500/10 dark:file:text-emerald-400
                                    hover:file:bg-emerald-100 dark:hover:file:bg-emerald-500/20
                                    border border-zinc-300 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 cursor-pointer transition-colors
                                    ">
                                </div>
                                <p class="mt-1.5 text-xs text-zinc-500">Ajuda muito os modeladores a entenderem visualmente. Selecione até 5 arquivos.</p>
                                @error('images')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                                @error('images.*')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror

                                <!-- Image Preview Area -->
                                <div id="imagePreviewArea" class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-2 hidden">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-zinc-200 dark:border-zinc-800 flex justify-end">
                    <a href="{{ route('home') }}" class="px-6 py-3 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold rounded-xl mr-4 hover:bg-zinc-50 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-emerald-600/20 hover:shadow-emerald-600/40 hover:-translate-y-0.5">
                        <i class="fas fa-paper-plane"></i> Enviar Solicitação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const imageInput = document.getElementById('images');
    const previewArea = document.getElementById('imagePreviewArea');

    if (imageInput) {
        imageInput.addEventListener('change', function() {
            previewArea.innerHTML = '';
            const files = Array.from(this.files);

            if (files.length === 0) {
                previewArea.classList.add('hidden');
                return;
            }

            if (files.length > 5) {
                alert('Você pode enviar no máximo 5 imagens. Apenas as 5 primeiras serão consideradas.');
            }

            previewArea.classList.remove('hidden');

            files.slice(0, 5).forEach((file, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'relative aspect-square rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800';

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        wrapper.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-full object-cover" alt="Preview ${index + 1}">
                            <div class="absolute bottom-0 inset-x-0 bg-black/50 text-white text-[10px] font-bold text-center py-0.5">${index + 1}</div>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    wrapper.innerHTML = `
                        <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400 gap-1">
                            <i class="fas fa-file text-xl"></i>
                            <span class="text-[10px] font-bold">${file.name.split('.').pop().toUpperCase()}</span>
                        </div>
                        <div class="absolute bottom-0 inset-x-0 bg-black/50 text-white text-[10px] font-bold text-center py-0.5">${index + 1}</div>
                    `;
                }

                previewArea.appendChild(wrapper);
            });
        });
    }
</script>
@endsection
