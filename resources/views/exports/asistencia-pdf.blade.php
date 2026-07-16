<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Asistencia</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #222; }
        h1 { background: #1C1C2E; color: white; padding: 10px; text-align: center; font-size: 14px; }
        .info { color: #888; font-size: 9px; margin-bottom: 10px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1C1C2E; color: white; padding: 6px; font-size: 8px; text-align: center; }
        td { padding: 4px 6px; border: 1px solid #DDD; font-size: 8px; text-align: center; }
        tr:nth-child(even) { background: #F5F5F5; }
        .nombre-cell { text-align: left; }
    </style>
</head>
<body>
    <h1>REPORTE DE ASISTENCIA</h1>
    <div class="info">Fecha: {{ $fecha }} | Total: {{ count($rows) }} registros</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>NOMBRE</th>
                <th>FECHA</th>
                <th>H. ENTRADA</th>
                <th>H. SALIDA</th>
                <th>TARDANZA (min)</th>
                <th>H. TRABAJADAS</th>
                <th>ETIQUETA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $a)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="nombre-cell">{{ $a['persona_nombre'] }}</td>
                    <td>{{ $a['fecha'] }}</td>
                    <td>{{ $a['hora_entrada'] }}</td>
                    <td>{{ $a['hora_salida'] }}</td>
                    <td>{{ $a['tardanza_min'] }}</td>
                    <td>{{ number_format($a['horas_trabajadas'], 1) }}h</td>
                    <td style="font-weight:bold">{{ $a['etiqueta'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
