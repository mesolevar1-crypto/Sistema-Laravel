# Módulo de Productos e Inventario — Vista del Vendedor

**Archivos involucrados:**
- Vista productos: `resources/views/vista_vendedor/productos.blade.php`
- Vista inventario: `resources/views/vista_vendedor/inventario.blade.php`
- Controlador productos: `app/Http/Controllers/ProductoController.php`
- Controlador inventario: `app/Http/Controllers/InventarioController.php`
- Modelos: `app/Models/producto.php`, `app/Models/inventario.php`
- Layout: `resources/views/layouts/dashboard.blade.php`
- Rutas: `routes/web.php`

---

## ¿Qué puede hacer el vendedor?

El vendedor tiene **acceso completo** a sus propios productos e inventario — puede crear, editar, activar/desactivar y eliminar sus productos, y ajustar el stock manualmente. Solo ve los productos e inventario que le pertenecen.

Esto es diferente al proyecto original donde el vendedor solo tenía lectura. En la versión actual de Laravel, el vendedor tiene las mismas operaciones que el admin pero **filtradas por su propio `id_usuario`**.

---

## MÓDULO DE PRODUCTOS

### Rutas del vendedor

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/vendedor/productos` | `vendedor.productos` | `ProductoController@vendedorIndex` |
| Crear | POST | `/vendedor/productos` | `vendedor.productos.store` | `ProductoController@store` |
| Editar | POST | `/vendedor/productos/{id}/editar` | `vendedor.productos.update` | `ProductoController@update` |
| Toggle estado | POST | `/vendedor/productos/{id}/toggle` | `vendedor.productos.toggle` | `ProductoController@toggleEstado` |
| Eliminar | DELETE | `/vendedor/productos/{id}` | `vendedor.productos.destroy` | `ProductoController@destroy` |
| Crear categoría | POST | `/vendedor/categorias` | `vendedor.categorias.store` | `ProductoController@storeCategoria` |
| Editar categoría | POST | `/vendedor/categorias/{id}/editar` | `vendedor.categorias.update` | `ProductoController@updateCategoria` |
| Eliminar categoría | DELETE | `/vendedor/categorias/{id}` | `vendedor.categorias.destroy` | `ProductoController@destroyCategoria` |

### Cómo se filtra por vendedor

```php
// En ProductoController@vendedorIndex:
$idUsuario = (int) (auth()->id() ?? 0);
$productos = Producto::obtenerTodos($idUsuario);  // Solo sus productos
```

### Seguridad de propiedad

El controlador verifica que el producto pertenezca al vendedor antes de editar, hacer toggle o eliminar:

```php
if ($this->esRutaVendedor() && !$this->productoPerteneceAlVendedorActual($producto)) {
    return $this->regresarConAlerta('error', 'Sin permiso', 'No puedes editar un producto que no es tuyo.');
}
```

### Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$productos` | Solo los productos del vendedor autenticado |
| `$categorias` | Todas las categorías disponibles (compartidas, sin dueño) |

### ¿Qué muestra la pantalla?

Idéntico al módulo de productos del admin: grid de tarjetas con imagen, badge de categoría, nombre, descripción, estado y botones de acción. El buscador en tiempo real y la paginación por JS funcionan igual.

### Categorías — compartidas sin dueño

Las categorías **no tienen dueño**. Cualquier usuario (admin o vendedor) puede crear, editar y eliminar categorías. Al eliminar, el modelo verifica que no tenga productos asociados.

### Sistema de alertas

`ProductoController@regresarConAlerta` usa `back()->with('alert', [...])` (vuelve a la página anterior). La vista lanza `Swal.fire()`.

---

## MÓDULO DE INVENTARIO

### Rutas del vendedor

| Acción | Método | URL | Nombre | Controlador |
|--------|--------|-----|--------|-------------|
| Listar | GET | `/vendedor/inventario` | `vendedor.inventario` | `InventarioController@vendedorIndex` |
| Actualizar stock | POST | `/vendedor/inventario/{id}/actualizar` | `vendedor.inventario.actualizar` | `InventarioController@actualizar` |

### Cómo se filtra por vendedor

```php
// En InventarioController@vendedorIndex:
$idUsuario = (int) (auth()->id() ?? 0);
$todos = Inventario::obtenerTodos($buscar, $estado, $idUsuario);  // Solo sus productos
$resumen = Inventario::obtenerResumen($idUsuario);
```

### Seguridad al actualizar stock

El controlador verifica que el producto pertenezca al vendedor antes de permitir la actualización:

```php
if ($this->esRutaVendedor()) {
    $producto = Producto::obtenerPorId($idProducto);
    if (!$producto || (int)($producto['id_usuario'] ?? 0) !== (int)(auth()->id() ?? 0)) {
        return $this->regresarConAlerta('error', 'Sin permiso', 'No puedes actualizar el inventario de un producto que no es tuyo.');
    }
}
```

### Datos que recibe la vista

| Variable | Contenido |
|----------|-----------|
| `$inventario` | Solo el stock de los productos del vendedor (5 por página) |
| `$resumen` | KPIs del inventario propio: total productos, unidades, stock bajo, agotados |
| `$buscar`, `$estado` | Filtros actuales |
| `$pagina`, `$paginas`, `$total` | Paginación |

### ¿Qué muestra la pantalla?

Idéntico al inventario del admin en diseño: 4 KPIs, filtros, buscador y tabla con stock actual, mínimo y estado. Solo cambia que todos los datos son del vendedor autenticado.

### Sistema de alertas

`InventarioController@regresarConAlerta` detecta `esRutaVendedor()` y redirige a `vendedor.inventario` con `session('alert')`.

---

## Flujo completo

```
GET /vendedor/productos → ProductoController@vendedorIndex
    ├── Producto::obtenerTodos(auth()->id()) → sus productos
    └── Vista con grid de tarjetas (editar, toggle, eliminar disponibles)

CREAR: "Nuevo Producto" → modal → POST /vendedor/productos
    → Producto::registrar() con id_usuario = auth()->id()

EDITAR/TOGGLE/ELIMINAR: verifican propiedad antes de actuar

CATEGORÍAS: mismos flujos con rutas /vendedor/categorias/* (sin verificación de dueño)

GET /vendedor/inventario → InventarioController@vendedorIndex
    ├── Inventario::obtenerTodos(buscar, estado, auth()->id()) → su inventario
    └── Vista con tabla (puede actualizar stock)

ACTUALIZAR STOCK: lápiz → modal → POST /vendedor/inventario/{id}/actualizar
    → Verifica propiedad → Inventario::actualizarStock()
```
