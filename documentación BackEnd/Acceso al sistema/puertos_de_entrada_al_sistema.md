# Puertas de Entrada al Sistema — VentaNet (Laravel)

**Archivos involucrados:**
- Vista pública: `resources/views/welcome.blade.php`
- Vista login: `resources/views/autenticacion/login.blade.php`
- Vista registro: `resources/views/autenticacion/registre.blade.php`
- Controlador de auth: `app/Http/Controllers/AuthController.php`
- Controlador de registro: `app/Http/Controllers/UsuarioController.php`
- Modelos: `app/Models/Usuario.php`, `app/Models/Persona.php`, `app/Models/Rol.php`
- Rutas: `routes/web.php`

---

## 1. Página de Bienvenida (`/`)

### ¿Qué es y para qué sirve?

Es la primera pantalla que ve cualquier visitante. No requiere sesión. Presenta el sistema con productos de ejemplo (frutas y verduras) y dirige al usuario hacia el login.

### Ruta

| Método | URL | Nombre | Controlador |
|--------|-----|--------|-------------|
| GET | `/` | `welcome` | Ninguno — `view('welcome')` directo |

### ¿Qué contiene?

- **Header** con navegación y botón "Iniciar sesión" → ruta `login`
- **Hero** con imagen y botón "Comprar ahora" → ruta `login`
- **Sección de categorías** (Frutas, Verduras, Granos, Productos frescos)
- **Grid de 8 productos** — datos fijos en PHP dentro de la vista, con imagen, nombre, categoría y descripción
- **Footer** con copyright

### No tiene controlador ni modelo

Los productos de la landing son un array PHP estático dentro de la propia vista. No vienen de la base de datos.

---

## 2. Login (`/login`)

### ¿Qué hace?

Permite a administradores y vendedores autenticarse en el sistema. Si las credenciales son correctas, redirige según el rol. Si hay errores, muestra una alerta con SweetAlert2.

### Rutas

| Método | URL | Nombre | Controlador |
|--------|-----|--------|-------------|
| GET | `/login` | `login` | `AuthController@mostrarLogin` |
| POST | `/login` | `login.procesar` | `AuthController@login` |

### ¿Cómo está diseñada la pantalla?

Tarjeta de dos paneles:
- **Panel izquierdo**: formulario con correo, contraseña (con ojo para mostrar/ocultar), checkbox "Recordar mi sesión" y enlace de registro
- **Panel derecho**: imagen de fondo (Unsplash) con texto descriptivo y botón "← Regresar al Inicio"

### ¿Qué pasa al enviar el formulario?

```
POST /login → AuthController@login
    ├── ¿Está bloqueado por intentos? → SÍ → alerta "Acceso bloqueado"
    ├── Valida con $request->validate() → correo y password requeridos
    ├── Busca el usuario: Usuario::with(['persona','rol'])->whereHas('persona', correo)
    ├── ¿No existe? → registra intento fallido → alerta "Correo no encontrado"
    ├── ¿estado usuario ≠ 1 o estado persona ≠ 1? → alerta "Cuenta inactiva"
    ├── ¿Hash::check(password) falla? → registra intento fallido → alerta "Contraseña incorrecta"
    └── Todo correcto →
            Limpia intentos/bloqueo
            session()->regenerate()
            auth()->login($usuario)          ← autenticación Laravel
            session(['usuario' => [...]])    ← sesión manual adicional
            id_rol = 1 → redirect('inicio.index')
            otro rol  → redirect('vendedor.inicio')
```

### Bloqueo por intentos fallidos

| Variable de sesión | Qué guarda |
|---|---|
| `login_intentos` | Contador de intentos fallidos (se incrementa con correo o contraseña incorrectos) |
| `login_bloqueado` | `true` cuando se alcanzan 5 intentos |
| `login_tiempo` | Timestamp del momento del bloqueo |

Después de **5 intentos fallidos**, el acceso se bloquea **2 minutos (120 segundos)**. El sistema revisa automáticamente si ya pasó el tiempo al mostrar la pantalla de login (`mostrarLogin`) y al procesar cada intento (`login`).

