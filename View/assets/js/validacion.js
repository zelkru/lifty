$(function () {

    $.extend($.validator.messages, {
        required: 'Este campo es obligatorio.',
        email: 'Ingrese un correo electrónico válido.',
        number: 'Ingrese un número válido.',
        digits: 'Solo se permiten números.',
        url: 'Ingrese una dirección web válida (https://...).',
        equalTo: 'Los valores no coinciden.',
        maxlength: $.validator.format('Máximo {0} caracteres.'),
        minlength: $.validator.format('Ingrese al menos {0} caracteres.'),
        min: $.validator.format('Ingrese un valor mayor o igual a {0}.'),
        max: $.validator.format('Ingrese un valor menor o igual a {0}.')
    });

    $.validator.addMethod('telefono', function (value, element) {
        return this.optional(element) || /^[0-9]{8}$/.test(value);
    }, 'El teléfono debe tener 8 dígitos.');

    $.validator.addMethod('contrasenna', function (value, element) {
        return this.optional(element) || (value.length >= 8 && /[A-Za-z]/.test(value) && /\d/.test(value));
    }, 'Mínimo 8 caracteres, con letras y números.');

    $.validator.addMethod('vencimiento', function (value, element) {
        return this.optional(element) || /^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(value);
    }, 'Use el formato MM/AA.');

    $.validator.addMethod('tarjeta', function (value, element) {
        return this.optional(element) || /^[0-9]{13,19}$/.test(value.replace(/\s+/g, ''));
    }, 'El número de tarjeta debe tener entre 13 y 19 dígitos.');

    $.validator.setDefaults({
        errorElement: 'div',
        errorClass: 'invalid-feedback d-block',
        highlight: function (element) { $(element).addClass('is-invalid'); },
        unhighlight: function (element) { $(element).removeClass('is-invalid'); },
        errorPlacement: function (error, element) {
            var grupo = element.closest('.input-group');
            error.insertAfter(grupo.length ? grupo : element);
        }
    });

    $('form.validar').each(function () {
        $(this).validate();
    });

    $(document).on('click', '.toggle-password', function () {
        var campo = $($(this).data('target'));
        var visible = campo.attr('type') === 'text';
        campo.attr('type', visible ? 'password' : 'text');
        $(this).find('i').toggleClass('ti-eye', visible).toggleClass('ti-eye-off', !visible);
    });

    $('table.tabla-apilada').each(function () {
        var etiquetas = $(this).find('thead th').map(function () { return $(this).text().trim(); }).get();
        $(this).find('tbody tr').each(function () {
            $(this).children('td').each(function (i) {
                if (!$(this).attr('data-etiqueta') && etiquetas[i]) { $(this).attr('data-etiqueta', etiquetas[i]); }
            });
        });
    });

    $(document).on('submit', 'form[data-confirm]', function (e) {
        if (!window.confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    });

    document.addEventListener('show.bs.modal', function (e) {
        var boton = e.relatedTarget;
        var modal = $(e.target);
        var formulario = modal.find('form');

        if (!formulario.length) { return; }

        formulario.find('.is-invalid').removeClass('is-invalid');
        formulario.find('.invalid-feedback').remove();

        if (boton && boton.dataset && boton.dataset.fill) {
            var datos = JSON.parse(boton.dataset.fill);
            Object.keys(datos).forEach(function (nombre) {
                formulario.find('[name="' + nombre + '"]').val(datos[nombre]);
            });
        }
        else if (boton && boton.dataset && boton.dataset.nuevo !== undefined) {
            formulario[0].reset();
            formulario.find('input[type="hidden"]:not([name="tokenCsrf"])').val('');
        }
    });
});

$(document).on('change', 'select[data-autoenvio]', function () {
    this.form.submit();
});
