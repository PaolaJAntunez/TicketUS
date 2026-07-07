<x-app-layout>
    <div style="max-width: 700px; margin: 40px auto; padding: 0 20px;">
        <div x-data="{ 
            idioma: localStorage.getItem('ticketus_lang') || 'es',
            notifEmail: true,
            notifSonido: false,
            filtroDefecto: 'todos',
            
            textos: {
                es: {
                    titulo: 'Configuración del Sistema',
                    subtitulo: 'Personaliza tu experiencia dentro de TicketUS.',
                    seccionIdioma: 'Preferencias de Idioma',
                    seleccionaIdioma: 'Selecciona el idioma de la interfaz:',
                    seccionNotif: 'Notificaciones',
                    alertasEmail: 'Recibir alertas por correo electrónico',
                    alertasSonido: 'Activar sonido para nuevos tickets',
                    seccionFiltros: 'Preferencias de Tickets',
                    lblFiltros: 'Vista predeterminada al entrar a Tickets:',
                    optTodos: 'Todos los tickets',
                    optAsignados: 'Solo mis tickets asignados',
                    seccionSeguridad: 'Seguridad de la Cuenta',
                    passActual: 'Contraseña Actual',
                    passNueva: 'Nueva Contraseña',
                    placeHolderPass: 'Escribe tu contraseña',
                    btnGuardar: 'Guardar Configuración',
                    alerta: '¡Configuración guardada!',
                    seccionAcerca: 'Acerca de TicketUS',
                    version: 'Versión del Software:',
                    desarrollado: 'Desarrollado por:',
                    equipo: 'Equipo de Ingeniería TicketUS',
                    licencia: 'Licencia:',
                    estadoLicencia: 'Activa (Entorno de Desarrollo)'
                },
                en: {
                    titulo: 'System Settings',
                    subtitulo: 'Customize your experience within TicketUS.',
                    seccionIdioma: 'Language Preferences',
                    seleccionaIdioma: 'Select interface language:',
                    seccionNotif: 'Notifications',
                    alertasEmail: 'Receive email alerts',
                    alertasSonido: 'Enable sound for new tickets',
                    seccionFiltros: 'Ticket Preferences',
                    lblFiltros: 'Default view when entering Tickets:',
                    optTodos: 'All tickets',
                    optAsignados: 'Only my assigned tickets',
                    seccionSeguridad: 'Account Security',
                    passActual: 'Current Password',
                    passNueva: 'New Password',
                    placeHolderPass: 'Enter your password',
                    btnGuardar: 'Save Settings',
                    alerta: 'Settings saved!',
                    seccionAcerca: 'About TicketUS',
                    version: 'Software Version:',
                    desarrollado: 'Developed by:',
                    equipo: 'TicketUS Engineering Team',
                    licencia: 'License:',
                    estadoLicencia: 'Active (Development Environment)'
                }
            },
            
            guardar() {
                localStorage.setItem('ticketus_lang', this.idioma);
                alert(this.textos[this.idioma].alerta);
                window.location.reload(); // Recarga la app para aplicar el idioma globalmente
            }
        }"
        style="background-color: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
            
            <h2 x-text="textos[idioma].titulo" style="color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 8px;"></h2>
            <p x-text="textos[idioma].subtitulo" style="color: #94a3b8; font-size: 14px; margin-bottom: 32px; border-bottom: 1px solid #334155; padding-bottom: 16px;"></p>

            <!-- 1. IDIOMA -->
            <div style="margin-bottom: 28px;">
                <h3 x-text="textos[idioma].seccionIdioma" style="color: #3b82f6; font-size: 16px; font-weight: 600; margin-bottom: 12px;"></h3>
                <label x-text="textos[idioma].seleccionaIdioma" style="display: block; color: #cbd5e1; font-size: 14px; margin-bottom: 8px;"></label>
                <select x-model="idioma" style="width: 100%; max-width: 200px; background-color: #0f172a; color: #f1f5f9; border: 1px solid #475569; border-radius: 6px; padding: 10px; font-size: 14px; outline: none; cursor: pointer;">
                    <option value="es">Español (ES)</option>
                    <option value="en">English (EN)</option>
                </select>
            </div>

            <!-- 2. NOTIFICACIONES -->
            <div style="margin-bottom: 28px; border-top: 1px solid #334155; padding-top: 20px;">
                <h3 x-text="textos[idioma].seccionNotif" style="color: #3b82f6; font-size: 16px; font-weight: 600; margin-bottom: 16px;"></h3>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <span x-text="textos[idioma].alertasEmail" style="color: #cbd5e1; font-size: 14px;"></span>
                    <input type="checkbox" x-model="notifEmail" style="width: 40px; height: 20px; cursor: pointer;">
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span x-text="textos[idioma].alertasSonido" style="color: #cbd5e1; font-size: 14px;"></span>
                    <input type="checkbox" x-model="notifSonido" style="width: 40px; height: 20px; cursor: pointer;">
                </div>
            </div>

            <!-- 3. PREFERENCIAS DE TICKETS -->
            <div style="margin-bottom: 28px; border-top: 1px solid #334155; padding-top: 20px;">
                <h3 x-text="textos[idioma].seccionFiltros" style="color: #3b82f6; font-size: 16px; font-weight: 600; margin-bottom: 12px;"></h3>
                <label x-text="textos[idioma].lblFiltros" style="display: block; color: #cbd5e1; font-size: 14px; margin-bottom: 8px;"></label>
                <select x-model="filtroDefecto" style="width: 100%; max-width: 250px; background-color: #0f172a; color: #f1f5f9; border: 1px solid #475569; border-radius: 6px; padding: 10px; font-size: 14px; outline: none; cursor: pointer;">
                    <option value="todos" x-text="textos[idioma].optTodos"></option>
                    <option value="asignados" x-text="textos[idioma].optAsignados"></option>
                </select>
            </div>

            <!-- 4. SEGURIDAD -->
            <div style="margin-bottom: 32px; border-top: 1px solid #334155; padding-top: 20px;">
                <h3 x-text="textos[idioma].seccionSeguridad" style="color: #3b82f6; font-size: 16px; font-weight: 600; margin-bottom: 16px;"></h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label x-text="textos[idioma].passActual" style="display: block; color: #cbd5e1; font-size: 13px; margin-bottom: 6px;"></label>
                        <input type="password" :placeholder="textos[idioma].placeHolderPass" style="width: 100%; background-color: #0f172a; color: #f1f5f9; border: 1px solid #475569; border-radius: 6px; padding: 10px; font-size: 14px; outline: none;">
                    </div>
                    <div>
                        <label x-text="textos[idioma].passNueva" style="display: block; color: #cbd5e1; font-size: 13px; margin-bottom: 6px;"></label>
                        <input type="password" :placeholder="textos[idioma].placeHolderPass" style="width: 100%; background-color: #0f172a; color: #f1f5f9; border: 1px solid #475569; border-radius: 6px; padding: 10px; font-size: 14px; outline: none;">
                    </div>
                </div>
            </div>

            <!-- BOTÓN GUARDAR -->
            <button type="button" 
                    @click="guardar()"
                    x-text="textos[idioma].btnGuardar"
                    style="width: 100%; background-color: #10b981; color: #ffffff; font-size: 14px; font-weight: 600; padding: 12px; border: none; border-radius: 6px; cursor: pointer; transition: background-color 0.2s; margin-bottom: 32px;">
            </button>

            <!-- 5. SECCIÓN ACERCA DE -->
            <div style="border-top: 1px dashed #475569; padding-top: 24px; background-color: #0f172a; padding: 20px; border-radius: 6px; border: 1px solid #334155;">
                <h3 x-text="textos[idioma].seccionAcerca" style="color: #94a3b8; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0; margin-bottom: 12px;"></h3>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span x-text="textos[idioma].version" style="color: #64748b;"></span>
                        <span style="color: #cbd5e1; font-family: monospace; font-weight: 600;">v1.4.2</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span x-text="textos[idioma].desarrollado" style="color: #64748b;"></span>
                        <span x-text="textos[idioma].equipo" style="color: #cbd5e1;"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span x-text="textos[idioma].licencia" style="color: #64748b;"></span>
                        <span x-text="textos[idioma].estadoLicencia" style="color: #10b981; font-weight: 500;"></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>