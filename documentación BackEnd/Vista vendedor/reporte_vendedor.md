# Módulo de Reportes — Vista del Vendedor

**Archivos involucrados:**
- Vista: `resources/views/vista_vendedor/reporte_ventas.blade.php`
- Modelo: `app/Models/reporte.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Ruta: `routes/web.php`

---

## ¿Qué es este módulo?

Muestra al vendedor un reporte de **sus propias ventas** en un rango de fechas. Incluye tabla de ventas, KPIs del período y un gráfico de barras con ventas vs. ganancias. No puede ver datos de otros vendedores.

---

## Ruta y acceso

| Método | URL | Nombre | Controlador |
|--------|-----|--------|-------------|
| GET | `/vendedor/reporte` | `vendedor.reporte` | Ninguno — closure en `routes/web.php` |

**No tiene controlador propio.** La lógica está en la ruta directamente. El `id_usuario` **siempre** se toma de `auth()->id()` — nunca del request, para que el vendedor no pueda cambiar el ID en la URL y ver ventas ajenas.

```php
Route::get('/vendedor/reporte', function (Request $request) {
    $idUsuario = (int) (auth()->id() ?? 0);  // ← fijo, no del request
    // ...
    $ventas = Reporte::reporteVentas($desde, $hasta, $idUsuario);
});
```

---

## Modelo relacionado

`App\Models\reporte` — los mismos métodos que usa el reporte del admin, pero siempre reciben el `$idUsuario` del vendedor:

| Método | Qué hace |
|--------|----------|
| `reporteVentas($desde, $hasta, $idUsuario)` | Ventas del vendedor en el rango de fechas |
| `gananciasPorPeriodo($desde, $hasta, $agrupacion, $idUsuario)` | Datos del gráfico filtrados por vendedor |

---

## Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$desde`, `$hasta` | Fechas del filtro (default: inicio del mes hasta hoy) |
| `$agrupacion` | Agrupación del gráfico: `dia`, `semana`, `mes` |
| `$ventas` | Solo las ventas del vendedor en el período |
| `$totalRegistros` | Cantidad de sus ventas en el período |
| `$totalVendido` | Suma de sus ventas |
| `$totalGanancia` | Su ganancia estimada del período |
| `$margenGeneral` | Porcentaje de margen sobre sus ventas |
| `$chartLabels` | Etiquetas del eje X para el gráfico |
| `$chartVendido` | Valores de "Total Vendido" para el gráfico |
| `$chartGanancia` | Valores de "Ganancia" para el gráfico |
| `$etiquetaAgrupacion` | Texto descriptivo de la agrupación |
| `$nombreVendedor` | Nombre del vendedor para el encabezado del reporte |
| `$nombreArchivoPDF` | Nombre sugerido al exportar |

---

## Diferencias con el reporte del administrador

| Aspecto | Admin | Vendedor |
|---------|-------|----------|
| Datos | Todas las ventas (filtrable por vendedor) | Solo las suyas (fijo) |
| Filtro de vendedor | Select con todos los vendedores | No tiene — siempre su propio ID |
| `$nombreVendedor` | No determinado | Sí recibe este dato |

---

## ¿Cómo funcionan los filtros?

Los filtros se envían por **GET** a la misma URL:
```
/vendedor/reporte?desde=2026-09-01&hasta=2026-09-24&agrupacion=dia
```

La ruta sanitiza los parámetros:
- Si `$desde > $hasta`, iguala `$desde = $hasta`
- Solo acepta `$agrupacion` en `['dia', 'semana', 'mes']`; si es otro valor, usa `'dia'`

---

## ¿Cómo funciona el gráfico?

**Chart.js** recibe los arrays `$chartLabels`, `$chartVendido` y `$chartGanancia` serializados como JSON. Dibuja un gráfico de barras doble (ventas vs. ganancias) agrupado por el período seleccionado.

---

## Dependencias externas

| Recurso | Tipo | Para qué |
|---------|------|----------|
| Chart.js (CDN, vía layout) | JS | Gráfico de barras |
| Font Awesome (CDN, vía layout) | Iconos | Íconos de KPIs |

---

## Flujo completo

```
GET /vendedor/reporte?desde=&hasta=&agrupacion=
    ├── $idUsuario = auth()->id()  ← siempre fijo
    ├── Reporte::reporteVentas($desde, $hasta, $idUsuario)
    ├── Reporte::gananciasPorPeriodo($desde, $hasta, $agrupacion, $idUsuario)
    ├── Calcular KPIs (totalRegistros, totalVendido, totalGanancia, margenGeneral)
    ├── Preparar datos para Chart.js (chartLabels, chartVendido, chartGanancia)
    └── Vista con tabla + KPIs + gráfico (solo datos del vendedor)
```
