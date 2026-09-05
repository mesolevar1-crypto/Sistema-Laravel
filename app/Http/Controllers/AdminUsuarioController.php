<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUsuarioController extends Controller
{
    // ============================================================
    // MOSTRAR USUARIOS
    // ============================================================

    public function index()
    {
        $usuarios = Usuario::with(['persona', 'rol'])
            ->orderBy('id_usuario')
            ->paginate(5)
            ->withQueryString();

        $roles = Rol::all();

        return view('dashboard.admin', compact('usuarios', 'roles'));
    }

    // ============================================================
    // CREAR USUARIO (desde el panel admin, con rol seleccionable)
    // ============================================================

    public function crear(Request $request)
    {
        $request->validate(
            [
                'nombre' => 'required|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'correo' => 'required|email|max:255|unique:people,correo',
                'password' => 'required|min:6',
                'confirmar_password' => 'required|same:password',
                'rol' => 'required|integer|exists:roles,id_rol',
            ],
            [
                'nombre.required' => 'El nombre es obligatorio.',
                'correo.required' => 'El correo es obligatorio.',
                'correo.email' => 'El correo no es válido.',
                'correo.unique' => 'El correo ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener mínimo 6 caracteres.',
                'confirmar_password.same' => 'Las contraseñas no coinciden.',
                'rol.required' => 'Debes seleccionar un rol.',
                'rol.exists' => 'El rol seleccionado no es válido.',
            ]
        );

        try {

            DB::beginTransaction();

            $persona = Persona::create([
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'estado' => 1,
            ]);

            Usuario::create([
                'contraseña' => Hash::make($request->password),
                'id_persona' => $persona->id_persona,
                'id_rol' => $request->rol,
                'estado' => 1,
            ]);

            DB::commit();

            return back()->with('alert', [
                'icon' => 'success',
                'title' => 'Usuario creado',
                'text' => 'El usuario fue registrado correctamente.',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('alert', [
                'icon' => 'error',
                'title' => 'Error del sistema',
                'text' => 'No se pudo crear el usuario.',
            ]);
        }
    }

    // ============================================================
    // ACTIVAR / DESACTIVAR USUARIO
    // ============================================================

    public function toggleEstado($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {

            return back()->with('alert', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'El usuario no existe.'
            ]);
        }

        $usuario->estado = $usuario->estado == 1 ? 0 : 1;
        $usuario->save();

        return back()->with('alert', [
            'icon' => 'success',

            'title' => $usuario->estado == 1
                ? 'Usuario activado'
                : 'Usuario desactivado',

            'text' => $usuario->estado == 1
                ? 'El usuario fue activado correctamente.'
                : 'El usuario fue desactivado correctamente.'
        ]);
    }

    // ============================================================
    // ELIMINAR USUARIO
    // ============================================================

    public function eliminar($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {

            return back()->with('alert', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'El usuario no existe.'
            ]);
        }

        try {

            $usuario->delete();

            return back()->with('alert', [
                'icon' => 'success',
                'title' => 'Usuario eliminado',
                'text' => 'El usuario fue eliminado correctamente.'
            ]);

        } catch (\Exception $e) {

            return back()->with('alert', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => 'No se pudo eliminar el usuario.'
            ]);
        }
    }

    // ============================================================
    // EDITAR USUARIO
    // ============================================================

    public function editar(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'rol' => 'required|integer|exists:roles,id_rol',
            'password' => 'nullable|min:6',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'rol.required' => 'Debes seleccionar un rol.',
            'rol.exists' => 'El rol no es válido.',
            'password.min' => 'La contraseña debe tener mínimo 6 caracteres.',
        ]);

        $usuario = Usuario::with('persona')->find($id);

        if (!$usuario) {

            return back()->with('alert', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'El usuario no existe.'
            ]);
        }

        if ($usuario->persona) {

            $usuario->persona->nombre = $request->nombre;
            $usuario->persona->telefono = $request->telefono;
            $usuario->persona->save();
        }

        $usuario->id_rol = $request->rol;
        $usuario->save();

        if ($request->filled('password')) {

            $usuario->contraseña = Hash::make($request->password);
            $usuario->save();
        }

        return back()->with('alert', [
            'icon' => 'success',
            'title' => 'Usuario actualizado',
            'text' => 'Los datos del usuario fueron actualizados correctamente.'
        ]);
    }
}