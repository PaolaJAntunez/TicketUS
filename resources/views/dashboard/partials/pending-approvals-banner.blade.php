@if(($myPendingApprovals ?? 0) > 0)
    <a href="{{ route('approvals.index') }}"
       style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px 16px; background: #78350f; border: 1px solid #b45309; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px; text-decoration: none;">
        <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
            <span style="background: #b45309; color: #fff; width: 36px; height: 36px; min-width: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px;">
                {{ $myPendingApprovals }}
            </span>
            <div>
                <p x-text="textosDashboard[idioma].aprobacionesPendientesTitulo" style="margin: 0; color: #fff; font-weight: 700; font-size: 15px;"></p>
                <p style="margin: 2px 0 0 0; color: #fde68a; font-size: 13px;">
                    {{ $myPendingApprovals }} <span x-text="textosDashboard[idioma].aprobacionesPendientesDesc"></span>
                </p>
            </div>
        </div>
        <span x-text="textosDashboard[idioma].aprobacionesPendientesBoton"
              style="background: #fde68a; color: #78350f; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 700; white-space: nowrap;">
        </span>
    </a>
@endif
