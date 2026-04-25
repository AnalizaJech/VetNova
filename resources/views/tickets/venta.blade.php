<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ str_pad((string)$venta->id, 6, '0', STR_PAD_LEFT) }} - VetNova</title>
    <style>
        /* Reset y estilos base para ticketera térmica */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 10px;
            width: 80mm; /* Ancho estándar de ticketera */
            margin: 0 auto;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        .header {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 16px;
            margin: 0 0 5px 0;
        }

        .header p { margin: 2px 0; }

        .info { margin-bottom: 10px; }
        .info p { margin: 2px 0; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            text-align: left;
        }

        td {
            padding: 4px 0;
            vertical-align: top;
        }

        .totals {
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-bottom: 15px;
        }

        .totals p {
            margin: 3px 0;
            display: flex;
            justify-content: space-between;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        /* Ocultar elementos UI en la impresión */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }

        /* Botón de volver para la pantalla normal */
        .btn-volver {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #0ea5e9;
            color: #fff;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
            font-family: sans-serif;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body onload="window.print()">

    <a href="{{ route('caja') }}" class="btn-volver no-print">← Volver a la Caja</a>

    <div class="header text-center">
        <h1>VETNOVA</h1>
        <p>{{ auth()->user()->clinica->nombre ?? 'Clínica Veterinaria' }}</p>
        <p>RUC: 20123456789</p> <!-- Ejemplo, luego se jala de la clinica -->
        <p>Tel: {{ auth()->user()->clinica->telefono ?? '---' }}</p>
    </div>

    <div class="info">
        <p><span class="font-bold">Comprobante:</span> {{ $venta->tipo_comprobante }}</p>
        <p><span class="font-bold">Nro:</span> {{ $venta->serie_correlativo ?? str_pad((string)$venta->id, 8, '0', STR_PAD_LEFT) }}</p>
        <p><span class="font-bold">Fecha:</span> {{ $venta->created_at->format('d/m/Y H:i') }}</p>
        <p><span class="font-bold">Cajero:</span> {{ $venta->cajero->name }}</p>
        <p><span class="font-bold">Cliente:</span> {{ $venta->cliente->nombres ?? 'PÚBLICO GENERAL' }}</p>
        @if($venta->cliente && $venta->cliente->numero_documento)
            <p><span class="font-bold">Doc:</span> {{ $venta->cliente->numero_documento }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Cant</th>
                <th style="width: 55%">Descripción</th>
                <th style="width: 30%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
                <tr>
                    <td class="text-center">{{ $detalle->cantidad }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($detalle->descripcion, 20) }}</td>
                    <td class="text-right">S/ {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p>
            <span>Subtotal:</span>
            <span>S/ {{ number_format($venta->subtotal, 2) }}</span>
        </p>
        <p>
            <span>IGV (18%):</span>
            <span>S/ {{ number_format($venta->igv, 2) }}</span>
        </p>
        <p class="font-bold" style="font-size: 14px; margin-top: 5px;">
            <span>TOTAL:</span>
            <span>S/ {{ number_format($venta->total, 2) }}</span>
        </p>
    </div>

    <div class="info">
        <p><span class="font-bold">Método de Pago:</span> {{ $venta->metodo_pago }}</p>
    </div>

    <div class="footer">
        @if($venta->nubefact_enlace_pdf)
            <p style="margin-bottom: 5px;">Representación Impresa de la {{ $venta->tipo_comprobante }} ELECTRÓNICA</p>
            <p>Descargue su comprobante en:</p>
            <p style="font-size: 9px; word-break: break-all;">{{ $venta->nubefact_enlace_pdf }}</p>
            <br>
        @endif
        <p>¡Gracias por confiar en nosotros!</p>
        <p>Software veterinario por VetNova.</p>
    </div>

</body>
</html>
