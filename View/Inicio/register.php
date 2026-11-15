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
                    <h1 class="card-title mb-5 h5">Crea tu cuenta</h1>
                </div>

                <form action="" method="POST" id="registerForm" class="needs-validation" novalidate>

                    <div class="mb-3">
                        <label for="txtIdentificacion" class="form-label">Identificación</label>
                        <input id="txtIdentificacion" name="txtIdentificacion" type="text" class="form-control" inputmode="numeric" autocomplete="off"
                         onkeyup="ConsultarNombre()" required>
                        <div class="invalid-feedback">Por favor ingrese su identificacion.</div>
                        <div id="nombreConsultaEstado" class="form-text" aria-live="polite"></div>
                    </div>

                    <div class="mb-3">
                        <label for="txtNombre" class="form-label">Nombre completo</label>
                        <input id="txtNombre" name="txtNombre" type="text" class="form-control" readonly required>
                        <div class="invalid-feedback">Por favor ingrese su nombre.</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input id="txtCorreoElectronico" name="txtCorreoElectronico" type="email" class="form-control" required>
                        <div class="invalid-feedback">Por favor ingrese un correo electrónico válido.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input id="txtContrasenna" name="txtContrasenna" type="password" class="form-control" required minlength="6">
                        <div class="invalid-feedback">Por favor, proporcione una contraseña (mínimo 6 caracteres).</div>
                    </div>

                    <div class="mb-3">
                        <label for="txtconfirmarContrasenna" class="form-label">Confirmar contraseña</label>
                        <input id="txtconfirmarContrasenna" name="txtconfirmarContrasenna" type="password" class="form-control" required>
                        <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                    </div>

                    <div class="mb-3 form-check">
                        <input id="terms" name ="terms" class="form-check-input" type="checkbox" required>
                        <label class="form-check-label small" for="terms">Acepto los <a href="#" class="text-decoration-none">términos y privacidad</a></label>
                        <div class="invalid-feedback">Debe aceptar antes de continuar.</div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit" id="btnRegistro" name="btnRegistro">Registrarse</button>

                </form>

                <div class="text-center mt-3 small text-muted">
                    ¿Ya tienes una cuenta? <a href="login.php" class="link-primary">Iniciar Sesión</a>
                </div>

            </div>
        </div>
    </div>

    <?php IncludeJS(); ?>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js"></script>
    <script src="../assets/js/register.js?v=2"></script>

</body>

</html>
