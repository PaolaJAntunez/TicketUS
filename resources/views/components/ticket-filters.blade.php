<form method="GET" action="{{ url()->current() }}"
      style="background:#fff;
             padding:20px;
             border-radius:10px;
             margin-bottom:25px;
             box-shadow:0 2px 8px rgba(0,0,0,.08);">

    <div style="display:flex;
                flex-wrap:wrap;
                gap:15px;
                align-items:end;">

        {{-- Buscar --}}
        <div style="flex:2;">
            <label style="display:block;margin-bottom:5px;font-weight:600;">
                Buscar
            </label>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Título del ticket..."
                style="width:100%;
                       padding:10px;
                       border:1px solid #d1d5db;
                       border-radius:6px;">
        </div>

        {{-- Categoría --}}
        <div style="flex:1;">
            <label style="display:block;margin-bottom:5px;font-weight:600;">
                Categoría
            </label>

            <select
                name="category"
                style="width:100%;
                       padding:10px;
                       border:1px solid #d1d5db;
                       border-radius:6px;">

                <option value="">Todas</option>

                @foreach($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach

            </select>
        </div>

        {{-- Prioridad --}}
        <div style="flex:1;">
            <label style="display:block;margin-bottom:5px;font-weight:600;">
                Prioridad
            </label>

            <select
                name="priority"
                style="width:100%;
                       padding:10px;
                       border:1px solid #d1d5db;
                       border-radius:6px;">

                <option value="">Todas</option>
                <option value="low" {{ request('priority')=='low'?'selected':'' }}>Baja</option>
                <option value="medium" {{ request('priority')=='medium'?'selected':'' }}>Media</option>
                <option value="high" {{ request('priority')=='high'?'selected':'' }}>Alta</option>
                <option value="urgent" {{ request('priority')=='urgent'?'selected':'' }}>Urgente</option>

            </select>
        </div>

        {{-- Estado --}}
        <div style="flex:1;">
            <label style="display:block;margin-bottom:5px;font-weight:600;">
                Estado
            </label>

            <select
                name="status"
                style="width:100%;
                       padding:10px;
                       border:1px solid #d1d5db;
                       border-radius:6px;">

                <option value="">Todos</option>

                <option value="open" {{ request('status')=='open'?'selected':'' }}>
                    Abierto
                </option>

                <option value="assigned" {{ request('status')=='assigned'?'selected':'' }}>
                    Asignado
                </option>

                <option value="pending_approval" {{ request('status')=='pending_approval'?'selected':'' }}>
                    Pendiente
                </option>

                <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>
                    En Progreso
                </option>

                <option value="resolved" {{ request('status')=='resolved'?'selected':'' }}>
                    Resuelto
                </option>

                <option value="closed" {{ request('status')=='closed'?'selected':'' }}>
                    Cerrado
                </option>

            </select>
        </div>

        {{-- Botones --}}
        <div style="display:flex; gap:10px; align-items:flex-end;">

    <button
        type="submit"
        style="background:#1e3a5f;
               color:white;
               border:none;
               padding:10px 18px;
               border-radius:6px;
               cursor:pointer;
               height:42px;">
        Buscar
    </button>

    <a href="{{ url()->current() }}"
       style="background:#6b7280;
              color:white;
              padding:10px 18px;
              border-radius:6px;
              text-decoration:none;
              display:flex;
              align-items:center;
              justify-content:center;
              height:42px;
              box-sizing:border-box;">
        Limpiar
    </a>

</div>

    </div>

</form>