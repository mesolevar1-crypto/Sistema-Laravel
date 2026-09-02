<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const MAX_INTENTOS = 5;
    private const TIEMPO_BLOQUEO = 120;

    public function mostrarLogin(Request $request)
    {
        $this->revisarBloqueo($request);

        $bloqueado = $request->session()->get('login_bloqueado', false);

        return view('autenticacion.login', compact('bloqueado'));
    }

    public function login(Request $request)
    {
        $this->revisarBloqueo($request);

        if ($request->session()->get('login_bloqueado', false)) {

            return back()->with('alert', [
                'icon' => 'error',
                'title' => 'Acceso bloqueado',
                'text' => 'Demasiados intentos fallidos. Espera 2 minutos antes de intentar de nuevo.',
            ]);
        }

        $request->validate(
            [
                'correo' => 'required|email',
                'password' => 'required',
            ],
            [
                'correo.required' => 'Debe ingresar el correo.',
                'correo.email' => 'Debe ingresar un correo válido.',
                'password.required' => 'Debe ingresar la contraseña.',
            ]
        );

        $usuario = Usuario::with(['persona', 'rol'])
            ->whereHas('persona', function ($query) use ($request) {
                $query->where('correo', $request->correo);
            })
            ->first();

        if (!$usuario) {

            $this->registrarIntentoFallido($request);

            return back()
                ->with('alert', [
                    'icon' => 'error',
                    'title' => 'Correo no encontrado',
                    'text' => 'El correo no está registrado.',
                ])
                ->withInput();
        }

        if ((int) $usuario->estado !== 1) {

            return back()->with('alert', [
                'icon' => 'error',
                'title' => 'Cuenta inactiva',
                'text' => 'Tu cuenta está desactivada.',
            ]);
        }

        if ($usuario->persona && (int) $usuario->persona->estado !== 1) {

            return back()->with('alert', [
                'icon' => 'error',
                'title' => 'Cuenta inactiva',
                'text' => 'Tu cuenta está desactivada.',
            ]);
        }

        if (!Hash::check($request->password, $usuario->contraseña)) {

            $this->registrarIntentoFallido($request);

            return back()
                ->with('alert', [
                    'icon' => 'error',
                    'title' => 'Contraseña incorrecta',
                    'text' => 'La contraseña no coincide.',
                ])
                ->withInput();
        }

        $request->session()->forget([
            'login_intentos',
            'login_bloqueado',
            'login_tiempo',
        ]);

        $request->session()->regenerate();

        // =====================================================
        // AUTENTICAR CON LARAVEL (para que auth()->user() funcione
        // en los layouts, sidebars, etc.)
        // =====================================================

        auth()->login($usuario);

        // Se mantiene también tu sesión manual por si la usas
        // en otras partes del código.
        session([
            'usuario' => [
                'id_usuario' => $usuario->id_usuario,
                'id_persona' => $usuario->id_persona,
                'nombre' => $usuario->persona->nombre,
                'email' => $usuario->persona->correo,
                'telefono' => $usuario->persona->telefono,
                'rol_id' => $usuario->id_rol,
                'rol' => $usuario->rol->nombre,
            ]
        ]);

        // =====================================================
        // REDIRECCIÓN SEGÚN ROL
        // =====================================================

        if ((int) $usuario->id_rol === 1) {

            // ROL 1 = ADMINISTRADOR
            return redirect()->route('inicio.index');

        } else {

            // OTROS ROLES = VENDEDOR
            return redirect()->route('vendedor.dashboard');
        }
    }

    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function revisarBloqueo(Request $request): void
    {
        $bloqueado = $request->session()->get('login_bloqueado', false);
        $tiempoBloqueo = $request->session()->get('login_tiempo', 0);

        if ($bloqueado && $tiempoBloqueo > 0) {

            $tiempoPasado = time() - $tiempoBloqueo;

            if ($tiempoPasado >= self::TIEMPO_BLOQUEO) {

                $request->session()->forget([
                    'login_intentos',
                    'login_bloqueado',
                    'login_tiempo',
                ]);
            }
        }
    }

    private function registrarIntentoFallido(Request $request): void
    {
        $intentos = $request->session()->get('login_intentos', 0) + 1;

        $request->session()->put('login_intentos', $intentos);

        if ($intentos >= self::MAX_INTENTOS) {

            $request->session()->put('login_bloqueado', true);
            $request->session()->put('login_tiempo', time());
        }
    }
}