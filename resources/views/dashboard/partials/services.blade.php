<!-- ESTADO DE LOS SERVICIOS -->
            <div>
                <h2 x-text="textosDashboard[idioma].tituloStatus" style="color: #ffffff; font-size: 20px; font-weight: 600; margin-bottom: 16px;"></h2>
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