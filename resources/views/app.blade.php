<!DOCTYPE html>
<html lang="pt-BR" class="antialiased">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title inertia>{{ \App\Models\Setting::get('company_name', 'Central 3D') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
    <style>
        #app {
            display: flex;
            width: 100%;
            flex: 1 1 0%;
        }
    </style>
</head>
<body class="bg-white text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 min-h-screen flex text-sm sm:text-base">
    @inertia
</body>
</html>
