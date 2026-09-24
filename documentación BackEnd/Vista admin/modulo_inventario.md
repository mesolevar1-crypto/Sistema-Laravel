# Módulo de Inventario — Vista del Administrador

**Archivos involucrados:**
- Vista: `resources/views/vista_admin/inventario.blade.php`
- Controlador: `app/Http/Controllers/InventarioController.php`
- Modelo: `app/Models/inventario.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

## ¿Qué es este módulo?

Muestra el estado actual del stock de todos los productos del sistema. El administrador puede ver cuáles están en nivel normal, cuáles tienen stock bajo y cuáles están agotados. También puede ajustar manualmente el stock actual y el stock mínimo de cualquier producto.

El stock se actualiza automáticamente cuando se registran ventas (baja) o compras (sube) — la actualización manual es solo para correcciones.

---

## Rutas

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/admin/inventario` | `admin.inventario` | `InventarioController@index` |
| Actualizar stock | POST | `/admin/inventario/{id}/actualizar` | `admin.inventario.actualizar` | `InventarioController@actualizar` |

---

## Modelo relacionado

`App\Models\inventario` — métodos estáticos:

| Método | Qué hace |
|--------|----------|
| `obtenerTodos($buscar, $estado, $idUsuario = null)` | Trae todos los productos con su stock; `$buscar` filtra por nombre, `$estado` por nivel de stock, `$idUsuario` para el vendedor |
| `obtenerResumen($idUsuario = null)` | KPIs: total productos, total unidades, stock bajo, agotados |
| `actualizarStock($id, $stockActual, $stockMinimo)` | UPDATE `inventories` con los nuevos valores |

---

## Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$inventario` | Array de productos con stock de la página actual (5 por página) |
| `$resumen` | KPIs: total_productos, total_unidades, stock_bajo, agotados |
| `$buscar` | Término de búsqueda actual |
| `$estado` | Filtro de estado actual (vacío, `bajo` o `agotado`) |
| `$pagina`, `$paginas`, `$total` | Datos de paginación |

---

## ¿Qué muestra la pantalla?

**4 tarjetas KPI:**

| Tarjeta | Qué muestra |
|---------|-------------|
| Productos | Total de productos en el catálogo |
| Unidades | Suma de todos los stocks actuales |
| Stock Bajo | Productos con stock entre 1 y el mínimo |
| Agotados | Productos con stock = 0 |

**Filtros:**
- Búsqueda por nombre (input de texto, GET param `buscar`)
- Filtro de estado (`bajo`, `agotado`, o vacío para todos)

**Tabla con columnas:** Producto, Categoría, Stock Actual, Stock Mínimo, Estado (badge de color), Acciones (lápiz para editar).

---

## Estado visual de cada producto

El estado se determina comparando `stock_actual` con `stock_minimo`:

| Condición | Estado | Color |
|-----------|--------|-------|
| `stock_actual = 0` | Agotado | Rojo |
| `stock_actual <= stock_minimo` | Stock Bajo | Amarillo/naranja |
| `stock_actual > stock_minimo` | Normal | Verde |

---

## Acción: Actualizar stock manualmente

### ¿Cómo se activa?
Clic en el **lápiz** de cualquier producto → modal con campos de stock actual y mínimo.

### Validaciones del controlador

| Validación | Error si falla |
|-----------|----------------|
| `stock_actual` es número válido | "Ingresa un valor válido para el stock." |
| `stock_actual >= 0` | "El stock no puede ser negativo." |
| `stock_minimo` es número válido | "Ingresa un valor válido para el stock mínimo." |
| `stock_minimo >= 0` | "El stock mínimo no puede ser negativo." |

Si el usuario es vendedor, el controlador también verifica que el producto pertenezca al vendedor autenticado antes de permitir la edición.

---

## Actualización automática del stock

| Evento | Qué hace al stock |
|--------|------------------|
| Registrar venta | Baja el stock (`stock_actual - cantidad_vendida`) |
| Registrar compra | Sube el stock (`stock_actual + cantidad_comprada`) |
| Anular venta | Devuelve el stock (`stock_actual + cantidad_vendida`) |
| Reactivar venta | Descuenta el stock nuevamente |
| Eliminar compra | Stock NO se revierte |

---

## Sistema de alertas

`InventarioController@regresarConAlerta` redirige a `admin.inventario` (o `vendedor.inventario`) con `session('alert')`. La vista lanza `Swal.fire()`.

---

## Alerta global de stock bajo

Independientemente de este módulo, el sistema tiene una alerta flotante que aparece en **todas las páginas** del panel. Cuando hay productos con stock bajo o agotados, muestra un panel en la esquina de la pantalla con los nombres y stocks de esos productos.

Esta alerta la alimenta el endpoint:
- `GET /stock-alertas` → `VentaController@alertasStock` → devuelve JSON con productos críticos

---

## Flujo completo

```
GET /admin/inventario → InventarioController@index
    ├── Inventario::obtenerTodos(buscar, estado) → lista filtrada
    ├── Inventario::obtenerResumen() → KPIs
    ├── Paginación manual (5 por página)
    └── Vista con KPIs + filtros + tabla

ACTUALIZAR STOCK: lápiz → modal → POST /admin/inventario/{id}/actualizar
    → Validaciones → Inventario::actualizarStock() → UPDATE inventories

AUTOMÁTICO (ventas): VentaController → UPDATE inventories stock_actual - cantidad
AUTOMÁTICO (compras): CompraController → UPDATE inventories stock_actual + cantidad
AUTOMÁTICO (anular): VentaController → UPDATE inventories stock_actual + cantidad
```
