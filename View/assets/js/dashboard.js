$(function () {
    $('.stat-value').each(function () {
        var $el = $(this), texto = $.trim($el.text()), m = texto.match(/^([^\d]*)([\d.,]+)(.*)$/);
        if (!m) { return; }
        var destino = parseFloat(m[2].replace(/[.,](?=\d{3})/g, '').replace(',', '.'));
        if (isNaN(destino) || destino === 0) { return; }
        var decimales = /[.,]\d{1,2}$/.test(m[2]) && destino % 1 !== 0;
        $({ v: 0 }).animate({ v: destino }, { duration: 1100, easing: 'swing', step: function (v) {
            $el.text(m[1] + (decimales ? v.toFixed(2) : String(Math.round(v)).replace(/\B(?=(\d{3})+(?!\d))/g, '.')) + m[3]);
        }, complete: function () { $el.text(texto); } });
    });
});
