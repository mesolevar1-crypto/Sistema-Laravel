# Módulo de Ventas — Vista del Vendedor

**Archivos involucrados:**
- Vista principal: `resources/views/vista_vendedor/ventas.blade.php`
- Vista factura (modal): cargada via JSON en la misma vista
- Controlador: `app/Http/Controllers/VentaController.php` (compartido con admin)
- Modelo: `app/Models/venta.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

## ¿Qué es esta vista?

Es la vista de ventas **exclusiva del vendedor**. Funciona igual al módulo del admin, pero el vendedor **solo ve y gestiona sus propias ventas**. No puede ver las de otros vendedores ni las del administrador.

El mismo controlador `VentaController` atiende ambos paneles. La separación la hace el método privado `esRutaVendedor()` que detecta si la ruta empieza con `vendedor.`.

---

## Rutas

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/vendedor/ventas` | `vendedor.ventas` | `VentaController@vendedorIndex` |
| Registrar | POST | `/vendedor/ventas` | `vendedor.ventas.store` | `VentaController@store` |
| Ver detalle | GET | `/vendedor/ventas/{id}/detalle` | `vendedor.ventas.detalle` | `VentaController@detalle` |
| Anular | POST | `/vendedor/ventas/{id}/anular` | `vendedor.ventas.anular` | `VentaController@anular` |
| Reactivar | POST | `/vendedor/ventas/{id}/reactivar` | `vendedor.ventas.reactivar` | `VentaController@reactivar` |
| Factura JSON | GET | `/vendedor/ventas/{id}/factura-json` | `vendedor.ventas.factura.json` | `VentaController@facturaJson` |
| Factura página | GET | `/vendedor/ventas/{id}/factura` | `vendedor.ventas.factura` | `VentaController@factura` |
| Factura PDF | GET | `/vendedor/ventas/{id}/factura-pdf` | `vendedor.ventas.factura.pdf` | `VentaController@facturaPdf` |

---

## Seguridad de propiedad

El controlador verifica en **cada acción** que la venta pertenezca al vendedor autenticado:

```php
if ($this->esRutaVendedor() && !$this->ventaPerteneceAlVendedorActual($idVenta)) {
    return $this->regresarConAlerta('error', 'Sin permiso', 'No puedes ver/anular esta venta.');
}
```

Si un vendedor manipula el ID en la URL para intentar ver ventas ajenas, recibe un error y es redirigido a su propio panel.

---

## Modelo relacionado

Mismo `App\Models\venta`. La diferencia está en cómo se llaman los métodos:

- `Venta::obtenerTodas(auth()->id())` → solo sus ventas
- `Venta::obtenerResumen(auth()->id())` → KPIs filtrados por el vendedor

---

## Datos que recibe la vista

Idénticos al panel admin:

| Variable | Contenido |
|----------|-----------|
| `$ventas` | Solo las ventas del vendedor autenticado (5 por página) |
| `$resumen` | KPIs filtrados: sus ventas, sus ingresos, sus ventas hoy, sus ingresos hoy |
| `$clientes` | Lista de clientes activos (para el select del formulario) |
| `$productos` | Productos disponibles con stock |
| `$unidades` | Unidades de medida |
| `$pagina`, `$paginas`, `$total` | Datos de paginación |

---

## ¿Qué muestra la pantalla?

**4 tarjetas KPI** — igual que el admin pero con datos solo del vendedor:

| Tarjeta | Qué muestra |
|---------|-------------|
| Mis Ventas | Total de ventas que ha registrado |
| Mis Ingresos | Suma de todas sus ventas |
| Ventas Hoy | Cuántas vendió hoy |
| Ingresos Hoy | Cuánto generó hoy |

**Tabla de ventas:** Fecha, Cliente, Total, Ganancia, Margen %, Estado, Acciones.

> La columna "Registrado por" no aparece en la vista del vendedor (siempre sería él mismo).

---

## Diferencias clave con la vista del admin

| Aspecto | Admin | Vendedor |
|---------|-------|----------|
| Ventas que ve | Todas del sistema | Solo las suyas |
| Columna "Registrado por" | ✅ Visible | ❌ No aparece |
| KPIs | Totales del sistema | Solo los suyos |
| Validación de propiedad | No aplica (ve todo) | Sí — verifica `id_usuario` antes de cada acción |

---

## Modal de Factura

El modal de factura es idéntico al del admin en diseño y comportamiento. La diferencia está en las URLs que usa el JS:

```javascript
var urlFacturaJsonBase = "{{ route('vendedor.ventas.factura.json', ['id' => 'ID_PLACEHOLDER']) }}";
```

La función `verFactura(id)` reemplaza `ID_PLACEHOLDER` con el ID real y hace el `fetch`.

---

## Sistema de alertas

`VentaController@regresarConAlerta` detecta `esRutaVendedor()` y redirige a `vendedor.ventas` (no a `admin.ventas`) con `session('alert')`. La vista lanza `Swal.fire()`.

---

## JavaScript de la vista

Las mismas funciones que en la vista del admin, pero usando URLs base del vendedor:

| Variable JS | URL del vendedor |
|------------|-----------------|
| `urlDetalleBase` | `/vendedor/ventas/ID_PLACEHOLDER/detalle` |
| `urlAnularBase` | `/vendedor/ventas/ID_PLACEHOLDER/anular` |
| `urlReactivarBase` | `/vendedor/ventas/ID_PLACEHOLDER/reactivar` |
| `urlFacturaJsonBase` | `/vendedor/ventas/ID_PLACEHOLDER/factura-json` |

---

## Flujo completo

```
GET /vendedor/ventas → VentaController@vendedorIndex
    ├── Venta::obtenerTodas(auth()->id()) → solo sus ventas
    ├── Venta::obtenerResumen(auth()->id()) → sus KPIs
    ├── Paginación manual (5 por página)
    └── Vista con KPIs + tabla

REGISTRAR: "Nueva Venta" → modal → POST /vendedor/ventas
    → Mismo proceso que admin → Venta::registrar() (transacción)
    → El id_usuario se toma de auth()->id() (nunca del formulario)

VER DETALLE: ojo → fetch GET /vendedor/ventas/{id}/detalle
    → Verifica propiedad → JSON → modal

FACTURA: ícono → fetch GET /vendedor/ventas/{id}/factura-json
    → Verifica propiedad → JSON → modal ticket

ANULAR: POST /vendedor/ventas/{id}/anular
    → Verifica propiedad → Venta::eliminar() → stock devuelto

REACTIVAR: POST /vendedor/ventas/{id}/reactivar
    → Verifica propiedad → Venta::reactivar() → stock descontado
```
