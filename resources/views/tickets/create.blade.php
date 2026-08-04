<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Nuevo Ticket
        </h2>
    </x-slot>

    <style>
        .form-grid-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        @media (max-width: 640px) {
            .form-grid-2col { grid-template-columns: 1fr; }
        }
    </style>

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

                    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
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

                        <div class="form-grid-2col">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Categoría</label>
                                <select name="category_id" id="category_id"
                                        style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                    <option value="">Selecciona una categoría</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $selectedCategoryId) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Subcategoría</label>
                                <select name="subcategory_id" id="subcategory_id"
                                        style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                    <option value="">Selecciona primero una categoría</option>
                                </select>
                            </div>
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

                        <div style="margin-bottom: 24px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Archivos adjuntos (opcional)</label>
                            <input type="file" name="attachments[]" multiple
                                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                            <p style="font-size: 12px; color: #94a3b8; margin: 8px 0 0 0;">Máx. 10MB por archivo. Imágenes, PDF, Office, ZIP o TXT.</p>
                        </div>

                        <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 12px;">
                            <a href="{{ route('tickets.index') }}"
                               style="padding: 12px 20px; background-color: #e5e7eb; color: #374151; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                Cancelar
                            </a>
                            <button type="submit"
                                    style="background-color: #1e3a5f; color: #ffffff; padding: 12px 20px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                Crear Ticket
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const subcategoriesByCategory = @json(
                $categories->mapWithKeys(fn ($c) => [
                    $c->id => $c->subcategories->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),
                ])
            );
            const preselectedSubcategoryId = @json(old('subcategory_id', $selectedSubcategoryId));

            const categorySelect = document.getElementById('category_id');
            const subcategorySelect = document.getElementById('subcategory_id');

            function populateSubcategories(categoryId, selectedId) {
                const subs = subcategoriesByCategory[categoryId] || [];
                subcategorySelect.innerHTML = '';

                if (!categoryId || subs.length === 0) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = categoryId ? 'Sin subcategorías para esta categoría' : 'Selecciona primero una categoría';
                    subcategorySelect.appendChild(opt);
                    subcategorySelect.disabled = true;
                    return;
                }

                subcategorySelect.disabled = false;
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Selecciona una subcategoría (opcional)';
                subcategorySelect.appendChild(placeholder);

                subs.forEach((s) => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    if (selectedId && String(selectedId) === String(s.id)) opt.selected = true;
                    subcategorySelect.appendChild(opt);
                });
            }

            categorySelect.addEventListener('change', () => populateSubcategories(categorySelect.value, null));
            populateSubcategories(categorySelect.value, preselectedSubcategoryId);
        })();
    </script>
</x-app-layout>
