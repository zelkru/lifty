$(function () {
    $('#recoverForm').validate({
        rules: {
            txtIdentificacion: { required: true, digits: true, minlength: 9 }
        },
        messages: {
            txtIdentificacion: {
                required: 'La identificación es obligatoria.',
                digits: 'Ingrese solo números.',
                minlength: 'La identificación debe tener al menos 9 dígitos.'
            }
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
