 Módulo de Inicio — Administrador

**Archivos involucrados:**
- Vista: `resources/views/vista_admin/inicio.blade.php`
- Modelo: `app/Models/inicio.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Ruta: `routes/web.php`

---

 ¿Qué es este módulo?

Es la **pantalla principal del administrador** al iniciar sesión. Muestra un resumen visual del estado del negocio: ventas del día, ingresos, stock crítico, productos más vendidos y comportamiento de ventas de los últimos 7 días.

---

 Ruta y acceso

| Método | URL | Nombre | Controlador |
|--------|-----|--------|-------------|
| GET | `/admin` | `inicio.index` | Ninguno — `view('vista_admin.inicio')` directo desde la ruta |

La ruta usa el middleware `auth`. Si el usuario no está autenticado, Laravel lo redirige automáticamente al login.

**La vista no tiene controlador propio.** Los datos se obtienen llamando directamente al modelo `App\Models\Inicio` desde dentro de la vista blade.

---

 Modelo relacionado

`App\Models\inicio` — conecta a la base de datos y ejecuta las consultas de KPIs y rankings.

Los métodos del modelo reciben opcionalmente un `$idUsuario`. Cuando no se pasa (valor `null`), las consultas devuelven datos globales del negocio. Cuando se pasa un ID, filtran por ese vendedor.

La vista del admin siempre llama los métodos sin filtro de usuario para ver todo el negocio.

---

 ¿Qué muestra la pantalla?

 Tarjetas KPI

| Tarjeta | Método del modelo | Qué muestra |
|---------|------------------|-------------|
| Ventas del día | `ventasDia()` | Suma total de ventas activas del día actual |
| Ventas del mes | `ventasMes()` | Suma total de ventas activas del mes y año actual |
| Ganancias semana | `gananciasSemana()` | Subtotal - costo de los últimos 7 días |
| Stock bajo | `stockBajo()` | Cantidad de productos con `stock_actual <= stock_minimo` |
| Total productos | `totalProductos()` | Productos activos (`estado = 1`) |
| Usuarios activos | `totalUsuarios()` | Usuarios con `estado = 1` |

Análisis de ventas (últimos 7 días)

Gráfico de barras generado con **Chart.js** usando los datos de `ventasUltimos7Dias()`. El modelo devuelve un arreglo por fecha; los días sin ventas se rellenan con `0` para que siempre aparezcan los 7 días completos.

Las fechas se transforman de `YYYY-MM-DD` a formato legible: `Vie 21`.

Productos más vendidos

Lista con los 5 productos de mayor cantidad vendida. Usa `productosMasVendidos(5)` que agrupa por producto y ordena por `SUM(cantidad) DESC LIMIT 5`.

Si no hay ventas registradas, muestra un mensaje: *"Sin ventas registradas aún"*.

---

Datos que usa de la base de datos

| Tabla | Para qué |
|-------|----------|
| `sales` (ventas) | KPIs de ventas del día, mes y semana |
| `sales_details` (detalle_venta) | Calcular ganancias y productos más vendidos |
| `products` (productos) | Contar activos y detectar stock bajo |
| `inventories` (inventario) | Comparar stock actual con stock mínimo |
| `users` (usuarios) | Contar usuarios activos |

---

 Layout que extiende

```blade
@extends('layouts.dashboard')
```

El layout detecta el rol del usuario autenticado y carga el sidebar correcto (admin o vendedor). Para el admin, carga `layouts.sidebar`.

---
 Formato de moneda

Los valores monetarios se muestran en pesos colombianos:

```php
'$' . number_format($valor, 0, ',', '.')
// Ejemplo: 125000 → $125.000
```

---

 Dependencias externas

| Recurso | Tipo | Para qué |
|---------|------|----------|
| Chart.js 4.4.0 (CDN) | JS | Gráfico de barras de ventas |
| Font Awesome (CDN, vía layout) | Iconos | Íconos de tarjetas KPI |
| Tailwind CSS (CDN, vía layout) | CSS | Clases de diseño responsivo |
