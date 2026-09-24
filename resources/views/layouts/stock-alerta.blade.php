{{--
    Ícono flotante de alerta de stock.
    Se incluye en cualquier vista o layout con:
        @include('layouts.stock-alerta')
    Solo aparece si hay un usuario logueado. El JS arranca solo y
    evita cargarse dos veces si por error se incluye repetido.
--}}
@auth
    <script>window.STOCK_ALERTAS_URL = "{{ route('stock.alertas') }}";</script>
    <script src="{{ asset('js/stock-alerta.js') }}"></script>
@endauth