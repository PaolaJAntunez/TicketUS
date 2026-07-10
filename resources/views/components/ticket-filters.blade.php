@props(['categories'])

<form method="GET"
      action="{{ url()->current() }}"
      style="width:100%;
             background:#fff;
             padding:20px;
             border-radius:10px;
             margin-bottom:25px;
             box-shadow:0 2px 8px rgba(0,0,0,.08);
             box-sizing:border-box;">

    <div style="display:flex;
                flex-wrap:wrap;
                gap:15px;
                align-items:end;">

        {{-- Buscar --}}
        <div style="flex:2;">

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
                          border-radius:6px;">

        </div>

        {{-- Categoría --}}
        <div style="flex:1;">

            <label x-text="textosTickets[idioma].lblCategoria"
                   style="display:block;
                          margin-bottom:5px;
                          font-weight:600;">
            </label>

            <select name="category"
                    style="width:100%;
                           padding:10px;
                           border:1px solid #d1d5db;
                           border-radius:6px;">

                <option value=""
                        x-text="textosTickets[idioma].optTodas">
                </option>

                @foreach($categories as $category)

                    <option value="{{ $category->id }}"
                            {{ request('category') == $category->id ? 'selected' : '' }}>

                        {{ $category->name }}

                    </option>

                @endforeach

            </select>

        </div>

        {{-- Prioridad --}}
        <div style="flex:1;">

            <label x-text="textosTickets[idioma].lblPrioridad"
                   style="display:block;
                          margin-bottom:5px;
                          font-weight:600;">
            </label>

            <select name="priority"
                    style="width:100%;
                           padding:10px;
                           border:1px solid #d1d5db;
                           border-radius:6px;">

                <option value=""
                        x-text="textosTickets[idioma].optTodas">
                </option>

                <option value="low"
                        {{ request('priority')=='low'?'selected':'' }}
                        x-text="textosTickets[idioma].prioridadBaja">
                </option>

                <option value="medium"
                        {{ request('priority')=='medium'?'selected':'' }}
                        x-text="textosTickets[idioma].prioridadMedia">
                </option>

                <option value="high"
                        {{ request('priority')=='high'?'selected':'' }}
                        x-text="textosTickets[idioma].prioridadAlta">
                </option>

                <option value="urgent"
                        {{ request('priority')=='urgent'?'selected':'' }}
                        x-text="textosTickets[idioma].prioridadUrgente">
                </option>

            </select>

        </div>

        {{-- Estado --}}
        <div style="flex:1;">

            <label x-text="textosTickets[idioma].lblEstado"
                   style="display:block;
                          margin-bottom:5px;
                          font-weight:600;">
            </label>

            <select name="status"
                    style="width:100%;
                           padding:10px;
                           border:1px solid #d1d5db;
                           border-radius:6px;">

                <option value=""
                        x-text="textosTickets[idioma].optTodos">
                </option>

                <option value="open"
                        {{ request('status')=='open'?'selected':'' }}
                        x-text="textosTickets[idioma].estadoAbierto">
                </option>

                <option value="assigned"
                        {{ request('status')=='assigned'?'selected':'' }}
                        x-text="textosTickets[idioma].estadoAsignado">
                </option>

                <option value="pending_approval"
                        {{ request('status')=='pending_approval'?'selected':'' }}
                        x-text="textosTickets[idioma].estadoPendiente">
                </option>

                <option value="in_progress"
                        {{ request('status')=='in_progress'?'selected':'' }}
                        x-text="textosTickets[idioma].estadoProgreso">
                </option>

                <option value="resolved"
                        {{ request('status')=='resolved'?'selected':'' }}
                        x-text="textosTickets[idioma].estadoResuelto">
                </option>

                <option value="closed"
                        {{ request('status')=='closed'?'selected':'' }}
                        x-text="textosTickets[idioma].estadoCerrado">
                </option>

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