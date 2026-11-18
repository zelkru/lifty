$(function () {
    $('html').addClass('js');
    var sinMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var $ventana = $(window);

    function limitarEntreCeroYUno(valor) {
        return Math.min(1, Math.max(0, valor));
    }

    function marcarScroll() {
        $('.tienda-nav').toggleClass('scrolled', $ventana.scrollTop() > 8);
    }

    $ventana.on('scroll', marcarScroll);
    marcarScroll();

    if (sinMovimiento) {
        $('.rv').addClass('vis');
        $('.revela-stage').css({ '--p': 1, '--t': 1, '--u': 1 });
        return;
    }

    var $pista = $('#cintaPista');
    if ($pista.length) {
        var posicion = 0;
        var scrollAnterior = $ventana.scrollTop();
        var impulso = 0;

        (function moverCinta() {
            var scrollActual = $ventana.scrollTop();
            impulso = impulso * 0.92 + Math.abs(scrollActual - scrollAnterior) * 0.35;
            scrollAnterior = scrollActual;
            posicion += 0.9 + impulso;

            var mitadDeLaPista = $pista[0].scrollWidth / 2;
            if (posicion >= mitadDeLaPista) {
                posicion -= mitadDeLaPista;
            }

            $pista[0].style.transform = 'translateX(' + (-posicion).toFixed(1) + 'px)';
            window.requestAnimationFrame(moverCinta);
        })();
    }

    $(document).on('mousemove', '.pcard', function (evento) {
        var caja = this.getBoundingClientRect();
        var desplazamientoX = (evento.clientX - caja.left) / caja.width - 0.5;
        var desplazamientoY = (evento.clientY - caja.top) / caja.height - 0.5;
        this.style.setProperty('--rx', (-desplazamientoY * 6).toFixed(2) + 'deg');
        this.style.setProperty('--ry', (desplazamientoX * 6).toFixed(2) + 'deg');
    }).on('mouseleave', '.pcard', function () {
        this.style.setProperty('--rx', '0deg');
        this.style.setProperty('--ry', '0deg');
    });

    var $elementosPorRevelar = $('.rv');

    function revelarVisibles() {
        var limiteVisible = $ventana.scrollTop() + $ventana.height() * 0.92;

        $elementosPorRevelar.not('.vis').each(function (indice) {
            if ($(this).offset().top < limiteVisible) {
                var $elemento = $(this);
                setTimeout(function () { $elemento.addClass('vis'); }, (indice % 5) * 70);
            }
        });
    }

    $ventana.on('scroll resize', revelarVisibles);
    revelarVisibles();

    var $seccionRevela = $('#revela');
    if ($seccionRevela.length) {
        var escena = $seccionRevela.find('.revela-stage')[0];

        var actualizarRevela = function () {
            var altoVentana = $ventana.height();
            var distanciaArriba = $seccionRevela[0].getBoundingClientRect().top;
            var progreso = limitarEntreCeroYUno((altoVentana * 0.95 - distanciaArriba) / (altoVentana * 0.7));

            escena.style.setProperty('--p', progreso.toFixed(4));
            escena.style.setProperty('--t', limitarEntreCeroYUno((progreso - 0.2) / 0.45).toFixed(4));
            escena.style.setProperty('--u', limitarEntreCeroYUno((progreso - 0.5) / 0.3).toFixed(4));
        };

        $ventana.on('scroll resize', actualizarRevela);
        actualizarRevela();
    }
});
