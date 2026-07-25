@props(['categories', 'agents'])

<form method="GET"
      action="{{ url()->current() }}"
      style="width:100%;
             background:#fff;
             padding:20px;
             border-radius:10px;
             margin-bottom:20px;
             box-shadow:0 2px 8px rgba(0,0,0,.08);
             box-sizing:border-box;">

    <div style="display:flex;
                flex-wrap:wrap;
                gap:15px;
                align-items:end;">

        {{-- Buscar --}}
        <div style="flex:2; min-width:180px;">

            <label x-text="textosTickets[idioma].lblBuscar"
                   style="display:block;
                          margin-bottom:5px;
                          font-weight:600;">
            </label>

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   x-bind:placeholder="textosTickets[idioma].phBuscar"
                   style="width:100%;
                          padding:10px;
                          border:1px solid #d1d5db;
                          border-radius:6px;
                          box-sizing:border-box;">

        </div>

        {{-- Categoría / Aplicación --}}
        <div style="flex:1; min-width:160px;">

            <label x-text="textosTickets[idioma].lblCategoria"
                   style="display:block;
                          margin-bottom:5px;
                          font-weight:600;">
            </label>

            <select name="category"
                    style="width:100%;
                           padding:10px;
                           border:1px solid #d1d5db;
                           border-radius:6px;
                           box-sizing:border-box;">

                <option value="" x-text="textosTickets[idioma].optTodas"></option>

                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach

            </select>

        </div>

        {{-- Estado --}}
        <div style="flex:1; min-width:160px;">

            <label x-text="textosTickets[idioma].lblEstado"
                   style="display:block;
                          margin-bottom:5px;
                          font-weight:600;">
            </label>

            <select name="status"
                    style="width:100%;
                           padding:10px;
                           border:1px solid #d1d5db;
                           border-radius:6px;
                           box-sizing:border-box;">

                <option value="" x-text="textosTickets[idioma].optTodos"></option>
                <option value="open" {{ request('status')=='open'?'selected':'' }} x-text="textosTickets[idioma].estadoAbierto"></option>
                <option value="assigned" {{ request('status')=='assigned'?'selected':'' }} x-text="textosTickets[idioma].estadoAsignado"></option>
                <option value="pending_approval" {{ request('status')=='pending_approval'?'selected':'' }} x-text="textosTickets[idioma].estadoPendiente"></option>
                <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }} x-text="textosTickets[idioma].estadoProgreso"></option>
                <option value="on_hold" {{ request('status')=='on_hold'?'selected':'' }} x-text="textosTickets[idioma].estadoEnEspera"></option>
                <option value="resolved" {{ request('status')=='resolved'?'selected':'' }} x-text="textosTickets[idioma].estadoResuelto"></option>
                <option value="closed" {{ request('status')=='closed'?'selected':'' }} x-text="textosTickets[idioma].estadoCerrado"></option>
                <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rechazado</option>
                <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }} x-text="textosTickets[idioma].estadoCancelado"></option>

            </select>

        </div>

        {{-- Asignado a --}}
        <div style="flex:1; min-width:160px;">

            <label style="display:block; margin-bottom:5px; font-weight:600;">
                Asignado a
            </label>

            <select name="assigned_to"
                    style="width:100%;
                           padding:10px;
                           border:1px solid #d1d5db;
                           border-radius:6px;
                           box-sizing:border-box;">

                <option value="">Todos</option>
                <option value="0" {{ request('assigned_to') === '0' ? 'selected' : '' }}>Sin asignar</option>

                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ request('assigned_to') == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }}
                    </option>
                @endforeach

            </select>

        </div>

        {{-- Botones --}}
        <div style="display:flex;
                    gap:10px;
                    align-items:flex-end;">

            <button type="submit"
                    x-text="textosTickets[idioma].btnBuscar"
                    style="background:#1e3a5f;
                           color:white;
                           border:none;
                           padding:10px 18px;
                           border-radius:6px;
                           cursor:pointer;
                           height:42px;">
            </button>

            <a href="{{ url()->current() }}"
               x-text="textosTickets[idioma].btnLimpiar"
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
            </a>

        </div>

    </div>

</form>
