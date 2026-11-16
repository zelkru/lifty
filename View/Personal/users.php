<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/PersonalController.php';

ValidarAdmin();

$usuarios = ConsultarUsuarios();
$roles = ConsultarRoles();
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Usuarios - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content py-10">
        <div class="container-fluid">

            <div class="page-head">
                <div>
                    <h1>Usuarios</h1>
                    <p>Cree y edite cuentas, cambie su rol y active o inactive el acceso</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" data-nuevo>
                    <i class="ti ti-user-plus"></i> Nuevo usuario
                </button>
            </div>

            <?php MostrarMensaje(); ?>

            <div class="barra-filtros">
                <input type="search" class="form-control ft ft-buscar" data-tabla="#tablaUsuarios" placeholder="Buscar por nombre, correo o teléfono..." aria-label="Buscar">
                <select class="form-select ft" data-tabla="#tablaUsuarios" data-col="4" aria-label="Rol">
                    <option value="">Todos los roles</option>
                    <?php foreach ($roles as $r) { ?><option value="<?php echo Texto($r['NombreRol']); ?>"><?php echo Texto($r['NombreRol']); ?></option><?php } ?>
                </select>
                <select class="form-select ft" data-tabla="#tablaUsuarios" data-col="6" aria-label="Estado">
                    <option value="">Todos los estados</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
                <label class="fecha">Registro desde <input type="date" class="form-control ft" data-tabla="#tablaUsuarios" data-col="5" data-rango="desde"></label>
                <label class="fecha">hasta <input type="date" class="form-control ft" data-tabla="#tablaUsuarios" data-col="5" data-rango="hasta"></label>
                <button type="button" class="btn btn-outline-secondary limpiar-filtros" data-tabla="#tablaUsuarios"><i class="ti ti-filter-off"></i> Limpiar</button>
            </div>

            <div class="l-card">
                <div class="table-responsive">
                    <table class="table l-table table-hover tabla-apilada tabla-datos" id="tablaUsuarios" data-pagina="12">
                        <thead>
                            <tr>
                                <th data-orden="numero">#</th><th data-orden="texto">Nombre</th><th data-orden="texto">Correo</th>
                                <th data-orden="texto">Teléfono</th><th data-orden="texto">Rol</th><th data-orden="fecha">Registro</th>
                                <th data-orden="texto">Estado</th><th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($usuarios as $u) {
                            $esUno = $u['Consecutivo'] == $_SESSION['ConsecutivoUsuario'];
                            $datos = ['txtConsecutivo' => $u['Consecutivo'], 'txtNombre' => $u['Nombre'], 'txtApellido1' => $u['Apellido1'],
                                      'txtApellido2' => $u['Apellido2'], 'txtCorreoElectronico' => $u['CorreoElectronico'], 'txtTelefono' => $u['Telefono'],
                                      'txtRol' => $u['ConsecutivoRol'], 'txtEstado' => $u['Estado'], 'txtContrasenna' => '']; ?>
                            <tr>
                                <td><?php echo $u['Consecutivo']; ?></td>
                                <td><?php echo Texto(trim($u['Nombre'] . ' ' . $u['Apellido1'] . ' ' . $u['Apellido2'])); ?><?php echo $esUno ? ' <span class="badge text-bg-light border">Usted</span>' : ''; ?></td>
                                <td><?php echo Texto($u['CorreoElectronico']); ?></td>
                                <td><?php echo Texto($u['Telefono']); ?></td>
                                <td data-valor="<?php echo Texto($u['NombreRol']); ?>"><span class="badge text-bg-<?php echo $u['ConsecutivoRol'] == ROL_ADMINISTRADOR ? 'dark' : 'light border'; ?>"><?php echo Texto($u['NombreRol']); ?></span></td>
                                <td class="text-nowrap" data-valor="<?php echo date('Y-m-d H:i', strtotime($u['FechaRegistro'])); ?>"><?php echo date('d/m/Y', strtotime($u['FechaRegistro'])); ?></td>
                                <td data-valor="<?php echo $u['Estado'] == 1 ? 'Activo' : 'Inactivo'; ?>"><span class="badge text-bg-<?php echo $u['Estado'] == 1 ? 'success' : 'secondary'; ?>"><?php echo $u['Estado'] == 1 ? 'Activo' : 'Inactivo'; ?></span></td>
                                <td class="text-end text-nowrap">
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario"
                                            data-propio="<?php echo $esUno ? 1 : 0; ?>" data-fill="<?php echo Texto(json_encode($datos)); ?>"><i class="ti ti-edit"></i> Editar</button>
                                    <?php if (!$esUno) { ?>
                                        <form action="" method="POST" class="d-inline m-0" data-confirm="<?php echo $u['Estado'] == 1 ? '¿Inactivar esta cuenta?' : '¿Activar esta cuenta?'; ?>">
                                            <?php CampoCsrf(); ?>
                                            <input type="hidden" name="txtConsecutivo" value="<?php echo $u['Consecutivo']; ?>">
                                            <input type="hidden" name="txtEstado" value="<?php echo $u['Estado'] == 1 ? 0 : 1; ?>">
                                            <button type="submit" name="btnCambiarEstadoUsuario" class="btn btn-sm btn-outline-<?php echo $u['Estado'] == 1 ? 'danger' : 'success'; ?>">
                                                <i class="ti <?php echo $u['Estado'] == 1 ? 'ti-user-off' : 'ti-user-check'; ?>"></i> <?php echo $u['Estado'] == 1 ? 'Inactivar' : 'Activar'; ?>
                                            </button>
                                        </form>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="tituloUsuario" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="" method="POST" class="modal-content validar" novalidate>
                <?php CampoCsrf(); ?>
                <input type="hidden" name="txtConsecutivo" value="">
                <div class="modal-header">
                    <h2 class="modal-title" id="tituloUsuario">Usuario</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="txtNombre">Nombre</label>
                            <input id="txtNombre" name="txtNombre" type="text" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="txtApellido1">Primer apellido</label>
                            <input id="txtApellido1" name="txtApellido1" type="text" class="form-control" maxlength="50" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="txtApellido2">Segundo apellido</label>
                            <input id="txtApellido2" name="txtApellido2" type="text" class="form-control" maxlength="50">
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label" for="txtCorreoElectronico">Correo electrónico</label>
                            <input id="txtCorreoElectronico" name="txtCorreoElectronico" type="email" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="txtTelefono">Teléfono</label>
                            <input id="txtTelefono" name="txtTelefono" type="text" class="form-control" inputmode="numeric" maxlength="8" data-rule-telefono="true" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="txtRol">Rol</label>
                            <select id="txtRol" name="txtRol" class="form-select" required>
                                <?php foreach ($roles as $r) { ?><option value="<?php echo $r['Consecutivo']; ?>"><?php echo Texto($r['NombreRol']); ?></option><?php } ?>
                            </select>
                        </div>
                        <div class="col-6" id="grupoEstadoUsuario">
                            <label class="form-label" for="txtEstado">Estado</label>
                            <select id="txtEstado" name="txtEstado" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="txtContrasenna" id="etiquetaContrasenna">Contraseña</label>
                            <input id="txtContrasenna" name="txtContrasenna" type="password" class="form-control" autocomplete="new-password" data-rule-contrasenna="true">
                            <div class="form-text" id="ayudaContrasenna">Mínimo 8 caracteres, con letras y números.</div>
                        </div>
                        <div class="col-12" id="avisoPropio">
                            <div class="alert alert-secondary mb-0 small"><i class="ti ti-info-circle"></i> Es su propia cuenta: puede cambiar sus datos y su contraseña, pero no su rol ni su estado.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btnGuardarUsuario" class="btn btn-primary"><i class="ti ti-check"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <?php IncludeJS(); ?>
    <script src="../assets/js/usuarios.js"></script>

</body>

</html>
