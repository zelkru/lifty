$(function () {
    function mostrarCamposDePago() {
        var tipoPago = $('.metodo-pago:checked').data('tipo');
        $('.pay-box').removeClass('active').filter('[data-tipo="' + tipoPago + '"]').addClass('active');
    }

    $('.metodo-pago').on('change', mostrarCamposDePago);
    mostrarCamposDePago();

    $('.form-cantidad').each(function () {
        var $formulario = $(this);
        var $cantidad = $formulario.find('input[type=number]');
        var stockMaximo = parseInt($cantidad.attr('max'), 10);

        function leerCantidad() {
            return parseInt($cantidad.val(), 10);
        }

        function guardarCantidad(cantidad) {
            $cantidad.val(cantidad);
            $formulario[0].submit();
        }

        $formulario.find('.mas').on('click', function () {
            if (leerCantidad() < stockMaximo) {
                guardarCantidad(leerCantidad() + 1);
            }
        });

        $formulario.find('.menos').on('click', function () {
            guardarCantidad(Math.max(leerCantidad() - 1, 0));
        });

        $cantidad.on('change', function () {
            $formulario[0].submit();
        });
    });
});