### Alertas

El controlador redirige con `back()->with('alert', [...])`. La vista detecta `session('alert')` y lanza `Swal.fire()`:

```javascript
Swal.fire({
    icon: 'error',
    title: 'Correo no encontrado',
    text: 'El correo no está registrado.',
    confirmButtonColor: '#00875F'
});
```

### Sesión guardada al iniciar sesión correctamente

```php
session([
    'usuario' => [
        'id_usuario' => $usuario->id_usuario,
        'id_persona' => $usuario->id_persona,
        'nombre'     => $usuario->persona->nombre,
        'email'      => $usuario->persona->correo,
        'telefono'   => $usuario->persona->telefono,
        'rol_id'     => $usuario->id_rol,
        'rol'        => $usuario->rol->nombre,
    ]
]);
```

Además, Laravel ejecuta `auth()->login($usuario)` para que `auth()->user()` esté disponible en toda la aplicación.

### JavaScript

| Función | Qué hace |
|---------|----------|
| `togglePass()` | Alterna `type="password"` / `type="text"` en el campo de contraseña y cambia el ícono SVG del ojo |

---

## 3. Registro (`/registro`)

### ¿Qué hace?

Permite que un visitante cree una cuenta nueva en el sistema.

### Rutas

| Método | URL | Nombre | Controlador |
|--------|-----|--------|-------------|
| GET | `/registro` | `registro` | Ninguno — `view('autenticacion.registre')` directo |
| POST | `/registro` | `registro.guardar` | `UsuarioController@registrar` |

### ¿Qué hace el controlador al registrar?

`UsuarioController@registrar` usa una **transacción de base de datos** en dos pasos:

```
Paso 1: Persona::create([nombre, telefono, correo, estado=1])
        → Obtiene el id_persona generado

Paso 2: Usuario::create([contraseña=Hash::make(password), id_persona, id_rol, estado=1])
        → Vincula el usuario a la persona

Si falla → DB::rollBack() → nada queda guardado
Si sale bien → DB::commit() → redirige con alerta de éxito
```

### Validaciones (usando `$request->validate()`)

| Campo | Regla |
|-------|-------|
| nombre | required, string, max:255 |
| telefono | nullable, string, max:20 |
| correo | required, email, max:255, unique:people,correo |
| password | required, min:6 |
| confirmar_password | required, same:password |
| rol | required, integer, exists:roles,id_rol |

### Modelos relacionados

- `App\Models\Persona` — tabla `people`, guarda nombre, teléfono, correo y estado
- `App\Models\Usuario` — tabla `users`, guarda contraseña cifrada, id_persona, id_rol y estado
- `App\Models\Rol` — tabla `roles`, catálogo de roles disponibles

### Cierre de sesión

| Método | URL | Nombre | Controlador |
|--------|-----|--------|-------------|
| POST | `/logout` | `logout` | `AuthController@logout` |

Al cerrar sesión: `auth()->logout()`, `$request->session()->flush()`, `invalidate()`, `regenerateToken()`, luego redirige a `login`.

---

## 4. Flujo completo de entrada al sistema

```
Visitante abre el navegador
↓
GET /  → welcome.blade.php (sin sesión requerida)
↓ clic "Iniciar sesión"
GET /login → login.blade.php
↓ envía formulario
POST /login → AuthController@login
    ├── Bloqueado → alerta SweetAlert2
    ├── Correo no existe → alerta
    ├── Cuenta inactiva → alerta
    ├── Contraseña incorrecta → alerta
    └── Correcto →
            auth()->login($usuario)
            session(['usuario' => [...]])
            ├── id_rol = 1 → GET /admin  (vista_admin.inicio)
            └── otro rol  → GET /vendedor (dashboard.vendedor)

Ruta alternativa — registro público:
GET /registro → registre.blade.php
↓ envía formulario
POST /registro → UsuarioController@registrar
    → Persona::create() + Usuario::create() (transacción)
    → Redirige con alerta de éxito
    → El usuario debe hacer login manualmente
```
