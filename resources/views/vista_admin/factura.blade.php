<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $venta['numero_factura'] ?? ('VENTA-' . $venta['id_venta']) }}</title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: 'Courier New', monospace;
        background: #e5e5e5;
        margin: 0;
        padding: 20px 0;
    }
    .ticket {
        width: 300px;
        margin: 0 auto;
        background: #fff;
        padding: 18px 16px;
        border: 1px solid #ccc;
    }
    .centrado { text-align: center; }
    .negocio-nombre {
        font-size: 1.4rem;
        font-weight: bold;
        letter-spacing: 2px;
    }
    .negocio-sub {
        font-size: .7rem;
        color: #444;
    }
    hr {
        border: none;
        border-top: 1px dashed #000;
        margin: 10px 0;
    }
    table { width: 100%; border-collapse: collapse; font-size: .72rem; }
    th { text-align: left; border-bottom: 1px solid #000; padding-bottom: 3px; }
    td { padding: 3px 0; vertical-align: top; }
    .num { text-align: right; }
    .totales td { padding: 2px 0; }
    .totales .label { font-weight: bold; }
    .gran-total { font-size: 1rem; font-weight: bold; }
    .footer-msg { font-size: .68rem; text-align: center; margin-top: 12px; }

    .acciones-comprobante {
        width: 300px;
        margin: 14px auto 0;
        display: flex;
        gap: 8px;
        font-family: sans-serif;
    }
    .btn-volver {
        flex: 1;
        padding: 10px;
        background: #fff;
        color: #01614B;
        border: 1.5px solid #00875F;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: .85rem;
    }
    .btn-volver:hover { background: #DDF5EC; }
    .btn-imprimir {
        flex: 1;
        padding: 10px;
        background: #00875F;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        font-family: sans-serif;
        font-size: .85rem;
    }
    .btn-imprimir:hover { background: #01614B; }

    @media print {
        body { background: #fff; padding: 0; }
        .acciones-comprobante { display: none; }
        .ticket { border: none; }
        @page { margin: 5mm; }
    }
</style>
</head>
<body>

    <div class="ticket">

        <div class="centrado">
            <div class="negocio-nombre">VentaNet</div>
            <div class="negocio-sub">Sistema de gestión comercial</div>
        </div>

        <hr>

        <div style="font-size:.75rem;">
            <div><strong>Comprobante:</strong> {{ $venta['numero_factura'] ?? '---' }}</div>
            <div><strong>Venta N°:</strong> {{ $venta['id_venta'] }}</div>
            <div><strong>Fecha:</strong> {{ \Illuminate\Support\Carbon::parse($venta['fecha'])->format('d/m/Y H:i') }}</div>
            <div><strong>Cliente:</strong> {{ $venta['cliente'] ?? 'Cliente final' }}</div>
            <div><strong>Atendió:</strong> {{ $venta['vendedor'] ?? '---' }}</div>
        </div>

        <hr>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="num">Cant.</th>
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalle as $d)
                <tr>
                    <td>
                        {{ $d['producto'] }}
                        <br>
                        <span style="font-size:.65rem;color:#555;">
                            ${{ number_format($d['precio_venta'], 0, ',', '.') }} c/u
                            @if (!empty($d['descuento_valor']) && $d['descuento_valor'] > 0)
                                · Desc: ${{ number_format($d['descuento_valor'], 0, ',', '.') }}
                            @endif
                        </span>
                    </td>
                    <td class="num">{{ $d['cantidad'] }}</td>
                    <td class="num">${{ number_format($d['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr>

        <table class="totales">
            <tr>
                <td class="label">Subtotal</td>
                <td class="num">${{ number_format($venta['factura_subtotal'] ?? $venta['total'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Descuento</td>
                <td class="num">${{ number_format($venta['factura_descuento'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="gran-total">
                <td class="label">TOTAL</td>
                <td class="num">${{ number_format($venta['total'], 0, ',', '.') }}</td>
            </tr>
        </table>

        <hr>

        <div style="font-size:.72rem;">
            <strong>Forma de pago:</strong> {{ ucfirst($venta['metodo_pago'] ?? 'Efectivo') }}
        </div>

        @if (!$venta['estado'])
        <div class="centrado" style="margin-top:10px;color:#E53935;font-weight:bold;font-size:.8rem;">
            *** VENTA ANULADA ***
        </div>
        @endif

        <div class="footer-msg">
            ¡Gracias por su compra!<br>
            Generado por VentaNet
        </div>

    </div>

    <div class="acciones-comprobante">
        <a href="{{ route('admin.ventas') }}" class="btn-volver">← Volver</a>
        <button class="btn-imprimir" onclick="exportarPDF()">Exportar a PDF</button>
    </div>

    <script>
        function exportarPDF() {
            document.title = "Comprobante_{{ $venta['numero_factura'] ?? ('VENTA-' . $venta['id_venta']) }}";
            window.print();
        }
    </script>

</body>
</html>