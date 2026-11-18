document.getElementById('modalUsuario').addEventListener('show.bs.modal', function (evento) {
    var boton = evento.relatedTarget;
    var editando = !!(boton && boton.dataset.fill);
    var esCuentaPropia = editando && boton.dataset.propio === '1';
    var $contrasenna = $('#txtContrasenna');
    var ayudaContrasenna = 'Mínimo 8 caracteres, con letras y números.';

    $('#tituloUsuario').text(editando ? 'Editar usuario' : 'Nuevo usuario');
    $('#grupoEstadoUsuario').toggle(editando && !esCuentaPropia);
    $('#txtRol').prop('disabled', esCuentaPropia);
    $('#avisoPropio').toggle(esCuentaPropia);
    $('#etiquetaContrasenna').text(editando ? 'Nueva contraseña (opcional)' : 'Contraseña inicial');
    $('#ayudaContrasenna').text(editando ? 'Déjela vacía para conservar la actual. ' + ayudaContrasenna : ayudaContrasenna);
    $contrasenna.prop('required', !editando);

    if ($contrasenna.data('rule-required') !== undefined) {
        $contrasenna.removeData('rule-required');
    }
    if (!editando) {
        $('#modalUsuario form')[0].reset();
    }
});

$('#modalUsuario form').on('submit', function () {
    $('#txtRol').prop('disabled', false);
});
