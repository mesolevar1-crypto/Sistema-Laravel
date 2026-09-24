# Módulo de Usuarios — Vista del Administrador

**Archivos involucrados:**
- Vista: `resources/views/dashboard/admin.blade.php`
- Controlador: `app/Http/Controllers/AdminUsuarioController.php`
- Modelos: `app/Models/Usuario.php`, `app/Models/Persona.php`, `app/Models/Rol.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

## ¿Qué es este módulo?

Permite al administrador gestionar las cuentas de usuario del sistema. Puede crear nuevos usuarios (asignando rol), editar sus datos, activarlos/desactivarlos y eliminarlos.

**Solo el administrador puede acceder.** Laravel protege esta ruta con el middleware `auth`.

---

## Rutas

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/admin/usuarios` | `admin.usuarios` | `AdminUsuarioController@index` |
| Crear | POST | `/admin/usuarios` | `admin.usuarios.crear` | `AdminUsuarioController@crear` |
| Toggle estado | POST | `/admin/usuarios/{id}/toggle` | `admin.usuarios.toggle` | `AdminUsuarioController@toggleEstado` |
| Editar | POST | `/admin/usuarios/{id}/editar` | `admin.usuarios.editar` | `AdminUsuarioController@editar` |
| Eliminar | DELETE | `/admin/usuarios/{id}` | `admin.usuarios.eliminar` | `AdminUsuarioController@eliminar` |

---

## Modelo relacionado

`App\Models\Usuario` — usa Eloquent con relaciones:
- `belongsTo(Persona::class)` — datos personales (nombre, teléfono, correo)
- `belongsTo(Rol::class)` — rol del usuario (Administrador, Vendedor)

El controlador carga usuarios con `Usuario::with(['persona', 'rol'])->orderBy('id_usuario')->paginate(5)`.

---

## Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$usuarios` | Paginación Laravel (5 por página) de usuarios con `persona` y `rol` cargados |
| `$roles` | Todos los roles disponibles (`Rol::all()`) para el select del formulario |

---

## ¿Qué muestra la pantalla?

Tabla con todos los usuarios del sistema:

| Columna | Qué muestra |
|---------|-------------|
| Nombre Completo | `$usuario->persona->nombre` |
| Correo | `$usuario->persona->correo` |
| Teléfono | `$usuario->persona->telefono` o "No registrado" |
| Rol | Badge de color — azul: Administrador, amarillo: Vendedor |
| Estado | Badge verde "Activo" o rojo "Inactivo" |
| Acciones | Lápiz (editar), ban/check (toggle estado), basura (eliminar) |

---

## Acción 1: Crear usuario

### ¿Cómo se activa?
Clic en **"Nuevo Usuario"** → abre modal con formulario.

### Campos del formulario
- Nombre completo (requerido)
- Teléfono (opcional)
- Correo electrónico (requerido, único en tabla `people`)
- Contraseña (requerido, mínimo 6 caracteres)
- Confirmar contraseña (debe coincidir)
- Rol (requerido, debe existir en tabla `roles`)

### ¿Cómo se guarda?
El controlador usa `DB::beginTransaction()` en dos pasos:

```
Paso 1: Persona::create([nombre, telefono, correo, estado=1])
Paso 2: Usuario::create([contraseña=Hash::make(password), id_persona, id_rol, estado=1])

Si algo falla → DB::rollBack()
Si todo bien  → DB::commit()
```

---

## Acción 2: Editar usuario

### Campos editables
- ✅ Nombre, Teléfono
- ✅ Rol
- ✅ Contraseña (opcional — si se deja vacío, no cambia)
- ❌ Correo — no se puede modificar

### ¿Qué hace el controlador?

```php
// Actualiza la persona
$usuario->persona->nombre   = $request->nombre;
$usuario->persona->telefono = $request->telefono;
$usuario->persona->save();

// Actualiza el rol
$usuario->id_rol = $request->rol;
$usuario->save();

// Actualiza la contraseña solo si se envió
if ($request->filled('password')) {
    $usuario->contraseña = Hash::make($request->password);
    $usuario->save();
}
```

---

## Acción 3: Activar / Desactivar

```php
$usuario->estado = $usuario->estado == 1 ? 0 : 1;
$usuario->save();
```

Un usuario con `estado = 0` **no puede iniciar sesión**. El controlador de autenticación lo verifica antes de procesar el login.

---

## Acción 4: Eliminar

El controlador llama a `$usuario->delete()`. Elimina el usuario de la tabla `users`. Los datos de la persona en `people` se conservan o eliminan según las restricciones de clave foránea de la base de datos.

⚠️ **No se puede deshacer.**

---

## Sistema de alertas

El controlador usa `back()->with('alert', [...])`. La vista detecta `session('alert')` y lanza `Swal.fire()`:

```javascript
Swal.fire({
    icon: 'success',
    title: 'Usuario creado',
    text: 'El usuario fue registrado correctamente.',
    confirmButtonColor: '#00875F'
});
```

---

## Flujo completo

```
GET /admin/usuarios → AdminUsuarioController@index
    ├── Carga usuarios paginados con persona y rol
    └── Muestra tabla con 5 usuarios por página

CREAR: "Nuevo Usuario" → modal → POST /admin/usuarios
    → Validación Laravel → Persona::create() + Usuario::create() (transacción)
    → back()->with('alert', success)

EDITAR: lápiz → modal con datos → POST /admin/usuarios/{id}/editar
    → Actualiza persona + usuario
    → back()->with('alert', success)

TOGGLE: ban/check → POST /admin/usuarios/{id}/toggle
    → Invierte $usuario->estado
    → back()->with('alert', success)

ELIMINAR: basura → modal confirmación → DELETE /admin/usuarios/{id}
    → $usuario->delete()
    → back()->with('alert', success)
```
