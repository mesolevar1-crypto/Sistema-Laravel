<?php
session_start();

/* =====================================================
   CONTROLAR BLOQUEO DEL LOGIN
===================================================== */

$bloqueado = $_SESSION['login_bloqueado'] ?? false;
$tiempoBloqueo = $_SESSION['login_tiempo'] ?? 0;

/* Si está bloqueado, comprobar si ya pasaron 2 minutos */

if ($bloqueado && $tiempoBloqueo > 0) {

    $tiempoPasado = time() - $tiempoBloqueo;

    if ($tiempoPasado >= 120) {

        /* DESBLOQUEAR */

        $_SESSION['login_intentos'] = 0;
        $_SESSION['login_bloqueado'] = false;
        $_SESSION['login_tiempo'] = 0;

        /* ELIMINAR CUALQUIER ALERTA ANTERIOR */

        unset($_SESSION['alert']);

        $bloqueado = false;
    }
}


/* =====================================================
   ALERTA
===================================================== */

$alert = $_SESSION['alert'] ?? null;


/* Si ya no está bloqueado, eliminar alerta de bloqueo */

if (!$bloqueado) {

    if (
        isset($alert['title']) &&
        $alert['title'] === 'Acceso bloqueado'
    ) {
        $alert = null;
        unset($_SESSION['alert']);
    }
}


/* Evitar que la alerta quede guardada */

unset($_SESSION['alert']);

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VentaNet | Iniciar Sesión</title>
<link rel="shortcut icon" type="image/png" href="../../img/icon.png">

<style>
*{box-sizing:border-box;margin:0;padding:0}

body{
    font-family:Arial,sans-serif;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#F8F8F8;
    padding:20px
}

a{text-decoration:none}

.tarjeta{
    display:flex;
    width:100%;
    max-width:1000px;
    min-height:600px;
    background:#fff;
    border-radius:20px;
    box-shadow:0 16px 48px rgba(0,0,0,.10);
    overflow:hidden
}

.panel-form{
    flex:1;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:48px 40px
}

.form-contenedor{
    width:100%;
    max-width:380px
}

.form-header{
    text-align:center;
    margin-bottom:32px
}

.form-header img{
    width:64px;
    display:block;
    margin:0 auto 10px
}

.logo-nombre{
    font-size:28px;
    font-weight:bold;
    color:#01614B
}

.form-header p{
    font-size:14px;
    color:#5F6673;
    margin-top:6px;
    line-height:1.5
}

.campo{margin-bottom:16px}

.campo label{
    display:block;
    font-size:13px;
    font-weight:bold;
    color:#171717;
    margin-bottom:6px
}

.input-wrap{position:relative}

.icono{
    position:absolute;
    left:13px;
    top:50%;
    transform:translateY(-50%);
    display:flex;
    pointer-events:none
}

.icono svg{
    width:17px;
    height:17px;
    stroke:#9ABFA3;
    fill:none;
    stroke-width:1.6;
    stroke-linecap:round;
    stroke-linejoin:round
}

.input-wrap input{
    width:100%;
    padding:12px 38px;
    border:1.5px solid #E5E7EB;
    border-radius:10px;
    font-size:14px;
    background:#F3F4F6;
    color:#171717;
    outline:none
}

.input-wrap input:focus{
    border-color:#61D0A7;
    background:#DDF5EC
}

