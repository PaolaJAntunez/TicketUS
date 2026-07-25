<nav x-data="{ 
    open: false, 
    darkMode: localStorage.getItem('dark-mode') === 'true',
    idioma: localStorage.getItem('ticketus_lang') || 'es',
    
    textosNav: {
        es: {
            dashboard: 'Dashboard',
            tickets: 'Tickets',
            faqs: 'Preguntas Frecuentes',
            aprobaciones: 'Aprobaciones',
            administracion: 'Administración',
            perfil: 'Mi Perfil',
            settings: 'Settings',
            feedback: 'Feedback',
            claro: '☀️ Modo Claro',
            oscuro: '🌙 Modo Oscuro',
            cerrar: 'Cerrar sesión',
            nuevaSolicitud: 'Nueva Solicitud',
            catalogoTitulo: 'Catálogo de Solicitudes',
            catalogoBuscar: 'Buscar por nombre...',
            catalogoSinResultados: 'No se encontraron resultados para'
        },
        en: {
            dashboard: 'Dashboard',
            tickets: 'Tickets',
            faqs: 'FAQs',
            aprobaciones: 'Approvals',
            administracion: 'Administration',
            perfil: 'My Profile',
            settings: 'Settings',
            feedback: 'Feedback',
            claro: '☀️ Light Mode',
            oscuro: '🌙 Dark Mode',
            cerrar: 'Log Out',
            nuevaSolicitud: 'New Request',
            catalogoTitulo: 'Request Catalog',
            catalogoBuscar: 'Search by name...',
            catalogoSinResultados: 'No results found for'
        }
    },

    // El dropdown de usuario usa la clase Tailwind bg-white (fondo blanco
    // real en ambos temas) para su fondo, así que su texto no puede depender
    // del hack global de modo oscuro (que solo reconoce fondos por estilo
    // inline). Se resuelve con estos dos helpers atados a darkMode en vez
    // de clases dark: — que en este proyecto no funcionan porque
    // tailwind.config.js no tiene darkMode:'class'.
    menuItemStyle(extra = '') {
        return 'display:block; padding:10px 16px; font-size:14px; text-decoration:none; cursor:pointer; color:' + (this.darkMode ? '#e2e8f0' : '#374151') + ';' + extra;
    },
    menuHover(el) {
        el.style.backgroundColor = this.darkMode ? '#334155' : '#f1f5f9';
    }
}"
x-init="
    $watch('darkMode', value => {
        localStorage.setItem('dark-mode', value);
        if (value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    });
    if (darkMode) {
        document.documentElement.classList.add('dark');
    }
"
x-bind:style="darkMode ? 'background-color: #0f172a; border-bottom: 1px solid #334155;' : 'background-color: #1e3a5f; box-shadow: 0 2px 4px rgba(0,0,0,0.15);'">
    
    <template x-if="darkMode">
        <style>
            /* Fondo general de la página */
            body, main, .py-12, .bg-gray-100 {
                background-color: #0f172a !important;
            }

            /* Tarjetas/contenedores/controles claros -> slate oscuro. El color
               de texto se fuerza en el MISMO elemento (no solo en hijos sin
               fondo propio): así se cubren también botones/tabs que ya traían
               su propio texto oscuro (ej. "background:#ffffff;color:#1e3a5f"
               en un tab inactivo) — si no, quedaban oscuro sobre oscuro.
               #f8fafc (encabezados de tabla) y #eff6ff (fila/ítem "activo")
               son los otros tonos claros usados como fondo estructural fuera
               del blanco puro; badges de estado (verde/rojo/amarillo/azul) no
               están en esta lista a propósito, para no aplanar su color. */
            .bg-white, .card,
            [style*="background-color: #ffffff"],
            [style*="background-color: white"],
            [style*="background-color: #f8fafc"],
            [style*="background-color: #eff6ff"] {
                background-color: #1e293b !important;
                border-color: #334155 !important;
                color: #e2e8f0 !important;
            }

            /* Texto de los hijos que NO definen su propio background-color,
               para no aplanar badges/botones de color que sí traen su propia
               combinación fondo+texto ya pensada para contraste. */
            [style*="background-color: #ffffff"] :not([style*="background-color"]),
            [style*="background-color: white"] :not([style*="background-color"]),
            [style*="background-color: #f8fafc"] :not([style*="background-color"]),
            [style*="background-color: #eff6ff"] :not([style*="background-color"]) {
                color: #e2e8f0 !important;
            }

            h1, h2, h3, h4, th, .text-gray-900, .text-slate-900 {
                color: #f1f5f9 !important;
            }

            input, select, textarea {
                background-color: #1e293b !important;
                color: #f1f5f9 !important;
                border-color: #475569 !important;
            }
        </style>
    </template>

    {{-- Estilos del command palette (Ctrl+K): clases + "html.dark ...", no el
         hack de arriba, mismo patrón que tickets/index y tickets/kanban. --}}
    <style>
        .cmdk-shortcut-hint { opacity: .85; }
        @media (max-width: 768px) { .cmdk-shortcut-hint { display: none; } }

        .cmdk-panel { background-color: #ffffff; }
        html.dark .cmdk-panel { background-color: #1e293b !important; }

        .cmdk-input-wrap { border-bottom: 1px solid #e2e8f0; color: #64748b; }
        html.dark .cmdk-input-wrap { border-bottom-color: #334155 !important; color: #94a3b8 !important; }

        .cmdk-input { color: #1e293b; }
        .cmdk-input::placeholder { color: #94a3b8; }
        html.dark .cmdk-input { color: #f1f5f9 !important; }

        .cmdk-kbd { font-size: 11px; color: #94a3b8; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px 6px; }
        html.dark .cmdk-kbd { color: #cbd5e1 !important; border-color: #475569 !important; }

        .cmdk-empty { color: #64748b; }
        html.dark .cmdk-empty { color: #94a3b8 !important; }

        .cmdk-meta { color: #64748b; }
        html.dark .cmdk-meta { color: #94a3b8 !important; }

        .cmdk-result-title { color: #1e293b; }
        html.dark .cmdk-result-title { color: #f1f5f9 !important; }

        .cmdk-result:hover, .cmdk-result--active { background-color: #eff6ff; }
        html.dark .cmdk-result:hover, html.dark .cmdk-result--active { background-color: #334155 !important; }

        .cmdk-result mark.cmdk-highlight { background-color: #fef08a; color: #713f12; border-radius: 2px; padding: 0 1px; }
        html.dark .cmdk-result mark.cmdk-highlight { background-color: #a16207 !important; color: #fef9c3 !important; }
    </style>

    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; height: 64px;">
            <div style="display: flex; align-items: center;">
                <a href="{{ route('dashboard') }}" style="color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: 0.5px; text-decoration: none; margin-right: 40px;">
                    TicketUS
                </a>

                <div style="display: flex; gap: 4px;">
                    <a href="{{ route('dashboard') }}"
                       x-text="textosNav[idioma].dashboard"
                       style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('dashboard') ? '700' : '500' }}; {{ request()->routeIs('dashboard') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                    </a>
                    <a href="{{ route('tickets.index') }}"
                       x-text="textosNav[idioma].tickets"
                       style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('tickets.*') ? '700' : '500' }}; {{ request()->routeIs('tickets.*') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                    </a>
                    
                    {{-- Cualquier usuario puede ser designado aprobador (approver_id de un
                         flujo o de un ticket puntual), así que el link no se filtra por rol. --}}
                    <a href="{{ route('approvals.index') }}"
                       x-text="textosNav[idioma].aprobaciones"
                       style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('approvals.*') ? '700' : '500' }}; {{ request()->routeIs('approvals.*') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                    </a>
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.users.index') }}"
                           x-text="textosNav[idioma].administracion"
                           style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('admin.*') ? '700' : '500' }}; {{ request()->routeIs('admin.*') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                        </a>
                    @endif
                    <a href="{{ route('faqs') }}"
                       x-text="textosNav[idioma].faqs"
                       style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('faqs') ? '700' : '500' }}; {{ request()->routeIs('faqs') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                    </a>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                @if($catalogCategories->isNotEmpty())
                    <div x-data="{
                            showCatalog: false,
                            catalogSearch: '',
                            catalog: {{ Js::from($catalogCategories->map(fn ($c) => [
                                'category_id' => $c->id,
                                'category_name' => $c->name,
                                'subcategories' => $c->subcategories->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),
                            ])->values()) }},
                            baseUrl: '{{ route('tickets.create') }}',
                            filteredCatalog() {
                                const q = this.catalogSearch.trim().toLowerCase();
                                if (!q) return this.catalog;
                                return this.catalog
                                    .map(group => ({
                                        ...group,
                                        subcategories: group.subcategories.filter(s =>
                                            s.name.toLowerCase().includes(q) || group.category_name.toLowerCase().includes(q)
                                        ),
                                    }))
                                    .filter(group => group.subcategories.length > 0);
                            }
                         }"
                         @keydown.escape.window="showCatalog = false">
                        <button type="button" @click="showCatalog = true; $nextTick(() => $refs.catalogSearchInput.focus())"
                                x-text="textosNav[idioma].nuevaSolicitud"
                                style="display: inline-flex; align-items: center; gap: 6px; background-color: #2563eb; color: #ffffff; border: none; padding: 8px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        </button>

                        {{-- x-show toggles this element's own `display` — it strips any
                             `display` declared inline on the SAME element, so the flex
                             centering lives on a nested div that x-show never touches. --}}
                        <div x-show="showCatalog" x-cloak style="position: fixed; inset: 0; z-index: 60;">
                          <div style="width: 100%; height: 100%; background-color: rgba(15,23,42,0.6); display: flex; align-items: flex-start; justify-content: center; padding: 60px 16px; box-sizing: border-box;"
                               @click.self="showCatalog = false">
                            <div style="background-color: #ffffff; border-radius: 10px; max-width: 720px; width: 100%; max-height: 80vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.25);">
                                <div style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;" x-text="textosNav[idioma].catalogoTitulo"></h2>
                                        <button type="button" @click="showCatalog = false" style="background: none; border: none; cursor: pointer; color: #64748b; font-size: 22px; line-height: 1; padding: 0;">&times;</button>
                                    </div>
                                    <input type="text" x-ref="catalogSearchInput" x-model="catalogSearch"
                                           :placeholder="textosNav[idioma].catalogoBuscar"
                                           style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; box-sizing: border-box; font-size: 14px;">
                                </div>
                                <div style="padding: 8px 0; overflow-y: auto;">
                                    <template x-for="group in filteredCatalog()" :key="group.category_id">
                                        <div style="padding: 12px 24px;">
                                            <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px;" x-text="group.category_name"></p>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px 16px;">
                                                <template x-for="sub in group.subcategories" :key="sub.id">
                                                    <a :href="baseUrl + '?category_id=' + group.category_id + '&subcategory_id=' + sub.id"
                                                       style="display: block; padding: 8px 10px; border-radius: 6px; text-decoration: none; color: #1e293b; font-size: 13px;"
                                                       onmouseover="this.style.backgroundColor='#eff6ff'" onmouseout="this.style.removeProperty('background-color')"
                                                       x-text="sub.name">
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <p x-show="filteredCatalog().length === 0" style="padding: 24px; text-align: center; color: #64748b; font-size: 14px;">
                                        <span x-text="textosNav[idioma].catalogoSinResultados"></span> "<span x-text="catalogSearch"></span>"
                                    </p>
                                </div>
                            </div>
                          </div>
                        </div>
                    </div>
                @endif

                {{-- Búsqueda global (Ctrl+K / Cmd+K). x-data propio, separado
                     del x-data raíz del nav, para no arriesgar otro bug de
                     comillas sueltas dentro de un bloque JS grande (ya pasó
                     dos veces con comentarios "//" que colaban texto con
                     comillas o con pinta de etiqueta Blade). El contraste
                     claro/oscuro se resuelve con clases + "html.dark ..." en
                     vez de leer darkMode del scope padre, mismo patrón que
                     tickets/index y tickets/kanban. --}}
                <div x-data="{
                        showSearch: false,
                        query: '',
                        results: [],
                        loading: false,
                        activeIndex: -1,
                        debounceTimer: null,

                        openSearch() {
                            if (this.showSearch) return;
                            this.showSearch = true;
                            this.query = '';
                            this.results = [];
                            this.activeIndex = -1;
                            this.$nextTick(() => this.$refs.cmdkInput.focus());
                        },

                        closeSearch() {
                            this.showSearch = false;
                        },

                        onInput() {
                            clearTimeout(this.debounceTimer);
                            this.debounceTimer = setTimeout(() => this.runSearch(), 300);
                        },

                        async runSearch() {
                            const q = this.query.trim();
                            this.activeIndex = -1;

                            if (! q) {
                                this.results = [];
                                this.loading = false;
                                return;
                            }

                            this.loading = true;

                            try {
                                const res = await fetch('/tickets/search?q=' + encodeURIComponent(q), {
                                    headers: { 'Accept': 'application/json' },
                                });
                                const data = await res.json();
                                this.results = data.results || [];
                            } catch (e) {
                                this.results = [];
                            } finally {
                                this.loading = false;
                            }
                        },

                        moveDown() {
                            if (! this.results.length) return;
                            this.activeIndex = (this.activeIndex + 1) % this.results.length;
                            this.scrollActiveIntoView();
                        },

                        moveUp() {
                            if (! this.results.length) return;
                            this.activeIndex = (this.activeIndex - 1 + this.results.length) % this.results.length;
                            this.scrollActiveIntoView();
                        },

                        scrollActiveIntoView() {
                            this.$nextTick(() => {
                                const el = this.$refs.cmdkResults?.children[this.activeIndex];
                                if (el) el.scrollIntoView({ block: 'nearest' });
                            });
                        },

                        selectActive() {
                            if (this.activeIndex >= 0 && this.results[this.activeIndex]) {
                                window.location = this.results[this.activeIndex].url;
                            }
                        },

                        escapeHtml(str) {
                            const div = document.createElement('div');
                            div.textContent = str;
                            return div.innerHTML;
                        },

                        highlight(text) {
                            const escaped = this.escapeHtml(text);
                            const q = this.query.trim();
                            if (! q) return escaped;

                            const escapedQ = this.escapeHtml(q);
                            const idx = escaped.toLowerCase().indexOf(escapedQ.toLowerCase());
                            if (idx === -1) return escaped;

                            return escaped.substring(0, idx)
                                + '<mark class=' + String.fromCharCode(34) + 'cmdk-highlight' + String.fromCharCode(34) + '>'
                                + escaped.substring(idx, idx + escapedQ.length)
                                + '</mark>'
                                + escaped.substring(idx + escapedQ.length);
                        }
                     }"
                     @keydown.window="if (($event.ctrlKey || $event.metaKey) && $event.key.toLowerCase() === 'k') { $event.preventDefault(); openSearch(); }"
                     @keydown.escape.window="closeSearch()">

                    <button type="button" @click="openSearch()"
                            title="Buscar tickets (Ctrl+K)"
                            style="display: inline-flex; align-items: center; gap: 6px; background: transparent; border: 1px solid rgba(255,255,255,0.35); color: #ffffff; padding: 7px 12px; border-radius: 6px; font-size: 13px; cursor: pointer;">
                        <i class="ti ti-search"></i>
                        <span class="cmdk-shortcut-hint">Ctrl+K</span>
                    </button>

                    <div x-show="showSearch" x-cloak style="position: fixed; inset: 0; z-index: 80;">
                        <div style="width: 100%; height: 100%; background-color: rgba(15,23,42,0.6); display: flex; align-items: flex-start; justify-content: center; padding: 80px 16px; box-sizing: border-box;"
                             @click.self="closeSearch()">
                            <div class="cmdk-panel"
                                 style="border-radius: 10px; max-width: 640px; width: 100%; max-height: 70vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.3);"
                                 @keydown.arrow-down.prevent="moveDown()"
                                 @keydown.arrow-up.prevent="moveUp()"
                                 @keydown.enter.prevent="selectActive()">

                                <div class="cmdk-input-wrap" style="padding: 14px 20px; display: flex; align-items: center; gap: 10px;">
                                    <i class="ti ti-search" style="font-size: 18px;"></i>
                                    <input type="text" x-ref="cmdkInput" x-model="query" @input="onInput()"
                                           class="cmdk-input"
                                           placeholder="Buscar por #ID, título o solicitante..."
                                           style="flex: 1; border: none; outline: none; font-size: 15px; background: transparent;">
                                    <span class="cmdk-kbd">Esc</span>
                                </div>

                                <div x-ref="cmdkResults" style="overflow-y: auto; padding: 6px 0;">
                                    <template x-if="loading">
                                        <p class="cmdk-empty" style="padding: 24px; text-align: center; font-size: 13px; margin: 0;">Buscando...</p>
                                    </template>

                                    <template x-if="! loading && query.trim() !== '' && results.length === 0">
                                        <p class="cmdk-empty" style="padding: 24px; text-align: center; font-size: 13px; margin: 0;">No se encontraron tickets.</p>
                                    </template>

                                    <template x-if="! loading && query.trim() === ''">
                                        <p class="cmdk-empty" style="padding: 24px; text-align: center; font-size: 13px; margin: 0;">Escribe para buscar tickets por #ID, título o solicitante.</p>
                                    </template>

                                    <template x-for="(ticket, index) in results" :key="ticket.id">
                                        <a :href="ticket.url"
                                           class="cmdk-result"
                                           :class="activeIndex === index ? 'cmdk-result--active' : ''"
                                           @mouseenter="activeIndex = index"
                                           style="display: flex; align-items: center; gap: 12px; padding: 10px 20px; text-decoration: none;">

                                            <span class="cmdk-meta" style="font-family: monospace; font-weight: 700; font-size: 12px; flex-shrink: 0;" x-text="'#' + ticket.id"></span>

                                            <span style="flex: 1; min-width: 0;">
                                                <span class="cmdk-result-title" style="display: block; font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" x-html="highlight(ticket.title)"></span>
                                                <span class="cmdk-meta" style="font-size: 12px;">
                                                    <span x-html="highlight(ticket.requester)"></span>
                                                    &middot; <span x-text="ticket.created_at"></span>
                                                </span>
                                            </span>

                                            <span :style="'flex-shrink:0; background-color:' + ticket.status_bg + '; color:' + ticket.status_text + '; padding:3px 10px; border-radius:9999px; font-size:11px; font-weight:700; white-space:nowrap;'"
                                                  x-text="ticket.status_label">
                                            </span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $roleLabels = ['admin' => 'Admin', 'agent' => 'Agente', 'user' => 'Usuario'];
                    $roleLabel = $roleLabels[Auth::user()->role] ?? ucfirst(Auth::user()->role);
                @endphp
                <span style="background-color: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.4); padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ $roleLabel }}
                </span>

                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button type="button" style="display: flex; align-items: center; gap: 6px; background: transparent; border: none; color: #ffffff; font-size: 14px; font-weight: 500; cursor: pointer; padding: 8px 12px;">
                            <span>{{ Auth::user()->name }}</span>
                            <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div :style="'padding:12px 16px; border-bottom:1px solid ' + (darkMode ? '#334155' : '#e2e8f0') + ';'">
                            <p :style="'margin:0; font-size:14px; font-weight:600; color:' + (darkMode ? '#ffffff' : '#1e293b') + ';'">{{ Auth::user()->name }}</p>
                            <p style="margin: 2px 0 0 0; font-size: 12px; color: #94a3b8;">{{ Auth::user()->email }}</p>
                        </div>

                        <a href="{{ route('profile.edit') }}" x-text="textosNav[idioma].perfil"
                           :style="menuItemStyle()"
                           @mouseover="menuHover($el)" @mouseout="$el.style.removeProperty('background-color')">
                        </a>

                        <a href="/settings" @click.stop x-text="textosNav[idioma].settings"
                           :style="menuItemStyle()"
                           @mouseover="menuHover($el)" @mouseout="$el.style.removeProperty('background-color')">
                        </a>

                        <a href="/feedback" x-text="textosNav[idioma].feedback"
                           :style="menuItemStyle()"
                           @mouseover="menuHover($el)" @mouseout="$el.style.removeProperty('background-color')">
                        </a>

                        <button type="button"
                                @click="darkMode = !darkMode"
                                @mouseover="menuHover($el)" @mouseout="$el.style.removeProperty('background-color')"
                                :style="menuItemStyle('width:100%; text-align:left; background:transparent; border:none;')">
                            <span x-text="darkMode ? textosNav[idioma].claro : textosNav[idioma].oscuro"></span>
                        </button>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); this.closest('form').submit();"
                               x-text="textosNav[idioma].cerrar"
                               @mouseover="menuHover($el)" @mouseout="$el.style.removeProperty('background-color')"
                               style="display: block; padding: 10px 16px; font-size: 14px; color: #dc2626; text-decoration: none; border-top: 1px solid #e2e8f0;">
                            </a>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>