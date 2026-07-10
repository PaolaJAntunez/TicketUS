
    <div x-data="{ 
        idioma: localStorage.getItem('ticketus_lang') || 'es',
        
        textosDashboard: {
            es: {
                bienvenida: '¡Bienvenido de nuevo a TicketUS!',
                subtitulo: 'Aquí tienes un resumen del estado de tus soportes hoy.',
                tarjeta1Titulo: '🎫Tickets Abiertos',
                tarjeta1Desc: 'Esperando revisión o asignación.',
                tarjeta2Titulo: '⏳En Progreso',
                tarjeta2Desc: 'Soportes bajo atención técnica activa.',
                tarjeta3Titulo: 'Resueltos',
                tarjeta3Desc: '✅Cerrados con éxito esta semana.',
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
