 Módulo de Compras — Vista del Administrador

Archivos involucrados:
- Vista: `resources/views/vista_admin/compras.blade.php`
- Controlador: `app/Http/Controllers/CompraController.php`
- Modelo: `app/Models/compra.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

 ¿Qué es este módulo?

Permite al administrador registrar las compras realizadas a los proveedores. **Cada vez que se registra una compra, el stock de los productos comprados sube automáticamente** (el modelo lo hace dentro de una transacción). Solo existe en el panel del administrador.

 Rutas

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/admin/compras` | `admin.compras` | `CompraController@index` |
| Registrar | POST | `/admin/compras` | `admin.compras.store` | `CompraController@store` |
| Ver detalle | GET | `/admin/compras/{id}/detalle` | `admin.compras.detalle` | `CompraController@detalle` |
| Eliminar | DELETE | `/admin/compras/{id}` | `admin.compras.destroy` | `CompraController@destroy` |

---

 Modelo relacionado

`App\Models\compra` — métodos estáticos con SQL directo:

| Método | Qué hace |
|--------|----------|
| `obtenerTodas()` | Trae todas las compras con JOINs a proveedor, usuario y persona |
| `obtenerResumen()` | KPIs: total compras, gasto total, compras hoy, gasto hoy |
| `obtenerProveedores()` | Solo proveedores activos para el select del formulario |
| `obtenerProductos()` | Todos los productos con su stock y precio de compra |
| `obtenerUnidades()` | Catálogo de unidades de medida (`units`) |
| `obtenerDetalle($id)` | Productos de una compra específica (para el modal de detalle) |
| `registrar($idUsuario, $idProveedor, $items)` | INSERT compra + detalles + UPDATE stock (transacción) |
| `eliminar($id)` | DELETE detalles + DELETE compra (transacción) |


 Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$compras` | Array de compras de la página actual (5 por página) |
| `$resumen` | KPIs: total_compras, gasto_total, compras_hoy, gasto_hoy |
| `$proveedores` | Lista de proveedores activos |
| `$productos` | Lista de productos disponibles con stock y precio |
| `$unidades` | Lista de unidades de medida |
| `$pagina`, `$paginas`, `$total` | Datos de paginación |

---

 ¿Qué muestra la pantalla?

4 tarjetas KPI:

| Tarjeta | Qué muestra |
|---------|-------------|
| Total Compras | Cantidad de compras registradas |
| Gasto Total | Suma de todos los totales de compras |
| Compras Hoy | Cuántas compras se hicieron hoy |
| Gasto Hoy | Cuánto se gastó hoy |

Tabla de compras:fecha, proveedor, registrado por, total, acciones (ojo para detalle, basura para eliminar).



 Acción 1: Registrar compra

¿Qué hace el usuario?
1. Selecciona un PROVEEDOR ACTIVO
2. Agrega filas de productos — cada fila tiene:
   - Producto (select)
   - Cantidad (número entero)
   - Precio de compra negociado
   - Unidad de compra (caja, bulto, etc.)
   - Contenido por unidad (cuántas piezas trae)
   - Unidad de contenido (kg, litro, unidad, etc.)
3. Confirma la compra

 Validaciones del controlador

El controlador valida CADA FILA antes de enviar al modelo:

| Validación | Error si falla |
|-----------|----------------|
| Proveedor seleccionado | "Debes seleccionar un proveedor activo." |
| Producto en cada fila | "Hay una fila de la compra sin producto seleccionado." |
| Cantidad entera > 0 | "Todas las cantidades deben ser números enteros mayores que cero." |
| Precio > 0 | "Debes escribir el precio de compra negociado." |
| Unidad de compra seleccionada | "Debes seleccionar en qué unidad compraste cada producto." |
| Contenido por unidad > 0 | "Debes indicar cuánto contenido trae cada presentación." |
| Unidad de contenido seleccionada | "Debes seleccionar en qué unidad se mide el contenido." |

 ¿Cómo se guarda? (Transacción de 3 pasos)

```
Paso 1: INSERT INTO purchases (fecha, total, id_usuario, id_proveedor)
        → Obtiene id_compra

Paso 2: Por cada producto:
        INSERT INTO purchases_details (cantidad, precio_compra, subtotal,
                                       id_compra, id_producto, id_unidad,
                                       cantidad_por_unidad, id_unidad_contenido)

Paso 3: Por cada producto:
        UPDATE inventories SET stock_actual = stock_actual + cantidad

Si algo falla → rollBack() → nada queda guardado
```

El controlador **nunca confía en precios calculados desde el navegador** — solo los registra. El cálculo de subtotales y el total los hace el modelo.

---

 Acción 2: Ver detalle de una compra

El JavaScript hace `fetch` a `/admin/compras/{id}/detalle`. El controlador responde con JSON de los productos de esa compra. El JS construye el HTML del modal con esa información.

---

Acción 3: Eliminar compra

⚠️ No se puede deshacer. El modelo elimina en transacción:
1. DELETE detalles de la compra
2. DELETE encabezado de la compra

El stock NO se revierte al eliminar una compra.

---

 JavaScript de la vista

| Función | Qué hace |
|---------|----------|
| `agregarFila()` | Agrega una nueva fila de producto al formulario |
| `quitarFila(idx)` | Elimina una fila por índice |
| `calcularFila(idx)` | Calcula el subtotal visual de una fila (cantidad × precio) |
| `recalcularTotal()` | Suma todos los subtotales y actualiza el total visible |
| `verDetalle(id)` | Llama a `/admin/compras/{id}/detalle` via `fetch` y renderiza el modal |
| `confirmarEliminar()` | Envía el formulario de eliminación |

---

 Sistema de alertas

`CompraController@regresarConAlerta` redirige siempre a `admin.compras` con `session('alert')`. La vista lanza `Swal.fire()`.

---

Flujo completo

```
GET /admin/compras → CompraController@index
    ├── compra::obtenerTodas() + obtenerResumen()
    ├── obtenerProveedores() + obtenerProductos() + obtenerUnidades()
    └── Vista con KPIs + tabla

REGISTRAR: "Nueva Compra" → modal con filas dinámicas → POST /admin/compras
    → Validaciones por fila → compra::registrar() (transacción)
    → INSERT purchases + purchases_details + UPDATE inventories (stock +)

VER DETALLE: ojo → fetch GET /admin/compras/{id}/detalle → JSON → modal

ELIMINAR: basura → modal → DELETE /admin/compras/{id}
    → compra::eliminar() (transacción) → DELETE detalles + DELETE compra
```
