<x-app-layout>
    <div x-data="{ 
        idioma: localStorage.getItem('ticketus_lang') || 'es',
        
        // Diccionario de traducción para la sección de Tickets
        textosTickets: {
            es: {
                titulo: 'Gestión de Tickets',
                subtitulo: 'Historial y estado actual de tus reportes de soporte.',
                btnCrear: '+ Crear Nuevo Ticket',
                thId: 'ID',
                thAsunto: 'Asunto',
                thCategoria: 'Categoría',
                thEstado: 'Estado',
                thPrioridad: 'Prioridad',
                thAcciones: 'Acciones',
                badgeAbierto: 'Abierto',
                badgeProgreso: 'En Progreso',
                badgeResuelto: 'Resuelto',
                btnVer: 'Ver Detalle',
                sinTickets: 'No se encontraron tickets en el sistema.'
            },
            en: {
                titulo: 'Ticket Management',
                subtitulo: 'History and current status of your support reports.',
                btnCrear: '+ Create New Ticket',
                thId: 'ID',
                thAsunto: 'Subject',
                thCategoria: 'Category',
                thEstado: 'Status',
                thPrioridad: 'Priority',
                thAcciones: 'Actions',
                badgeAbierto: 'Open',
                badgeProgreso: 'In Progress',
                badgeResuelto: 'Resolved',
                btnVer: 'View Details',
                sinTickets: 'No tickets found in the system.'
            }
        }
    }" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">

        <!-- Encabezado de la Sección -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 x-text="textosTickets[idioma].titulo" style="font-size: 28px; font-weight: 700; color: #ffffff; margin-bottom: 6px;"></h1>
                <p x-text="textosTickets[idioma].subtitulo" style="font-size: 15px; color: #94a3b8; margin: 0;"></p>
            </div>
            <!-- Botón de crear ticket dinámico (cambia la ruta si tu ruta de creación tiene otro nombre) -->
            <div>
                <a href="#" 
                   x-text="textosTickets[idioma].btnCrear"
                   style="background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 6px; font-size: 14px; font-weight: 600; display: inline-block; transition: background-color 0.2s;">
                </a>
            </div>
        </div>

        <!-- Tabla de Tickets con soporte multi-idioma -->
        <div style="background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.15);">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="background-color: #0f172a; border-bottom: 1px solid #334155;">
                        <th x-text="textosTickets[idioma].thId" style="padding: 16px; color: #94a3b8; font-weight: 600; width: 80px;"></th>
                        <th x-text="textosTickets[idioma].thAsunto" style="padding: 16px; color: #94a3b8; font-weight: 600;"></th>
                        <th x-text="textosTickets[idioma].thCategoria" style="padding: 16px; color: #94a3b8; font-weight: 600;"></th>
                        <th x-text="textosTickets[idioma].thEstado" style="padding: 16px; color: #94a3b8; font-weight: 600;"></th>
                        <th x-text="textosTickets[idioma].thPrioridad" style="padding: 16px; color: #94a3b8; font-weight: 600;"></th>
                        <th x-text="textosTickets[idioma].thAcciones" style="padding: 16px; color: #94a3b8; font-weight: 600; text-align: center; width: 150px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Fila de ejemplo 1 (Abierto) -->
                    <tr style="border-bottom: 1px solid #334155;">
                        <td style="padding: 16px; color: #f1f5f9; font-family: monospace;">#1024</td>
                        <td style="padding: 16px; color: #f1f5f9; font-weight: 500;">Fallo de conexión a la VPN de la empresa</td>
                        <td style="padding: 16px; color: #cbd5e1;">Redes / IT</td>
                        <td style="padding: 16px;">
                            <span x-text="textosTickets[idioma].badgeAbierto" style="background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;"></span>
                        </td>
                        <td style="padding: 16px; color: #ef4444; font-weight: 600;">Alta</td>
                        <td style="padding: 16px; text-align: center;">
                            <a href="#" x-text="textosTickets[idioma].btnVer" style="color: #3b82f6; text-decoration: none; font-weight: 500;"></a>
                        </td>
                    </tr>

                    <!-- Fila de ejemplo 2 (En Progreso) -->
                    <tr style="border-bottom: 1px solid #334155;">
                        <td style="padding: 16px; color: #f1f5f9; font-family: monospace;">#1025</td>
                        <td style="padding: 16px; color: #f1f5f9; font-weight: 500;">Instalación de nueva licencia de Microsoft Office</td>
                        <td style="padding: 16px; color: #cbd5e1;">Software</td>
                        <td style="padding: 16px;">
                            <span x-text="textosTickets[idioma].badgeProgreso" style="background-color: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;"></span>
                        </td>
                        <td style="padding: 16px; color: #eab308; font-weight: 600;">Media</td>
                        <td style="padding: 16px; text-align: center;">
                            <a href="#" x-text="textosTickets[idioma].btnVer" style="color: #3b82f6; text-decoration: none; font-weight: 500;"></a>
                        </td>
                    </tr>

                    <!-- Fila de ejemplo 3 (Resuelto) -->
                    <tr>
                        <td style="padding: 16px; color: #f1f5f9; font-family: monospace;">#1026</td>
                        <td style="padding: 16px; color: #f1f5f9; font-weight: 500;">Configuración inicial de monitor secundario</td>
                        <td style="padding: 16px; color: #cbd5e1;">Hardware</td>
                        <td style="padding: 16px;">
                            <span x-text="textosTickets[idioma].badgeResuelto" style="background-color: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;"></span>
                        </td>
                        <td style="padding: 16px; color: #94a3b8; font-weight: 600;">Baja</td>
                        <td style="padding: 16px; text-align: center;">
                            <a href="#" x-text="textosTickets[idioma].btnVer" style="color: #3b82f6; text-decoration: none; font-weight: 500;"></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>