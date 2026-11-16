document.getElementById('modalMaestro').addEventListener('show.bs.modal', function (e) {
    var boton = e.relatedTarget;
    $('#btnGuardarMaestro').attr('name', boton.dataset.boton);
    $('#tituloMaestro').text(boton.dataset.titulo);
    $('#grupoEstadoMaestro').toggle(!!boton.dataset.fill);
});
