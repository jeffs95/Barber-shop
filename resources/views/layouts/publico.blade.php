<!DOCTYPE html>
<html lang="es" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reservar cita · {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script>
        // Respeta el tema elegido por el cliente (compartido con la landing).
        (function () {
            const t = localStorage.getItem('theme') || 'system';
            const dark = t === 'dark' || (t === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-amber-50 via-white to-amber-100 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800 text-gray-900 dark:text-gray-100">
    <main class="mx-auto w-full max-w-3xl px-4 py-8 sm:py-12">
        {{ $slot }}
    </main>
</body>
</html>
