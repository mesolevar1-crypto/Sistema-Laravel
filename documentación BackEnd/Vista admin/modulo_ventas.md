# Módulo de Ventas — Vista del Administrador

**Archivos involucrados:**
- Vista principal: `resources/views/vista_admin/ventas.blade.php`
- Vista factura (página): `resources/views/vista_admin/factura.blade.php`
- Vista comprobante PDF: `resources/views/vista_admin/comprobante_pdf.blade.php`
- Controlador: `app/Http/Controllers/VentaController.php`
- Modelo: `app/Models/venta.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

## ¿Qué es este módulo?

Permite registrar nuevas ventas, consultar el historial completo (de todos los vendedores), ver el detalle de cada venta, consultar la factura/comprobante, anular ventas activas y reactivar ventas anuladas.

El administrador ve **todas** las ventas del sistema — sin filtro por usuario.

---

## Rutas

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/admin/ventas` | `admin.ventas` | `VentaController@index` |
| Registrar | POST | `/admin/ventas` | `admin.ventas.store` | `VentaController@store` |
| Ver detalle | GET | `/admin/ventas/{id}/detalle` | `admin.ventas.detalle` | `VentaController@detalle` |
| Anular | POST | `/admin/ventas/{id}/anular` | `admin.ventas.anular` | `VentaController@anular` |
| Reactivar | POST | `/admin/ventas/{id}/reactivar` | `admin.ventas.reactivar` | `VentaController@reactivar` |
| Factura JSON | GET | `/admin/ventas/{id}/factura-json` | `admin.ventas.factura.json` | `VentaController@facturaJson` |
| Factura página | GET | `/admin/ventas/{id}/factura` | `admin.ventas.factura` | `VentaController@factura` |
| Factura PDF | GET | `/admin/ventas/{id}/factura-pdf` | `admin.ventas.factura.pdf` | `VentaController@facturaPdf` |

---

## Modelo relacionado

`App\Models\venta` — métodos estáticos con SQL directo:

| Método | Qué hace |
|--------|----------|
| `obtenerTodas($idUsuario = null)` | Trae todas las ventas; con `$idUsuario` filtra por vendedor |
| `obtenerResumen($idUsuario = null)` | KPIs: total ventas, ingresos, ventas hoy, ingresos hoy |
| `obtenerClientes()` | Solo clientes activos para el select del formulario |
| `obtenerProductosDisponibles()` | Productos activos con stock, precio y stock mínimo |
| `obtenerUnidades()` | Catálogo de unidades de medida |
| `registrar($idUsuario, $idCliente, $metodoPago, $items)` | INSERT venta + detalles + UPDATE stock (transacción) |
| `obtenerDetalle($id)` | Productos de una venta con ganancia por línea |
| `eliminar($id)` | Anula la venta y DEVUELVE el stock |
| `reactivar($id)` | Reactiva la venta y DESCUENTA el stock nuevamente |
| `obtenerVentaCompleta($id)` | Datos completos de una venta para la factura |

---

## Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$ventas` | Array de ventas de la página actual (5 por página) |
| `$resumen` | KPIs: total_ventas, ingresos_total, ventas_hoy, ingresos_hoy |
| `$clientes` | Lista de clientes activos |
| `$productos` | Lista de productos disponibles con stock y stock mínimo |
| `$unidades` | Lista de unidades de medida |
| `$pagina`, `$paginas`, `$total` | Datos de paginación |

---

## ¿Qué muestra la pantalla?

**4 tarjetas KPI:**

| Tarjeta | Qué muestra |
|---------|-------------|
| Total Ventas | Cantidad de ventas históricas |
| Ingresos Total | Suma acumulada de todos los totales |
| Ventas Hoy | Cuántas ventas se hicieron hoy |
| Ingresos Hoy | Cuánto se generó hoy |

**Tabla de ventas:** Fecha, Cliente, Registrado por, Total, Ganancia, Margen %, Estado (activa/anulada), Acciones.

---

## Formulario Nueva Venta (multi-producto)

El formulario permite agregar múltiples productos en una sola venta. Cada fila tiene:

| Campo | Descripción |
|-------|-------------|
| Producto | Select con stock disponible entre corchetes; agotados deshabilitados |
| Cantidad | Entero, cuántas unidades de venta se entregan |
| Unidad de venta | Cómo se le vende al cliente (pieza, paquete, caja) |
| Contenido | Cuántas piezas trae cada unidad de venta |
| Unidad de contenido | En qué se mide ese contenido (kg, litro, unidad) |
| Precio de venta | Precio cobrado al cliente por unidad de venta |
| Descuento % | Descuento sobre esa línea (0–100) |
| Subtotal | Calculado automáticamente — solo lectura |

**El controlador no acepta precios calculados desde el navegador.** El modelo recalcula todo en el servidor.

---

## Validaciones del controlador (VentaController@store)

| Validación | Error si falla |
|-----------|----------------|
| Usuario autenticado | "Tu sesión expiró, vuelve a iniciar sesión." |
| Cliente seleccionado | "Debes seleccionar un cliente." |
| Al menos un producto | "Debes agregar al menos un producto." |
| Stock disponible por producto | "No se pueden vender: [nombre]. Debes comprar stock primero." |

