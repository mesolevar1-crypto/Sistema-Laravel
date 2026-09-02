<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * Registrar un nuevo usuario
     */
    public function registrar(Request $request)
    {
        // =====================================================
        // VALIDAR DATOS
        // =====================================================

        $request->validate(
            [
                'nombre' => 'required|string|max:255',
                'telefono' => 'required|string|max:20',
                'correo' => 'required|email|max:255',
                'password' => 'required|min:6',
                'confirmar_password' => 'required|same:password',
                'terminos' => 'required',
            ],
            [
                'nombre.required' => 'El nombre es obligatorio.',
                'telefono.required' => 'El teléfono es obligatorio.',
                'correo.required' => 'El correo es obligatorio.',
                'correo.email' => 'El correo no es válido.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener mínimo 6 caracteres.',
                'confirmar_password.required' => 'Debe confirmar la contraseña.',
                'confirmar_password.same' => 'Las contraseñas no coinciden.',
                'terminos.required' => 'Debe aceptar los términos y condiciones.',
            ]
        );

        // =====================================================
        // VERIFICAR SI EL CORREO YA EXISTE
        // =====================================================

        $correoExiste = Persona::where(
            'correo',
            $request->correo
        )->exists();

        if ($correoExiste) {

            return back()
                ->with('registro_alert', [
                    'icon' => 'error',
                    'title' => 'Correo existente',
                    'text' => 'El correo ya está registrado.',
                ])
                ->withInput();
        }

        // =====================================================
        // REGISTRAR PERSONA + USUARIO
        // =====================================================

        try {

            DB::beginTransaction();

            // =================================================
            // CREAR PERSONA
            // =================================================

            $persona = Persona::create([
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'estado' => 1,
            ]);

            // =================================================
            // ROL PREDETERMINADO
            // =================================================
            // El registro público SIEMPRE tendrá rol 1
            // =================================================

            $idRol = 1;

            // Verificar que exista el rol 1

            $rolExiste = Rol::where(
                'id_rol',
                $idRol
            )->exists();

            if (!$rolExiste) {

                DB::rollBack();

                return back()
                    ->with('registro_alert', [
                        'icon' => 'error',
                        'title' => 'Error',
                        'text' => 'El rol 1 no existe en la base de datos.',
                    ])
                    ->withInput();
            }

            // =================================================
            // CREAR USUARIO
            // =================================================

            Usuario::create([
                'contraseña' => Hash::make(
                    $request->password
                ),
                'id_persona' => $persona->id_persona,
                'id_rol' => 1,
                'estado' => 1,
            ]);

            // =================================================
            // CONFIRMAR
            // =================================================

            DB::commit();

            // =================================================
            // REGISTRO EXITOSO
            // =================================================

            return redirect()
                ->route('registro')
                ->with('registro_alert', [
                    'icon' => 'success',
                    'title' => 'Cuenta creada',
                    'text' => 'Tu cuenta fue creada correctamente. Ahora puedes iniciar sesión.',
                ]);

        } catch (\Exception $e) {

            // =================================================
            // DESHACER SI OCURRE UN ERROR
            // =================================================

            DB::rollBack();

            return back()
                ->with('registro_alert', [
                    'icon' => 'error',
                    'title' => 'Error del sistema',
                    'text' => 'No se pudo crear el usuario.',
                ])
                ->withInput();
        }
    }
}