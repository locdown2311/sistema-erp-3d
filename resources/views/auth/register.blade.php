<!DOCTYPE html>
<html lang="pt-BR" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Loja — {{ \App\Models\Setting::get('company_name', 'ERP Impressão 3D') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-lg mb-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
            <i class="fas fa-arrow-left"></i> Voltar ao início
        </a>
    </div>

    <div class="w-full max-w-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm p-6 sm:p-8 max-h-[95vh] overflow-y-auto custom-scrollbar">
        <div class="text-center mb-8">
            <i class="fas fa-cube text-4xl text-zinc-900 dark:text-white mb-4"></i>
            <h1 class="text-xl font-bold text-zinc-900 dark:text-white">Criar Sua Loja</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Monte seu negócio de impressão 3D</p>
        </div>

        @if($errors->any())
            <div class="mb-6 flex gap-3 p-4 bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 rounded-lg border border-red-200 dark:border-red-500/20 shadow-sm text-sm">
                <i class="fas fa-exclamation-circle flex-shrink-0 mt-0.5 text-red-500 dark:text-red-400"></i>
                <div class="flex-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <h3 class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-user"></i> Dados Pessoais
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Seu Nome <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nome completo" 
                            class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">E-mail <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="seu@email.com" 
                                class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">WhatsApp</label>
                            <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="5511999999999" 
                                class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Senha <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required placeholder="Mínimo 6 caracteres" 
                                class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Confirmar Senha <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required placeholder="Repita a senha" 
                                class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <h3 class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-4 border-t border-zinc-200 dark:border-zinc-800 pt-6 flex items-center gap-2">
                    <i class="fas fa-store"></i> Sua Loja
                </h3>

                <div class="space-y-4">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 mx-auto rounded-xl border-2 border-dashed border-zinc-300 dark:border-zinc-700 flex items-center justify-center cursor-pointer overflow-hidden transition-colors hover:border-zinc-900 dark:hover:border-zinc-100 bg-zinc-50 dark:bg-zinc-950" onclick="document.getElementById('logoInput').click()">
                            <img id="logoPreview" class="w-full h-full object-cover hidden">
                            <i id="logoIcon" class="fas fa-camera text-2xl text-zinc-400 dark:text-zinc-500"></i>
                        </div>
                        <div class="text-xs text-zinc-500 mt-2">Logo da loja (opcional)</div>
                        <input type="file" id="logoInput" name="store_logo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                        <input type="hidden" id="croppedImageData" name="cropped_image">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome da Loja <span class="text-red-500">*</span></label>
                        <input type="text" name="store_name" id="storeName" value="{{ old('store_name') }}" required placeholder="Ex: Igor 3D Prints" 
                            class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Slug da Loja <span class="text-red-500">*</span></label>
                        <input type="text" name="slug" id="storeSlug" value="{{ old('slug') }}" required placeholder="minha-loja" pattern="[a-z0-9\-]+" 
                            class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none lowercase">
                        <div class="text-xs text-zinc-500 mt-1.5">Sua loja ficará em: <strong class="text-zinc-700 dark:text-zinc-300">{{ url('/') }}/loja/<span id="slugText">...</span></strong></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Descrição</label>
                        <textarea name="store_description" placeholder="Conte sobre sua loja..." 
                            class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none min-h-[80px] resize-y">{{ old('store_description') }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full flex justify-center py-2.5 px-4 rounded-lg font-medium text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-colors text-sm items-center gap-2 mt-4">
                <i class="fas fa-rocket"></i> Criar Loja
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-800 text-center text-sm">
            <span class="text-zinc-500 dark:text-zinc-400">Já tem conta?</span> 
            <a href="{{ route('login') }}" class="font-medium text-zinc-900 dark:text-white hover:underline transition-all">Fazer login</a>
        </div>
    </div>

    {{-- Crop Modal --}}
    <div id="cropModal" class="fixed inset-0 z-50 hidden bg-zinc-950/80 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-lg w-full overflow-hidden shadow-2xl">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-crop-alt"></i> Recortar Logo
                </h3>
                <button onclick="cancelCrop()" class="text-zinc-500 hover:text-zinc-900 dark:hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6 bg-zinc-100 dark:bg-zinc-950 max-h-[50vh] flex justify-center overflow-hidden">
                <img id="cropImage" class="max-w-full block">
            </div>
            
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-3 bg-zinc-50 dark:bg-zinc-900">
                <button type="button" onclick="cancelCrop()" class="px-4 py-2 border border-zinc-300 dark:border-zinc-700 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                    Cancelar
                </button>
                <button type="button" onclick="applyCrop()" class="px-4 py-2 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 rounded-lg text-sm font-medium hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors flex items-center gap-2">
                    <i class="fas fa-check"></i> Aplicar
                </button>
            </div>
        </div>
    </div>

    <script>
    let cropper = null;

    // Auto-generate slug from store name
    document.getElementById('storeName').addEventListener('input', function() {
        const slug = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
        document.getElementById('storeSlug').value = slug;
        document.getElementById('slugText').textContent = slug || '...';
    });

    document.getElementById('storeSlug').addEventListener('input', function() {
        this.value = this.value.toLowerCase().replace(/[^a-z0-9\-]/g, '');
        document.getElementById('slugText').textContent = this.value || '...';
    });

    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.getElementById('cropImage');
                img.src = e.target.result;
                document.getElementById('cropModal').classList.remove('hidden');

                if (cropper) { cropper.destroy(); cropper = null; }
                img.onload = () => {
                    cropper = new Cropper(img, {
                        viewMode: 1,
                        aspectRatio: 1,
                        autoCropArea: 0.9,
                    });
                };
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function applyCrop() {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({ width: 300, height: 300, imageSmoothingQuality: 'high' });
        canvas.toBlob((blob) => {
            const url = URL.createObjectURL(blob);
            document.getElementById('logoPreview').src = url;
            document.getElementById('logoPreview').classList.remove('hidden');
            document.getElementById('logoIcon').classList.add('hidden');

            const reader = new FileReader();
            reader.onload = (e) => { document.getElementById('croppedImageData').value = e.target.result; };
            reader.readAsDataURL(blob);

            document.getElementById('logoInput').value = '';
            closeCropModal();
        }, 'image/jpeg', 0.9);
    }

    function cancelCrop() { closeCropModal(); }
    function closeCropModal() {
        document.getElementById('cropModal').classList.add('hidden');
        if (cropper) { cropper.destroy(); cropper = null; }
    }
    </script>
</body>
</html>
