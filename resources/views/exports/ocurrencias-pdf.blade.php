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
        td { padding: 4px 6px; border: 1px solid #DDD; font-size: 8px; text-align: center; }
        tr:nth-child(even) { background: #F5F5F5; }
        .left { text-align: left; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <h1>REPORTE DE OCURRENCIAS</h1>
    <div class="info">Filtro: {{ $filtro }} | Total: {{ count($rows) }} registros</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    @foreach($columns as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $oc)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        @foreach($columns as $key => $label)
                            @php
                                $val = $oc[$key] ?? '';
                                $isLeft = in_array($key, ['persona_nombre', 'detalles', 'observacion']);
                                $isBold = $key === 'tipo';
                            @endphp
                            <td class="{{ $isLeft ? 'left' : '' }} {{ $isBold ? 'bold' : '' }}">
                                @if(in_array($key, ['detalles', 'observacion']))
                                    {{ Str::limit($val, $key === 'detalles' ? 80 : 60) ?: '—' }}
                                @else
                                    {{ $val ?: '—' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
</body>
</html>
