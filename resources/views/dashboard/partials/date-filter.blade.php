<!-- FILTRO DE FECHAS: afecta el conteo total y las gráficas de tendencia.
     Las tarjetas/listas "accionables" (vencidos, sin asignar, próximos a
     vencer, aprobaciones pendientes, top agentes del mes) son fotos del
     presente y no cambian con este rango. -->
<form method="GET" action="{{ route('dashboard') }}"
      style="display: flex; flex-wrap: wrap; align-items: end; gap: 12px; background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px;">
    <div>
        <label x-text="textosDashboard[idioma].filtroDesde" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;"></label>
        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
               style="background: #0f172a; border: 1px solid #334155; border-radius: 6px; padding: 8px 10px; color: #ffffff; font-size: 13px;">
    </div>
    <div>
        <label x-text="textosDashboard[idioma].filtroHasta" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;"></label>
        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
               style="background: #0f172a; border: 1px solid #334155; border-radius: 6px; padding: 8px 10px; color: #ffffff; font-size: 13px;">
    </div>
    <button type="submit"
            x-text="textosDashboard[idioma].filtroAplicar"
            style="background: #2563eb; color: #ffffff; border: none; padding: 9px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
    </button>
    <a href="{{ route('dashboard') }}"
       x-text="textosDashboard[idioma].filtroLimpiar"
       style="color: #94a3b8; font-size: 13px; text-decoration: none; padding: 9px 4px;">
    </a>
    <span x-text="textosDashboard[idioma].filtroNota" style="color: #64748b; font-size: 12px; margin-left: auto; align-self: center;"></span>
</form>
