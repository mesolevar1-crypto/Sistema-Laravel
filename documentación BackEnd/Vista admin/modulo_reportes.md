# Módulo de Reportes — Vista del Administrador

**Archivos involucrados:**
- Vista índice: `resources/views/vista_admin/reporte.blade.php`
- Vista ventas: `resources/views/vista_admin/reporte_ventas.blade.php`
- Vista compras: `resources/views/vista_admin/reporte_compras.blade.php`
- Vista inventario: `resources/views/vista_admin/reporte_inventario.blade.php`
- Modelo: `app/Models/reporte.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

## ¿Qué es este módulo?

Proporciona al administrador una visión analítica del negocio. Tiene cuatro pantallas: un dashboard con KPIs generales y tres reportes especializados (ventas, compras e inventario) con filtros y gráficos.

**No tiene controlador propio.** Toda la lógica está directamente en las rutas de `routes/web.php`, que llaman al modelo `Reporte` y pasan las variables a las vistas.

---

## Rutas

| Vista | Método | URL | Nombre | Controlador |
|-------|--------|-----|--------|-------------|
| Índice general | GET | `/admin/reportes` | `admin.reportes` | Sin controlador (closure en web.php) |
| Reporte ventas | GET | `/admin/reportes/ventas` | `admin.reportes.ventas` | Sin controlador (closure en web.php) |
| Reporte compras | GET | `/admin/reportes/compras` | `admin.reportes.compras` | Sin controlador (closure en web.php) |
| Reporte inventario | GET | `/admin/reportes/inventario` | `admin.reportes.inventario` | Sin controlador (closure en web.php) |

---

## Modelo relacionado

`App\Models\reporte` — métodos estáticos con SQL directo:

| Método | Qué hace |
|--------|----------|
| `ventasHoy()` | Suma de ventas del día |
| `ventasMes()` | Suma de ventas del mes actual |
| `comprasMes()` | Suma de compras del mes actual |
| `gananciasMes()` | Ganancias estimadas del mes |
| `contarStockBajo()` | Productos con stock <= mínimo |
| `contarAgotados()` | Productos con stock = 0 |
| `reporteVentas($desde, $hasta, $idUsuario)` | Ventas en rango de fechas, filtrable por vendedor |
| `listaUsuarios()` | Vendedores para el filtro del reporte |
| `gananciasPorPeriodo($desde, $hasta, $agrupacion, $idUsuario)` | Datos del gráfico agrupados por día/semana/mes |
| `reporteCompras($desde, $hasta, $idProveedor)` | Compras en rango de fechas, filtrable por proveedor |
| `listaProveedores()` | Proveedores para el filtro del reporte |
| `reporteInventario($buscar, $idCategoria, $estado)` | Estado actual del inventario con filtros |
| `listaCategorias()` | Categorías para el filtro del inventario |

---

## 1. Dashboard General (`reporte.blade.php`)

### Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$ventasHoy` | Total en dinero de las ventas del día |
| `$ventasMes` | Total en dinero de las ventas del mes |
| `$comprasMes` | Total en dinero de las compras del mes |
| `$gananciasMes` | Ganancias estimadas del mes |
| `$stockBajo` | Cantidad de productos con stock bajo |
| `$agotados` | Cantidad de productos agotados |

### ¿Qué muestra?
KPIs del negocio en tarjetas con iconos y colores diferenciados. Punto de entrada para navegar a los tres reportes especializados.

---

## 2. Reporte de Ventas (`reporte_ventas.blade.php`)

### Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$desde`, `$hasta` | Fechas del filtro (default: inicio del mes hasta hoy) |
| `$idUsuario` | ID del vendedor filtrado (0 = todos) |
| `$agrupacion` | Agrupación del gráfico: `dia`, `semana`, `mes` |
| `$ventas` | Array de ventas en el período |
| `$usuarios` | Lista de vendedores para el select |
| `$totalRegistros` | Cantidad de ventas en el período |
| `$totalVendido` | Suma de dinero de las ventas |
| `$totalGanancia` | Ganancia total del período |
| `$margenGeneral` | Porcentaje de margen sobre las ventas |
| `$chartLabels` | Etiquetas del eje X para el gráfico |
| `$chartVendido` | Valores de "Total Vendido" para el gráfico |
| `$chartGanancia` | Valores de "Ganancia" para el gráfico |
| `$etiquetaAgrupacion` | Texto descriptivo de la agrupación ("por día", "por semana", etc.) |
| `$nombreArchivoPDF` | Nombre sugerido para el archivo al exportar |

