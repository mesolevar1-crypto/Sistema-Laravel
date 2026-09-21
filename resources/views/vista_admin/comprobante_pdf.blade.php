<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $venta['numero_factura'] ?? ('VENTA-' . $venta['id_venta']) }}</title>
<style>
    /* dompdf: sin flex, sin grid, sin gradientes reales -> todo con
       tablas/bloques y color sólido. El alto del papel se calcula
       dinámicamente en el controlador (ver VentaController::facturaPdf)
       según la cantidad de productos, para que TODO el comprobante
       quede en una sola página sin importar cuántos ítems tenga. */
    * { box-sizing: border-box; }
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        margin: 0;
        padding: 20px;
        background: #F0F4F0;
        color: #171717;
    }
    .tarjeta {
        width: 100%;
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
    }

    /* Cabecera */
    .cabecera {
        background: #01614B;
        padding: 24px 32px 20px;
        text-align: center;
    }
    /* Logo: badge de texto "VN" en vez de un glifo Unicode (el
       carácter &#8962; que se usaba antes no existe en la fuente
       Helvetica de dompdf y por eso se veía como un signo de
       interrogación / cuadro). Texto = 100% compatible. */
    .icono-tienda {
        width: 52px;
        height: 52px;
        background: #0f7a5f;
        border-radius: 50%;
        margin: 0 auto 10px;
        text-align: center;
        line-height: 52px;
        color: #ffffff;
        font-size: 18px;
        font-weight: 900;
        letter-spacing: 1px;
    }
    .marca {
        font-size: 24px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: 3px;
        margin: 0;
    }
    .marca-sub {
        font-size: 12px;
        color: #d7ece4;
        margin: 5px 0 0;
    }

    /* Franja comprobante */
    .franja {
        background: #DDF5EC;
        border-bottom: 1px dashed #61D0A7;
        padding: 12px 32px;
        text-align: center;
    }
    .franja-label {
        font-size: 11px;
        color: #01614B;
        font-weight: bold;
        letter-spacing: 1.5px;
    }
    .franja-num {
        font-size: 19px;
        font-weight: 900;
        color: #01614B;
        margin: 4px 0 0;
        letter-spacing: 1.5px;
    }

    /* Datos */
    .datos {
        padding: 16px 32px;
        border-bottom: 1px dashed #E5E7EB;
    }
    .datos table { width: 100%; border-collapse: collapse; }
    .datos td { width: 50%; padding: 6px 10px 6px 0; vertical-align: top; }
    .dato-label {
        font-size: 10px;
        color: #9CA3AF;
        font-weight: bold;
        letter-spacing: .7px;
        text-transform: uppercase;
        margin: 0 0 3px;
    }
    .dato-valor {
        font-size: 14px;
        color: #171717;
        font-weight: bold;
        margin: 0;
    }

    /* Productos */
    .seccion-titulo {
        font-size: 10px;
        font-weight: bold;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 14px 32px 8px;
        margin: 0;
    }
    .tabla-productos { margin: 0 32px; width: calc(100% - 64px); border-collapse: collapse; }
    .tabla-productos th {
        text-align: left;
        border-bottom: 2px solid #E5E7EB;
        padding-bottom: 6px;
        font-size: 11px;
        color: #5F6673;
        font-weight: bold;
    }
    .tabla-productos th.num, .tabla-productos td.num { text-align: right; }
    .tabla-productos th.centro, .tabla-productos td.centro { text-align: center; }
    .tabla-productos td {
        padding: 9px 0;
        border-bottom: 1px solid #F3F4F6;
        font-size: 13px;
        vertical-align: top;
    }
    .prod-nombre { font-weight: bold; color: #171717; font-size: 13px; }
    .prod-detalle { font-size: 10px; color: #9CA3AF; margin-top: 2px; }
    .prod-subtotal { font-weight: bold; color: #00875F; font-size: 13px; }

    /* Totales */
    .totales {
        margin: 12px 32px 0;
        padding-top: 10px;
        border-top: 1px dashed #E5E7EB;
    }
    .totales table { width: 100%; border-collapse: collapse; }
    .totales td { padding: 4px 0; font-size: 13px; color: #5F6673; }
    .totales td.num { text-align: right; }

    .caja-total {
        margin: 10px 32px 0;
        background: #DDF5EC;
        border-radius: 12px;
        padding: 14px 22px;
    }
    .caja-total table { width: 100%; }
    .caja-total .label {
        font-size: 14px;
        font-weight: bold;
        color: #01614B;
    }
    .caja-total .valor {
        font-size: 23px;
        font-weight: 900;
        color: #00875F;
        text-align: right;
    }

    .anulada {
        margin: 14px 32px;
        background: #fde8e8;
        border: 2px solid #E53935;
        border-radius: 12px;
        padding: 10px;
        text-align: center;
        color: #E53935;
        font-weight: 900;
        font-size: 14px;
        letter-spacing: 3px;
    }

    .pie {
        padding: 16px 32px 22px;
        text-align: center;
        border-top: 1px dashed #E5E7EB;
        margin-top: 12px;
    }
    .pie p {
        font-size: 11px;
        color: #9CA3AF;
        line-height: 1.7;
        margin: 0;
    }
</style>
</head>
<body>

    <div class="tarjeta">

        <div class="cabecera">
            <div class="icono-tienda">VN</div>
            <p class="marca">VentaNet</p>
            <p class="marca-sub">Sistema de gestión comercial</p>
        </div>

        <div class="franja">
            <span class="franja-label">COMPROBANTE</span>
            <p class="franja-num">{{ $venta['numero_factura'] ?? ('VENTA-' . $venta['id_venta']) }}</p>
        </div>

        <div class="datos">
            <table>
                <tr>
                    <td>
                        <p class="dato-label">Fecha</p>
                        <p class="dato-valor">{{ \Illuminate\Support\Carbon::parse($venta['fecha'])->format('d/m/Y H:i') }}</p>
                    </td>
                    <td>
                        <p class="dato-label">Venta N°</p>
                        <p class="dato-valor">#{{ $venta['id_venta'] }}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="dato-label">Cliente</p>
                        <p class="dato-valor">{{ $venta['cliente'] ?? 'Cliente final' }}</p>
                    </td>
                    <td>
                        <p class="dato-label">Atendió</p>
                        <p class="dato-valor">{{ $venta['vendedor'] ?? '---' }}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="dato-label">Pago</p>
                        <p class="dato-valor">{{ ucfirst($venta['metodo_pago'] ?? 'Efectivo') }}</p>
                    </td>
                    <td></td>
                </tr>
            </table>
        </div>

        <p class="seccion-titulo">Productos</p>
        <table class="tabla-productos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="centro">Cant.</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalle as $d)
                <tr>
                    <td>
                        <div class="prod-nombre">{{ $d['producto'] }}</div>
                        <div class="prod-detalle">
                            ${{ number_format($d['precio_venta'], 0, ',', '.') }} c/u
                            @if (!empty($d['descuento_porcentaje']) && $d['descuento_porcentaje'] > 0)
                                · Desc {{ $d['descuento_porcentaje'] }}%
                            @endif
                        </div>
                    </td>
                    <td class="centro">{{ $d['cantidad'] }}</td>
                    <td class="num prod-subtotal">${{ number_format($d['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totales">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td class="num">${{ number_format($venta['factura_subtotal'] ?? $venta['total'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Descuento</td>
                    <td class="num">${{ number_format($venta['factura_descuento'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="caja-total">
            <table>
                <tr>
                    <td class="label">TOTAL</td>
                    <td class="valor">${{ number_format($venta['total'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        @if (!$venta['estado'])
        <div class="anulada">★ VENTA ANULADA ★</div>
        @endif

        <div class="pie">
            <p>
                @if (!$venta['estado'])
                    Esta venta fue anulada.
                @else
                    ¡Gracias por su compra!
                @endif
                <br>Generado por VentaNet
            </p>
        </div>

    </div>

</body>
</html>