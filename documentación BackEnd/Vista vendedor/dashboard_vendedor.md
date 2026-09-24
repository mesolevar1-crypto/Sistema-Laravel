# Dashboard del Vendedor

**Archivos involucrados:**
- Vista: `resources/views/dashboard/vendedor.blade.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Sidebar: `resources/views/layouts/sidebar_vendedor.blade.php`
- Modelo: `app/Models/inicio.php`
- Ruta: `routes/web.php`

---

## ¿Qué es esta vista?

Es la **pantalla principal del vendedor** al iniciar sesión. Muestra un resumen de su propio rendimiento: sus ventas, sus ingresos, y acceso rápido a los módulos disponibles para su rol.

---

## Ruta y acceso

| Método | URL | Nombre | Controlador |
|--------|-----|--------|-------------|
| GET | `/vendedor` | `vendedor.inicio` | Ninguno — `view('dashboard.vendedor')` directo |

La ruta usa el middleware `auth`. Si el usuario no está autenticado, Laravel lo redirige al login.

**La vista no tiene controlador propio.** Llama directamente al modelo `App\Models\Inicio` con el ID del vendedor para filtrar solo sus datos.

---

## Modelo relacionado

`App\Models\inicio` — los mismos métodos que usa el panel del admin, pero llamados con `$idUsuario = auth()->id()` para filtrar solo las ventas y datos del vendedor autenticado.

Cuando el método recibe un `$idUsuario`, agrega `AND id_usuario = :id` a las consultas de ventas.

---

## ¿Qué muestra la pantalla?

### Tarjetas KPI (solo del vendedor)

| Tarjeta | Método del modelo | Qué muestra |
|---------|-----------------|-------------|
| Mis Ventas | `ventasDia($id)` + `ventasMes($id)` | Sus ventas del día y del mes |
| Mis Ingresos | Suma de sus ventas | Solo sus ingresos |
| Ventas Hoy | `contarVentasHoy($id)` | Cuántas vendió hoy |
| Ingresos Hoy | `ventasDia($id)` | Cuánto generó hoy |

### Gráfico de ventas

Igual que el admin pero filtrado por el vendedor. Usa **Chart.js** con los datos de `ventasUltimos7Dias($id)`.

### Productos más vendidos por el vendedor

`productosMasVendidos(5, $id)` — top 5 de sus propias ventas.

---

## Sidebar del vendedor (`sidebar_vendedor.blade.php`)

El layout detecta que el usuario es vendedor y carga `layouts.sidebar_vendedor` en lugar de `layouts.sidebar`. El menú tiene solo los módulos disponibles para el vendedor:

| Ítem | Ruta | Descripción |
|------|------|-------------|
| Inicio | `vendedor.inicio` | Este dashboard |
| Ventas | `vendedor.ventas` | Sus ventas (solo las suyas) |
| Clientes | `vendedor.clientes` | Sus clientes |
| Productos | `vendedor.productos` | Sus productos |
| Inventario | `vendedor.inventario` | Stock de sus productos |
| Reportes | `vendedor.reporte` | Su reporte de ventas |

**No tiene acceso a:** Usuarios, Proveedores, Compras, Reportes globales.

---

## Diferencias con el dashboard del administrador

| Aspecto | Admin | Vendedor |
|---------|-------|----------|
| Ventas | Todo el sistema | Solo las suyas (`WHERE id_usuario = auth()->id()`) |
| KPI "Ganancias" | Visible | No visible (información sensible del negocio) |
| KPI "Usuarios activos" | Visible | No visible |
| Módulos en el sidebar | 9 ítems | 6 ítems |

---

## Seguridad

El layout detecta el rol del usuario autenticado al decidir qué sidebar cargar. Si un vendedor intentara acceder manualmente a `/admin`, las rutas del panel admin están protegidas por el mismo middleware `auth` pero **no hacen verificación adicional de rol** a nivel de ruta — la separación se basa en que cada panel tiene sus propias rutas y el sidebar del vendedor no muestra los enlaces al panel admin.

---

## Flujo

```
POST /login → AuthController@login
    └── id_rol ≠ 1 → redirect('vendedor.inicio')

GET /vendedor → view('dashboard.vendedor')
    ├── Llama a Inicio::ventasDia(auth()->id())
    ├── Llama a Inicio::ventasMes(auth()->id())
    ├── Llama a Inicio::ventasUltimos7Dias(auth()->id())
    ├── Llama a Inicio::productosMasVendidos(5, auth()->id())
    └── Muestra dashboard con datos filtrados del vendedor
```
