let esperaConsulta;
let consultaActual;

function ConsultarNombre() {
    const $identificacion = $('#txtIdentificacion');
    const $nombre = $('#txtNombre');
    const $estado = $('#nombreConsultaEstado');
    const identificacion = $identificacion.val().trim();

    clearTimeout(esperaConsulta);
    $nombre.val('');
    $estado.text('');

    if (consultaActual) {
        consultaActual.abort();
        consultaActual = null;
    }

    if (!/^\d{9,}$/.test(identificacion)) {
        return;
    }

    $estado.text('Consultando nombre…');
    esperaConsulta = setTimeout(function () {
        consultarNombreEnApi(identificacion);
    }, 350);
}

function consultarNombreEnApi(identificacion) {
    const $identificacion = $('#txtIdentificacion');
    const $nombre = $('#txtNombre');
    const $estado = $('#nombreConsultaEstado');

    consultaActual = $.ajax({
        url: 'https://apis.gometa.org/cedulas/' + encodeURIComponent(identificacion),
        method: 'GET',
        dataType: 'json',
        timeout: 10000
    })
    .done(function (response) {
        // Evita mostrar una respuesta antigua si la cédula cambió mientras se consultaba.
        if ($identificacion.val().trim() !== identificacion) {
            return;
        }

        if (response && Number(response.resultcount) > 0 && response.nombre) {
            $nombre.val(response.nombre.trim());
            $estado.text('Nombre encontrado.');
        } else {
            $estado.text('No se encontró una persona con esa identificación.');
        }
    })
    .fail(function (xhr, status) {
        if (status !== 'abort' && $identificacion.val().trim() === identificacion) {
            $estado.text('No se pudo consultar el nombre. Inténtelo de nuevo.');
        }
    })
    .always(function () {
        consultaActual = null;
    });
}

$(function () {
    $('#registerForm').validate({
        rules: {
            txtIdentificacion: { required: true, digits: true, minlength: 9 },
            txtNombre: { required: true },
            txtCorreoElectronico: { required: true, email: true },
            txtContrasenna: { required: true, minlength: 6 },
            txtconfirmarContrasenna: {
                required: true,
                equalTo: '#txtContrasenna'
            },
            terms: { required: true }
        },
        messages: {
            txtIdentificacion: {
                required: 'La identificación es obligatoria.',
                digits: 'Ingrese solo números.',
                minlength: 'La identificación debe tener al menos 9 dígitos.'
            },
            txtNombre: { required: 'Consulte una identificación válida para obtener el nombre.' },
            txtCorreoElectronico: {
                required: 'El correo electrónico es obligatorio.',
                email: 'Ingrese un correo electrónico válido.'
            },
            txtContrasenna: {
                required: 'La contraseña es obligatoria.',
                minlength: 'La contraseña debe tener al menos 6 caracteres.'
            },
            txtconfirmarContrasenna: {
                required: 'Confirme su contraseña.',
                equalTo: 'Las contraseñas deben coincidir.'
            },
            terms: { required: 'Debe aceptar los términos y privacidad.' }
        },
        errorElement: 'div',
        errorClass: 'invalid-feedback d-block',
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        },
        highlight: function (element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        }
    });
});
