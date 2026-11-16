$(function () {
    $('input[name="txtVariante"]').on('change', function () {
        var stock = parseInt($(this).data('stock'), 10);
        var $cantidad = $('#txtCantidad');
        $cantidad.attr('max', stock);
        if (parseInt($cantidad.val(), 10) > stock) { $cantidad.val(stock); }
    });

    $('input[name="txtVariante"]').rules('add', { messages: { required: 'Seleccione una presentación.' } });
    var validator = $('form.validar').data('validator');
    if (validator) {
        validator.settings.errorPlacement = function (error, element) {
            if (element.attr('name') === 'txtVariante') { error.appendTo('#errorVariante'); }
            else { error.insertAfter(element); }
        };
    }
});
