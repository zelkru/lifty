$(function () {
    function mostrarPago() {
        var tipo = $('.metodo-pago:checked').data('tipo');
        $('.pay-box').removeClass('active').filter('[data-tipo="' + tipo + '"]').addClass('active');
    }
    $('.metodo-pago').on('change', mostrarPago);
    mostrarPago();

    $('.form-cantidad').each(function () {
        var $f = $(this), $c = $f.find('input[type=number]');
        var max = parseInt($c.attr('max'), 10);
        $f.find('.mas').on('click', function () { if (parseInt($c.val(), 10) < max) { $c.val(parseInt($c.val(), 10) + 1); $f[0].submit(); } });
        $f.find('.menos').on('click', function () { var v = parseInt($c.val(), 10) - 1; $c.val(v < 1 ? 0 : v); $f[0].submit(); });
        $c.on('change', function () { $f[0].submit(); });
    });
});
