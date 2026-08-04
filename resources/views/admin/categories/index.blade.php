<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Administración de Categorías
        </h2>
    </x-slot>

    {{-- Todo el CRUD (categorías y subcategorías) vive en un solo x-data, con
         fetch a los endpoints JSON de admin.categories.*/admin.subcategories.*
         — sin recargar página. Contraste claro/oscuro con clases + "html.dark
         ...", mismo patrón que tickets/index y tickets/kanban. --}}
    <style>
        .catadmin-card { background-color: #ffffff; border: 1px solid #e2e8f0; }
        html.dark .catadmin-card { background-color: #1e293b !important; border-color: #334155 !important; }

        .catadmin-row-title { color: #1e293b; }
        html.dark .catadmin-row-title { color: #f1f5f9 !important; }

        .catadmin-meta { color: #64748b; }
        html.dark .catadmin-meta { color: #94a3b8 !important; }

        .catadmin-subrow { border-top: 1px solid #e2e8f0; }
        html.dark .catadmin-subrow { border-top-color: #334155 !important; }
        html.dark .catadmin-subrow:hover { background-color: #16213a !important; }

        .catadmin-badge-active { background-color: #dcfce7; color: #166534; }
        html.dark .catadmin-badge-active { background-color: #14532d !important; color: #bbf7d0 !important; }

        .catadmin-badge-inactive { background-color: #f3f4f6; color: #6b7280; }
        html.dark .catadmin-badge-inactive { background-color: #334155 !important; color: #cbd5e1 !important; }

        .catadmin-modal-panel { background-color: #ffffff; }
        html.dark .catadmin-modal-panel { background-color: #1e293b !important; }

        .catadmin-label { color: #374151; }
        html.dark .catadmin-label { color: #cbd5e1 !important; }

        .catadmin-input {
            border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; box-sizing: border-box;
            width: 100%; font-size: 14px; background-color: #ffffff; color: #1e293b;
        }
        html.dark .catadmin-input { background-color: #0f172a !important; color: #f1f5f9 !important; border-color: #475569 !important; }

        .catadmin-error { color: #dc2626; font-size: 12px; margin-top: 4px; }

        .catadmin-toast-success { background-color: #dcfce7; color: #166534; }
        .catadmin-toast-error { background-color: #fee2e2; color: #991b1b; }
    </style>

    <div style="padding: 32px 0;" x-data="{
            categories: {{ Js::from($categories) }},
            expanded: {},

            urls: {
                categoriesStore: '{{ route('admin.categories.store') }}',
                categoryBase: '{{ url('/admin/categories') }}',
                subcategoryBase: '{{ url('/admin/subcategories') }}',
            },

            categoryModalOpen: false,
            categoryMode: 'create',
            categoryForm: { id: null, name: '', description: '', requires_approval: false },
            categoryErrors: {},
            categorySaving: false,

            subcategoryModalOpen: false,
            subcategoryMode: 'create',
            subcategoryForm: { id: null, category_id: null, name: '' },
            subcategoryErrors: {},
            subcategorySaving: false,

            confirmOpen: false,
            confirmTitle: '',
            confirmMessage: '',
            confirmBusy: false,
            confirmAction: null,

            toastMessage: '',
            toastType: 'success',

            csrfToken() {
                return document.querySelector('meta[name=csrf-token]').content;
            },

            async apiRequest(url, method, body) {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: body !== undefined ? JSON.stringify(body) : undefined,
                });
                const data = await res.json().catch(() => ({}));
                return { ok: res.ok, status: res.status, data: data };
            },

            showToast(message, type) {
                this.toastMessage = message;
                this.toastType = type || 'success';
                setTimeout(() => { this.toastMessage = ''; }, 4000);
            },

            toggleExpand(categoryId) {
                this.expanded[categoryId] = ! this.expanded[categoryId];
            },

            findCategoryIndex(id) {
                return this.categories.findIndex(c => c.id === id);
            },

            openNewCategory() {
                this.categoryMode = 'create';
                this.categoryForm = { id: null, name: '', description: '', requires_approval: false };
                this.categoryErrors = {};
                this.categoryModalOpen = true;
            },

            openEditCategory(category) {
                this.categoryMode = 'edit';
                this.categoryForm = {
                    id: category.id,
                    name: category.name,
                    description: category.description || '',
                    requires_approval: category.requires_approval,
                };
                this.categoryErrors = {};
                this.categoryModalOpen = true;
            },

            async submitCategory() {
                this.categorySaving = true;
                this.categoryErrors = {};

                const url = this.categoryMode === 'create' ? this.urls.categoriesStore : (this.urls.categoryBase + '/' + this.categoryForm.id);
                const method = this.categoryMode === 'create' ? 'POST' : 'PUT';

                const result = await this.apiRequest(url, method, {
                    name: this.categoryForm.name,
                    description: this.categoryForm.description,
                    requires_approval: this.categoryForm.requires_approval,
                });

                this.categorySaving = false;

                if (! result.ok) {
                    this.categoryErrors = (result.data.errors) || {};
                    return;
                }

                const saved = result.data.category;

                if (this.categoryMode === 'create') {
                    this.categories.push(saved);
                    this.categories.sort((a, b) => a.name.localeCompare(b.name));
                } else {
                    const idx = this.findCategoryIndex(saved.id);
                    if (idx !== -1) this.categories[idx] = saved;
                }

                this.categoryModalOpen = false;
                this.showToast(this.categoryMode === 'create' ? 'Categoría creada correctamente.' : 'Categoría actualizada correctamente.', 'success');
            },

            askToggleCategory(category) {
                if (category.is_active) {
                    this.confirmTitle = 'Desactivar categoría';
                    this.confirmMessage = 'La categoría dejará de aparecer como opción nueva en tickets, pero los tickets existentes seguirán mostrándola con normalidad. ¿Continuar?';
                } else {
                    this.confirmTitle = 'Activar categoría';
                    this.confirmMessage = 'La categoría volverá a estar disponible como opción nueva en tickets. ¿Continuar?';
                }
                this.confirmAction = async () => {
                    const result = await this.apiRequest(this.urls.categoryBase + '/' + category.id + '/toggle', 'PATCH');
                    if (result.ok) {
                        const idx = this.findCategoryIndex(result.data.category.id);
                        if (idx !== -1) this.categories[idx] = result.data.category;
                        this.showToast('Estado actualizado.', 'success');
                    } else {
                        this.showToast(result.data.message || 'No se pudo actualizar.', 'error');
                    }
                };
                this.confirmOpen = true;
            },

            askDeleteCategory(category) {
                this.confirmTitle = 'Eliminar categoría';
                this.confirmMessage = '¿Eliminar «' + category.name + '»? Esta acción no se puede deshacer.';
                this.confirmAction = async () => {
                    const result = await this.apiRequest(this.urls.categoryBase + '/' + category.id, 'DELETE');
                    if (result.ok) {
                        const idx = this.findCategoryIndex(category.id);
                        if (idx !== -1) this.categories.splice(idx, 1);
                        this.showToast('Categoría eliminada.', 'success');
                    } else {
                        this.showToast(result.data.message || 'No se pudo eliminar.', 'error');
                    }
                };
                this.confirmOpen = true;
            },

            openNewSubcategory(category) {
                this.subcategoryMode = 'create';
                this.subcategoryForm = { id: null, category_id: category.id, name: '' };
                this.subcategoryErrors = {};
                this.subcategoryModalOpen = true;
            },

            openEditSubcategory(subcategory) {
                this.subcategoryMode = 'edit';
                this.subcategoryForm = { id: subcategory.id, category_id: subcategory.category_id, name: subcategory.name };
                this.subcategoryErrors = {};
                this.subcategoryModalOpen = true;
            },

            async submitSubcategory() {
                this.subcategorySaving = true;
                this.subcategoryErrors = {};

                const url = this.subcategoryMode === 'create'
                    ? (this.urls.categoryBase + '/' + this.subcategoryForm.category_id + '/subcategories')
                    : (this.urls.subcategoryBase + '/' + this.subcategoryForm.id);
                const method = this.subcategoryMode === 'create' ? 'POST' : 'PUT';

                const result = await this.apiRequest(url, method, { name: this.subcategoryForm.name });

                this.subcategorySaving = false;

                if (! result.ok) {
                    this.subcategoryErrors = (result.data.errors) || {};
                    return;
                }

                const saved = result.data.subcategory;
                const categoryIdx = this.findCategoryIndex(saved.category_id);

                if (categoryIdx !== -1) {
                    const subs = this.categories[categoryIdx].subcategories;
                    const subIdx = subs.findIndex(s => s.id === saved.id);
                    if (subIdx !== -1) {
                        subs[subIdx] = saved;
                    } else {
                        subs.push(saved);
                        subs.sort((a, b) => a.name.localeCompare(b.name));
                    }
                }

                this.subcategoryModalOpen = false;
                this.showToast(this.subcategoryMode === 'create' ? 'Subcategoría creada correctamente.' : 'Subcategoría actualizada correctamente.', 'success');
            },

            askToggleSubcategory(subcategory) {
                if (subcategory.is_active) {
                    this.confirmTitle = 'Desactivar subcategoría';
                    this.confirmMessage = 'Dejará de aparecer como opción nueva en tickets, pero los tickets existentes seguirán mostrándola con normalidad. ¿Continuar?';
                } else {
                    this.confirmTitle = 'Activar subcategoría';
                    this.confirmMessage = 'Volverá a estar disponible como opción nueva en tickets. ¿Continuar?';
                }
                this.confirmAction = async () => {
                    const result = await this.apiRequest(this.urls.subcategoryBase + '/' + subcategory.id + '/toggle', 'PATCH');
                    if (result.ok) {
                        const categoryIdx = this.findCategoryIndex(result.data.subcategory.category_id);
                        if (categoryIdx !== -1) {
                            const subs = this.categories[categoryIdx].subcategories;
                            const subIdx = subs.findIndex(s => s.id === result.data.subcategory.id);
                            if (subIdx !== -1) subs[subIdx] = result.data.subcategory;
                        }
                        this.showToast('Estado actualizado.', 'success');
                    } else {
                        this.showToast(result.data.message || 'No se pudo actualizar.', 'error');
                    }
                };
                this.confirmOpen = true;
            },

            askDeleteSubcategory(subcategory) {
                this.confirmTitle = 'Eliminar subcategoría';
                this.confirmMessage = '¿Eliminar «' + subcategory.name + '»? Esta acción no se puede deshacer.';
                this.confirmAction = async () => {
                    const result = await this.apiRequest(this.urls.subcategoryBase + '/' + subcategory.id, 'DELETE');
                    if (result.ok) {
                        const categoryIdx = this.findCategoryIndex(subcategory.category_id);
                        if (categoryIdx !== -1) {
                            const subs = this.categories[categoryIdx].subcategories;
                            const subIdx = subs.findIndex(s => s.id === subcategory.id);
                            if (subIdx !== -1) subs.splice(subIdx, 1);
                        }
                        this.showToast('Subcategoría eliminada.', 'success');
                    } else {
                        this.showToast(result.data.message || 'No se pudo eliminar.', 'error');
                    }
                };
                this.confirmOpen = true;
            },

            async runConfirmedAction() {
                if (! this.confirmAction) return;
                this.confirmBusy = true;
                await this.confirmAction();
                this.confirmBusy = false;
                this.confirmOpen = false;
                this.confirmAction = null;
            }
         }">
        <div style="max-width: 1100px; margin: 0 auto; padding: 0 24px;">

            <div x-show="toastMessage" x-cloak
                 :class="toastType === 'success' ? 'catadmin-toast-success' : 'catadmin-toast-error'"
                 style="margin-bottom: 16px; padding: 16px; border-radius: 6px;"
                 x-text="toastMessage">
            </div>

            <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('admin.users.index') }}"
                       style="background-color: #ffffff; color: #1e3a5f; border: 1px solid #1e3a5f; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        Usuarios
                    </a>
                    <a href="{{ route('admin.categories.index') }}"
                       style="background-color: #1e3a5f; color: #ffffff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        Categorías
                    </a>
                    <a href="{{ route('admin.approval-flows.index') }}"
                       style="background-color: #ffffff; color: #1e3a5f; border: 1px solid #1e3a5f; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        Flujos de Aprobación
                    </a>
                </div>

                <button type="button" @click="openNewCategory()"
                        style="background-color: #2563eb; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; font-size: 14px; font-weight: 500; cursor: pointer;">
                    + Nueva Categoría
                </button>
            </div>

            <div class="catadmin-card" style="border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                <template x-if="categories.length === 0">
                    <p class="catadmin-meta" style="padding: 24px; text-align: center; font-size: 14px; margin: 0;">No hay categorías registradas.</p>
                </template>

                <template x-for="(category, index) in categories" :key="category.id">
                    <div :class="index > 0 ? 'catadmin-subrow' : ''">
                        <div style="padding: 16px 20px; display: flex; align-items: center; gap: 12px;">
                            <button type="button" @click="toggleExpand(category.id)"
                                    style="background: none; border: none; cursor: pointer; font-size: 16px; width: 20px; color: inherit;">
                                <span x-text="expanded[category.id] ? '▾' : '▸'"></span>
                            </button>

                            <div style="flex: 1; min-width: 0; cursor: pointer;" @click="toggleExpand(category.id)">
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                    <span class="catadmin-row-title" x-text="category.name" style="font-size: 15px; font-weight: 600;"></span>
                                    <span :class="category.is_active ? 'catadmin-badge-active' : 'catadmin-badge-inactive'"
                                          style="padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700;"
                                          x-text="category.is_active ? 'Activa' : 'Inactiva'">
                                    </span>
                                    <span x-show="category.requires_approval" style="background-color: #fef9c3; color: #854d0e; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700;">
                                        Requiere aprobación
                                    </span>
                                </div>
                                <p class="catadmin-meta" style="margin: 4px 0 0 0; font-size: 12px;">
                                    <span x-text="category.subcategories.length"></span> subcategoría(s) &middot;
                                    <span x-text="category.tickets_count"></span> ticket(s)
                                </p>
                            </div>

                            <div style="display: flex; gap: 6px; flex-shrink: 0;">
                                <button type="button" @click="openEditCategory(category)"
                                        style="background-color: #2563eb; color: #ffffff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer;">
                                    Editar
                                </button>
                                <button type="button" @click="askToggleCategory(category)"
                                        style="background-color: #ffffff; color: #374151; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer;"
                                        x-text="category.is_active ? 'Desactivar' : 'Activar'">
                                </button>
                                <button type="button" @click="askDeleteCategory(category)"
                                        style="background-color: #ffffff; color: #991b1b; border: 1px solid #991b1b; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer;">
                                    Eliminar
                                </button>
                            </div>
                        </div>

                        <div x-show="expanded[category.id]" x-cloak style="padding: 0 20px 16px 52px;">
                            <template x-for="subcategory in category.subcategories" :key="subcategory.id">
                                <div style="display: flex; align-items: center; gap: 10px; padding: 8px 0; flex-wrap: wrap;">
                                    <span class="catadmin-row-title" x-text="subcategory.name" style="font-size: 13px; flex: 1; min-width: 120px;"></span>
                                    <span :class="subcategory.is_active ? 'catadmin-badge-active' : 'catadmin-badge-inactive'"
                                          style="padding: 1px 8px; border-radius: 9999px; font-size: 10px; font-weight: 700;"
                                          x-text="subcategory.is_active ? 'Activa' : 'Inactiva'">
                                    </span>
                                    <span class="catadmin-meta" style="font-size: 11px;" x-text="subcategory.tickets_count + ' ticket(s)'"></span>

                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" @click="openEditSubcategory(subcategory)"
                                                style="background-color: #ffffff; color: #2563eb; border: 1px solid #2563eb; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 500; cursor: pointer;">
                                            Editar
                                        </button>
                                        <button type="button" @click="askToggleSubcategory(subcategory)"
                                                style="background-color: #ffffff; color: #374151; border: 1px solid #cbd5e1; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 500; cursor: pointer;"
                                                x-text="subcategory.is_active ? 'Desactivar' : 'Activar'">
                                        </button>
                                        <button type="button" @click="askDeleteSubcategory(subcategory)"
                                                style="background-color: #ffffff; color: #991b1b; border: 1px solid #991b1b; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 500; cursor: pointer;">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="category.subcategories.length === 0">
                                <p class="catadmin-meta" style="font-size: 12px; margin: 8px 0;">Sin subcategorías.</p>
                            </template>

                            <button type="button" @click="openNewSubcategory(category)"
                                    style="margin-top: 8px; background-color: #ffffff; color: #2563eb; border: 1px dashed #2563eb; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer;">
                                + Nueva Subcategoría
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Modal: Nueva/Editar Categoría --}}
        <div x-show="categoryModalOpen" x-cloak style="position: fixed; inset: 0; z-index: 70;">
            <div style="width: 100%; height: 100%; background-color: rgba(15,23,42,0.6); display: flex; align-items: center; justify-content: center; padding: 16px; box-sizing: border-box;"
                 @click.self="categoryModalOpen = false">
                <div class="catadmin-modal-panel" style="border-radius: 10px; max-width: 480px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.3);"
                     @keydown.escape.window="categoryModalOpen = false">
                    <form @submit.prevent="submitCategory()">
                        <div style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0;">
                            <h2 class="catadmin-row-title" style="margin: 0; font-size: 16px; font-weight: 700;"
                                x-text="categoryMode === 'create' ? 'Nueva Categoría' : 'Editar Categoría'"></h2>
                        </div>

                        <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 14px;">
                            <div>
                                <label class="catadmin-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Nombre</label>
                                <input type="text" class="catadmin-input" x-model="categoryForm.name" placeholder="Ej. Hardware" maxlength="255">
                                <p class="catadmin-error" x-show="categoryErrors.name" x-text="categoryErrors.name ? categoryErrors.name[0] : ''"></p>
                            </div>

                            <div>
                                <label class="catadmin-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Descripción</label>
                                <textarea class="catadmin-input" x-model="categoryForm.description" rows="3" placeholder="Opcional"></textarea>
                                <p class="catadmin-error" x-show="categoryErrors.description" x-text="categoryErrors.description ? categoryErrors.description[0] : ''"></p>
                            </div>

                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px;" class="catadmin-label">
                                <input type="checkbox" x-model="categoryForm.requires_approval">
                                Requiere aprobación
                            </label>
                        </div>

                        <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                            <button type="button" @click="categoryModalOpen = false"
                                    style="background-color: #e5e7eb; color: #374151; border: none; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="categorySaving"
                                    style="background-color: #1e3a5f; color: #ffffff; border: none; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;"
                                    x-text="categorySaving ? 'Guardando...' : 'Guardar'">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal: Nueva/Editar Subcategoría --}}
        <div x-show="subcategoryModalOpen" x-cloak style="position: fixed; inset: 0; z-index: 70;">
            <div style="width: 100%; height: 100%; background-color: rgba(15,23,42,0.6); display: flex; align-items: center; justify-content: center; padding: 16px; box-sizing: border-box;"
                 @click.self="subcategoryModalOpen = false">
                <div class="catadmin-modal-panel" style="border-radius: 10px; max-width: 420px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.3);"
                     @keydown.escape.window="subcategoryModalOpen = false">
                    <form @submit.prevent="submitSubcategory()">
                        <div style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0;">
                            <h2 class="catadmin-row-title" style="margin: 0; font-size: 16px; font-weight: 700;"
                                x-text="subcategoryMode === 'create' ? 'Nueva Subcategoría' : 'Editar Subcategoría'"></h2>
                        </div>

                        <div style="padding: 20px 24px;">
                            <label class="catadmin-label" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Nombre</label>
                            <input type="text" class="catadmin-input" x-model="subcategoryForm.name" placeholder="Ej. Impresoras" maxlength="255">
                            <p class="catadmin-error" x-show="subcategoryErrors.name" x-text="subcategoryErrors.name ? subcategoryErrors.name[0] : ''"></p>
                        </div>

                        <div style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                            <button type="button" @click="subcategoryModalOpen = false"
                                    style="background-color: #e5e7eb; color: #374151; border: none; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="subcategorySaving"
                                    style="background-color: #1e3a5f; color: #ffffff; border: none; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;"
                                    x-text="subcategorySaving ? 'Guardando...' : 'Guardar'">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal: Confirmación (eliminar / activar / desactivar) --}}
        <div x-show="confirmOpen" x-cloak style="position: fixed; inset: 0; z-index: 80;">
            <div style="width: 100%; height: 100%; background-color: rgba(15,23,42,0.7); display: flex; align-items: center; justify-content: center; padding: 16px; box-sizing: border-box;"
                 @click.self="confirmOpen = false">
                <div class="catadmin-modal-panel" style="border-radius: 10px; max-width: 420px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.3); padding: 24px;"
                     @keydown.escape.window="confirmOpen = false">
                    <h2 class="catadmin-row-title" style="margin: 0 0 10px 0; font-size: 16px; font-weight: 700;" x-text="confirmTitle"></h2>
                    <p class="catadmin-meta" style="margin: 0 0 20px 0; font-size: 14px;" x-text="confirmMessage"></p>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" @click="confirmOpen = false"
                                style="background-color: #e5e7eb; color: #374151; border: none; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">
                            Cancelar
                        </button>
                        <button type="button" @click="runConfirmedAction()" :disabled="confirmBusy"
                                style="background-color: #991b1b; color: #ffffff; border: none; padding: 9px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;"
                                x-text="confirmBusy ? 'Procesando...' : 'Confirmar'">
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
