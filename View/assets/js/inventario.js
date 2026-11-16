$('#modalProducto form').on('submit', function () {
    var editando = $(this).find('[name="txtConsecutivo"]').val() !== '';
    $('#btnGuardarProducto').attr('name', editando ? 'btnActualizarProducto' : 'btnRegistrarProducto');
});
document.getElementById('modalProducto').addEventListener('show.bs.modal', function (e) {
    var editando = e.relatedTarget && e.relatedTarget.dataset.fill;
    $('#grupoEstado, #grupoDestacado').toggle(!!editando);
    $('#tituloProducto').text(editando ? 'Editar producto' : 'Nuevo producto');
});
