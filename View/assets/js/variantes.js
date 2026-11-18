$('#modalVariante form').on('submit', function () {
    var editando = $(this).find('[name="txtConsecutivo"]').val() !== '';
    $('#btnGuardarVariante').attr('name', editando ? 'btnActualizarVariante' : 'btnRegistrarVariante');
});

document.getElementById('modalVariante').addEventListener('show.bs.modal', function (evento) {
    var editando = evento.relatedTarget && evento.relatedTarget.dataset.fill;
    $('#grupoEstadoVariante').toggle(!!editando);
    $('#tituloVariante').text(editando ? 'Editar presentación' : 'Nueva presentación');
});
