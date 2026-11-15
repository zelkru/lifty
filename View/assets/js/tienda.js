/*
 * tienda.js: efectos de la tienda (solo jQuery y CSS, sin librerias extra).
 *  - Cinta diagonal cuya velocidad reacciona al scroll.
 *  - Inclinacion suave de las tarjetas de producto.
 *  - Aparicion de elementos .rv al entrar en pantalla.
 *  - Banner de scroll: el resplandor crece y el titular se enfoca.
 */
$(function () {
    $('html').addClass('js');
    var sinMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var $ventana = $(window);

    function limitar(v) { return Math.min(1, Math.max(0, v)); }

    /* Barra superior con sombra al hacer scroll */
    function marcarScroll() { $('.tienda-nav').toggleClass('scrolled', $ventana.scrollTop() > 8); }
    $ventana.on('scroll', marcarScroll);
    marcarScroll();

    if (sinMovimiento) {
        $('.rv').addClass('vis');
        $('.revela-stage').css({ '--p': 1, '--t': 1, '--u': 1 });
        return;
    }

    /* Cinta diagonal: avanza sola y se acelera con el scroll */
    var $pista = $('#cintaPista');
    if ($pista.length) {
        var posicion = 0, ultimo = $ventana.scrollTop(), boost = 0;
        (function mover() {
            var actual = $ventana.scrollTop();
            boost = boost * 0.92 + Math.abs(actual - ultimo) * 0.35;
            ultimo = actual;
            posicion += 0.9 + boost;
            var mitad = $pista[0].scrollWidth / 2;
            if (posicion >= mitad) { posicion -= mitad; }
            $pista[0].style.transform = 'translateX(' + (-posicion).toFixed(1) + 'px)';
            window.requestAnimationFrame(mover);
        })();
    }

    /* Inclinacion suave de las tarjetas */
    $(document).on('mousemove', '.pcard', function (e) {
        var caja = this.getBoundingClientRect();
        var x = (e.clientX - caja.left) / caja.width - 0.5;
        var y = (e.clientY - caja.top) / caja.height - 0.5;
        this.style.setProperty('--rx', (-y * 6).toFixed(2) + 'deg');
        this.style.setProperty('--ry', (x * 6).toFixed(2) + 'deg');
    }).on('mouseleave', '.pcard', function () {
        this.style.setProperty('--rx', '0deg');
        this.style.setProperty('--ry', '0deg');
    });

    /* Aparicion al entrar en pantalla */
    var $revelar = $('.rv');
    function revisar() {
        var limite = $ventana.scrollTop() + $ventana.height() * 0.92;
        $revelar.not('.vis').each(function (i) {
            if ($(this).offset().top < limite) {
                var $e = $(this);
                setTimeout(function () { $e.addClass('vis'); }, (i % 5) * 70);
            }
        });
    }
    $ventana.on('scroll resize', revisar);
    revisar();

    /* Banner de scroll */
    var $seccion = $('#revela');
    if ($seccion.length) {
        var escena = $seccion.find('.revela-stage')[0];
        var actualizarRevela = function () {
            /* El efecto avanza mientras el banner sube por la ventana (de abajo hacia la mitad) */
            var alto = $ventana.height();
            var arriba = $seccion[0].getBoundingClientRect().top;
            var p = limitar((alto * 0.95 - arriba) / (alto * 0.7));
            escena.style.setProperty('--p', p.toFixed(4));
            escena.style.setProperty('--t', limitar((p - 0.2) / 0.45).toFixed(4));
            escena.style.setProperty('--u', limitar((p - 0.5) / 0.3).toFixed(4));
        };
        $ventana.on('scroll resize', actualizarRevela);
        actualizarRevela();
    }

});
