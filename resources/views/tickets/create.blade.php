<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Nuevo Ticket
        </h2>
    </x-slot>

    <div style="padding: 32px 0;">
        <div style="max-width: 960px; margin: 0 auto; padding: 0 24px;">
            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: #1e3a5f; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Datos del Ticket</span>
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

                    <form action="{{ route('tickets.store') }}" method="POST">
                        @csrf

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Título</label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;"
                                   placeholder="Describe brevemente el problema">
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Descripción</label>
                            <textarea name="description" rows="5"
                                      style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;"
                                      placeholder="Detalla el problema lo más posible">{{ old('description') }}</textarea>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Categoría</label>
                            <select name="category_id"
                                    style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                <option value="">Selecciona una categoría</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom: 24px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Prioridad</label>
                            <select name="priority"
                                    style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }} selected>Media</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                            </select>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                            <a href="{{ route('tickets.index') }}"
                               style="padding: 10px 18px; background-color: #e5e7eb; color: #374151; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                Cancelar
                            </a>
                            <button type="submit"
                                    style="background-color: #1e3a5f; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                Crear Ticket
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
