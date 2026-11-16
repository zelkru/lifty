<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/PersonalController.php';

ValidarSesion();

$usuario = ConsultarUsuario();
$direcciones = EsCliente() ? ConsultarDirecciones() : [];
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Mi cuenta - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content py-10">
        <div class="container-fluid">

            <div class="page-head">
                <div>
                    <h1>Mi cuenta</h1>
                    <p>Miembro desde <?php echo date('d/m/Y', strtotime($usuario['FechaRegistro'])); ?> &middot; <?php echo Texto($usuario['NombreRol']); ?></p>
                </div>
            </div>

            <?php MostrarMensaje(); ?>

            <div class="row g-4">

                <div class="col-12 col-xl-7">
                    <div class="l-card l-card-body">
                        <h2 class="l-card-title">Datos personales</h2>
                        <form action="" method="POST" class="validar" novalidate>
                            <?php CampoCsrf(); ?>
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label for="txtNombre" class="form-label">Nombre</label>
                                    <input id="txtNombre" name="txtNombre" type="text" class="form-control" maxlength="50" value="<?php echo Texto($usuario['Nombre']); ?>" required>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label for="txtApellido1" class="form-label">Primer apellido</label>
                                    <input id="txtApellido1" name="txtApellido1" type="text" class="form-control" maxlength="50" value="<?php echo Texto($usuario['Apellido1']); ?>" required>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label for="txtApellido2" class="form-label">Segundo apellido</label>
                                    <input id="txtApellido2" name="txtApellido2" type="text" class="form-control" maxlength="50" value="<?php echo Texto($usuario['Apellido2']); ?>">
                                </div>
                                <div class="col-12 col-md-8">
                                    <label for="txtCorreoElectronico" class="form-label">Correo electrónico</label>
                                    <input id="txtCorreoElectronico" name="txtCorreoElectronico" type="email" class="form-control" maxlength="100" value="<?php echo Texto($usuario['CorreoElectronico']); ?>" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="txtTelefono" class="form-label">Teléfono</label>
                                    <input id="txtTelefono" name="txtTelefono" type="text" class="form-control" inputmode="numeric" maxlength="8" value="<?php echo Texto($usuario['Telefono']); ?>" data-rule-telefono="true" required>
                                </div>
                            </div>
                            <button type="submit" name="btnCambiarDatosCuenta" class="btn btn-primary mt-4">Guardar cambios</button>
                        </form>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="l-card l-card-body">
                        <h2 class="l-card-title">Cambiar contraseña</h2>
                        <form action="" method="POST" class="validar" novalidate>
                            <?php CampoCsrf(); ?>
                            <div class="mb-3">
                                <label for="txtContrasennaActual" class="form-label">Contraseña actual</label>
                                <input id="txtContrasennaActual" name="txtContrasennaActual" type="password" class="form-control" autocomplete="current-password" required>
                            </div>
                            <div class="mb-3">
                                <label for="txtContrasennaNueva" class="form-label">Contraseña nueva</label>
                                <input id="txtContrasennaNueva" name="txtContrasennaNueva" type="password" class="form-control" autocomplete="new-password" data-rule-contrasenna="true" required>
                                <div class="form-text">Mínimo 8 caracteres, con letras y números.</div>
                            </div>
                            <div class="mb-3">
                                <label for="txtConfirmarContrasennaNueva" class="form-label">Confirmar contraseña nueva</label>
                                <input id="txtConfirmarContrasennaNueva" name="txtConfirmarContrasennaNueva" type="password" class="form-control" autocomplete="new-password" data-rule-equalTo="#txtContrasennaNueva" required>
                            </div>
                            <button type="submit" name="btnCambiarContrasenna" class="btn btn-outline-primary">Actualizar contraseña</button>
                        </form>
                    </div>
                </div>

                <?php if (EsCliente()) { ?>
                <div class="col-12">
                    <div class="l-card l-card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="l-card-title mb-0">Direcciones de entrega</h2>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDireccion" data-nuevo>
                                <i class="ti ti-plus"></i> Agregar dirección
                            </button>
                        </div>

                        <?php if (count($direcciones) == 0) { ?>
                            <div class="empty-state py-4">
                                <i class="ti ti-map-pin"></i>
                                <p class="mb-0">Aún no tiene direcciones. Agregue una para poder realizar compras.</p>
                            </div>
                        <?php } else { ?>
                            <div class="row g-3">
                                <?php foreach ($direcciones as $d) {
                                    $datos = ['txtConsecutivo' => $d['Consecutivo'], 'txtProvincia' => $d['Provincia'], 'txtCanton' => $d['Canton'],
                                              'txtDistrito' => $d['Distrito'], 'txtDetalle' => $d['Detalle'], 'txtCodigoPostal' => $d['CodigoPostal']]; ?>
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                                            <div class="fw-semibold"><i class="ti ti-map-pin text-primary"></i> <?php echo Texto($d['Provincia']); ?>, <?php echo Texto($d['Canton']); ?></div>
                                            <div class="text-secondary small"><?php echo Texto($d['Distrito']); ?><?php echo $d['CodigoPostal'] != '' ? ' &middot; ' . Texto($d['CodigoPostal']) : ''; ?></div>
                                            <p class="small mt-2 mb-3"><?php echo Texto($d['Detalle']); ?></p>
                                            <div class="mt-auto d-flex gap-2">
                                                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDireccion"
                                                        data-fill="<?php echo Texto(json_encode($datos)); ?>">Editar</button>
                                                <form action="" method="POST" class="m-0" data-confirm="¿Eliminar esta dirección?">
                                                    <?php CampoCsrf(); ?>
                                                    <input type="hidden" name="txtConsecutivo" value="<?php echo $d['Consecutivo']; ?>">
                                                    <button type="submit" name="btnInactivarDireccion" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

            </div>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <?php if (EsCliente()) { ?>
    <div class="modal fade" id="modalDireccion" tabindex="-1" aria-labelledby="tituloDireccion" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" class="modal-content validar" novalidate>
                <?php CampoCsrf(); ?>
                <input type="hidden" name="txtConsecutivo" value="">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="tituloDireccion">Dirección de entrega</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="txtProvincia">Provincia</label>
                            <input id="txtProvincia" name="txtProvincia" type="text" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="txtCanton">Cantón</label>
                            <input id="txtCanton" name="txtCanton" type="text" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="txtDistrito">Distrito</label>
                            <input id="txtDistrito" name="txtDistrito" type="text" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="txtDetalle">Señas</label>
                            <input id="txtDetalle" name="txtDetalle" type="text" class="form-control" maxlength="250" required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="txtCodigoPostal">Código postal <span class="text-secondary small">(opcional)</span></label>
                            <input id="txtCodigoPostal" name="txtCodigoPostal" type="text" class="form-control" inputmode="numeric" maxlength="10" data-rule-digits="true">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btnGuardarDireccion" class="btn btn-primary" id="btnGuardarDireccion">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>

    <?php IncludeJS(); ?>
    <script src="../assets/js/cuenta.js"></script>

</body>

</html>