.input-wrap input::placeholder{color:#9CA3AF}

.input-password{padding-right:42px!important}

.ojo-label{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    display:flex
}

.ojo-label svg{
    width:17px;
    height:17px;
    fill:none;
    stroke:#9ABFA3;
    stroke-width:1.6;
    stroke-linecap:round;
    stroke-linejoin:round
}

.ojo-label:hover svg{stroke:#00875F}

.fila-opciones{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:4px 0 24px
}

.recordar{
    display:flex;
    align-items:center;
    gap:7px;
    font-size:13px;
    color:#5F6673
}

.recordar input{
    accent-color:#00875F;
    width:15px;
    height:15px
}

.link-olvide{
    font-size:13px;
    color:#00875F;
    font-weight:bold
}

.link-olvide:hover,
.pie-form a:hover{
    color:#01614B;
    text-decoration:underline
}

.btn-ingresar{
    width:100%;
    background:#00875F;
    color:#fff;
    border:0;
    border-radius:10px;
    padding:13px;
    font-size:15px;
    font-weight:bold;
    cursor:pointer;
    box-shadow:0 4px 14px rgba(0,135,95,.28)
}

.btn-ingresar:hover{background:#01614B}

.btn-ingresar:disabled{
    opacity:.5;
    cursor:not-allowed
}

.pie-form{
    text-align:center;
    font-size:14px;
    color:#5F6673;
    margin-top:24px
}

.pie-form a{
    color:#00875F;
    font-weight:bold
}

.panel-imagen{
    flex:1;
    position:relative;
    background:url('https://images.unsplash.com/photo-1542838132-92c53300491e?w=900&q=80') center/cover;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    padding:32px
}

.panel-imagen:before{
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(
        to bottom,
        rgba(0,0,0,.45),
        rgba(0,0,0,.10) 50%,
        rgba(1,97,75,.85)
    )
}

.btn-regresar{
    position:relative;
    z-index:1;
    align-self:flex-end;
    color:#fff;
    border:2px solid rgba(255,255,255,.60);
    padding:10px 20px;
    border-radius:8px;
    font-size:14px;
    font-weight:bold;
    background:rgba(1,97,75,.40)
}

.btn-regresar:hover{background:rgba(1,97,75,.70)}

.imagen-texto{
    position:relative;
    z-index:1;
    color:#fff
}

.imagen-texto h2{
    font-size:34px;
    line-height:1.2;
    margin-bottom:12px
}

.imagen-texto p{
    font-size:14px;
    line-height:1.6;
    opacity:.9
}

.modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:1000
}

.modal-caja{
    background:#fff;
    border-radius:20px;
    padding:40px 36px 32px;
    width:90%;
    max-width:380px;
    text-align:center;
    box-shadow:0 20px 60px rgba(0,0,0,.15)
}

.modal-icono{
    width:72px;
    height:72px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 20px
}

.modal-icono svg{
    width:36px;
    height:36px;
    fill:none;
    stroke-width:2.5;
    stroke-linecap:round;
    stroke-linejoin:round
}

.modal-icono.exito{background:#DDF5EC}
.modal-icono.error{background:#fde8e8}
.modal-icono.aviso{background:#fffbeb}
.modal-icono.exito svg{stroke:#00875F}
.modal-icono.error svg{stroke:#E53935}
.modal-icono.aviso svg{stroke:#FFB51B}

.modal-titulo{
    font-size:20px;
    font-weight:bold;
    color:#171717;
    margin-bottom:8px
}

.modal-texto{
    font-size:14px;
    color:#5F6673;
    margin-bottom:28px;
    line-height:1.5
}

.modal-btn{
    padding:11px 48px;
    border:0;
    border-radius:10px;
    font-size:15px;
    font-weight:bold;
    cursor:pointer;
    color:#fff
}

.modal-btn.exito{background:#00875F}
.modal-btn.exito:hover{background:#01614B}
.modal-btn.error{background:#E53935}
.modal-btn.aviso{
    background:#FFB51B;
    color:#1F3552
}

@media(max-width:768px){
    body{padding:0;align-items:stretch}
    .tarjeta{
        flex-direction:column;
        border-radius:0;
        min-height:100vh
    }
    .panel-imagen{
        flex:none;
        min-height:220px;
        padding:20px
    }
    .imagen-texto h2{font-size:22px}
    .panel-form{padding:32px 24px}
}
</style>
</head>

<body>

<div class="tarjeta">

    <div class="panel-form">
        <div class="form-contenedor">

            <div class="form-header">
                <img src="../../img/icon.png" alt="VentaNet">
                <div class="logo-nombre">VentaNet</div>
                <p>Bienvenido, ingresa tus credenciales<br>para iniciar sesión</p>
            </div>

            <form action="../../controllers/AuthController.php" method="POST">

                <div class="campo">
                    <label for="correo">Correo electrónico</label>
                    <div class="input-wrap">
                        <span class="icono">
                            <svg viewBox="0 0 24 24">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                        </span>
                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            placeholder="correo@ejemplo.com"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div class="campo">
                    <label for="campo-pass">Contraseña</label>
                    <div class="input-wrap">
                        <span class="icono">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>

                        <input
                            class="input-password"
                            id="campo-pass"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >

                        <span class="ojo-label" id="ojo-btn" onclick="togglePass()">
                            <svg viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="fila-opciones">
                    <label class="recordar">
                        <input type="checkbox" name="remember">
                        Recordar mi sesión
                    </label>

                    <a href="#" class="link-olvide">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button
                    type="submit"
                    class="btn-ingresar"
                    <?= $bloqueado ? 'disabled' : '' ?>
                >
                    Iniciar sesión
                </button>

            </form>

            <div class="pie-form">
    ¿No tienes cuenta?
    <a href="{{ route('registre') }}">Regístrate aquí</a>
</div>

        </div>
    </div>

    <div class="panel-imagen">

        <a href="{{ route('welcome') }}" class="btn-regresar">
    ← Regresar al Inicio
</a>

        <div class="imagen-texto">
            <h2>Frescura y Calidad<br>Directo a tu Mesa</h2>
            <p>
                Accede a VentaNet para gestionar tus compras,
                revisar tus pedidos y descubrir lo mejor del
                campo en un solo lugar.
            </p>
        </div>

    </div>

</div>

<?php if ($alert || $bloqueado): ?>

<?php
if ($bloqueado && !$alert) {
    $mTipo = 'error';
    $mTitulo = 'Acceso bloqueado';
    $mTexto = 'Demasiados intentos fallidos. Espera 2 minutos antes de intentar de nuevo.';
} else {
    $mTipo = ($alert['icon'] ?? '') === 'success'
        ? 'exito'
        : (($alert['icon'] ?? '') === 'warning' ? 'aviso' : 'error');

    $mTitulo = $alert['title'] ?? 'Error';
    $mTexto = $alert['text'] ?? 'No fue posible iniciar sesión.';
}
?>

<div class="modal-overlay">
    <div class="modal-caja">

        <div class="modal-icono <?= $mTipo ?>">

            <?php if ($mTipo === 'exito'): ?>

                <svg viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>

            <?php elseif ($mTipo === 'error'): ?>

                <svg viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>

            <?php else: ?>

                <svg viewBox="0 0 24 24">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>

            <?php endif; ?>

        </div>

        <p class="modal-titulo"><?= htmlspecialchars($mTitulo) ?></p>
        <p class="modal-texto"><?= htmlspecialchars($mTexto) ?></p>

        <a href="login.php">
            <button class="modal-btn <?= $mTipo ?>">OK</button>
        </a>

    </div>
</div>

<?php endif; ?>

<script>
const SVG_VER = '<svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>';

const SVG_OCULTO = '<svg viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

function togglePass(){
    const input = document.getElementById('campo-pass');
    const boton = document.getElementById('ojo-btn');

    if(input.type === 'password'){
        input.type = 'text';
        boton.innerHTML = SVG_OCULTO;
    }else{
        input.type = 'password';
        boton.innerHTML = SVG_VER;
    }
}
</script>

</body>
</html>