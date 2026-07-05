<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'TicketUS') }} - Error del servidor</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    </head>
    <body style="margin: 0; font-family: 'Figtree', sans-serif; background-color: #1e3a5f; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div style="width: 100%; max-width: 420px; padding: 24px; box-sizing: border-box;">
            <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); padding: 40px 32px; box-sizing: border-box; text-align: center;">

                <p style="margin: 0 0 8px 0; font-size: 13px; font-weight: 600; color: #94a3b8; letter-spacing: 0.5px; text-transform: uppercase;">TicketUS</p>

                <p style="margin: 0 0 8px 0; font-size: 64px; font-weight: 800; color: #1e3a5f; line-height: 1;">500</p>

                <h1 style="margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #1e293b;">Error del servidor</h1>
                <p style="margin: 0 0 28px 0; font-size: 14px; color: #64748b;">Ocurrió un problema inesperado. Intenta de nuevo más tarde.</p>

                <a href="{{ route('dashboard') }}"
                   style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 10px 24px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                    Volver al Dashboard
                </a>
            </div>
        </div>
    </body>
</html>
