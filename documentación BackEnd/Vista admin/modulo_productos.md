 Módulo de Productos — Vista del Administrador

**Archivos involucrados:**
- Vista: `resources/views/vista_admin/productos.blade.php`
- Controlador: `app/Http/Controllers/ProductoController.php`
- Modelo: `app/Models/producto.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

 ¿Qué es este módulo?

Permite gestionar el catálogo de productos del negocio. El administrador ve y gestiona **todos** los productos del sistema (de todos los vendedores). También permite gestionar las categorías de productos desde el mismo módulo.

---

 Rutas — Productos

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/admin/productos` | `admin.productos` | `ProductoController@index` |
| Crear | POST | `/admin/productos` | `admin.productos.store` | `ProductoController@store` |
| Editar | POST | `/admin/productos/{id}/editar` | `admin.productos.update` | `ProductoController@update` |
| Toggle estado | POST | `/admin/productos/{id}/toggle` | `admin.productos.toggle` | `ProductoController@toggleEstado` |
| Eliminar | DELETE | `/admin/productos/{id}` | `admin.productos.destroy` | `ProductoController@destroy` |


 Rutas — Categorías (gestionadas desde el mismo módulo)

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Crear | POST | `/admin/categorias` | `admin.categorias.store` | `ProductoController@storeCategoria` |
| Editar | POST | `/admin/categorias/{id}/editar` | `admin.categorias.update` | `ProductoController@updateCategoria` |
| Eliminar | DELETE | `/admin/categorias/{id}` | `admin.categorias.destroy` | `ProductoController@destroyCategoria` |

---

 Modelo relacionado

`App\Models\producto` — métodos estáticos:

| Método | Qué hace |
|--------|----------|
| `obtenerTodos($idUsuario = null)` | Trae todos los productos con nombre de categoría; si se pasa `$idUsuario`, filtra por dueño |
| `obtenerPorId($id)` | Devuelve un producto por su ID |
| `registrar($datos)` | INSERT en tabla `products` |
| `editar($id, $datos)` | UPDATE del producto |
| `toggleEstado($id)` | Invierte estado activo/inactivo |
| `eliminar($id)` | DELETE del producto |
| `existeNombre($nombre, $excluirId = null)` | Verifica si el nombre ya está en uso (el segundo parámetro excluye el propio producto al editar) |
| `obtenerCategorias()` | Trae todas las categorías para los selects |
| `registrarCategoria($datos)` | INSERT en tabla `categories` |
| `editarCategoria($id, $datos)` | UPDATE de la categoría |
| `eliminarCategoria($id)` | DELETE de la categoría (falla si tiene productos asociados) |
| `existeCategoriaTipo($tipo, $excluirId = null)` | Verifica duplicados de nombre de categoría |

---

 Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$productos` | Array con todos los productos (nombre, descripción, categoría, imagen, estado) |
| `$categorias` | Array con todas las categorías disponibles |

---

 ¿Qué muestra la pantalla?

**Encabezado** con botones "Agregar Categorías" y "Nuevo Producto".

**Buscador en tiempo real** — filtra las tarjetas por nombre o categoría sin recargar la página.

**Grid de tarjetas** — una tarjeta por producto con:
- Imagen del producto (o gradiente de color si no tiene imagen)
- Badge de categoría con color automático
- Nombre del producto
- Descripción (truncada a 2 líneas)
- Badge de estado (Activo / Inactivo)
- Botones: editar (lápiz), toggle estado (power), eliminar (basura)

**Paginación por JS** — sobre el listado ya cargado en memoria (sin recargar).

---

 Asignación automática de colores por categoría

Las tarjetas usan una paleta de 8 colores. El color se asigna con:

```php
$indice = ((int) $idCategoria - 1) % count($paleta);
```

Esto es **puramente visual** — no se guarda en la base de datos. Cada categoría siempre tendrá el mismo color mientras su ID no cambie.

---

 Acción 1: Crear producto

Campos del formulario
- Nombre (obligatorio, único)
- Descripción (opcional)
- Categoría (obligatorio, debe existir)
- Imagen (opcional, subida de archivo — JPG, PNG, GIF, WEBP, máx. 2MB)

 ¿Cómo se guarda la imagen?
El controlador guarda el archivo en `public/uploads/productos/` con nombre único (`producto_YmdHis_random.ext`). La ruta relativa se guarda en la base de datos. El modelo `Asset` de Laravel sirve la imagen con `asset($producto['imagen'])`.

 ¿Quién es el dueño del producto?
El producto queda asociado al `auth()->id()` del usuario que lo crea. Esto permite al vendedor filtrar "sus productos".

---

 Acción 2: Editar producto

Al editar, si se sube una nueva imagen el controlador elimina la imagen anterior del servidor (solo si es un archivo local en `uploads/productos/`).

---

 Acción 3: Activar / Desactivar

Un producto inactivo **no aparece en el select de ventas** ni de compras. El modelo `Venta::obtenerProductosDisponibles()` filtra por `estado = 1`.

---

 Acción 4: Eliminar

⚠️ No se puede deshacer. El controlador también elimina el archivo de imagen del servidor si existe.

---

 Gestión de categorías

Las categorías son **compartidas** — no tienen dueño. Tanto admin como vendedor pueden crearlas, editarlas y eliminarlas. No se puede eliminar una categoría si tiene productos asociados (el modelo lo verifica y devuelve el error).

---

 Sistema de alertas

`ProductoController@regresarConAlerta` usa `back()->with('alert', [...])` (no redirige a una ruta específica sino que vuelve a la página anterior). La vista lanza `Swal.fire()`.

---

 JavaScript de la vista

| Función | Qué hace |
|---------|----------|
| `openModal(id)` / `closeModal(id)` | Abre/cierra cualquier modal por ID |
| `openEditModal(p)` | Pre-carga el modal de edición con los datos del producto `p` (JSON de PHP) |
| `openDeleteModal(id, nombre)` | Abre el modal de confirmación de eliminación |
| `cambiarEstado(id, estadoActual)` | Envía el formulario de toggle de estado |
| `confirmarEliminarProducto()` | Envía el formulario de eliminación |
| `openEditCategoriaModal(cat)` | Pre-carga el modal de edición de categoría |
| `openDeleteCategoriaModal(id, nombre)` | Abre la confirmación de eliminación de categoría |
| `previewImagen(input, imgId, placeholderId)` | Previsualiza la imagen antes de subir |
| Buscador tiempo real | Filtra tarjetas por `data-nombre` y `data-categoria` sin recargar |
| Paginación JS | Pagina las tarjetas en memoria (8 por página) |

---

 Flujo completo

```
GET /admin/productos → ProductoController@index
    ├── Producto::obtenerTodos() → todos los productos
    ├── Producto::obtenerCategorias() → lista de categorías
    └── Vista con grid de tarjetas

CREAR: "Nuevo Producto" → modal → POST /admin/productos
    → Validaciones → guardar imagen → Producto::registrar() → back con alerta

EDITAR: lápiz → modal precargado → POST /admin/productos/{id}/editar
    → Validaciones → actualizar imagen → Producto::editar() → back con alerta

TOGGLE: power → POST /admin/productos/{id}/toggle
    → Producto::toggleEstado() → back con alerta

ELIMINAR: basura → modal → DELETE /admin/productos/{id}
    → Producto::eliminar() + eliminar imagen → back con alerta

CATEGORÍAS: mismos flujos con rutas /admin/categorias/*
```
