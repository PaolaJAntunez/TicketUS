<nav x-data="{ open: false }" style="background-color: #1e3a5f; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; height: 64px;">
            <div style="display: flex; align-items: center;">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" style="color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: 0.5px; text-decoration: none; margin-right: 40px;">
                    TicketUS
                </a>

                <!-- Navigation Links -->
                <div style="display: flex; gap: 4px;">
                    <a href="{{ route('dashboard') }}"
                       style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('dashboard') ? '700' : '500' }}; {{ request()->routeIs('dashboard') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('tickets.index') }}"
                       style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('tickets.*') ? '700' : '500' }}; {{ request()->routeIs('tickets.*') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                        Tickets
                    </a>
                    @if(in_array(Auth::user()->role, ['approver', 'admin']))
                        <a href="{{ route('approvals.index') }}"
                           style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('approvals.*') ? '700' : '500' }}; {{ request()->routeIs('approvals.*') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                            Aprobaciones
                        </a>
                    @endif
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.users.index') }}"
                           style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: {{ request()->routeIs('admin.*') ? '700' : '500' }}; {{ request()->routeIs('admin.*') ? 'background-color: rgba(255,255,255,0.18); box-shadow: inset 0 -2px 0 #2563eb;' : '' }}">
                            Administración
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
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
                            <p style="margin: 0; font-size: 14px; font-weight: 600; color: #1e293b;">{{ Auth::user()->name }}</p>
                            <p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;">{{ Auth::user()->email }}</p>
                        </div>

                        <a href="{{ route('profile.edit') }}" style="display: block; padding: 10px 16px; font-size: 14px; color: #374151; text-decoration: none;">
                            Mi Perfil
                        </a>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); this.closest('form').submit();"
                               style="display: block; padding: 10px 16px; font-size: 14px; color: #dc2626; text-decoration: none; border-top: 1px solid #e2e8f0;">
                                Cerrar sesión
                            </a>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
