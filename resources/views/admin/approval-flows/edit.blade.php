<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Gestionar Flujo: {{ $approvalFlow->name }}
        </h2>
    </x-slot>

    <div style="padding: 32px 0;">
        <div style="max-width: 720px; margin: 0 auto; padding: 0 24px; display: flex; flex-direction: column; gap: 24px;">

            @if(session('success'))
                <div style="padding: 16px; background-color: #dcfce7; color: #166534; border-radius: 6px;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="padding: 16px; background-color: #fee2e2; color: #991b1b; border-radius: 6px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Datos del flujo -->
            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: #1e3a5f; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Datos del Flujo</span>
                </div>
                <div style="padding: 24px;">
                    <div style="margin-bottom: 16px;">
                        <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Categoría</p>
                        <p style="font-weight: 500; color: #1e293b; margin: 0;">{{ $approvalFlow->category->name }}</p>
                    </div>

                    <form action="{{ route('admin.approval-flows.update', $approvalFlow) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Nombre del flujo</label>
                            <input type="text" name="name" value="{{ old('name', $approvalFlow->name) }}"
                                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                        </div>

                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit"
                                    style="background-color: #1e3a5f; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                Guardar Nombre
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Niveles de aprobación -->
            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: #1e3a5f; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Niveles de Aprobación</span>
                </div>
                <div style="padding: 24px;">
                    @php
                        $roleLabels = ['user' => 'Usuario', 'agent' => 'Agente', 'approver' => 'Aprobador', 'admin' => 'Admin'];
                    @endphp

                    @forelse($approvalFlow->levels as $level)
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding: 12px 0;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span style="background-color: #1e3a5f; color: #ffffff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;">
                                    {{ $level->order }}
                                </span>
                                <div>
                                    <p style="margin: 0; font-size: 14px; font-weight: 500; color: #1e293b;">{{ $level->name }}</p>
                                    <p style="margin: 0; font-size: 12px; color: #64748b;">Rol aprobador: {{ $roleLabels[$level->role] ?? ucfirst($level->role) }}</p>
                                </div>
                            </div>
                            <form action="{{ route('admin.approval-levels.destroy', $level) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar este nivel?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        style="background-color: #ffffff; color: #991b1b; border: 1px solid #991b1b; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    @empty
                        <p style="font-size: 14px; color: #64748b; margin: 0 0 16px 0;">Este flujo aún no tiene niveles de aprobación.</p>
                    @endforelse

                    <h3 style="font-size: 14px; font-weight: 600; color: #1e3a5f; margin: 24px 0 12px 0;">Agregar Nivel</h3>
                    <form action="{{ route('admin.approval-flows.levels.store', $approvalFlow) }}" method="POST">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 12px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 13px; color: #4b5563; margin-bottom: 4px;">Orden</label>
                                <input type="number" name="order" min="1" value="{{ $approvalFlow->levels->count() + 1 }}"
                                       style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 13px; color: #4b5563; margin-bottom: 4px;">Rol aprobador</label>
                                <select name="role" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                    <option value="approver">Aprobador</option>
                                    <option value="agent">Agente</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">Usuario</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 13px; color: #4b5563; margin-bottom: 4px;">Nombre del nivel</label>
                                <input type="text" name="name" placeholder="Ej. Jefe de área"
                                       style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                            </div>
                        </div>
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit"
                                    style="background-color: #2563eb; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                Agregar Nivel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div>
                <a href="{{ route('admin.approval-flows.index') }}"
                   style="background-color: #ffffff; color: #1e3a5f; border: 1px solid #1e3a5f; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600;">
                    &larr; Volver a Flujos de Aprobación
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
