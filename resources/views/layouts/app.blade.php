<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Utilidad compartida para texto "muted" que vive directo sobre el
             fondo de la página (no dentro de una tarjeta blanca ni de una
             oscura) y por eso necesita su propio color por tema. --}}
        <style>
            .text-muted-adaptive { color: #64748b; }
            html.dark .text-muted-adaptive { color: #94a3b8 !important; }
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body style="font-family: 'Figtree', sans-serif; margin: 0; background-color: #f1f5f9;">
        <div style="min-height: 100vh; background-color: #f1f5f9;">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header style="background-color: #425475; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="max-width: 1280px; margin: 0 auto; padding: 24px;">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            @if(session('error'))
                <div style="max-width: 1280px; margin: 16px auto 0; padding: 0 24px;">
                    <div style="padding: 16px; background-color: #fee2e2; color: #991b1b; border-radius: 6px;">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        </div>
    </body>
</html>