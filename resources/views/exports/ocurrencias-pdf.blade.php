<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ocurrencias</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #222; }
        h1 { background: #1C1C2E; color: white; padding: 10px; text-align: center; font-size: 14px; }
        .info { color: #888; font-size: 9px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1C1C2E; color: white; padding: 6px; font-size: 8px; text-align: center; }
        td { padding: 4px 6px; border: 1px solid #DDD; font-size: 8px; }
        tr:nth-child(even) { background: #F5F5F5; }
        .tipo-cell { font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <h1>REPORTE DE OCURRENCIAS</h1>
    <div class="info">Filtro: {{ $filtro }} | Total: {{ count($rows) }} registros</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>FECHA</th>
                    <th>H. INGRESO</th>
                    <th>H. SALIDA</th>
                    <th>NOMBRE</th>
                    <th>DETALLES</th>
                    <th>OBSERVACIÓN</th>
                    <th>CARGO</th>
                    <th>TIPO</th>
                    <th>OTRO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $oc)
                    <tr>
                        <td style="text-align:center">{{ $i + 1 }}</td>
                        <td style="text-align:center">{{ $oc['fecha'] }}</td>
                        <td style="text-align:center">{{ $oc['hora_ingreso'] }}</td>
                        <td style="text-align:center">{{ $oc['hora_salida'] }}</td>
                        <td>{{ $oc['persona_nombre'] }}</td>
                        <td>{{ Str::limit($oc['detalles'] ?? '', 80) }}</td>
                        <td>{{ Str::limit($oc['observacion'] ?? '', 60) }}</td>
                        <td style="text-align:center">{{ $oc['persona_cargo'] ?? '—' }}</td>
                        <td class="tipo-cell">{{ $oc['tipo'] }}</td>
                        <td style="text-align:center">{{ $oc['otro'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
</body>
</html>
