<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title inertia>{{ config('app.name', 'HomeMessage') }}</title>

    <!-- Telegram Mini App SDK -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead

    <style>
        /* Prevent overscroll bounce on iOS */
        html, body {
            overscroll-behavior: none;
            -webkit-overflow-scrolling: touch;
        }

        /* Safe area padding for notched devices */
        body {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
            padding-left: env(safe-area-inset-left);
            padding-right: env(safe-area-inset-right);
        }

        /* Hide scrollbar but allow scrolling */
        ::-webkit-scrollbar {
            display: none;
        }

        * {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="antialiased">
    @inertia

    <script>
        // Expand the Mini App on load
        if (window.Telegram?.WebApp) {
            window.Telegram.WebApp.ready();
            window.Telegram.WebApp.expand();
        }
    </script>
</body>
</html>
