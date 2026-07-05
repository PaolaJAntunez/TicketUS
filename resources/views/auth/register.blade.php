<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TicketUS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body style="margin: 0; font-family: 'Figtree', sans-serif; background-color: #1e3a5f; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div style="width: 100%; max-width: 420px; padding: 24px; box-sizing: border-box;">

            <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); padding: 40px 32px; box-sizing: border-box;">
                <div style="text-align: center; margin-bottom: 8px;">
                    <span style="color: #1e3a5f; font-size: 32px; font-weight: 700; letter-spacing: 0.5px;">TicketUS</span>
                </div>
                <p style="text-align: center; margin: 0 0 28px 0; font-size: 14px; color: #64748b;">Sistema de Gestión de Tickets - USAP</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div style="margin-bottom: 16px;">
                        <label for="name" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Nombre</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                               style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                        @error('name')
                            <p style="color: #dc2626; font-size: 13px; margin: 4px 0 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div style="margin-bottom: 16px;">
                        <label for="email" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                               style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                        @error('email')
                            <p style="color: #dc2626; font-size: 13px; margin: 4px 0 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div style="margin-bottom: 16px;">
                        <label for="password" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Contraseña</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                               style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                        @error('password')
                            <p style="color: #dc2626; font-size: 13px; margin: 4px 0 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div style="margin-bottom: 20px;">
                        <label for="password_confirmation" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Confirmar contraseña</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                               style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                        @error('password_confirmation')
                            <p style="color: #dc2626; font-size: 13px; margin: 4px 0 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                        <a href="{{ route('login') }}" style="font-size: 13px; color: #2563eb; text-decoration: none;">
                            ¿Ya tienes cuenta? Inicia sesión
                        </a>

                        <button type="submit"
                                style="background-color: #2563eb; color: #ffffff; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; white-space: nowrap;">
                            Registrarse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