### ¿Cómo funcionan los filtros?
Los filtros se envían por **GET** a la misma URL (`/admin/reportes/ventas?desde=...&hasta=...&id_usuario=...&agrupacion=...`). La ruta recoge los parámetros con `$request->input()`, los sanitiza y los pasa al modelo.

### ¿Cómo funciona el gráfico?
**Chart.js** recibe los arrays `$chartLabels`, `$chartVendido` y `$chartGanancia` serializados como JSON desde PHP. Dibuja un gráfico de barras doble (ventas vs. ganancias) por período.

---

## 3. Reporte de Compras (`reporte_compras.blade.php`)

### Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$desde`, `$hasta` | Fechas del filtro |
| `$idProv` | ID del proveedor filtrado (0 = todos) |
| `$agrupacion` | Agrupación: `dia`, `semana`, `mes` |
| `$compras` | Array de compras en el período |
| `$proveedores` | Lista de proveedores para el select |
| `$totalRegistros` | Cantidad de compras |
| `$totalComprado` | Suma de dinero de las compras |
| `$promedioCompra` | Promedio por compra |
| `$proveedoresUnicos` | Cantidad de proveedores distintos que aparecen |
| `$comprasPeriodo` | Datos agrupados para el gráfico |
| `$chartLabels`, `$chartTotal`, `$chartCant` | Datos para Chart.js |
| `$etiquetaAgrupacion` | Texto de agrupación |
| `$nombreArchivoPDF` | Nombre para exportar |

### Agrupación del gráfico de compras
La agrupación la hace **la ruta** (closure en `web.php`) iterando sobre el array de compras y agrupando por día/semana/mes con `date()` de PHP. El resultado se pasa listo a Chart.js.

---

## 4. Reporte de Inventario (`reporte_inventario.blade.php`)

No tiene filtro de fechas — muestra el estado **actual** del stock.

### Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$inventario` | Array de productos con stock filtrado |
| `$categorias` | Categorías para el select de filtro |
| `$buscar` | Término de búsqueda actual |
| `$idCat` | Categoría seleccionada (0 = todas) |
| `$estado` | Filtro de estado: vacío, `bajo`, `agotado` |
| `$totalProductos` | Cantidad de productos que cumplen el filtro |
| `$totalUnidades` | Suma de stock de los productos filtrados |
| `$totalBajo` | Productos con stock entre 1 y el mínimo |
| `$totalAgotado` | Productos con stock = 0 |
| `$nombreArchivoPDF` | Nombre para exportar |

---

## Dependencias externas

| Recurso | Tipo | Para qué |
|---------|------|----------|
| Chart.js (CDN, vía layout) | JS | Gráficos de barras en reportes de ventas y compras |
| Font Awesome (CDN, vía layout) | Iconos | Íconos de KPIs y tabla |
| Tailwind CSS (CDN, vía layout) | CSS | Diseño responsivo |

---

## Flujo completo

```
GET /admin/reportes → Reporte::ventasHoy() + ventasMes() + comprasMes()
    + gananciasMes() + contarStockBajo() + contarAgotados()
    → Vista con 6 KPIs y accesos a reportes especializados

GET /admin/reportes/ventas?desde=&hasta=&id_usuario=&agrupacion=
    → Reporte::reporteVentas() + gananciasPorPeriodo() + listaUsuarios()
    → Vista con tabla + KPIs + Chart.js (barras ventas vs ganancias)

GET /admin/reportes/compras?desde=&hasta=&id_proveedor=&agrupacion=
    → Reporte::reporteCompras() + listaProveedores() + agrupar por periodo
    → Vista con tabla + KPIs + Chart.js

GET /admin/reportes/inventario?buscar=&id_categoria=&estado=
    → Reporte::reporteInventario() + listaCategorias()
    → Vista con tabla + KPIs de stock
```
