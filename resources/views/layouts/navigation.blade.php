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
                       style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: 500; {{ request()->routeIs('dashboard') ? 'background-color: rgba(255,255,255,0.15);' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('tickets.index') }}"
                       style="color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 4px; font-size: 14px; font-weight: 500; {{ request()->routeIs('tickets.*') ? 'background-color: rgba(255,255,255,0.15);' : '' }}">
                        Tickets
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div style="display: flex; align-items: center;">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" style="display: flex; align-items: center; gap: 6px; background: transparent; border: none; color: #ffffff; font-size: 14px; font-weight: 500; cursor: pointer; padding: 8px 12px;">
                            <span>{{ Auth::user()->name }}</span>
                            <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
