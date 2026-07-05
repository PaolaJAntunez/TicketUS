<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'TicketUS') }} - Acceso Denegado</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    </head>
    <body style="margin: 0; font-family: 'Figtree', sans-serif; background-color: #1e3a5f; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div style="width: 100%; max-width: 420px; padding: 24px; box-sizing: border-box;">
            <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); padding: 40px 32px; box-sizing: border-box; text-align: center;">

                <p style="margin: 0 0 20px 0; font-size: 13px; font-weight: 600; color: #94a3b8; letter-spacing: 0.5px; text-transform: uppercase;">TicketUS</p>

                <div style="width: 72px; height: 72px; border-radius: 50%; background-color: #eff6ff; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px auto;">
                    <svg viewBox="0 0 24 24" style="width: 32px; height: 32px; fill: none; stroke: #1e3a5f; stroke-width: 2;">
                        <rect x="5" y="11" width="14" height="9" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="15.5" r="1.5" fill="#1e3a5f" stroke="none"/>
                    </svg>
                </div>

                <h1 style="margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #1e293b;">Acceso Denegado</h1>
                <p style="margin: 0 0 28px 0; font-size: 14px; color: #64748b;">No tienes permisos para acceder a esta página</p>

                <a href="{{ route('dashboard') }}"
                   style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 10px 24px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                    Volver al Dashboard
                </a>
            </div>
        </div>
    </body>
</html>
