
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>VentaNet | Crear cuenta</title>

    <link rel="shortcut icon"
          type="image/png"
          href="{{ asset('img/icon.png') }}">

    <style>

        /* ── Reset ── */

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F8F8F8;
            padding: 20px;
        }

        a {
            text-decoration: none;
        }

        /* ════════════════════
           TARJETA PRINCIPAL
        ════════════════════ */

        .tarjeta {
            display: flex;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.10);
            overflow: hidden;
        }

        /* ════════════════════
           PANEL IZQUIERDO — Formulario
        ════════════════════ */

        .panel-form {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 36px;
            background: #fff;
        }

        .form-contenedor {
            width: 100%;
            max-width: 400px;
        }

        /* Logo y título */

        .form-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .form-header img {
            width: 64px;
            display: block;
            margin: 0 auto 8px;
        }

        .logo-nombre {
            font-size: 26px;
            font-weight: bold;
            color: #01614B;
        }

        .form-header h1 {
            font-size: 18px;
            color: #171717;
            margin-top: 4px;
        }

        .form-header p {
            font-size: 13px;
            color: #5F6673;
            margin-top: 3px;
        }

        /* Campos */

        .campo {
            margin-bottom: 14px;
        }

        .campo label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #171717;
            margin-bottom: 5px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .icono {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        .input-wrap .icono svg {
            width: 17px;
            height: 17px;
            stroke: #9ABFA3;
            fill: none;
            stroke-width: 1.6;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .input-wrap input {
            width: 100%;
            padding: 11px 12px 11px 36px;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            background: #F3F4F6;
            color: #171717;
            outline: none;
        }

        .input-wrap input::placeholder {
            color: #9CA3AF;
        }

        .input-wrap input:focus {
            border-color: #61D0A7;
            background: #DDF5EC;
        }

        /* Dos columnas */

        .fila-doble {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        .fila-doble .campo {
            margin-bottom: 0;
        }

        /* Términos */

        .terminos {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 14px 0;
        }

        .terminos input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #00875F;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .terminos label {
            font-size: 13px;
            color: #5F6673;
        }

        .terminos label a {
            color: #00875F;
            font-weight: bold;
        }

        /* ════════════════════
           MODAL DE ALERTAS
        ════════════════════ */

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-caja {
            background: #fff;
            border-radius: 20px;
            padding: 40px 36px 32px;
            width: 90%;
            max-width: 380px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .modal-icono {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .modal-icono svg {
            width: 36px;
            height: 36px;
            fill: none;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .modal-icono.exito {
            background: #DDF5EC;
        }

        .modal-icono.error {
            background: #fde8e8;
        }

        .modal-icono.aviso {
            background: #fffbeb;
        }

        .modal-icono.exito svg {
            stroke: #00875F;
        }

        .modal-icono.error svg {
            stroke: #E53935;
        }

        .modal-icono.aviso svg {
            stroke: #FFB51B;
        }

        .modal-titulo {
            font-size: 20px;
            font-weight: bold;
            color: #171717;
            margin-bottom: 8px;
        }

        .modal-texto {
            font-size: 14px;
            color: #5F6673;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .modal-btn {
            padding: 11px 48px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            color: #fff;
        }

        .modal-btn.exito {
            background: #00875F;
        }

        .modal-btn.exito:hover {
            background: #01614B;
        }

        .modal-btn.error {
            background: #E53935;
        }

        .modal-btn.aviso {
            background: #FFB51B;
            color: #1F3552;
        }

        /* Botón */

        .btn-crear {
            width: 100%;
            background: #00875F;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 135, 95, 0.28);
        }

        .btn-crear:hover {
            background: #01614B;
        }

        /* Enlace al login */

        .pie-form {
            text-align: center;
            font-size: 14px;
            color: #5F6673;
            margin-top: 20px;
        }

        .pie-form a {
            color: #00875F;
            font-weight: bold;
        }

        .pie-form a:hover {
            color: #01614B;
            text-decoration: underline;
        }

        /* ════════════════════
           PANEL DERECHO — Imagen
        ════════════════════ */

        .panel-imagen {
            flex: 1;
            position: relative;
            background-image: url('https://images.unsplash.com/photo-1542838132-92c53300491e?w=900&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 32px;
        }

        /* Capa oscura */

        .panel-imagen::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(0, 0, 0, 0.45) 0%,
                rgba(0, 0, 0, 0.10) 50%,
                rgba(1, 97, 75, 0.85) 100%
            );
        }

        /* Botón volver */

        .btn-volver {
            position: relative;
            z-index: 1;
            align-self: flex-start;
            display: inline-block;
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.60);
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            background: rgba(1, 97, 75, 0.40);
        }

        .btn-volver:hover {
            background: rgba(1, 97, 75, 0.70);
        }

        /* Texto inferior */

        .imagen-texto {
            position: relative;
            z-index: 1;
            color: #fff;
        }

        .imagen-texto h2 {
            font-size: 34px;
            line-height: 1.2;
            margin-bottom: 10px;
        }

        .imagen-texto p {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.90;
        }

        /* ════════════════════
           ERRORES DE VALIDACIÓN
        ════════════════════ */

        .errores {
            background: #fde8e8;
            border: 1px solid #E53935;
            color: #B91C1C;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .errores ul {
            margin-left: 18px;
        }

        /* ════════════════════
           RESPONSIVE
        ════════════════════ */

        @media (max-width: 768px) {

            body {
                padding: 0;
                align-items: stretch;
            }

            .tarjeta {
                flex-direction: column;
                border-radius: 0;
                min-height: 100vh;
            }

            .panel-imagen {
                flex: none;
                min-height: 200px;
                padding: 20px;
            }

            .imagen-texto h2 {
                font-size: 22px;
            }

            .panel-form {
                padding: 28px 20px;
            }

            .fila-doble {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="tarjeta">

    <!-- ============================
         PANEL IZQUIERDO — Imagen
    ============================ -->

    <div class="panel-imagen">

        <a href="{{ route('login') }}" class="btn-volver">
            ← Volver al Login
        </a>

        <div class="imagen-texto">

            <h2>
                Únete a la Familia<br>
                VentaNet
            </h2>

            <p>
                Regístrate y disfruta de nuestros productos frescos.
            </p>

        </div>

    </div>


    <!-- ============================
         PANEL DERECHO — Formulario
    ============================ -->

    <div class="panel-form">

        <div class="form-contenedor">

            <div class="form-header">

                <img
                    src="{{ asset('img/icon.png') }}"
                    alt="VentaNet"
                >

                <div class="logo-nombre">
                    VentaNet
                </div>

                <h1>
                    Crear tu cuenta
                </h1>

                <p>
                    Crea tu cuenta para comenzar
                </p>

            </div>


            <!-- ============================
                 ERRORES DE VALIDACIÓN
            ============================ -->

            @if ($errors->any())

                <div class="errores">

                    <ul>

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <!-- ============================
                 FORMULARIO
            ============================ -->

            <form
                action="{{ route('registro.guardar') }}"
                method="POST"
            >

                @csrf


                <!-- Nombre + Teléfono -->

                <div class="fila-doble">

                    <div class="campo">

                        <label>
                            Nombre completo
                        </label>

                        <div class="input-wrap">

                            <span class="icono">

                                <svg viewBox="0 0 24 24">

                                    <circle
                                        cx="12"
                                        cy="8"
                                        r="4"
                                    />

                                    <path
                                        d="M4 20c0-4 3.6-7 8-7s8 3 8 7"
                                    />

                                </svg>

                            </span>

                            <input
                                type="text"
                                name="nombre"
                                placeholder="Ingresar nombre"
                                value="{{ old('nombre') }}"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label>
                            Teléfono
                        </label>

                        <div class="input-wrap">

                            <span class="icono">

                                <svg viewBox="0 0 24 24">

                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2A19.86 19.86 0 0 1 3.09 4.18 2 2 0 0 1 5.07 2h3a2 2 0 0 1 2 1.72c.13 1 .37 1.97.72 2.9a2 2 0 0 1-.45 2.11L9.09 9.91a16 16 0 0 0 6 6l1.18-1.18a2 2 0 0 1 2.11-.45c.93.35 1.9.59 2.9.72A2 2 0 0 1 22 16.92Z"
                                    />

                                </svg>

                            </span>

                            <input
                                type="tel"
                                name="telefono"
                                placeholder="3001234567"
                                value="{{ old('telefono') }}"
                                required
                            >

                        </div>

                    </div>

                </div>


                <!-- Correo -->

                <div class="campo">

                    <label>
                        Correo electrónico
                    </label>

                    <div class="input-wrap">

                        <span class="icono">

                            <svg viewBox="0 0 24 24">

                                <rect
                                    x="2"
                                    y="4"
                                    width="20"
                                    height="16"
                                    rx="2"
                                />

                                <path
                                    d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"
                                />

                            </svg>

                        </span>

                        <input
                            type="email"
                            name="correo"
                            placeholder="correo@ejemplo.com"
                            value="{{ old('correo') }}"
                            required
                        >

                    </div>

                </div>


                <!-- Contraseña + Confirmar -->

                <div class="fila-doble">

                    <div class="campo">

                        <label>
                            Contraseña
                        </label>

                        <div class="input-wrap">

                            <span class="icono">

                                <svg viewBox="0 0 24 24">

                                    <rect
                                        x="3"
                                        y="11"
                                        width="18"
                                        height="11"
                                        rx="2"
                                    />

                                    <path
                                        d="M7 11V7a5 5 0 0 1 10 0v4"
                                    />

                                </svg>

                            </span>

                            <input
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label>
                            Confirmar contraseña
                        </label>

                        <div class="input-wrap">

                            <span class="icono">

                                <svg viewBox="0 0 24 24">

                                    <rect
                                        x="3"
                                        y="11"
                                        width="18"
                                        height="11"
                                        rx="2"
                                    />

                                    <path
                                        d="M7 11V7a5 5 0 0 1 10 0v4"
                                    />

                                </svg>

                            </span>

                            <input
                                type="password"
                                name="confirmar_password"
                                placeholder="••••••••"
                                required
                            >

                        </div>

                    </div>

                </div>


                <!-- Términos -->

                <div class="terminos">

                    <input
                        type="checkbox"
                        name="terminos"
                        id="terminos"
                        value="1"
                        required
                    >

                    <label for="terminos">

                        Acepto los

                        <a href="#">
                            términos y condiciones
                        </a>

                        del sistema

                    </label>

                </div>


                <!-- Botón -->

                <button
                    type="submit"
                    class="btn-crear"
                >
                    Crear cuenta
                </button>

            </form>


            <!-- Volver al login -->

            <div class="pie-form">

                ¿Ya tienes una cuenta?

                <a href="{{ route('login') }}">
                    Inicia sesión
                </a>

            </div>

        </div>

    </div>

</div>


<!-- ============================
     MODAL DE ALERTAS
============================ -->

@if (session('registro_alert'))

    @php

        $alert = session('registro_alert');

        $tipo = $alert['icon'] === 'success'
            ? 'exito'
            : ($alert['icon'] === 'warning'
                ? 'aviso'
                : 'error');

    @endphp


    <div class="modal-overlay">

        <div class="modal-caja">

            <div class="modal-icono {{ $tipo }}">

                @if ($tipo === 'exito')

                    <svg viewBox="0 0 24 24">

                        <polyline
                            points="20 6 9 17 4 12"
                        />

                    </svg>

                @elseif ($tipo === 'error')

                    <svg viewBox="0 0 24 24">

                        <line
                            x1="18"
                            y1="6"
                            x2="6"
                            y2="18"
                        />

                        <line
                            x1="6"
                            y1="6"
                            x2="18"
                            y2="18"
                        />

                    </svg>

                @else

                    <svg viewBox="0 0 24 24">

                        <path
                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                        />

                        <line
                            x1="12"
                            y1="9"
                            x2="12"
                            y2="13"
                        />

                        <line
                            x1="12"
                            y1="17"
                            x2="12.01"
                            y2="17"
                        />

                    </svg>

                @endif

            </div>


            <p class="modal-titulo">
                {{ $alert['title'] }}
            </p>


            <p class="modal-texto">
                {{ $alert['text'] }}
            </p>


            <a href="{{ $alert['icon'] === 'success'
                ? route('login')
                : route('registro') }}">

                <button
                    type="button"
                    class="modal-btn {{ $tipo }}"
                >
                    OK
                </button>

            </a>

        </div>

    </div>

@endif

</body>

</html>