La validación de stock usa `estadoStock()` con la misma regla que el panel flotante:
- `stock = 0` → agotado → bloqueado
- `stock <= stock_minimo` → bajo → bloqueado

---

## Cómo funciona Anular / Reactivar

| Acción | Qué hace en el inventario |
|--------|--------------------------|
| Anular venta | **Devuelve** el stock (`stock_actual + cantidad_vendida`) |
| Reactivar venta | **Descuenta** el stock nuevamente (`stock_actual - cantidad_vendida`); si no hay stock suficiente, falla |

---

## Modal de Factura / Comprobante

El botón de factura en la tabla abre un **modal** (no una página nueva) con el diseño de ticket:

1. Cabecera verde degradada con logo VentaNet
2. Banda verde claro con el número de comprobante
3. Grid de datos: fecha, venta N°, cliente, vendedor, método de pago
4. Tabla de productos con precio unitario, descuento y subtotal
5. Bloque de totales con TOTAL destacado
6. Si está anulada: banda roja "★ VENTA ANULADA ★"
7. Footer con mensaje de cierre

El modal carga los datos via `fetch GET /admin/ventas/{id}/factura-json` que devuelve JSON con `{venta, detalle}`.

---

## Factura en PDF (comprobante_pdf.blade.php)

El controlador `facturaPdf()` usa la librería **DomPDF** (`barryvdh/laravel-dompdf`) para generar el PDF. El alto del papel se calcula dinámicamente:

```
Alto = 660px base
     + (cantidad de productos × 36px)
     + 60px colchón
     + 40px extra si la venta está anulada
```

Esto garantiza que todo el comprobante quepa en **una sola hoja**, sin importar cuántos productos tenga.

---

## Sistema de alertas

`VentaController@regresarConAlerta` detecta si la petición es de ruta `vendedor.*` o `admin.*` y redirige al panel correcto con `session('alert')`. La vista lanza `Swal.fire()`.

Ejemplos:
- ✅ `success` — "¡Venta registrada! Factura: VENTA-2026-0001"
- ✅ `success` — "Venta anulada — el inventario fue restaurado."
- ✅ `success` — "Venta reactivada — el inventario fue descontado."
- ❌ `error` — "Productos sin stock disponible: Banano, Zanahoria."
- ⚠️ `warning` — "Cliente requerido"

---

## JavaScript de la vista

| Función | Qué hace |
|---------|----------|
| `abrirModal(id)` / `cerrarModal(id)` | Muestra/oculta modales por ID |
| `abrirModalNuevaVenta()` | Abre el modal y agrega la primera fila automáticamente |
| `cerrarModalCrear()` | Cierra y limpia completamente el formulario |
| `agregarFila()` | Agrega una nueva fila de producto al formulario |
| `quitarFila(idx)` | Elimina una fila |
| `reconstruirOpcionesProducto(select, idx)` | Llena el select de producto con el catálogo |
| `calcularFila(idx)` | Calcula subtotal visual: `cantidad × precio × (1 - descuento%)` |
| `recalcularTotal()` | Suma todos los subtotales y actualiza el total |
| `verDetalle(id)` | `fetch` a `/admin/ventas/{id}/detalle` → construye tabla en modal |
| `verFactura(id)` | `fetch` a `/admin/ventas/{id}/factura-json` → construye ticket en modal |
| `renderFacturaModal(data)` | Genera el HTML visual del comprobante para el modal |
| `_factItem(label, value)` | Genera un bloque campo-valor para el grid de datos de la factura |
| `imprimirFactura()` | Llama a `window.print()` con el título del documento cambiado |
| `abrirModalEliminar(id)` | Abre confirmación de anulación |
| `confirmarAnular()` | Envía formulario `#formAnular` a la ruta de anulación |
| `abrirModalReactivar(id)` | Abre confirmación de reactivación |
| `confirmarReactivar()` | Envía formulario `#formReactivar` a la ruta de reactivación |
| `escapeHtml(text)` | Escapa caracteres HTML para evitar XSS en contenido dinámico |

---

## Flujo completo

```
GET /admin/ventas → VentaController@index
    ├── Venta::obtenerTodas() + obtenerResumen()
    ├── obtenerClientes() + obtenerProductosDisponibles() + obtenerUnidades()
    ├── Paginación manual (5 por página)
    └── Vista con KPIs + tabla

REGISTRAR: "Nueva Venta" → modal multi-producto → POST /admin/ventas
    → Validar stock → Venta::registrar() (transacción)
    → INSERT sales + sales_details + UPDATE inventories (stock -)

VER DETALLE: ojo → fetch GET /admin/ventas/{id}/detalle → JSON → modal

FACTURA: ícono factura → fetch GET /admin/ventas/{id}/factura-json → JSON → modal ticket

ANULAR: ban → modal confirmación → POST /admin/ventas/{id}/anular
    → Venta::eliminar() → estado = 0 + stock devuelto

REACTIVAR: rotate → modal confirmación → POST /admin/ventas/{id}/reactivar
    → Venta::reactivar() → estado = 1 + stock descontado

PDF: GET /admin/ventas/{id}/factura-pdf → DomPDF → descarga Comprobante_XXX.pdf
```
