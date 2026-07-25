@props([
    'action',
    'method' => 'POST',
    'categories',
    'users',
    'flowName' => '',
    'categoryId' => '',
    'levels' => [],
    'submitLabel' => 'Guardar Flujo',
])

<div x-data="{
        levels: {{ Js::from(collect($levels)->map(fn ($l) => [
            'approver_id' => $l['approver_id'] ?? null,
            'approver_label' => $l['approver_label'] ?? '',
            'search' => '',
            'open' => false,
        ])->values()) }},
        users: {{ Js::from($users->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])->values()) }},
        init() {
            // Si volvimos aquí por un error de validación, old('levels') trae
            // approver_id pero no la etiqueta (el form solo envía el id) — la
            // reconstruimos buscando en la lista de usuarios.
            this.levels.forEach((level) => {
                if (level.approver_id && !level.approver_label) {
                    const user = this.users.find(u => u.id == level.approver_id);
                    if (user) {
                        level.approver_label = user.name + ' (' + user.email + ')';
                    }
                }
            });
        },
        addLevel() {
            this.levels.push({ approver_id: null, approver_label: '', search: '', open: false });
        },
        removeLevel(index) {
            this.levels.splice(index, 1);
        },
        moveUp(index) {
            if (index === 0) return;
            const items = this.levels;
            [items[index - 1], items[index]] = [items[index], items[index - 1]];
        },
        moveDown(index) {
            if (index === this.levels.length - 1) return;
            const items = this.levels;
            [items[index + 1], items[index]] = [items[index], items[index + 1]];
        },
        filteredUsers(query) {
            if (!query) return this.users;
            const q = query.toLowerCase();
            return this.users.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q));
        },
        selectApprover(index, user) {
            this.levels[index].approver_id = user.id;
            this.levels[index].approver_label = user.name + ' (' + user.email + ')';
            this.levels[index].search = '';
            this.levels[index].open = false;
        },
        clearApprover(index) {
            this.levels[index].approver_id = null;
            this.levels[index].approver_label = '';
        },
    }">

    <form action="{{ $action }}" method="POST">
        @csrf
        @if(strtoupper($method) !== 'POST')
            @method($method)
        @endif

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Nombre del flujo</label>
            <input type="text" name="name" value="{{ old('name', $flowName) }}"
                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;"
                   placeholder="Ej. Flujo de Compras">
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Categoría</label>
            <select name="category_id" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                <option value="">Selecciona una categoría</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $categoryId) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 14px; font-weight: 600; color: #1e3a5f; margin: 0;">Niveles de aprobación</h3>
                <button type="button" @click="addLevel()"
                        style="background-color: #ffffff; color: #2563eb; border: 1px solid #2563eb; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    + Agregar nivel
                </button>
            </div>

            @if($users->isEmpty())
                <div style="background-color: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; border-radius: 6px; padding: 10px 14px; font-size: 13px; margin-bottom: 12px;">
                    No hay usuarios disponibles aún. Puedes crear los niveles igual y asignar el aprobador más adelante.
                </div>
            @endif

            <template x-if="levels.length === 0">
                <p style="font-size: 13px; color: #94a3b8; margin: 0 0 12px 0; font-style: italic;">
                    Aún no has agregado ningún nivel.
                </p>
            </template>

            <template x-for="(level, index) in levels" :key="index">
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 10px; background-color: #f8fafc;">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <span style="background-color: #1e3a5f; color: #ffffff; width: 26px; height: 26px; min-width: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; margin-top: 4px;"
                              x-text="index + 1"></span>

                        <div style="flex: 1; position: relative;">
                            <label style="display: block; font-size: 12px; color: #64748b; margin-bottom: 4px;">Aprobador</label>

                            <template x-if="users.length === 0">
                                <p style="font-size: 13px; color: #94a3b8; font-style: italic; margin: 0;">No hay usuarios disponibles aún.</p>
                            </template>

                            <template x-if="users.length > 0 && level.approver_id">
                                <div style="display: flex; align-items: center; justify-content: space-between; background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px;">
                                    <span style="font-size: 13px; color: #1e293b;" x-text="level.approver_label"></span>
                                    <button type="button" @click="clearApprover(index)" style="background: none; border: none; color: #991b1b; cursor: pointer; font-size: 12px;">Quitar</button>
                                </div>
                            </template>

                            <template x-if="users.length > 0 && !level.approver_id">
                                <div @click.outside="level.open = false">
                                    <input type="text" x-model="level.search" @focus="level.open = true"
                                           placeholder="Buscar por nombre o correo..."
                                           style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box; font-size: 13px;">
                                    <div x-show="level.open" x-cloak
                                         style="position: absolute; z-index: 10; width: 100%; max-height: 180px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; margin-top: 4px; box-shadow: 0 4px 6px rgba(0,0,0,.1);">
                                        <template x-for="user in filteredUsers(level.search)" :key="user.id">
                                            <div @mousedown.prevent="selectApprover(index, user)"
                                                 style="padding: 8px 10px; font-size: 13px; cursor: pointer; border-bottom: 1px solid #f1f5f9;"
                                                 onmouseover="this.style.backgroundColor='#eff6ff'" onmouseout="this.style.backgroundColor='#ffffff'">
                                                <span x-text="user.name"></span>
                                                <span style="color: #94a3b8;" x-text="'(' + user.email + ')'"></span>
                                            </div>
                                        </template>
                                        <template x-if="filteredUsers(level.search).length === 0">
                                            <div style="padding: 8px 10px; font-size: 13px; color: #94a3b8;">Sin resultados.</div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <input type="hidden" :name="`levels[${index}][approver_id]`" :value="level.approver_id">
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <button type="button" @click="moveUp(index)" :disabled="index === 0"
                                    :style="index === 0 ? 'opacity:.3; cursor:default;' : 'cursor:pointer;'"
                                    style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; width: 26px; height: 26px; font-size: 12px;">↑</button>
                            <button type="button" @click="moveDown(index)" :disabled="index === levels.length - 1"
                                    :style="index === levels.length - 1 ? 'opacity:.3; cursor:default;' : 'cursor:pointer;'"
                                    style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; width: 26px; height: 26px; font-size: 12px;">↓</button>
                            <button type="button" @click="removeLevel(index)"
                                    style="background: #ffffff; border: 1px solid #fecaca; color: #991b1b; border-radius: 4px; width: 26px; height: 26px; font-size: 12px; cursor: pointer;">🗑</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('admin.approval-flows.index') }}"
               style="padding: 10px 18px; background-color: #e5e7eb; color: #374151; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                Cancelar
            </a>
            <button type="submit"
                    style="background-color: #1e3a5f; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>
