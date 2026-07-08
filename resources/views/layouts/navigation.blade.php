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
            cerrar: 'Cerrar sesión'
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
            cerrar: 'Log Out'
        }
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
            body, main, .py-12, .bg-gray-100, [style*="background-color"] {
                background-color: #0f172a !important; 
            }
            .bg-white, .card, [style*="background-color: #ffffff"], [style*="background-color: white"] {
                background-color: #1e293b !important;
                border-color: #334155 !important;
            }
            h1, h2, h3, h4, th, .text-gray-900, .text-slate-900, [style*="color: #1e293b"] {
                color: #f1f5f9 !important;
            }
            p, td, label, .text-gray-600, .text-slate-600 {
                color: #cbd5e1 !important;
            }
            input, select, textarea {
                background-color: #1e293b !important;
                color: #f1f5f9 !important;
                border-color: #475569 !important;
            }
        </style>
    </template>

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
                    <a href="{{ route('faqs') }}"
                       x-text="textosNav[idioma].faqs"
                       style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('faqs') ? '700' : '500' }}; {{ request()->routeIs('faqs') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                    </a>
                    @if(in_array(Auth::user()->role, ['approver', 'admin']))
                        <a href="{{ route('approvals.index') }}"
                           x-text="textosNav[idioma].aprobaciones"
                           style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('approvals.*') ? '700' : '500' }}; {{ request()->routeIs('approvals.*') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                        </a>
                    @endif
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.users.index') }}"
                           x-text="textosNav[idioma].administracion"
                           style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('admin.*') ? '700' : '500' }}; {{ request()->routeIs('admin.*') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                        </a>
                    @endif
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                @php
                    $roleLabels = ['admin' => 'Admin', 'agent' => 'Agente', 'approver' => 'Aprobador', 'user' => 'Usuario'];
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
                        <div style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 14px; font-weight: 600; color: #ffffff;">{{ Auth::user()->name }}</p>
                            <p style="margin: 2px 0 0 0; font-size: 12px; color: #94a3b8;">{{ Auth::user()->email }}</p>
                        </div>

                        <a href="{{ route('profile.edit') }}" x-text="textosNav[idioma].perfil" style="display: block; padding: 10px 16px; font-size: 14px; color: #e2e8f0; text-decoration: none;">
                        </a>

                        <a href="/settings" @click.stop x-text="textosNav[idioma].settings" style="display: block; padding: 10px 16px; font-size: 14px; color: #e2e8f0; text-decoration: none;">
                        </a>

                        <a href="/feedback" x-text="textosNav[idioma].feedback" style="display: block; padding: 10px 16px; font-size: 14px; color: #e2e8f0; text-decoration: none;">
                        </a>

                        <button type="button" 
                                @click="darkMode = !darkMode" 
                                style="display: block; width: 100%; text-align: left; padding: 10px 16px; font-size: 14px; color: #e2e8f0; background: transparent; border: none; cursor: pointer;">
                            <span x-text="darkMode ? textosNav[idioma].claro : textosNav[idioma].oscuro"></span>
                        </button>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); this.closest('form').submit();"
                               x-text="textosNav[idioma].cerrar"
                               style="display: block; padding: 10px 16px; font-size: 14px; color: #dc2626; text-decoration: none; border-top: 1px solid #e2e8f0;">
                            </a>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>