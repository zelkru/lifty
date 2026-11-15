<?php
include_once __DIR__ . '/../layoutExterno.php';
include_once __DIR__ . '/../../Controller/InicioController.php';

if (EstaAutenticado()) {
    Redirigir(PaginaInicioRol());
}

if (isset($_POST['mensaje'])) {
    AsignarMensaje($_POST['mensaje'], 'danger');
}
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Crear cuenta - Lifty'); ?>

<body>

    <div class="auth-split invertido">
        <section class="auth-form">
            <a href="../Catalogo/catalog.php" class="auth-volver"><i class="ti ti-arrow-left"></i> Volver a la tienda</a>

            <div class="auth-form-in ancho">
                <?php MostrarLogo(); ?>
                <h1 class="auth-titulo">Crea tu<br>cuenta</h1>
                <p class="auth-sub">Regístrate para comprar y dar seguimiento a tus pedidos.</p>

                <?php MostrarMensaje(); ?>

                <form action="" method="POST" class="validar" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="txtNombre" class="form-label">Nombre</label>
                            <input id="txtNombre" name="txtNombre" type="text" class="form-control" maxlength="50"
                                   value="<?php echo Texto($_POST['txtNombre'] ?? ''); ?>" autocomplete="given-name" required autofocus>
                        </div>
                        <div class="col-sm-6">
                            <label for="txtApellido1" class="form-label">Primer apellido</label>
                            <input id="txtApellido1" name="txtApellido1" type="text" class="form-control" maxlength="50"
                                   value="<?php echo Texto($_POST['txtApellido1'] ?? ''); ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="txtApellido2" class="form-label">Segundo apellido <span class="text-secondary small">(opcional)</span></label>
                            <input id="txtApellido2" name="txtApellido2" type="text" class="form-control" maxlength="50"
                                   value="<?php echo Texto($_POST['txtApellido2'] ?? ''); ?>">
                        </div>
                        <div class="col-sm-7">
                            <label for="txtCorreoElectronico" class="form-label">Correo electrónico</label>
                            <input id="txtCorreoElectronico" name="txtCorreoElectronico" type="email" class="form-control" maxlength="100"
                                   value="<?php echo Texto($_POST['txtCorreoElectronico'] ?? ''); ?>" autocomplete="email" required>
                        </div>
                        <div class="col-sm-5">
                            <label for="txtTelefono" class="form-label">Teléfono</label>
                            <input id="txtTelefono" name="txtTelefono" type="text" class="form-control" inputmode="numeric" maxlength="8"
                                   value="<?php echo Texto($_POST['txtTelefono'] ?? ''); ?>" data-rule-telefono="true" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="txtContrasenna" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input id="txtContrasenna" name="txtContrasenna" type="password" class="form-control" autocomplete="new-password"
                                       data-rule-contrasenna="true" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#txtContrasenna" aria-label="Mostrar contraseña">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label for="txtConfirmarContrasenna" class="form-label">Confirmar contraseña</label>
                            <input id="txtConfirmarContrasenna" name="txtConfirmarContrasenna" type="password" class="form-control"
                                   autocomplete="new-password" data-rule-equalTo="#txtContrasenna" required>
                        </div>
                        <div class="col-12">
                            <p class="small text-secondary mb-0">Mínimo 8 caracteres, con letras y números.</p>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 mt-4" type="submit" name="btnRegistro">Crear cuenta</button>
                </form>

                <p class="auth-pie">¿Ya tienes una cuenta? <a href="login.php" class="auth-enlace fw-bold">Inicia sesión</a></p>
            </div>
        </section>

        <aside class="auth-art"><?php MostrarArteAuth(); ?></aside>
    </div>

    <?php IncludeJS(); ?>

</body>

</html>
