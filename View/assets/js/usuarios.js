document.getElementById('modalUsuario').addEventListener('show.bs.modal', function (e) {
    var boton = e.relatedTarget;
    var editando = !!(boton && boton.dataset.fill);
    var propio = editando && boton.dataset.propio === '1';
    var $contrasenna = $('#txtContrasenna');

    $('#tituloUsuario').text(editando ? 'Editar usuario' : 'Nuevo usuario');
    $('#grupoEstadoUsuario').toggle(editando && !propio);
    $('#txtRol').prop('disabled', propio);
    $('#avisoPropio').toggle(propio);
    $('#etiquetaContrasenna').text(editando ? 'Nueva contraseña (opcional)' : 'Contraseña inicial');
    $('#ayudaContrasenna').text(editando ? 'Déjela vacía para conservar la actual. Mínimo 8 caracteres, con letras y números.' : 'Mínimo 8 caracteres, con letras y números.');
    $contrasenna.prop('required', !editando);
    if ($contrasenna.data('rule-required') !== undefined) { $contrasenna.removeData('rule-required'); }
    if (!editando) { $('#modalUsuario form')[0].reset(); }
});
$('#modalUsuario form').on('submit', function () { $('#txtRol').prop('disabled', false); });
