<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VentaNet </title>

  <style>
    /* ── Reset y base ── */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; background: #F8F8F8; color: #171717; }
    a { text-decoration: none; color: inherit; }
    img { display: block; width: 100%; }

    .container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

    /* ── Botones ── */
    .btn-verde {
      background: #00875F; color: #fff;
      padding: 12px 28px; border-radius: 8px;
      font-weight: bold; display: inline-block;
    }
    .btn-verde:hover { background: #01614B; }

    .btn-amarillo {
      background: #FFB51B; color: #1F3552;
      padding: 12px 28px; border-radius: 8px;
      font-weight: bold; display: inline-block;
    }

    /* ════════════════════
       HEADER
    ════════════════════ */
    header {
      background: #fff;
      border-bottom: 1px solid #E5E7EB;
      padding: 16px 0;
      position: sticky; top: 0; z-index: 10;
    }
    .header-inner {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .logo-nombre { font-size: 22px; font-weight: bold; color: #00875F; }
    .logo-sub    { font-size: 11px; color: #5F6673; display: block; }

    nav a {
      margin-left: 20px;
      color: #5F6673;
      font-size: 14px;
      font-weight: 500;
    }
    nav a:hover { color: #00875F; }

    .btn-login {
      background: #00875F; color: #fff !important;
      padding: 8px 18px; border-radius: 8px;
      font-size: 14px; font-weight: bold;
    }
    .btn-login:hover { background: #01614B; }

    /* ════════════════════
       HERO
    ════════════════════ */
    .hero {
      background: #DDF5EC;
      padding: 60px 0;
    }
    .hero-inner {
      display: flex;
      align-items: center;
      gap: 40px;
    }
    .hero-texto { flex: 1; }
    .hero-texto h1 {
      font-size: 36px;
      color: #01614B;
      line-height: 1.3;
      margin-bottom: 16px;
    }
    .hero-texto p {
      font-size: 16px;
      color: #5F6673;
      margin-bottom: 28px;
    }
    .hero-imagen { flex: 1; }
    .hero-imagen img {
      border-radius: 16px;
      max-height: 320px;
      object-fit: cover;
    }

    /* ════════════════════
       CATEGORÍAS
    ════════════════════ */
    .categorias { padding: 60px 0; background: #fff; }
    .categorias h2,
    .productos h2,
    .oferta h2 {
      text-align: center;
      font-size: 28px;
      color: #171717;
      margin-bottom: 36px;
    }

    .categorias-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
    }
    .categoria {
      background: #F8F8F8;
      border: 1px solid #E5E7EB;
      border-radius: 12px;
      padding: 28px 20px;
      text-align: center;
    }
    .categoria:hover { border-color: #61D0A7; }
    .cat-emoji  { font-size: 40px; display: block; margin-bottom: 12px; }
    .categoria h3 { font-size: 16px; color: #171717; margin-bottom: 6px; }
    .categoria p  { font-size: 13px; color: #5F6673; margin-bottom: 14px; }
    .categoria a  { font-size: 13px; color: #00875F; font-weight: bold; }

    /* ════════════════════
       PRODUCTOS
    ════════════════════ */
    .productos { padding: 60px 0; }

    .productos-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
    }
    .producto {
      background: #fff;
      border: 1px solid #E5E7EB;
      border-radius: 12px;
      overflow: hidden;
    }
    .producto:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
    .producto img {
      height: 160px;
      object-fit: cover;
    }
    .producto-info { padding: 14px; }
    .producto-cat  { font-size: 11px; color: #61D0A7; font-weight: bold; text-transform: uppercase; }
    .producto-info h3 { font-size: 15px; color: #171717; margin: 4px 0; }
    .producto-precio  { font-size: 18px; font-weight: bold; color: #00875F; margin: 6px 0; }
    .producto-unidad  { font-size: 12px; color: #5F6673; }

    .disponible { font-size: 12px; color: #00875F; font-weight: bold; display: block; margin: 8px 0; }
    .agotado    { font-size: 12px; color: #E53935; font-weight: bold; display: block; margin: 8px 0; }

    .btn-carrito {
      width: 100%; padding: 10px;
      background: #00875F; color: #fff;
      border: none; border-radius: 8px;
      font-size: 14px; font-weight: bold;
      cursor: pointer; margin-top: 4px;
      display: block; text-align: center;
    }
    .btn-carrito:hover { background: #01614B; }
    .btn-carrito:disabled {
      background: #E5E7EB; color: #5F6673; cursor: not-allowed;
    }


    /* ════════════════════
       FOOTER
    ════════════════════ */
    footer {
      background: #01614B;
      color: #DDF5EC;
      padding: 40px 0 20px;
    }
    .footer-inner {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 30px;
      margin-bottom: 30px;
    }
    footer .logo-nombre { color: #61D0A7; }
    footer .logo-sub    { color: #DDF5EC; opacity: .7; }
    .footer-nav a {
      display: inline-block;
      margin-right: 20px;
      color: #DDF5EC;
      font-size: 14px;
    }
    .footer-nav a:hover { color: #61D0A7; }
    .footer-copy {
      text-align: center;
      font-size: 12px;
      color: #DDF5EC;
      opacity: .6;
      border-top: 1px solid rgba(255,255,255,.15);
      padding-top: 20px;
    }

    /* ════════════════════
       RESPONSIVE
    ════════════════════ */
    @media (max-width: 768px) {
      .hero-inner        { flex-direction: column; }
      .categorias-grid   { grid-template-columns: repeat(2, 1fr); }
      .productos-grid    { grid-template-columns: repeat(2, 1fr); }
      .oferta-inner      { flex-direction: column; text-align: center; }
      .oferta h2         { text-align: center; }
      .footer-inner      { flex-direction: column; }
      nav a:not(.btn-login) { display: none; }
    }

    @media (max-width: 480px) {
      .categorias-grid { grid-template-columns: 1fr; }
      .productos-grid  { grid-template-columns: 1fr; }
      .hero-texto h1   { font-size: 26px; }
    }
  </style>
</head>
<body>

<!-- ============================
     HEADER
============================ -->
<header>
  <div class="container header-inner">
    <div class="logo">
      <span class="logo-nombre">VentaNet</span>
      <span class="logo-sub">Sistema de Gestión</span>
    </div>
    <nav>
      <a href="#inicio">Inicio</a>
      <a href="#productos">Productos</a>
      <a href="#categorias">Categorías</a>
      <a href="#oferta">Ofertas</a>
      <a href="#contacto">Contacto</a>
      <a href="../views/usuarios/login.php" class="btn-login">Iniciar sesión</a>
    </nav>
  </div>
</header>


<!-- ============================
     HERO
============================ -->
<section id="inicio" class="hero">
  <div class="container hero-inner">
    <div class="hero-texto">
      <h1>Frutas y verduras frescas directamente para ti</h1>
      <p>Encuentra productos frescos y de calidad para llevar lo mejor a tu mesa.</p>
      <a href="../views/usuarios/login.php" class="btn-verde">Comprar ahora</a>
    </div>
    <div class="hero-imagen">
      <img src="https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=600&q=80" alt="Frutas y verduras frescas">
    </div>
  </div>
</section>


<!-- ============================
     CATEGORÍAS
============================ -->
<section id="categorias" class="categorias">
  <div class="container">
    <h2>Explora nuestras categorías</h2>
    <div class="categorias-grid">

      <div class="categoria">
        <span class="cat-emoji">🍎</span>
        <h3>Frutas</h3>
        <p>Naranjas, manzanas, bananos y más.</p>
        <a href="#productos">Ver productos</a>
      </div>

      <div class="categoria">
        <span class="cat-emoji">🥕</span>
        <h3>Verduras</h3>
        <p>Zanahoria, tomate, cebolla y más.</p>
        <a href="#productos">Ver productos</a>
      </div>

      <div class="categoria">
        <span class="cat-emoji">🌾</span>
        <h3>Granos</h3>
        <p>Arroz, lentejas, frijoles y más.</p>
        <a href="#productos">Ver productos</a>
      </div>

      <div class="categoria">
        <span class="cat-emoji">🥬</span>
        <h3>Productos frescos</h3>
        <p>Lechuga, espinaca, cilantro y más.</p>
        <a href="#productos">Ver productos</a>
      </div>

    </div>
  </div>
</section>


<!-- ============================
     PRODUCTOS
============================ -->
<section id="productos" class="productos">
  <div class="container">
    <h2>Productos destacados</h2>
    <div class="productos-grid">

      <?php
      // Lista de productos — aquí puedes agregar o quitar fácilmente
      $productos = [
        ["nombre" => "Banano",       "categoria" => "Frutas",            "precio" => "$3.000", "unidad" => "kg",     "disponible" => true,  "img" => "https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=400&q=80"],
        ["nombre" => "Manzana Roja", "categoria" => "Frutas",            "precio" => "$5.000", "unidad" => "kg",     "disponible" => true,  "img" => "https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400&q=80"],
        ["nombre" => "Zanahoria",    "categoria" => "Verduras",          "precio" => "$2.500", "unidad" => "kg",     "disponible" => true,  "img" => "https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=400&q=80"],
        ["nombre" => "Papa Criolla", "categoria" => "Verduras",          "precio" => "$3.500", "unidad" => "kg",     "disponible" => true,  "img" => "https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=400&q=80"],
        ["nombre" => "Plátano",      "categoria" => "Frutas",            "precio" => "$4.000", "unidad" => "kg",     "disponible" => true,  "img" => "https://images.unsplash.com/photo-1614949941545-46b9fb8e2ac0?w=400&q=80"],
        ["nombre" => "Tomate",       "categoria" => "Verduras",          "precio" => "$3.000", "unidad" => "kg",     "disponible" => true,  "img" => "https://images.unsplash.com/photo-1546094096-0df4bcaad337?w=400&q=80"],
        ["nombre" => "Cebolla",      "categoria" => "Verduras",          "precio" => "$2.800", "unidad" => "kg",     "disponible" => false, "img" => "https://images.unsplash.com/photo-1618512496248-a07fe83aa8cb?w=400&q=80"],
        ["nombre" => "Lechuga",      "categoria" => "Productos frescos", "precio" => "$2.000", "unidad" => "unidad", "disponible" => true,  "img" => "https://images.unsplash.com/photo-1622206151226-18ca2c9ab4a1?w=400&q=80"],
      ];

      // Recorremos el arreglo y mostramos cada tarjeta
      foreach ($productos as $p):
      ?>
      <div class="producto">
        <img src="<?= $p['img'] ?>" alt="<?= $p['nombre'] ?>">
        <div class="producto-info">
          <span class="producto-cat"><?= $p['categoria'] ?></span>
          <h3><?= $p['nombre'] ?></h3>
          <p class="producto-precio"><?= $p['precio'] ?></p>
          <span class="producto-unidad">por <?= $p['unidad'] ?></span>
          <?php if ($p['disponible']): ?>
            <span class="disponible">● Disponible</span>
            <a href="../views/usuarios/login.php" class="btn-carrito">Comprar</a>
          <?php else: ?>
            <span class="agotado">● Agotado</span>
            <button class="btn-carrito" disabled>Sin stock</button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </div>
</section>



<!-- ============================
     FOOTER
============================ -->
<footer id="contacto">
  <div class="container footer-inner">
    <div class="logo">
      <span class="logo-nombre">VentaNet</span>
      <span class="logo-sub">Sistema de Gestión</span>
    </div>
    <nav class="footer-nav">
      <a href="#inicio">Inicio</a>
      <a href="#productos">Productos</a>
      <a href="#categorias">Categorías</a>
      <a href="#oferta">Ofertas</a>
      <a href="#contacto">Contacto</a>
    </nav>
  </div>
  <div class="container">
    <p class="footer-copy">© 2026 VentaNet. Todos los derechos reservados.</p>
  </div>
</footer>

</body>
</html>
