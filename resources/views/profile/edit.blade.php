<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Mi Perfil
        </h2>
    </x-slot>

    <div style="padding: 32px 0;">
        <div style="max-width: 800px; margin: 0 auto; padding: 0 24px; display: flex; flex-direction: column; gap: 24px;">

            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: #1e3a5f; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Información Personal</span>
                </div>
                <div style="padding: 24px;">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: #1e3a5f; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Cambiar Contraseña</span>
                </div>
                <div style="padding: 24px;">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
