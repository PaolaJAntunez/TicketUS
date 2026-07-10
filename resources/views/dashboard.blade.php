<x-app-layout>
    <div x-data="{ 
        idioma: localStorage.getItem('ticketus_lang') || 'es',
        
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
                
                tituloSoluciones: 'Manuales y Base de Conocimiento',
                sol1: 'Configurar Correo Electrónico Corporativo',
                sol2: 'Descargar Manual de Uso VPN',
                sol3: 'Políticas de Seguridad de TI',

                tituloStatus: 'Estado de los Servicios Globales',
                statusOnline: 'Operacional',
                statusOffline: 'Mantenimiento / Caído',
                srvRed: 'Red Local & WiFi Corp.',
                srvEmail: 'Servidor de Correo Office365',
                srvVpn: 'Acceso Remoto VPN'
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
                
                tituloSoluciones: 'Manuals & Knowledge Base',
                sol1: 'Configure Corporate Email',
                sol2: 'Download VPN User Manual',
                sol3: 'IT Security Policies',

                tituloStatus: 'Global Service Status',
                statusOnline: 'Operational',
                statusOffline: 'Maintenance / Down',
                srvRed: 'Local Network & Corp. WiFi',
                srvEmail: 'Office365 Email Server',
                srvVpn: 'VPN Remote Access'
            }
        }
    }" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">

        <!-- ENCABEZADO -->
        <div style="margin-bottom: 32px;">
            <h1 x-text="textosDashboard[idioma].bienvenida" style="font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 6px;"></h1>
            <p x-text="textosDashboard[idioma].subtitulo" style="font-size: 15px; color: #64748b; margin: 0;"></p>
        </div>

        <!-- REJILLA DE TARJETAS (MÉTRICAS) - ESTILO CLARO -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 32px;">
            <!-- Tarjeta 1 -->
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-left: 4px solid #ef4444; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <span style="width: 40px; height: 40px; border-radius: 50%; background-color: #fee2e2; display: flex; align-items: center; justify-content: center;">
                        <i class="ti ti-ticket" style="font-size: 20px; color: #ef4444;"></i>
                    </span>
                    <h3 x-text="textosDashboard[idioma].tarjeta1Titulo" style="color: #334155; font-size: 15px; font-weight: 600; margin: 0;"></h3>
                </div>
                <span style="font-size: 32px; font-weight: 700; color: #1e293b; line-height: 1; display: block; margin-bottom: 8px;">12</span>
                <p x-text="textosDashboard[idioma].tarjeta1Desc" style="color: #64748b; font-size: 13px; margin: 0;"></p>
            </div>
            <!-- Tarjeta 2 -->
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-left: 4px solid #3b82f6; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <span style="width: 40px; height: 40px; border-radius: 50%; background-color: #dbeafe; display: flex; align-items: center; justify-content: center;">
                        <i class="ti ti-hourglass" style="font-size: 20px; color: #3b82f6;"></i>
                    </span>
                    <h3 x-text="textosDashboard[idioma].tarjeta2Titulo" style="color: #334155; font-size: 15px; font-weight: 600; margin: 0;"></h3>
                </div>
                <span style="font-size: 32px; font-weight: 700; color: #1e293b; line-height: 1; display: block; margin-bottom: 8px;">5</span>
                <p x-text="textosDashboard[idioma].tarjeta2Desc" style="color: #64748b; font-size: 13px; margin: 0;"></p>
            </div>
            <!-- Tarjeta 3 -->
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-left: 4px solid #10b981; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <span style="width: 40px; height: 40px; border-radius: 50%; background-color: #d1fae5; display: flex; align-items: center; justify-content: center;">
                        <i class="ti ti-circle-check" style="font-size: 20px; color: #10b981;"></i>
                    </span>
                    <h3 x-text="textosDashboard[idioma].tarjeta3Titulo" style="color: #334155; font-size: 15px; font-weight: 600; margin: 0;"></h3>
                </div>
                <span style="font-size: 32px; font-weight: 700; color: #1e293b; line-height: 1; display: block; margin-bottom: 8px;">28</span>
                <p x-text="textosDashboard[idioma].tarjeta3Desc" style="color: #64748b; font-size: 13px; margin: 0;"></p>
            </div>
        </div>

        <!-- BOTÓN DE ACCIÓN RÁPIDA -->
        <div style="text-align: center; margin-bottom: 48px;">
            <a href="{{ route('tickets.index') }}" 
               onmouseover="this.style.backgroundColor='#2563eb'"
               onmouseout="this.style.backgroundColor='#3b82f6'"
               style="display: inline-flex; align-items: center; gap: 8px; background-color: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 14px; font-weight: 600; box-shadow: 0 4px 12px rgba(59,130,246,0.3); transition: background-color 0.2s;">
                <span x-text="textosDashboard[idioma].botonAccion"></span>
                <i class="ti ti-arrow-right" style="font-size: 16px;"></i>
            </a>
        </div>

        <!-- SECCIÓN INFERIOR COMPARTIDA -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 32px; border-top: 1px solid #e2e8f0; padding-top: 32px;">
            
            <!-- MANUALES / DOCUMENTACIÓN -->
            <div>
                <h2 style="color: #1e293b; font-size: 20px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-books" style="font-size: 20px; color: #64748b;"></i>
                    <span x-text="textosDashboard[idioma].tituloSoluciones"></span>
                </h2>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="#" onmouseover="this.style.backgroundColor='#334155'" onmouseout="this.style.backgroundColor='#1e293b'" style="display: flex; align-items: center; justify-content: space-between; background-color: #1e293b; border: 1px solid #334155; padding: 16px; border-radius: 6px; color: #cbd5e1; text-decoration: none; transition: background-color 0.2s;">
                        <span x-text="textosDashboard[idioma].sol1" style="font-weight: 500;"></span>
                        <i class="ti ti-mail" style="font-size: 18px; color: #94a3b8;"></i>
                    </a>
                    <a href="#" onmouseover="this.style.backgroundColor='#334155'" onmouseout="this.style.backgroundColor='#1e293b'" style="display: flex; align-items: center; justify-content: space-between; background-color: #1e293b; border: 1px solid #334155; padding: 16px; border-radius: 6px; color: #cbd5e1; text-decoration: none; transition: background-color 0.2s;">
                        <span x-text="textosDashboard[idioma].sol2" style="font-weight: 500;"></span>
                        <i class="ti ti-shield-lock" style="font-size: 18px; color: #94a3b8;"></i>
                    </a>
                    <a href="#" onmouseover="this.style.backgroundColor='#334155'" onmouseout="this.style.backgroundColor='#1e293b'" style="display: flex; align-items: center; justify-content: space-between; background-color: #1e293b; border: 1px solid #334155; padding: 16px; border-radius: 6px; color: #cbd5e1; text-decoration: none; transition: background-color 0.2s;">
                        <span x-text="textosDashboard[idioma].sol3" style="font-weight: 500;"></span>
                        <i class="ti ti-lock" style="font-size: 18px; color: #94a3b8;"></i>
                    </a>
                </div>
            </div>

            <!-- ESTADO DE LOS SERVICIOS -->
            <div>
                <h2 style="color: #1e293b; font-size: 20px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-server-2" style="font-size: 20px; color: #64748b;"></i>
                    <span x-text="textosDashboard[idioma].tituloStatus"></span>
                </h2>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    
                    <!-- Servicio 1 -->
                    <div style="display: flex; align-items: center; justify-content: space-between; background-color: #1e293b; border: 1px solid #334155; padding: 14px 16px; border-radius: 6px;">
                        <span x-text="textosDashboard[idioma].srvRed" style="color: #cbd5e1; font-weight: 500;"></span>
                        <span style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #10b981; font-weight: 600;">
                            <span style="width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; display: inline-block;"></span>
                            <span x-text="textosDashboard[idioma].statusOnline"></span>
                        </span>
                    </div>

                    <!-- Servicio 2 -->
                    <div style="display: flex; align-items: center; justify-content: space-between; background-color: #1e293b; border: 1px solid #334155; padding: 14px 16px; border-radius: 6px;">
                        <span x-text="textosDashboard[idioma].srvEmail" style="color: #cbd5e1; font-weight: 500;"></span>
                        <span style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #10b981; font-weight: 600;">
                            <span style="width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; display: inline-block;"></span>
                            <span x-text="textosDashboard[idioma].statusOnline"></span>
                        </span>
                    </div>

                    <!-- Servicio 3 -->
                    <div style="display: flex; align-items: center; justify-content: space-between; background-color: #1e293b; border: 1px solid #334155; padding: 14px 16px; border-radius: 6px;">
                        <span x-text="textosDashboard[idioma].srvVpn" style="color: #cbd5e1; font-weight: 500;"></span>
                        <span style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #eab308; font-weight: 600;">
                            <span style="width: 8px; height: 8px; background-color: #eab308; border-radius: 50%; display: inline-block;"></span>
                            <span x-text="textosDashboard[idioma].statusOffline"></span>
                        </span>
                    </div>

                </div>
            </div>

        </div>

    </div>
</x-app-layout>