/* public/js/stock-alerta.js
 * Alerta global de stock: botón flotante con ícono + contador.
 * Al hacer clic se abre la lista de productos por comprar.
 * Se inicia solo en cualquier página donde se cargue.
 *
 * Cada producto debe traer: nombre, stock y stock_minimo.
 */

// 'agotado' -> stock 0 | 'bajo' -> stock POR DEBAJO del mínimo | 'ok' -> se puede vender
function stockEstado(p) {
    var stock  = parseInt(p.stock) || 0;
    var minimo = parseInt(p.stock_minimo) || 0;
    if (stock <= 0) return 'agotado';
    if (stock < minimo) return 'bajo';
    return 'ok';
}

function _escStock(t) {
    var d = document.createElement('div');
    d.textContent = t == null ? '' : t;
    return d.innerHTML;
}

// Toast al hacer clic en un producto bloqueado (SIN temporizador)
function avisarStock(p) {
    var estado = stockEstado(p);
    var stock  = parseInt(p.stock) || 0;

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: estado === 'agotado' ? 'error' : 'warning',
        title: estado === 'agotado' ? 'Producto agotado' : 'Stock bajo',
        text: estado === 'agotado'
            ? '«' + p.nombre + '» está agotado. Debes comprar stock para poder venderlo.'
            : '«' + p.nombre + '» se está agotando (quedan ' + stock + '). Debes comprar stock para poder venderlo.',
        showConfirmButton: false,
        showCloseButton: true
    });
}

// Vacía a propósito: si alguna vista todavía la llama, no truena.
function avisarResumenStock() {}

// ------------------------------------------------------------
// Botón flotante + panel desplegable
// ------------------------------------------------------------
var _stockPanelAbierto = false;

var _ICONO_ALERTA =
    '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
    'stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">' +
    '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>' +
    '<line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';

function _stockInyectarEstilos() {
    if (document.getElementById('stockAlertaStyles')) return;
    var st = document.createElement('style');
    st.id = 'stockAlertaStyles';
    st.textContent =
        '@keyframes stockPulso{0%{box-shadow:0 0 0 0 rgba(229,57,53,.5)}' +
        '70%{box-shadow:0 0 0 12px rgba(229,57,53,0)}100%{box-shadow:0 0 0 0 rgba(229,57,53,0)}}' +
        '#stockAlertaBtn{transition:transform .15s ease;}' +
        '#stockAlertaBtn:hover{transform:scale(1.08);}';
    document.head.appendChild(st);
}

function toggleStockPanel(forzar) {
    var panel = document.getElementById('stockAlertaPanel');
    if (!panel) return;
    _stockPanelAbierto = (typeof forzar === 'boolean') ? forzar : !_stockPanelAbierto;
    panel.style.display = _stockPanelAbierto ? 'block' : 'none';
}

// Cerrar el panel al hacer clic fuera (se registra una sola vez)
if (!window.__stockClickFuera) {
    window.__stockClickFuera = true;
    document.addEventListener('click', function (e) {
        var wrap = document.getElementById('stockAlertaWrap');
        if (wrap && _stockPanelAbierto && !wrap.contains(e.target)) {
            toggleStockPanel(false);
        }
    });
}

function mostrarAlertaStock(lista) {
    var pendientes = (lista || []).filter(function (p) { return stockEstado(p) !== 'ok'; });
    var wrap = document.getElementById('stockAlertaWrap');

    console.log('[stock-alerta] productos por comprar:', pendientes.length, pendientes);

    if (pendientes.length === 0) {
        if (wrap) wrap.remove();
        return;
    }

    _stockInyectarEstilos();

    pendientes.sort(function (a, b) {
        return (parseInt(a.stock) || 0) - (parseInt(b.stock) || 0);
    });

    var hayAgotado = pendientes.some(function (p) { return stockEstado(p) === 'agotado'; });
    var colorBtn   = hayAgotado ? '#E53935' : '#FFB51B';

    var items = pendientes.map(function (p) {
        var agotado = stockEstado(p) === 'agotado';
        return '<li style="display:flex;justify-content:space-between;gap:10px;padding:6px 0;border-bottom:1px solid #F3F4F6;">' +
            '<span style="font-weight:600;color:#171717;">' + _escStock(p.nombre) + '</span>' +
            '<span style="font-weight:700;white-space:nowrap;color:' + (agotado ? '#E53935' : '#B45309') + ';">' +
                (agotado ? 'AGOTADO' : 'Quedan ' + (parseInt(p.stock) || 0) + ' (mín. ' + (parseInt(p.stock_minimo) || 0) + ')') +
            '</span></li>';
    }).join('');

    if (!wrap) {
        wrap = document.createElement('div');
        wrap.id = 'stockAlertaWrap';
        wrap.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:2147483000;font-family:"Outfit",sans-serif;';
        document.body.appendChild(wrap);
    }

    wrap.innerHTML =
        '<div id="stockAlertaPanel" style="display:' + (_stockPanelAbierto ? 'block' : 'none') + ';' +
            'position:absolute;right:0;bottom:64px;width:360px;max-width:calc(100vw - 32px);' +
            'background:#fff;border:1.5px solid #FFB51B;border-radius:14px;' +
            'box-shadow:0 8px 28px rgba(0,0,0,.18);overflow:hidden;">' +
            '<div style="background:#fffbeb;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #FFB51B;">' +
                '<span style="font-weight:800;color:#92400E;font-size:.9rem;">' +
                    'Productos por comprar (' + pendientes.length + ')' +
                '</span>' +
                '<button type="button" onclick="toggleStockPanel(false)" ' +
                    'style="background:none;border:none;cursor:pointer;color:#92400E;font-size:1.3rem;line-height:1;" title="Cerrar">&times;</button>' +
            '</div>' +
            '<ul style="list-style:none;margin:0;padding:6px 14px 10px;max-height:260px;overflow-y:auto;font-size:.82rem;">' +
                items +
            '</ul>' +
        '</div>' +

        '<button id="stockAlertaBtn" type="button" onclick="toggleStockPanel()" title="Productos por comprar" ' +
            'style="position:relative;width:52px;height:52px;border-radius:50%;border:none;cursor:pointer;padding:0;' +
            'background:' + colorBtn + ';color:#fff;display:flex;align-items:center;justify-content:center;' +
            'box-shadow:0 4px 14px rgba(0,0,0,.25);' + (hayAgotado ? 'animation:stockPulso 2s infinite;' : '') + '">' +
            _ICONO_ALERTA +
            '<span style="position:absolute;top:-4px;right:-4px;min-width:20px;height:20px;padding:0 5px;' +
                'border-radius:10px;background:#171717;color:#fff;font-size:.7rem;font-weight:700;' +
                'display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-sizing:border-box;">' +
                pendientes.length +
            '</span>' +
        '</button>';
}

function cargarAlertaStock(url) {
    console.log('[stock-alerta] pidiendo:', url);

    fetch(url, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status + ' en ' + url);
            return r.json();
        })
        .then(mostrarAlertaStock)
        .catch(function (err) {
            console.error('[stock-alerta] falló:', err);
        });
}

// ------------------------------------------------------------
// Arranque automático (una sola vez por página)
// ------------------------------------------------------------
(function () {
    if (window.__stockAlertaIniciada) return;
    window.__stockAlertaIniciada = true;

    function iniciar() {
        cargarAlertaStock(window.STOCK_ALERTAS_URL || '/stock-alertas');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();