<?php
include_once __DIR__ . '/../layoutExterno.php';
include_once __DIR__ . '/../../Controller/InicioController.php';

if (EstaAutenticado()) {
    Redirigir(PaginaInicioRol());
}
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Recuperar contraseña - Lifty'); ?>

<body>

    <div class="auth-split">
        <section class="auth-form">
            <a href="login.php" class="auth-volver"><i class="ti ti-arrow-left"></i> Volver a iniciar sesión</a>

            <div class="auth-form-in">
                <?php MostrarLogo(); ?>
                <h1 class="auth-titulo">Recupera<br>tu acceso</h1>
                <p class="auth-sub">Escribe tu correo y te enviamos una contraseña temporal.</p>

                <?php MostrarMensaje(); ?>

                <form action="" method="POST" class="validar" novalidate>
                    <div class="mb-3">
                        <label for="txtCorreoElectronico" class="form-label">Correo electrónico</label>
                        <input id="txtCorreoElectronico" name="txtCorreoElectronico" type="email" class="form-control" autocomplete="email" required autofocus>
                    </div>

                    <button class="btn btn-primary w-100 mt-3" type="submit" name="btnRecuperarContrasenna">Enviar contraseña temporal</button>
                </form>

                <p class="auth-pie">¿Ya la recordaste? <a href="login.php" class="auth-enlace fw-bold">Inicia sesión</a></p>
            </div>
        </section>

        <aside class="auth-art"><?php MostrarArteAuth(); ?></aside>
    </div>

    <?php IncludeJS(); ?>

</body>

</html>
