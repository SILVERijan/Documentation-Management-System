<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="/favicon.png">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        <script>
            let initTheme = "{{ auth()->check() ? auth()->user()->theme_preference : 'system' }}";
            let useDark = false;

            if (initTheme === 'dark') {
                useDark = true;
            } else if (initTheme === 'light') {
                useDark = false;
            } else {
                useDark = localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            }
            
            if (useDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.style.backgroundColor = '#0a0a0f';
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.style.backgroundColor = '#f8fafc';
            }
        </script>
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-[#0a0a0f] text-slate-900 dark:text-[#ededed] transition-colors duration-300">
        @inertia
    </body>
</html>
