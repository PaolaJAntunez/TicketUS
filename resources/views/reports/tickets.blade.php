<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:12px;
        }

        h2{
            text-align:center;
            margin-bottom:5px;
        }

        p{
            text-align:center;
            margin-bottom:20px;
        }

        table{

            width:100%;
            border-collapse:collapse;

        }

        th{

            background:#1e3a5f;
            color:white;

        }

        th,td{

            border:1px solid #ccc;

            padding:8px;

            text-align:left;

        }

        tr:nth-child(even){

            background:#f5f5f5;

        }

    </style>

</head>

<body>

<h2>Reporte de Tickets</h2>

<p>

Generado el {{ now()->format('d/m/Y H:i') }}

</p>

<table>

    <thead>

        <tr>

            <th>ID</th>

            <th>Título</th>

            <th>Categoría</th>

            <th>Prioridad</th>

            <th>Estado</th>

            <th>Fecha</th>

        </tr>

    </thead>

    <tbody>

    @foreach($tickets as $ticket)

        <tr>

            <td>{{ $ticket->id }}</td>

            <td>{{ $ticket->title }}</td>

            <td>{{ $ticket->category->name }}</td>

            <td>{{ ucfirst($ticket->priority) }}</td>

            <td>{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</td>

            <td>{{ $ticket->created_at->format('d/m/Y') }}</td>

        </tr>

    @endforeach

    </tbody>

</table>

</body>
</html>