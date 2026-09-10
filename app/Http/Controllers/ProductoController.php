<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Controlador de Producto.
 *
 * Equivalente al antiguo controllers/ProductoController.php, pero
 * dividido en acciones REST en vez de un switch por $_GET['accion'].
 * Incluye también las acciones de categorías, igual que el legacy.
 *
 * Las imágenes se guardan en public/uploads/productos (igual que
 * en el proyecto legacy) para no depender de `php artisan storage:link`.
 * La columna `imagen` guarda la ruta relativa 'uploads/productos/archivo.ext',
 * igual que antes.
 */
class ProductoController extends Controller
{
    // ============================================================
    // LISTADO
    // ============================================================
    public function index()
    {
        $productos  = Producto::obtenerTodos();
        $categorias = Producto::obtenerCategorias();

        return view('vista_admin.productos', compact('productos', 'categorias'));
    }

    // ============================================================
    // REGISTRAR PRODUCTO
    // ============================================================
    public function store(Request $request)
    {
        $nombre       = trim($request->input('nombre', ''));
        $descripcion  = trim($request->input('descripcion', ''));
        $idCategoria  = $request->filled('id_categoria') ? (int) $request->input('id_categoria') : null;

        if ($nombre === '') {
            return $this->regresarConAlerta('warning', 'Falta información', 'Debes ingresar el nombre del producto.');
        }

        if ($idCategoria === null) {
            return $this->regresarConAlerta('warning', 'Falta información', 'Debes seleccionar una categoría.');
        }

        if (Producto::existeNombre($nombre)) {
            return $this->regresarConAlerta('warning', 'Producto duplicado', 'Ya existe un producto registrado con ese nombre.');
        }

        $imagen = null;

        if ($request->hasFile('imagen')) {
            $imagen = $this->guardarImagen($request->file('imagen'));

            if ($imagen === false) {
                return $this->regresarConAlerta('error', 'Imagen inválida', 'La imagen no es válida, supera los 2 MB o no se pudo guardar.');
            }
        }

        $resultado = Producto::registrar([
            'nombre'       => $nombre,
            'descripcion'  => $descripcion,
            'id_categoria' => $idCategoria,
            'imagen'       => $imagen,
        ]);

        if (is_int($resultado) && $resultado > 0) {
            return $this->regresarConAlerta('success', 'Producto registrado', 'El producto se registró correctamente.');
        }

        return $this->regresarConAlerta('error', 'No se pudo registrar', is_string($resultado) ? $resultado : 'No fue posible registrar el producto.');
    }

    // ============================================================
    // EDITAR PRODUCTO
    // ============================================================
    public function update(Request $request, $id)
    {
        $idProducto = (int) $id;

        if ($idProducto <= 0) {
            return $this->regresarConAlerta('error', 'Producto inválido', 'No se recibió un producto válido.');
        }

        $producto = Producto::obtenerPorId($idProducto);

        if (!$producto) {
            return $this->regresarConAlerta('error', 'Producto no encontrado', 'El producto que intentas editar no existe.');
        }

        $nombre      = trim($request->input('nombre', ''));
        $descripcion = trim($request->input('descripcion', ''));
        $idCategoria = $request->filled('id_categoria') ? (int) $request->input('id_categoria') : null;

        if ($nombre === '') {
            return $this->regresarConAlerta('warning', 'Falta información', 'Debes ingresar el nombre del producto.');
        }

        if ($idCategoria === null) {
            return $this->regresarConAlerta('warning', 'Falta información', 'Debes seleccionar una categoría.');
        }

        if (Producto::existeNombre($nombre, $idProducto)) {
            return $this->regresarConAlerta('warning', 'Producto duplicado', 'Ya existe otro producto con ese nombre.');
        }

        $imagen = null;

        if ($request->hasFile('imagen')) {
            $imagen = $this->guardarImagen($request->file('imagen'));

            if ($imagen === false) {
                return $this->regresarConAlerta('error', 'Imagen inválida', 'La nueva imagen no es válida, supera los 2 MB o no se pudo guardar.');
            }
        }

        $datos = [
            'nombre'       => $nombre,
            'descripcion'  => $descripcion,
            'id_categoria' => $idCategoria,
        ];

        if ($imagen !== null) {
            $datos['imagen'] = $imagen;
        }

        $resultado = Producto::editar($idProducto, $datos);

        if ($resultado === true) {

            // Eliminar imagen anterior si se subió una nueva
            if ($imagen !== null) {
                $this->eliminarArchivoImagen($producto['imagen'] ?? null, $imagen);
            }

            return $this->regresarConAlerta('success', 'Producto actualizado', 'Los cambios del producto se guardaron correctamente.');
        }

        return $this->regresarConAlerta('error', 'No se pudo actualizar', is_string($resultado) ? $resultado : 'No fue posible actualizar el producto.');
    }

