<!-- Cierre de la sección de contenido principal (abierta en sidebar.blade.php) -->
</section>

<!-- Footer -->
<footer style="background:#01614B;" class="text-white pt-12 pb-6 font-sans-ventanet">

    <div class="max-w-7xl mx-auto px-8 grid md:grid-cols-3 gap-10">

        <!-- Marca -->
        <div>
            <div class="flex items-center gap-2 mb-3">
                <img src="{{ asset('img/icon.png') }}" width="30" height="30" alt="Logo"
                     style="background:#fff;border-radius:50%;padding:4px;">
                <span class="font-serif-ventanet text-2xl leading-none tracking-tight">VentaNet</span>
            </div>
            <p style="color:rgba(255,255,255,.65); font-size:.85rem; line-height:1.6;" class="pr-4">
                Sistema de gestión de ventas e inventario, pensado para
                simplificar la operación diaria del negocio.
            </p>
        </div>

        <!-- Enlaces rápidos -->
        <div>
            <h4 style="font-size:.85rem; letter-spacing:.04em;"
                class="font-bold uppercase mb-4">Enlaces</h4>
            <ul style="color:rgba(255,255,255,.65); font-size:.85rem;" class="space-y-2.5">
                <li>
                    <a href="#" class="hover:text-white transition-colors flex items-center gap-2">
                        <i class="fas fa-chevron-right" style="font-size:.65rem;color:#61D0A7;"></i>
                        Soporte Técnico
                    </a>
                </li>
                <li>
                    <a href="#" class="hover:text-white transition-colors flex items-center gap-2">
                        <i class="fas fa-chevron-right" style="font-size:.65rem;color:#61D0A7;"></i>
                        Manual de Usuario
                    </a>
                </li>
                <li>
                    <a href="#" class="hover:text-white transition-colors flex items-center gap-2">
                        <i class="fas fa-chevron-right" style="font-size:.65rem;color:#61D0A7;"></i>
                        Política de Privacidad
                    </a>
                </li>
            </ul>
        </div>

        <!-- Contacto -->
        <div>
            <h4 style="font-size:.85rem; letter-spacing:.04em;"
                class="font-bold uppercase mb-4">Contacto</h4>
            <p style="color:rgba(255,255,255,.65); font-size:.85rem;" class="flex items-center gap-3 mb-3">
                <i class="fas fa-envelope" style="color:#61D0A7;"></i> soporte@ventanet.com
            </p>
            <p style="color:rgba(255,255,255,.65); font-size:.85rem;" class="flex items-center gap-3 mb-4">
                <i class="fas fa-phone" style="color:#61D0A7;"></i> +57 300 708 1694
            </p>
            <div class="flex gap-3">
                <a href="#" style="background:rgba(255,255,255,.10);"
                   class="w-9 h-9 rounded-full flex items-center justify-center hover:bg-white hover:text-[#01614B] transition-all">
                    <i class="fab fa-facebook-f" style="font-size:.85rem;"></i>
                </a>
                <a href="#" style="background:rgba(255,255,255,.10);"
                   class="w-9 h-9 rounded-full flex items-center justify-center hover:bg-white hover:text-[#01614B] transition-all">
                    <i class="fab fa-instagram" style="font-size:.85rem;"></i>
                </a>
                <a href="#" style="background:rgba(255,255,255,.10);"
                   class="w-9 h-9 rounded-full flex items-center justify-center hover:bg-white hover:text-[#01614B] transition-all">
                    <i class="fab fa-linkedin-in" style="font-size:.85rem;"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- Línea inferior -->
    <div style="border-top:1px solid rgba(255,255,255,.15);"
         class="max-w-7xl mx-auto mt-10 pt-5 px-8 flex items-center justify-between">
        <span style="color:rgba(255,255,255,.55); font-size:.75rem;">
            &copy; {{ date('Y') }} <strong style="color:#fff;">VentaNet</strong> — Sistema de Gestión
        </span>
        <span style="color:rgba(255,255,255,.55); font-size:.75rem;">
            Diseñado para el agro <i class="fas fa-heart" style="color:#E53935;"></i>
        </span>
    </div>
</footer>

<!-- Cierre del <main> abierto en sidebar.blade.php -->
</main>

<!-- Cierre del <div class="flex min-h-screen"> abierto en header.blade.php -->
</div>
</body>
</html>