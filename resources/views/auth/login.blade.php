<!DOCTYPE html>
<html lang="pt-BR" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ \App\Models\Setting::get('company_name', 'ERP Impressão 3D') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm p-6 sm:p-8">
        <div class="text-center mb-8">
            <i class="fas fa-cube text-4xl text-zinc-900 dark:text-white mb-4"></i>
            <h1 class="text-xl font-bold text-zinc-900 dark:text-white">Central 3D</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Acesse sua loja virtual de impressão 3D</p>
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

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="seu@email.com" 
                    class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Senha</label>
                <input type="password" name="password" required placeholder="••••••••" 
                    class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-300 dark:border-zinc-800 rounded-lg text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:border-transparent transition-shadow outline-none">
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-zinc-900 dark:text-white bg-zinc-100 dark:bg-zinc-900 border-zinc-300 dark:border-zinc-700 rounded focus:ring-zinc-900 dark:focus:ring-zinc-100 focus:ring-2">
                <label for="remember" class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                    Lembrar de mim
                </label>
            </div>

            <button type="submit" class="w-full flex justify-center py-2.5 px-4 rounded-lg font-medium text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 transition-colors text-sm items-center gap-2">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-800 text-center text-sm">
            <span class="text-zinc-500 dark:text-zinc-400">Não tem conta?</span> 
            <a href="{{ route('register') }}" class="font-medium text-zinc-900 dark:text-white hover:underline transition-all">Criar loja grátis</a>
        </div>
    </div>
</body>
</html>