    // ============================================================
    // ACTIVAR / DESACTIVAR (toggle)
    // ============================================================
    public function toggleEstado($id)
    {
        $idProducto = (int) $id;

        if ($idProducto <= 0) {
            return $this->regresarConAlerta('error', 'Producto inválido', 'No se recibió un producto válido.');
        }

        $producto = Producto::obtenerPorId($idProducto);

        if (!$producto) {
            return $this->regresarConAlerta('error', 'Producto no encontrado', 'El producto que intentas actualizar no existe.');
        }

        $resultado = Producto::toggleEstado($idProducto);

        if ($resultado === true) {
            $quedoActivo = !$producto['estado'];

            return $this->regresarConAlerta(
                'success',
                $quedoActivo ? 'Producto activado' : 'Producto desactivado',
                $quedoActivo
                    ? 'El producto ahora está disponible en el catálogo.'
                    : 'El producto ahora está marcado como inactivo.'
            );
        }

        return $this->regresarConAlerta('error', 'Error', is_string($resultado) ? $resultado : 'No fue posible cambiar el estado del producto.');
    }

    // ============================================================
    // ELIMINAR PRODUCTO
    // ============================================================
    public function destroy($id)
    {
        $idProducto = (int) $id;

        if ($idProducto <= 0) {
            return $this->regresarConAlerta('error', 'Producto inválido', 'No se recibió un producto válido.');
        }

        $producto = Producto::obtenerPorId($idProducto);

        if (!$producto) {
            return $this->regresarConAlerta('error', 'Producto no encontrado', 'El producto que intentas eliminar no existe.');
        }

        $resultado = Producto::eliminar($idProducto);

        if ($resultado === true) {
            $this->eliminarArchivoImagen($producto['imagen'] ?? null, null);

            return $this->regresarConAlerta('success', 'Producto eliminado', 'El producto fue eliminado correctamente.');
        }

        return $this->regresarConAlerta('error', 'No se puede eliminar', is_string($resultado) ? $resultado : 'No fue posible eliminar el producto.');
    }

    // ============================================================
    // CATEGORÍAS: REGISTRAR
    // ============================================================
    public function storeCategoria(Request $request)
    {
        $tipo = trim($request->input('tipo', ''));

        if ($tipo === '') {
            return $this->regresarConAlerta('warning', 'Falta información', 'Debes ingresar el nombre de la categoría.');
        }

        if (Producto::existeCategoriaTipo($tipo)) {
            return $this->regresarConAlerta('warning', 'Categoría duplicada', 'Ya existe una categoría con ese nombre.');
        }

        $resultado = Producto::registrarCategoria(['tipo' => $tipo]);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', 'Categoría registrada', 'La categoría se registró correctamente.');
        }

