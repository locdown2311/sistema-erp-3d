<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Loja — ERP Impressão 3D</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: var(--space-lg);
        }
        .auth-card {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-2xl);
            width: 100%;
            max-width: 520px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
            max-height: 90vh;
            overflow-y: auto;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: var(--space-xl);
        }
        .auth-logo i {
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .auth-logo h1 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-top: var(--space-sm);
            color: var(--text-primary);
        }
        .auth-logo p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: var(--space-xs);
        }
        .auth-footer {
            text-align: center;
            margin-top: var(--space-lg);
            padding-top: var(--space-md);
            border-top: 1px solid var(--border);
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .auth-footer a {
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 500;
        }
        .auth-footer a:hover { text-decoration: underline; }
        .section-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--warning);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: var(--space-lg) 0 var(--space-md);
            padding-top: var(--space-md);
            border-top: 1px solid var(--border);
        }
        .logo-upload {
            width: 80px;
            height: 80px;
            border-radius: var(--radius-lg);
            border: 2px dashed var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.2s;
            margin: 0 auto var(--space-sm);
        }
        .logo-upload:hover { border-color: var(--primary-light); }
        .logo-upload img { width: 100%; height: 100%; object-fit: cover; }
        .logo-upload i { font-size: 1.5rem; color: var(--text-muted); }
        .slug-preview {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-cube"></i>
            <h1>Criar Sua Loja</h1>
            <p>Monte seu negócio de impressão 3D</p>
        </div>

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom: var(--space-lg);">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf

            <div class="section-title" style="border-top:none; margin-top:0; padding-top:0;"><i class="fas fa-user"></i> Dados Pessoais</div>

            <div class="form-group">
                <label class="form-label">Seu Nome *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Nome completo">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">E-mail *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="seu@email.com">
                </div>
                <div class="form-group">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp') }}" placeholder="5511999999999">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Senha *</label>
                    <input type="password" name="password" class="form-control" required placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar Senha *</label>
                    <input type="password" name="password_confirmation" class="form-control" required placeholder="Repita a senha">
                </div>
            </div>

            <div class="section-title"><i class="fas fa-store"></i> Sua Loja</div>

            <div style="text-align: center; margin-bottom: var(--space-md);">
                <div class="logo-upload" onclick="document.getElementById('logoInput').click()">
                    <img id="logoPreview" style="display:none;">
                    <i id="logoIcon" class="fas fa-camera"></i>
                </div>
                <div style="font-size:0.75rem; color:var(--text-muted);">Logo da loja (clique para selecionar)</div>
                <input type="file" id="logoInput" name="store_logo" accept="image/*" style="display:none;" onchange="previewLogo(this)">
                <input type="hidden" id="croppedImageData" name="cropped_image">
            </div>

            <div class="form-group">
                <label class="form-label">Nome da Loja *</label>
                <input type="text" name="store_name" id="storeName" class="form-control" value="{{ old('store_name') }}" required placeholder="Ex: Igor 3D Prints">
            </div>

            <div class="form-group">
                <label class="form-label">Slug da Loja *</label>
                <input type="text" name="slug" id="storeSlug" class="form-control" value="{{ old('slug') }}" required placeholder="minha-loja" pattern="[a-z0-9\-]+" style="text-transform: lowercase;">
                <div class="slug-preview" id="slugPreview">Sua loja ficará em: <strong>{{ url('/') }}/loja/<span id="slugText">...</span></strong></div>
            </div>

            <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea name="store_description" class="form-control" style="min-height: 60px;" placeholder="Conte sobre sua loja...">{{ old('store_description') }}</textarea>
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center; padding: 0.7rem;">
                <i class="fas fa-rocket"></i> Criar Loja
            </button>
        </form>

        <div class="auth-footer">
            Já tem conta? <a href="{{ route('login') }}">Fazer login</a>
        </div>
    </div>

    {{-- Crop Modal --}}
    <div class="modal-overlay" id="cropModal">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-crop-alt" style="margin-right:8px; color:var(--primary-light);"></i>Recortar Logo</h3>
                <button class="modal-close" onclick="cancelCrop()"><i class="fas fa-times"></i></button>
            </div>
            <div style="max-height: 350px; overflow: hidden;">
                <img id="cropImage" style="max-width: 100%; display: block;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="cancelCrop()">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="applyCrop()"><i class="fas fa-check"></i> Aplicar</button>
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
                document.getElementById('cropModal').classList.add('active');

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
            document.getElementById('logoPreview').style.display = 'block';
            document.getElementById('logoIcon').style.display = 'none';

            const reader = new FileReader();
            reader.onload = (e) => { document.getElementById('croppedImageData').value = e.target.result; };
            reader.readAsDataURL(blob);

            document.getElementById('logoInput').value = '';
            closeCropModal();
        }, 'image/jpeg', 0.9);
    }

    function cancelCrop() { closeCropModal(); }
    function closeCropModal() {
        document.getElementById('cropModal').classList.remove('active');
        if (cropper) { cropper.destroy(); cropper = null; }
    }
    </script>
</body>
</html>
