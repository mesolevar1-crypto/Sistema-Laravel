# welcome.blade.php

**Ubicación:** `resources/views/welcome.blade.php`

---

## Para qué sirve

Es la página pública de bienvenida del sistema. Muestra información sobre los productos disponibles (frutas, verduras, etc.) sin requerir que el usuario esté autenticado. Es la primera página que ve cualquier visitante.

## Quién la usa

Cualquier visitante (público general, sin necesidad de login).

## Ruta y controlador

| Campo | Valor |
|-------|-------|
| URL | `/` |
| Nombre de ruta | `welcome` |
| Controlador | Ninguno — la ruta llama directamente a `view('welcome')` |

## Layout que extiende / vistas que incluye

No extiende ningún layout. Es un HTML completo autocontenido.

## Datos que recibe

No recibe variables desde PHP. Los productos mostrados están definidos directamente en la vista como un arreglo PHP `$productos`.

## Secciones principales de la pantalla

| Sección | Descripción |
|---------|-------------|
| **Header** | Barra de navegación con logo, enlaces a secciones y botón "Iniciar sesión" |
| **Hero** | Sección destacada con imagen y llamado a la acción "Comprar ahora" |
| **Categorías** | Grid de 4 categorías (Frutas, Verduras, Granos, Productos frescos) con emojis |
| **Productos destacados** | Grid de 8 tarjetas de productos, cada una con imagen, categoría, nombre y descripción |
| **Footer** | Pie de página con logo, navegación y copyright |

## Modales y acciones

No tiene modales. Los botones "Comprar ahora" e "Iniciar sesión" redirigen a la ruta `login`.

## JavaScript de la vista

No hay funciones JavaScript propias. Los enlaces de navegación son anclas HTML simples (`#inicio`, `#productos`, `#categorias`).

## Dependencias

| Recurso | Tipo | Descripción |
|---------|------|-------------|
| CSS inline `<style>` | Estilos | Todos los estilos están embebidos en la misma vista |
| Imágenes de Wikimedia Commons | Externo | URLs directas de imágenes para los productos |
