<?php include_once '../layoutExterno.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/lifty/Controller/InicioController.php';
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS(); ?>

<body>

    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card" style="max-width:420px; width:100%;">
            <div class="card-body p-5">

                <div class="text-center mb-3">
                    <?php MostrarLogo(); ?>
                    <h1 class="card-title mb-5 h5">Recuperar contraseña</h1>
                </div>

                <?php if(isset($_POST["mensaje"])) { ?>
                    <div class="alert alert-danger small" role="alert"><?php echo $_POST["mensaje"]; ?></div>
                <?php } ?>

                <p class="small text-muted">Ingrese su identificación y le enviaremos una contraseña temporal al correo electrónico con el que se registró.</p>

                <form action="" method="POST" id="recoverForm" class="needs-validation mt-3" novalidate>
                    <div class="mb-3">
                        <label for="txtIdentificacion" class="form-label">Identificación</label>
                        <input id="txtIdentificacion" name="txtIdentificacion" type="text" class="form-control" inputmode="numeric" autocomplete="off" required autofocus>
                        <div class="invalid-feedback">Por favor ingrese su identificación.</div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit" id="btnRecuperarAcceso" name="btnRecuperarAcceso">Procesar</button>
                </form>

                <div class="text-center mt-3 small text-muted">
                    ¿Recordó su contraseña? <a href="login.php" class="link-primary">Iniciar Sesión</a>
                </div>

                <div class="text-center mt-1 small text-muted">
                    ¿No tiene una cuenta? <a href="register.php" class="link-primary">Regístrese</a>
                </div>

            </div>
        </div>
    </div>

    <?php IncludeJS(); ?>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js"></script>
    <script src="../assets/js/forgot-password.js"></script>

</body>

</html>
