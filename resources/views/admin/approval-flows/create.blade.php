<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Nuevo Flujo de Aprobación
        </h2>
    </x-slot>

    <div style="padding: 32px 0;">
        <div style="max-width: 720px; margin: 0 auto; padding: 0 24px;">
            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: #1e3a5f; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Datos del Flujo</span>
                </div>

                <div style="padding: 24px;">

                    @if($errors->any())
                        <div style="margin-bottom: 16px; padding: 16px; background-color: #fee2e2; color: #991b1b; border-radius: 6px;">
                            <ul style="margin: 0; padding-left: 20px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($categories->isEmpty())
                        <p style="font-size: 14px; color: #64748b; margin: 0 0 16px 0;">
                            Todas las categorías ya tienen un flujo de aprobación asignado.
                        </p>
                    @endif

                    <x-approval-flow-form
                        :action="route('admin.approval-flows.store')"
                        method="POST"
                        :categories="$categories"
                        :users="$users"
                        :levels="old('levels', [])"
                        submit-label="Crear Flujo"
                    />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
