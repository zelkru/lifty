<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/InventarioController.php';

ValidarAdmin();

$categorias = ConsultarCategorias();
$marcas = ConsultarMarcas();
$metodos = ConsultarMetodosPago();

$maestros = [
    'categoria' => ['Categorías', $categorias, 'btnGuardarCategoria'],
    'marca' => ['Marcas', $marcas, 'btnGuardarMarca'],
];
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Categorías y marcas - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content py-10">
        <div class="container-fluid">

            <div class="page-head">
                <div>
                    <h1>Categorías y marcas</h1>
                    <p>Organice el catálogo y configure los métodos de pago</p>
                </div>
            </div>

            <?php MostrarMensaje(); ?>

            <div class="row g-4">
                <?php foreach ($maestros as $clave => $maestro) { ?>
                <div class="col-12 col-xl-6">
                    <div class="l-card">
                        <div class="l-card-body pb-2 d-flex justify-content-between align-items-center">
                            <h2 class="l-card-title mb-0"><?php echo $maestro[0]; ?></h2>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMaestro" data-nuevo data-boton="<?php echo $maestro[2]; ?>" data-titulo="Nueva <?php echo $clave == 'marca' ? 'marca' : 'categoría'; ?>">
                                <i class="ti ti-plus"></i> Agregar
                            </button>
                        </div>
                        <div class="barra-filtros px-3 pb-2">
                            <input type="search" class="form-control ft ft-buscar" data-tabla="#tabla-<?php echo $clave; ?>" placeholder="Buscar..." aria-label="Buscar">
                            <select class="form-select ft" data-tabla="#tabla-<?php echo $clave; ?>" data-col="2" aria-label="Estado">
                                <option value="">Todos los estados</option>
                                <option value="Activa">Activa</option>
                                <option value="Inactiva">Inactiva</option>
                            </select>
                        </div>
                        <div class="table-responsive">
                            <table class="table l-table table-hover tabla-apilada tabla-datos" id="tabla-<?php echo $clave; ?>" data-pagina="8">
                                <thead><tr><th data-orden="texto">Nombre</th><th data-orden="texto">Descripción</th><th data-orden="texto">Estado</th><th class="text-end">Acción</th></tr></thead>
                                <tbody>
                                <?php foreach ($maestro[1] as $m) {
                                    $datos = ['txtConsecutivo' => $m['Consecutivo'], 'txtNombre' => $m['Nombre'], 'txtDescripcion' => $m['Descripcion'], 'txtEstado' => $m['Estado']]; ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo Texto($m['Nombre']); ?></td>
                                        <td class="text-secondary small"><?php echo Texto($m['Descripcion']); ?></td>
                                        <td data-valor="<?php echo $m['Estado'] == 1 ? 'Activa' : 'Inactiva'; ?>"><span class="badge text-bg-<?php echo $m['Estado'] == 1 ? 'success' : 'secondary'; ?>"><?php echo $m['Estado'] == 1 ? 'Activa' : 'Inactiva'; ?></span></td>
                                        <td class="text-end">
                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMaestro"
                                                    data-boton="<?php echo $maestro[2]; ?>" data-titulo="Editar <?php echo $clave == 'marca' ? 'marca' : 'categoría'; ?>"
                                                    data-fill="<?php echo Texto(json_encode($datos)); ?>"><i class="ti ti-edit"></i> Editar</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <div class="col-12">
                    <div class="l-card">
                        <div class="l-card-body pb-2">
                            <h2 class="l-card-title mb-0">Métodos de pago</h2>
                            <p class="small text-secondary mb-0">Los pagos son simulados: solo se valida el formato y no se guardan datos de tarjeta.</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table l-table tabla-apilada tabla-datos" id="tabla-metodos" data-pagina="10">
                                <thead><tr><th data-orden="texto">Método</th><th data-orden="texto">Descripción</th><th data-orden="texto">Estado</th><th class="text-end">Acción</th></tr></thead>
                                <tbody>
                                <?php foreach ($metodos as $mp) { ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo Texto($mp['Nombre']); ?></td>
                                        <td class="text-secondary small"><?php echo Texto($mp['Descripcion']); ?></td>
                                        <td><span class="badge text-bg-<?php echo $mp['Estado'] == 1 ? 'success' : 'secondary'; ?>"><?php echo $mp['Estado'] == 1 ? 'Activo' : 'Inactivo'; ?></span></td>
                                        <td class="text-end">
                                            <form action="" method="POST" class="m-0">
                                                <?php CampoCsrf(); ?>
                                                <input type="hidden" name="txtConsecutivo" value="<?php echo $mp['Consecutivo']; ?>">
                                                <input type="hidden" name="txtEstado" value="<?php echo $mp['Estado'] == 1 ? 0 : 1; ?>">
                                                <button type="submit" name="btnCambiarEstadoMetodoPago" class="btn btn-sm btn-outline-<?php echo $mp['Estado'] == 1 ? 'danger' : 'success'; ?>">
                                                    <i class="ti <?php echo $mp['Estado'] == 1 ? 'ti-player-pause' : 'ti-player-play'; ?>"></i> <?php echo $mp['Estado'] == 1 ? 'Inactivar' : 'Activar'; ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <div class="modal fade" id="modalMaestro" tabindex="-1" aria-labelledby="tituloMaestro" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" class="modal-content validar" novalidate>
                <?php CampoCsrf(); ?>
                <input type="hidden" name="txtConsecutivo" value="">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="tituloMaestro">Registro</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="txtNombre">Nombre</label>
                        <input id="txtNombre" name="txtNombre" type="text" class="form-control" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="txtDescripcion">Descripción <span class="text-secondary small">(opcional)</span></label>
                        <input id="txtDescripcion" name="txtDescripcion" type="text" class="form-control" maxlength="150">
                    </div>
                    <div id="grupoEstadoMaestro">
                        <label class="form-label" for="txtEstado">Estado</label>
                        <select id="txtEstado" name="txtEstado" class="form-select">
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarMaestro" name="btnGuardarCategoria">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <?php IncludeJS(); ?>
    <script src="../assets/js/catalogos.js"></script>

</body>

</html>
