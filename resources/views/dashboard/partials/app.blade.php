
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
                sol6: 'Políticas de Ciberseguridad para Usuarios',

                filtroDesde: 'Desde',
                filtroHasta: 'Hasta',
                filtroAplicar: 'Aplicar',
                filtroLimpiar: 'Limpiar',
                filtroNota: 'Afecta el total y las gráficas de tendencia',

                aprobacionesPendientesTitulo: 'Tienes aprobaciones pendientes',
                aprobacionesPendientesDesc: 'aprobación(es) esperando tu revisión.',
                aprobacionesPendientesBoton: 'Ver aprobaciones',

                cardTotalTitulo: 'Total de Tickets',
                cardTotalDesc: 'Creados en el rango',
                cardVencidosTitulo: 'Tickets Vencidos',
                cardVencidosDesc: 'Pasaron su fecha límite sin resolverse.',
                cardPromedioTitulo: 'Tiempo Prom. Resolución',
                cardPromedioDesc: 'Horas promedio hasta resolver un ticket.',
                cardAprobSistemaTitulo: 'Aprobaciones Pendientes',
                cardAprobSistemaDesc: 'En todo el sistema, esperando revisión.',

                topAgentesTitulo: 'Top 5 Agentes (resueltos este mes)',
                sinAsignarTitulo: 'Tickets Sin Asignar (+24h)',
                proximosVencerTitulo: 'Próximos a Vencer (24h)',
                misAsignadosTitulo: 'Mis Tickets Asignados',
                misAsignadosVencidos: 'vencido(s) entre ellos',

                ultimosCreadosTitulo: 'Últimos Tickets Creados',
                ultimosResueltosTitulo: 'Últimos Tickets Resueltos',
                sinDatos: 'Sin datos por ahora.',

                cardReabiertosTitulo: 'Tickets Reabiertos',
                cardReabiertosDesc: 'En el rango de fechas seleccionado.',
                cardTendenciaTitulo: 'Tendencia Semanal',
                cardTendenciaVs: 'esta semana vs.',

                resolucionCategoriaTitulo: 'Resolución Promedio por Categoría',
                prioridadMesTitulo: 'Tickets por Prioridad y Mes',
                topCategoriasTitulo: 'Top 5 Categorías con Más Incidentes',
                colCategoria: 'Categoría',
                colPromedio: 'Promedio',
                colResueltos: 'Resueltos',

                btnDescargarPdf: 'Descargar PDF',
                btnDescargarExcel: 'Descargar Excel'
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
                sol6: 'Cybersecurity Policies for Users',

                filtroDesde: 'From',
                filtroHasta: 'To',
                filtroAplicar: 'Apply',
                filtroLimpiar: 'Clear',
                filtroNota: 'Affects the total and trend charts',

                aprobacionesPendientesTitulo: 'You have pending approvals',
                aprobacionesPendientesDesc: 'approval(s) waiting for your review.',
                aprobacionesPendientesBoton: 'View approvals',

                cardTotalTitulo: 'Total Tickets',
                cardTotalDesc: 'Created within the range',
                cardVencidosTitulo: 'Overdue Tickets',
                cardVencidosDesc: 'Past their due date, still unresolved.',
                cardPromedioTitulo: 'Avg. Resolution Time',
                cardPromedioDesc: 'Average hours to resolve a ticket.',
                cardAprobSistemaTitulo: 'Pending Approvals',
                cardAprobSistemaDesc: 'System-wide, awaiting review.',

                topAgentesTitulo: 'Top 5 Agents (resolved this month)',
                sinAsignarTitulo: 'Unassigned Tickets (+24h)',
                proximosVencerTitulo: 'Due Soon (24h)',
                misAsignadosTitulo: 'My Assigned Tickets',
                misAsignadosVencidos: 'overdue among them',

                ultimosCreadosTitulo: 'Latest Created Tickets',
                ultimosResueltosTitulo: 'Latest Resolved Tickets',
                sinDatos: 'No data yet.',

                cardReabiertosTitulo: 'Reopened Tickets',
                cardReabiertosDesc: 'Within the selected date range.',
                cardTendenciaTitulo: 'Weekly Trend',
                cardTendenciaVs: 'this week vs.',

                resolucionCategoriaTitulo: 'Average Resolution Time by Category',
                prioridadMesTitulo: 'Tickets by Priority and Month',
                topCategoriasTitulo: 'Top 5 Categories with Most Incidents',
                colCategoria: 'Category',
                colPromedio: 'Average',
                colResueltos: 'Resolved',

                btnDescargarPdf: 'Download PDF',
                btnDescargarExcel: 'Download Excel'
            }
        }
    }" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
