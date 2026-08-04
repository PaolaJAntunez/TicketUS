<!-- DESCARGA DE REPORTE: respeta el filtro de fecha actual (request()->query())
     y el rol (los datos vienen de buildDashboardData(), el mismo método que
     alimenta la pantalla, para admin/agent/user según quién esté logueado). -->
<div style="display:flex; flex-wrap:wrap; justify-content:flex-end; gap:10px; margin-bottom:20px;">
    <a href="{{ route('dashboard.report.pdf', request()->query()) }}"
       style="background:#dc2626; color:#ffffff; padding:9px 16px; border-radius:6px; text-decoration:none; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
        <i class="ti ti-file-type-pdf"></i>
        <span x-text="textosDashboard[idioma].btnDescargarPdf"></span>
    </a>
    <a href="{{ route('dashboard.report.excel', request()->query()) }}"
       style="background:#16a34a; color:#ffffff; padding:9px 16px; border-radius:6px; text-decoration:none; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
        <i class="ti ti-file-spreadsheet"></i>
        <span x-text="textosDashboard[idioma].btnDescargarExcel"></span>
    </a>
</div>