        return $this->regresarConAlerta('error', 'No se pudo registrar', is_string($resultado) ? $resultado : 'No fue posible registrar la categoría.');
    }

    // ============================================================
    // CATEGORÍAS: EDITAR
    // ============================================================
    public function updateCategoria(Request $request, $id)
    {
        $idCategoria = (int) $id;
        $tipo        = trim($request->input('tipo', ''));

        if ($idCategoria <= 0) {
            return $this->regresarConAlerta('error', 'Categoría inválida', 'No se recibió una categoría válida.');
        }

        if ($tipo === '') {
            return $this->regresarConAlerta('warning', 'Falta información', 'Debes ingresar el nombre de la categoría.');
        }

        if (Producto::existeCategoriaTipo($tipo, $idCategoria)) {
            return $this->regresarConAlerta('warning', 'Categoría duplicada', 'Ya existe otra categoría con ese nombre.');
        }

        $resultado = Producto::editarCategoria($idCategoria, ['tipo' => $tipo]);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', 'Categoría actualizada', 'La categoría se actualizó correctamente.');
        }

        return $this->regresarConAlerta('error', 'No se pudo actualizar', is_string($resultado) ? $resultado : 'No fue posible actualizar la categoría.');
    }

    // ============================================================
    // CATEGORÍAS: ELIMINAR
    // ============================================================
    public function destroyCategoria($id)
    {
        $idCategoria = (int) $id;

        if ($idCategoria <= 0) {
            return $this->regresarConAlerta('error', 'Categoría inválida', 'No se recibió una categoría válida.');
        }

        $resultado = Producto::eliminarCategoria($idCategoria);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', 'Categoría eliminada', 'La categoría fue eliminada correctamente.');
        }

        return $this->regresarConAlerta('error', 'No se puede eliminar', is_string($resultado) ? $resultado : 'No fue posible eliminar la categoría.');
    }

    // ============================================================
    // GUARDAR IMAGEN
    // Retorna la ruta relativa ('uploads/productos/archivo.ext'),
    // null si no se envió archivo, o false si la imagen no es válida.
    // ============================================================
    private function guardarImagen(?UploadedFile $archivo)
    {
        if (!$archivo || !$archivo->isValid()) {
            return false;
        }

        // Máximo 2 MB
        if ($archivo->getSize() > 2 * 1024 * 1024) {
            return false;
        }

        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        $mime = $archivo->getMimeType();

        if (!isset($tiposPermitidos[$mime])) {
            return false;
        }

        $directorio = public_path('uploads/productos');

        if (!is_dir($directorio)) {
            if (!mkdir($directorio, 0755, true) && !is_dir($directorio)) {
                return false;
            }
        }

        $extension     = $tiposPermitidos[$mime];
        $nombreArchivo = 'producto_' . now()->format('YmdHis') . '_' . Str::random(10) . '.' . $extension;

        $archivo->move($directorio, $nombreArchivo);

        return 'uploads/productos/' . $nombreArchivo;
    }

    // ============================================================
    // ELIMINAR ARCHIVO DE IMAGEN DEL SERVIDOR (si aplica)
    //
    // $imagenAnterior: ruta relativa guardada en BD antes del cambio
    // $imagenNueva: ruta relativa de la nueva imagen (para no borrarla
    // por error si coinciden, aunque en la práctica nunca coinciden
    // porque el nombre incluye timestamp + aleatorio).
    // ============================================================
    private function eliminarArchivoImagen(?string $imagenAnterior, ?string $imagenNueva): void
    {
        if (empty($imagenAnterior)) {
            return;
        }

        if (strpos($imagenAnterior, 'uploads/productos/') !== 0) {
            return;
        }

        $rutaAnterior = public_path($imagenAnterior);
        $rutaNueva    = $imagenNueva ? public_path($imagenNueva) : null;

        if (is_file($rutaAnterior) && $rutaAnterior !== $rutaNueva) {
            @unlink($rutaAnterior);
        }
    }

    // ============================================================
    // Regresa a la página anterior con alerta flash (equivalente al
    // HTTP_REFERER del controlador legacy).
    // ============================================================
    private function regresarConAlerta(string $icon, string $title, string $text)
    {
        return back()->with('alert', [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text,
        ]);
    }
}