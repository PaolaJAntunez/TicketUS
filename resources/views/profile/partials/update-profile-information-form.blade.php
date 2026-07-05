<section>
    <div style="margin-bottom: 20px;">
        <h3 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 600; color: #1e293b;">Información Personal</h3>
        <p style="margin: 0; font-size: 13px; color: #64748b;">Actualiza tu nombre. El correo electrónico no puede modificarse.</p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div style="margin-bottom: 16px;">
            <label for="name" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Nombre</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
            @error('name')
                <p style="color: #dc2626; font-size: 13px; margin: 4px 0 0 0;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Email</label>
            <p style="margin: 0; padding: 8px 0; font-size: 14px; color: #64748b;">{{ $user->email }}</p>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p style="margin: 8px 0 0 0; font-size: 13px; color: #92400e;">
                    Tu correo no está verificado.
                    <button form="send-verification" style="background: none; border: none; color: #2563eb; text-decoration: underline; cursor: pointer; font-size: 13px; padding: 0;">
                        Reenviar correo de verificación.
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p style="margin: 8px 0 0 0; font-size: 13px; color: #166534;">
                        Se ha enviado un nuevo enlace de verificación a tu correo.
                    </p>
                @endif
            @endif
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <button type="submit"
                    style="background-color: #1e3a5f; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                Guardar
            </button>

            @if (session('status') === 'profile-updated')
                <p style="font-size: 13px; color: #16a34a; margin: 0;">Guardado.</p>
            @endif
        </div>
    </form>
</section>
