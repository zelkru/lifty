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

<?php IncludeCSS('Iniciar sesión - Lifty'); ?>

<body>

    <div class="auth-split">
        <section class="auth-form">
            <a href="../Catalogo/catalog.php" class="auth-volver"><i class="ti ti-arrow-left"></i> Volver a la tienda</a>

            <div class="auth-form-in">
                <?php MostrarLogo(); ?>
                <h1 class="auth-titulo">Bienvenido<br>de nuevo</h1>
                <p class="auth-sub">Ingresa tus credenciales para continuar con tu compra.</p>

                <?php MostrarMensaje(); ?>

                <form action="" method="POST" class="validar" novalidate>
                    <div class="mb-3">
                        <label for="txtCorreoElectronico" class="form-label">Correo electrónico</label>
                        <input id="txtCorreoElectronico" name="txtCorreoElectronico" type="email" class="form-control"
                               value="<?php echo Texto($_POST['txtCorreoElectronico'] ?? ''); ?>" autocomplete="email" required autofocus>
                    </div>

                    <div class="mb-2">
                        <label for="txtContrasenna" class="form-label d-flex justify-content-between">
                            <span>Contraseña</span>
                            <a href="forgotPassword.php" class="small auth-enlace">¿Olvidaste tu contraseña?</a>
                        </label>
                        <div class="input-group">
                            <input id="txtContrasenna" name="txtContrasenna" type="password" class="form-control" autocomplete="current-password" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#txtContrasenna" aria-label="Mostrar contraseña">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 mt-4" type="submit" name="btnInicioSesion">Ingresar</button>
                </form>

                <p class="auth-pie">¿No tienes una cuenta? <a href="register.php" class="auth-enlace fw-bold">Regístrate</a></p>
            </div>
        </section>

        <aside class="auth-art"><?php MostrarArteAuth(); ?></aside>
    </div>

    <?php IncludeJS(); ?>

</body>

</html>
