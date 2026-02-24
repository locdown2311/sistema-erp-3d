<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — ERP Impressão 3D</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
            max-width: 420px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
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
        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-cube"></i>
            <h1>ERP Impressão 3D</h1>
            <p>Acesse sua loja</p>
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

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="seu@email.com">
            </div>

            <div class="form-group">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-group">
                <label class="form-label">Verificação de Segurança</label>
                <div style="margin-bottom: var(--space-xs); display: flex; align-items: center; gap: var(--space-sm);">
                    <div class="captcha-img" style="border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border); background: #fff;">
                        {!! captcha_img('math') !!}
                    </div>
                    <button type="button" class="btn btn-outline" style="padding: 0.5rem; border-color: var(--border);" onclick="document.querySelector('.captcha-img img').src = '/captcha/math?' + Math.random()" title="Recarregar Captcha">
                        <i class="fas fa-sync-alt" style="color: var(--text-muted);"></i>
                    </button>
                </div>
                <input type="text" name="captcha" class="form-control" required placeholder="Resolva a conta acima">
            </div>

            <div class="form-check" style="margin-bottom: var(--space-lg);">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" class="form-label" style="margin: 0;">Lembrar de mim</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.7rem;">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>

        <div class="auth-footer">
            Não tem conta? <a href="{{ route('register') }}">Criar loja grátis</a>
        </div>
    </div>
</body>
</html>
