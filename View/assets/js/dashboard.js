$(function () {
    $('.stat-value').each(function () {
        var $indicador = $(this);
        var textoOriginal = $.trim($indicador.text());
        var partes = textoOriginal.match(/^([^\d]*)([\d.,]+)(.*)$/);

        if (!partes) {
            return;
        }

        var prefijo = partes[1];
        var numeroTexto = partes[2];
        var sufijo = partes[3];
        var valorFinal = parseFloat(numeroTexto.replace(/[.,](?=\d{3})/g, '').replace(',', '.'));

        if (isNaN(valorFinal) || valorFinal === 0) {
            return;
        }

        var conDecimales = /[.,]\d{1,2}$/.test(numeroTexto) && valorFinal % 1 !== 0;

        function formatear(numero) {
            if (conDecimales) {
                return numero.toFixed(2);
            }
            return String(Math.round(numero)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        $({ valorActual: 0 }).animate({ valorActual: valorFinal }, {
            duration: 1100,
            easing: 'swing',
            step: function (valorActual) {
                $indicador.text(prefijo + formatear(valorActual) + sufijo);
            },
            complete: function () {
                $indicador.text(textoOriginal);
            }
        });
    });
});
