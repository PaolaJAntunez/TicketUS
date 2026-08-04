{{-- Campana de notificaciones del navbar: dropdown Alpine.js + polling simple
     (sin WebSockets), mismo patrón que el buscador Ctrl+K de al lado: x-data
     propio, fetch() con Accept: application/json, contraste claro/oscuro con
     clases + "html.dark ...". --}}
<div x-data="{
        open: false,
        unreadCount: 0,
        items: [],
        pollTimer: null,

        init() {
            this.fetchNotifications();
            this.pollTimer = setInterval(() => this.fetchNotifications(), 45000);
        },

        async fetchNotifications() {
            try {
                const res = await fetch('{{ route('notifications.index') }}', {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                this.items = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
            } catch (e) {}
        },

        toggle() {
            this.open = !this.open;
            if (this.open) this.fetchNotifications();
        },

        async openItem(item) {
            if (!item.read) {
                item.read = true;
                this.unreadCount = Math.max(0, this.unreadCount - 1);

                try {
                    await fetch('/notifications/' + item.id + '/read', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                    });
                } catch (e) {}
            }

            if (item.url) window.location = item.url;
        },

        async markAllRead() {
            this.items = this.items.map(i => ({ ...i, read: true }));
            this.unreadCount = 0;

            try {
                await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
            } catch (e) {}
        }
     }"
     @keydown.escape.window="open = false"
     @click.outside="open = false"
     style="position: relative;">

    <button type="button" @click="toggle()"
            title="Notificaciones"
            style="position: relative; display: inline-flex; align-items: center; justify-content: center; background: transparent; border: 1px solid rgba(255,255,255,0.35); color: #ffffff; width: 36px; height: 36px; border-radius: 6px; cursor: pointer;">
        <i class="ti ti-bell" style="font-size: 18px;"></i>
        <span x-show="unreadCount > 0" x-cloak x-text="unreadCount > 99 ? '99+' : unreadCount"
              style="position: absolute; top: -6px; right: -6px; background-color: #dc2626; color: #ffffff; font-size: 10px; font-weight: 700; line-height: 1; padding: 3px 5px; border-radius: 9999px; min-width: 16px; text-align: center;">
        </span>
    </button>

    <div x-show="open" x-cloak
         class="notif-panel"
         style="position: absolute; right: 0; top: 44px; width: 360px; max-width: 90vw; border-radius: 10px; box-shadow: 0 20px 40px rgba(0,0,0,0.25); z-index: 70; overflow: hidden; background-color: #ffffff;">

        <div class="notif-header" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">
            <span class="notif-title" style="font-size: 14px; font-weight: 700; color: #1e293b;">Notificaciones</span>
            <button type="button" @click="markAllRead()" x-show="unreadCount > 0"
                    style="background: none; border: none; color: #2563eb; font-size: 12px; font-weight: 600; cursor: pointer; padding: 0;">
                Marcar todas como leídas
            </button>
        </div>

        <div style="max-height: 420px; overflow-y: auto;">
            <template x-if="items.length === 0">
                <p class="notif-empty" style="padding: 24px; text-align: center; font-size: 13px; color: #64748b; margin: 0;">
                    No tienes notificaciones todavía.
                </p>
            </template>

            <template x-for="item in items" :key="item.id">
                <button type="button" @click="openItem(item)"
                        class="notif-item"
                        :style="'display:flex; align-items:flex-start; gap:10px; width:100%; text-align:left; padding:12px 16px; border:none; border-bottom:1px solid #f1f5f9; cursor:pointer; background-color:' + (item.read ? 'transparent' : '#eff6ff') + '; opacity:' + (item.read ? '0.65' : '1') + ';'">
                    <i :class="'ti ' + (item.icon || 'ti-bell')" style="font-size: 16px; color: #2563eb; margin-top: 2px; flex-shrink: 0;"></i>
                    <span style="min-width: 0; flex: 1;">
                        <span class="notif-item-title" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b;" x-text="item.title"></span>
                        <span class="notif-item-message" style="display: block; font-size: 12px; color: #475569; margin-top: 2px;" x-text="item.message"></span>
                        <span class="notif-item-time" style="display: block; font-size: 11px; color: #94a3b8; margin-top: 4px;" x-text="item.created_at"></span>
                    </span>
                    <span x-show="!item.read" style="flex-shrink: 0; width: 8px; height: 8px; border-radius: 9999px; background-color: #2563eb; margin-top: 6px;"></span>
                </button>
            </template>
        </div>
    </div>
</div>

<style>
    /* El panel se ancla con right:0 al wrapper del botón (36px), no al
       viewport — en pantallas angostas ese wrapper puede quedar bastante a
       la derecha del navbar, así que un panel de 360px se sale por la
       izquierda. position:fixed con insets propios lo ancla al viewport en
       vez de al wrapper. */
    @media (max-width: 480px) {
        .notif-panel {
            position: fixed !important;
            top: 60px !important;
            right: 8px !important;
            left: 8px !important;
            width: auto !important;
            max-width: none !important;
        }
    }

    html.dark .notif-panel { background-color: #1e293b !important; }
    html.dark .notif-header { border-bottom-color: #334155 !important; }
    html.dark .notif-title { color: #f1f5f9 !important; }
    html.dark .notif-empty { color: #94a3b8 !important; }
    html.dark .notif-item { border-bottom-color: #334155 !important; }
    html.dark .notif-item-title { color: #f1f5f9 !important; }
    html.dark .notif-item-message { color: #cbd5e1 !important; }
</style>
