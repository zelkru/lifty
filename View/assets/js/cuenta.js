$('#modalDireccion form').on('submit', function () {
    $('#btnGuardarDireccion').attr('name', $(this).find('[name="txtConsecutivo"]').val() ? 'btnActualizarDireccion' : 'btnRegistrarDireccion');
});
