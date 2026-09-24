# Módulo de Proveedores — Vista del Administrador

**Archivos involucrados:**
- Vista: `resources/views/vista_admin/proveedores.blade.php`
- Controlador: `app/Http/Controllers/ProveedorController.php`
- Modelo: `app/Models/proveedor.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

## ¿Qué es este módulo?

Permite gestionar los proveedores del negocio. Solo existe en el panel del administrador — los vendedores no tienen acceso. Los proveedores activos son los que aparecen disponibles al registrar una compra.

---

## Rutas

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/admin/proveedores` | `admin.proveedores` | `ProveedorController@index` |
| Crear | POST | `/admin/proveedores` | `admin.proveedores.store` | `ProveedorController@store` |
| Editar | POST | `/admin/proveedores/{id}/editar` | `admin.proveedores.update` | `ProveedorController@update` |
| Toggle estado | POST | `/admin/proveedores/{id}/toggle` | `admin.proveedores.toggle` | `ProveedorController@toggleEstado` |
| Eliminar | DELETE | `/admin/proveedores/{id}` | `admin.proveedores.destroy` | `ProveedorController@destroy` |

---

## Modelo relacionado

`App\Models\proveedor` — métodos estáticos con SQL directo:

| Método | Qué hace |
|--------|----------|
| `obtenerTodos()` | Trae todos los proveedores con JOIN a `people` |
| `obtenerPorId($id)` | Devuelve un proveedor por su ID |
| `registrar($datos)` | INSERT en tabla `suppliers` + `people` |
| `editar($id, $nombre, $telefono, $correo, $frecuencia)` | UPDATE datos del proveedor |
| `toggleEstado($id)` | Invierte estado activo/inactivo |
| `eliminar($id)` | DELETE del proveedor |

---

## Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$proveedores` | Array de proveedores de la página actual (5 por página) |
| `$pagina` | Número de página actual |
| `$paginas` | Total de páginas |
| `$total` | Total de proveedores registrados |

---

## ¿Qué muestra la pantalla?

Tabla con todos los proveedores:

| Columna | Qué muestra |
|---------|-------------|
| Nombre / Empresa | Nombre del proveedor |
| Correo | Correo o "Sin correo" |
| Teléfono | Teléfono o "Sin teléfono" |
| Frecuencia de entrega | Ej: "Semanal", "Mensual" o "Sin frecuencia" |
| Estado | Badge verde "Activo" o rojo "Inactivo" |
| Acciones | Lápiz, toggle, basura |

---

## Acción 1: Registrar proveedor

### Campos del formulario
- Nombre / Empresa (obligatorio)
- Teléfono (opcional)
- Correo electrónico (opcional; si se ingresa debe tener formato válido)
- Frecuencia de entrega (opcional, texto libre: "Semanal", "Mensual", etc.)

### Validaciones en el controlador

| Validación | Error si falla |
|-----------|----------------|
| Nombre no vacío | "El nombre del proveedor es obligatorio." |
| Formato de correo (si se ingresó) | "El formato del correo no es válido." |

---

## Acción 2: Editar proveedor

Todos los campos son editables: nombre, teléfono, correo y frecuencia de entrega.

---

## Acción 3: Activar / Desactivar

Un proveedor inactivo **no aparece en el select** al registrar una compra.

---

## Acción 4: Eliminar

⚠️ No se puede deshacer. Si el proveedor tiene compras asociadas, la eliminación puede fallar por clave foránea.

---

## Sistema de alertas

`ProveedorController@regresarConAlerta` redirige a `admin.proveedores` con `session('alert')`. La vista lanza `Swal.fire()`.

---

## Flujo completo

```
GET /admin/proveedores → ProveedorController@index
    ├── Proveedor::obtenerTodos() → todos los proveedores
    ├── Paginación manual (5 por página)
    └── Vista con tabla

CREAR: "Nuevo Proveedor" → modal → POST /admin/proveedores
    → 2 validaciones → Proveedor::registrar() → redirect con alerta

EDITAR: lápiz → modal precargado → POST /admin/proveedores/{id}/editar
    → Proveedor::editar() → redirect con alerta

TOGGLE: POST /admin/proveedores/{id}/toggle
    → Proveedor::toggleEstado() → redirect con alerta

ELIMINAR: DELETE /admin/proveedores/{id}
    → Proveedor::eliminar() → redirect con alerta
```
