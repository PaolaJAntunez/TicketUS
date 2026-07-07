<x-app-layout>
    <div x-data="{ 
        idioma: localStorage.getItem('ticketus_lang') || 'es',
        
        // Diccionario de textos para el Dashboard
        textosDashboard: {
            es: {
                bienvenida: '¡Bienvenido de nuevo a TicketUS!',
                subtitulo: 'Aquí tienes un resumen del estado de tus soportes hoy.',
                tarjeta1Titulo: 'Tickets Abiertos',
                tarjeta1Desc: 'Esperando revisión o asignación.',
                tarjeta2Titulo: 'En Progreso',
                tarjeta2Desc: 'Soportes bajo atención técnica activa.',
                tarjeta3Titulo: 'Resueltos',
                tarjeta3Desc: 'Cerrados con éxito esta semana.',
                botonAccion: 'Ir a mis Tickets',
                alertaInfo: 'Aviso del Sistema: Mantenimiento programado este viernes a las 11:00 PM.'
            },
            en: {
                bienvenida: 'Welcome back to TicketUS!',
                subtitulo: 'Here is a summary of your support status today.',
                tarjeta1Titulo: 'Open Tickets',
                tarjeta1Desc: 'Awaiting review or assignment.',
                tarjeta2Titulo: 'In Progress',
                tarjeta2Desc: 'Supports under active technical attention.',
                tarjeta3Titulo: 'Resolved',
                tarjeta3Desc: 'Successfully closed this week.',
                botonAccion: 'Go to my Tickets',
                alertaInfo: 'System Notice: Scheduled maintenance this Friday at 11:00 PM.'
            }
        }
    }" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">

        <!-- SECCIÓN DE ENCABEZADO DINÁMICO -->
        <div style="margin-bottom: 32px;">
            <h1 x-text="textosDashboard[idioma].bienvenida" style="font-size: 28px; font-weight: 700; color: #ffffff; margin-bottom: 6px;"></h1>
            <p x-text="textosDashboard[idioma].subtitulo" style="font-size: 15px; color: #94a3b8; margin: 0;"></p>
        </div>

        <!-- BANNER DE AVISO -->
        <div style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid #3b82f6; border-radius: 6px; padding: 14px 20px; color: #3b82f6; font-size: 14px; margin-bottom: 32px; display: flex; align-items: center; gap: 10px;">
            <span>ℹ️</span>
            <span x-text="textosDashboard[idioma].alertaInfo"></span>
        </div>

        <!-- REJILLA DE TARJETAS (CARDS) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 40px;">
            
            <!-- Tarjeta 1 -->
            <div style="background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.15);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <h3 x-text="textosDashboard[idioma].tarjeta1Titulo" style="color: #cbd5e1; font-size: 16px; font-weight: 600; margin: 0;"></h3>
                    <span style="font-size: 24px; font-weight: 700; color: #ef4444; line-height: 1;">12</span>
                </div>
                <p x-text="textosDashboard[idioma].tarjeta1Desc" style="color: #64748b; font-size: 13px; margin: 0;"></p>
            </div>

            <!-- Tarjeta 2 -->
            <div style="background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.15);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <h3 x-text="textosDashboard[idioma].tarjeta2Titulo" style="color: #cbd5e1; font-size: 16px; font-weight: 600; margin: 0;"></h3>
                    <span style="font-size: 24px; font-weight: 700; color: #3b82f6; line-height: 1;">5</span>
                </div>
                <p x-text="textosDashboard[idioma].tarjeta2Desc" style="color: #64748b; font-size: 13px; margin: 0;"></p>
            </div>

            <!-- Tarjeta 3 -->
            <div style="background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.15);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <h3 x-text="textosDashboard[idioma].tarjeta3Titulo" style="color: #cbd5e1; font-size: 16px; font-weight: 600; margin: 0;"></h3>
                    <span style="font-size: 24px; font-weight: 700; color: #10b981; line-height: 1;">28</span>
                </div>
                <p x-text="textosDashboard[idioma].tarjeta3Desc" style="color: #64748b; font-size: 13px; margin: 0;"></p>
            </div>

        </div>

        <!-- BOTÓN DE ACCIÓN RÁPIDA -->
        <div style="text-align: center;">
            <a href="{{ route('tickets.index') }}" 
               x-text="textosDashboard[idioma].botonAccion"
               style="display: inline-block; background-color: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 14px; font-weight: 600; transition: background-color 0.2s;">
            </a>
        </div>

    </div>
</x-app-layout>