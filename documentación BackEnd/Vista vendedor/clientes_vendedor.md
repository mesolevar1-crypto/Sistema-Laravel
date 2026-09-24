# Módulo de Clientes — Vista del Vendedor

**Archivos involucrados:**
- Vista: `resources/views/vista_vendedor/clientes.blade.php`
- Controlador: `app/Http/Controllers/ClienteController.php` (compartido con admin)
- Modelo: `app/Models/cliente.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

## ¿Qué es este módulo?

Permite al vendedor gestionar sus propios clientes. El vendedor puede registrar, editar, activar/desactivar y eliminar clientes — pero **solo ve los clientes que él mismo creó**, no los de otros vendedores ni los del administrador.

Esto es necesario porque el vendedor necesita poder crear clientes nuevos para asociarlos a sus ventas sin depender del administrador.

---

## Rutas

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/vendedor/clientes` | `vendedor.clientes` | `ClienteController@vendedorIndex` |
| Crear | POST | `/vendedor/clientes` | `vendedor.clientes.store` | `ClienteController@store` |
| Editar | POST | `/vendedor/clientes/{id}/editar` | `vendedor.clientes.update` | `ClienteController@update` |
| Toggle estado | POST | `/vendedor/clientes/{id}/toggle` | `vendedor.clientes.toggle` | `ClienteController@toggleEstado` |
| Eliminar | DELETE | `/vendedor/clientes/{id}` | `vendedor.clientes.destroy` | `ClienteController@destroy` |

---

## Seguridad de propiedad

El controlador verifica que el cliente pertenezca al vendedor antes de permitir editar, hacer toggle o eliminar:

```php
if ($this->esRutaVendedor() && !$this->clientePerteneceAlVendedorActual($idCliente)) {
    return $this->regresarConAlerta('error', 'Sin permiso', 'No puedes editar un cliente que no es tuyo.');
}
```

`clientePerteneceAlVendedorActual()` compara `$cliente['id_usuario']` con `auth()->id()`.

---

## Modelo relacionado

`App\Models\cliente` — los mismos métodos que usa el admin. La diferencia está en el método de listado:

```php
// Admin:
ClienteController@index → Cliente::obtenerTodos()          // sin filtro de usuario

// Vendedor:
ClienteController@vendedorIndex → Cliente::obtenerTodos(auth()->id())  // filtrado
```

---

## Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$clientes` | Solo los clientes del vendedor autenticado (5 por página) |
| `$pagina`, `$paginas`, `$total` | Datos de paginación |

---

## ¿Qué muestra la pantalla?

Tabla con los clientes del vendedor:

| Columna | Qué muestra |
|---------|-------------|
| Nombre Completo | Nombre del cliente |
| Correo | Correo o "Sin correo" |
| Teléfono | Teléfono o "Sin teléfono" |
| Estado | Badge verde "Activo" o rojo "Inactivo" |
| Acciones | Lápiz (editar), toggle (activar/desactivar), basura (eliminar) |

---

## Acción 1: Registrar cliente

### Campos del formulario
- Nombre completo (obligatorio)
- Teléfono (opcional)
- Correo electrónico (opcional; si se ingresa debe tener formato válido y no estar duplicado)

El cliente queda asociado al `auth()->id()` del vendedor, lo que garantiza que aparezca solo en su panel.

---

## Acción 2: Editar cliente

- ✅ Nombre, Teléfono, Correo
- El controlador verifica propiedad antes de actualizar

---

## Acción 3: Activar / Desactivar

Un cliente inactivo **no aparece en el select** de nueva venta del vendedor. Esto le permite "ocultar" clientes sin eliminarlos.

---

## Acción 4: Eliminar

⚠️ No se puede deshacer. Si el cliente tiene ventas asociadas, puede fallar por clave foránea.

---

## Sistema de alertas

`ClienteController@regresarConAlerta` detecta `esRutaVendedor()` y redirige a `vendedor.clientes` con `session('alert')`. La vista lanza `Swal.fire()`.

---

## Flujo completo

```
GET /vendedor/clientes → ClienteController@vendedorIndex
    ├── Cliente::obtenerTodos(auth()->id()) → solo sus clientes
    ├── Paginación manual (5 por página)
    └── Vista con tabla

CREAR: "Nuevo Cliente" → modal → POST /vendedor/clientes
    → Validaciones → Cliente::registrar() con id_usuario = auth()->id()

EDITAR: lápiz → modal → POST /vendedor/clientes/{id}/editar
    → Verifica propiedad → Cliente::editarCompleto()

TOGGLE: POST /vendedor/clientes/{id}/toggle
    → Verifica propiedad → Cliente::cambiarEstado()

ELIMINAR: DELETE /vendedor/clientes/{id}
    → Verifica propiedad → Cliente::eliminarCliente()
```
