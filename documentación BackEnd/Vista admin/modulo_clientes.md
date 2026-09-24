 Módulo de Clientes — Vista del Administrador

**Archivos involucrados:**
- Vista: `resources/views/vista_admin/clientes.blade.php`
- Controlador: `app/Http/Controllers/ClienteController.php`
- Modelo: `app/Models/cliente.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

 ¿Qué es este módulo?

Permite al administrador gestionar los clientes del negocio. Puede registrar nuevos clientes, editar sus datos, activarlos/desactivarlos y eliminarlos. El admin ve TODOS los clientes del sistema, sin importar qué vendedor los creó.

Los clientes activos son los que aparecen disponibles al registrar una venta.

---

 Rutas

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/admin/clientes` | `admin.clientes` | `ClienteController@index` |
| Crear | POST | `/admin/clientes` | `admin.clientes.store` | `ClienteController@store` |
| Editar | POST | `/admin/clientes/{id}/editar` | `admin.clientes.update` | `ClienteController@update` |
| Toggle estado | POST | `/admin/clientes/{id}/toggle` | `admin.clientes.toggle` | `ClienteController@toggleEstado` |
| Eliminar | DELETE | `/admin/clientes/{id}` | `admin.clientes.destroy` | `ClienteController@destroy` |

---

 Modelo relacionado

`App\Models\cliente` — métodos estáticos que ejecutan SQL directo (no Eloquent estándar):

| Método | Qué hace |
|--------|----------|
| `obtenerTodos($idUsuario = null)` | Trae todos los clientes; si se pasa `$idUsuario` filtra por vendedor |
| `obtenerPorId($id)` | Devuelve un cliente por su ID |
| `registrar($datos, $idUsuario)` | INSERT en tabla `customers`, vinculada a `people` |
| `editarCompleto($id, $nombre, $telefono, $correo)` | UPDATE datos del cliente |
| `cambiarEstado($id)` | Invierte el estado activo/inactivo |
| `eliminarCliente($id)` | DELETE del cliente |
| `existeCorreo($correo)` | Verifica si el correo ya está registrado |


Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$clientes` | Array de clientes de la página actual (5 por página) |
| `$pagina` | Número de página actual |
| `$paginas` | Total de páginas |
| `$total` | Total de clientes registrados |

---
 ¿Qué muestra la pantalla?

Tabla con todos los clientes:

| Columna | Qué muestra |
|---------|-------------|
| Nombre Completo | Nombre del cliente |
| Correo | Correo o "Sin correo" |
| Teléfono | Teléfono o "Sin teléfono" |
| Estado | Badge verde "Activo" o rojo "Inactivo" |
| Acciones | Lápiz (editar), toggle (activar/desactivar), basura (eliminar) |

---
 Acción 1: Registrar cliente

 Campos del formulario
- Nombre completo (obligatorio)
- Teléfono (opcional)
- Correo electrónico (opcional; si se ingresa debe tener formato válido y no estar duplicado)

 Validaciones en el controlador

| Validación | Error si falla |
|-----------|----------------|
| Nombre no vacío | "El nombre del cliente es obligatorio." |
| Formato de correo (si se ingresó) | "El formato del correo electrónico no es válido." |
| Correo no duplicado | "El correo electrónico ya está registrado." |

 ¿Cómo se guarda?

El cliente queda asociado al `auth()->id()` del usuario que lo crea. Esto permite que el vendedor después filtre solo sus propios clientes.

---

 Acción 2: Editar cliente

 ¿Qué se puede cambiar?
- ✅ Nombre, Teléfono, Correo

El controlador valida que el correo tenga formato válido antes de guardar.

---

 Acción 3: Activar / Desactivar

Un cliente inactivo **no aparece en el select** al registrar una venta. Su historial y datos se conservan.

---

Acción 4: Eliminar

⚠️ No se puede deshacer. Si el cliente tiene ventas asociadas, la eliminación puede fallar por restricción de clave foránea — el modelo devuelve el mensaje de error del servidor.

---

 Sistema de alertas

`ClienteController@regresarConAlerta` redirige a `admin.clientes` con `session('alert')`. La vista lanza `Swal.fire()` automáticamente al detectar la sesión.

Ejemplos:
- ✅ `success` — "¡Cliente registrado!"
- ✅ `success` — "¡Actualizado!"
- ❌ `error` — "Correo duplicado"
- ⚠️ `warning` — "El nombre del cliente es obligatorio."

---

 Flujo completo

```
GET /admin/clientes → ClienteController@index
    ├── Cliente::obtenerTodos() → todos los clientes
    ├── Paginación manual (5 por página con array_slice)
    └── Vista con tabla

CREAR: "Nuevo Cliente" → modal → POST /admin/clientes
    → 3 validaciones → Cliente::registrar() → redirect con alerta

EDITAR: lápiz → modal precargado → POST /admin/clientes/{id}/editar
    → Cliente::editarCompleto() → redirect con alerta

TOGGLE: ban/check → POST /admin/clientes/{id}/toggle
    → Cliente::cambiarEstado() → redirect con alerta

ELIMINAR: basura → modal confirmación → DELETE /admin/clientes/{id}
    → Cliente::eliminarCliente() → redirect con alerta
```
