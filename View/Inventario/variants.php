<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/InventarioController.php';

ValidarAdmin();

$producto = ConsultarProducto((int) ($_GET['id'] ?? 0));

if (!$producto) {
    AsignarMensaje('El producto no existe.', 'warning');
    Redirigir('inventory.php');
}

$variantes = ConsultarVariantes($producto['Consecutivo']);
$categorias = ConsultarCategorias(true);
$marcas = ConsultarMarcas(true);
$datosProducto = ['txtConsecutivo' => $producto['Consecutivo'], 'txtNombre' => $producto['Nombre'], 'txtDescripcion' => $producto['Descripcion'],
                  'txtImagen' => $producto['Imagen'], 'txtCategoria' => $producto['ConsecutivoCategoria'], 'txtMarca' => $producto['ConsecutivoMarca'],
                  'txtEstado' => $producto['Estado'], 'txtDestacado' => $producto['Destacado']];
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Presentaciones - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content py-10">
        <div class="container-fluid">

            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="inventory.php">Inventario</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo Texto($producto['Nombre']); ?></li>
                </ol>
            </nav>

            <div class="page-head">
                <div>
                    <h1><?php echo Texto($producto['Nombre']); ?>
                        <span class="badge fs-6 align-middle text-bg-<?php echo $producto['Estado'] == 1 ? 'success' : 'secondary'; ?>"><?php echo $producto['Estado'] == 1 ? 'Activo' : 'Inactivo'; ?></span>
                    </h1>
                    <p><?php echo Texto($producto['NombreMarca']); ?> &middot; <?php echo Texto($producto['NombreCategoria']); ?></p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalProducto" data-fill="<?php echo Texto(json_encode($datosProducto)); ?>">
                        <i class="ti ti-edit"></i> Editar producto
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalVariante" data-nuevo>
                        <i class="ti ti-plus"></i> Nueva presentación
                    </button>
                </div>
            </div>

            <?php MostrarMensaje(); ?>

            <div class="barra-filtros">
                <input type="search" class="form-control ft ft-buscar" data-tabla="#tablaVariantes" placeholder="Buscar por SKU o presentación..." aria-label="Buscar">
                <select class="form-select ft" data-tabla="#tablaVariantes" data-col="4" aria-label="Estado">
                    <option value="">Todos los estados</option>
                    <option value="Activa">Activa</option>
                    <option value="Inactiva">Inactiva</option>
                </select>
                <button type="button" class="btn btn-outline-secondary limpiar-filtros" data-tabla="#tablaVariantes"><i class="ti ti-filter-off"></i> Limpiar</button>
            </div>

            <div class="l-card">
                <div class="table-responsive">
                    <table class="table l-table table-hover tabla-apilada tabla-datos" id="tablaVariantes" data-pagina="15">
                        <thead>
                            <tr><th data-orden="texto">SKU</th><th data-orden="texto">Presentación</th><th data-orden="numero" class="text-end">Precio</th><th data-orden="numero" class="text-center">Stock</th><th data-orden="texto">Estado</th><th class="text-end">Acciones</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($variantes as $v) {
                            $datos = ['txtConsecutivo' => $v['Consecutivo'], 'txtSku' => $v['Sku'], 'txtPresentacion' => $v['Presentacion'],
                                      'txtPrecio' => $v['Precio'], 'txtStock' => $v['Stock'], 'txtEstado' => $v['Estado']]; ?>
                            <tr>
                                <td><code><?php echo Texto($v['Sku']); ?></code></td>
                                <td><?php echo Texto($v['Presentacion']); ?></td>
                                <td class="text-end" data-valor="<?php echo (float) $v['Precio']; ?>"><?php echo Dinero($v['Precio']); ?></td>
                                <td class="text-center" data-valor="<?php echo (int) $v['Stock']; ?>"><span class="badge text-bg-<?php echo $v['Stock'] == 0 ? 'danger' : ($v['Stock'] <= UMBRAL_STOCK_BAJO ? 'warning' : 'light border'); ?>"><?php echo $v['Stock']; ?></span></td>
                                <td data-valor="<?php echo $v['Estado'] == 1 ? 'Activa' : 'Inactiva'; ?>"><span class="badge text-bg-<?php echo $v['Estado'] == 1 ? 'success' : 'secondary'; ?>"><?php echo $v['Estado'] == 1 ? 'Activa' : 'Inactiva'; ?></span></td>
                                <td class="text-end">
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalVariante" data-fill="<?php echo Texto(json_encode($datos)); ?>"><i class="ti ti-edit"></i> Editar</button>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if (count($variantes) == 0) { ?>
                            <tr><td colspan="6" class="text-center text-secondary py-4">Este producto aún no tiene presentaciones. Agregue la primera para que se pueda vender.</td></tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <div class="modal fade" id="modalVariante" tabindex="-1" aria-labelledby="tituloVariante" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" class="modal-content validar" novalidate>
                <?php CampoCsrf(); ?>
                <input type="hidden" name="txtConsecutivo" value="">
                <input type="hidden" name="txtProducto" value="<?php echo $producto['Consecutivo']; ?>">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="tituloVariante">Presentación</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="txtSku">SKU</label>
                            <input id="txtSku" name="txtSku" type="text" class="form-control text-uppercase" maxlength="30" required>
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label" for="txtPresentacion">Presentación</label>
                            <input id="txtPresentacion" name="txtPresentacion" type="text" class="form-control" maxlength="100" placeholder="Ej: Chocolate 2 lb, Talla M" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="txtPrecio">Precio (₡)</label>
                            <input id="txtPrecio" name="txtPrecio" type="number" class="form-control" min="1" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="txtStock">Stock</label>
                            <input id="txtStock" name="txtStock" type="number" class="form-control" min="0" step="1" data-rule-digits="true" required>
                        </div>
                        <div class="col-12" id="grupoEstadoVariante">
                            <label class="form-label" for="txtEstadoVariante">Estado</label>
                            <select id="txtEstadoVariante" name="txtEstado" class="form-select">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btnRegistrarVariante" class="btn btn-primary" id="btnGuardarVariante">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="tituloProducto" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="" method="POST" class="modal-content validar" novalidate>
                <?php CampoCsrf(); ?>
                <input type="hidden" name="txtConsecutivo" value="">
                <input type="hidden" name="txtRetorno" value="variants">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="tituloProducto">Editar producto</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="txtNombre">Nombre</label>
                            <input id="txtNombre" name="txtNombre" type="text" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="txtCategoria">Categoría</label>
                            <select id="txtCategoria" name="txtCategoria" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($categorias as $c) { ?><option value="<?php echo $c['Consecutivo']; ?>"><?php echo Texto($c['Nombre']); ?></option><?php } ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="txtMarca">Marca</label>
                            <select id="txtMarca" name="txtMarca" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($marcas as $m) { ?><option value="<?php echo $m['Consecutivo']; ?>"><?php echo Texto($m['Nombre']); ?></option><?php } ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="txtDescripcion">Descripción</label>
                            <textarea id="txtDescripcion" name="txtDescripcion" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label" for="txtImagen">Imagen <span class="text-secondary small">(URL o nombre de archivo en assets/img/productos, opcional)</span></label>
                            <input id="txtImagen" name="txtImagen" type="text" class="form-control" maxlength="255" placeholder="https://... o whey-chocolate.png">
                        </div>
                        <div class="col-12 col-md-4" id="grupoDestacado">
                            <label class="form-label" for="txtDestacado">Portada</label>
                            <select id="txtDestacado" name="txtDestacado" class="form-select">
                                <option value="0">Normal</option>
                                <option value="1">Destacado</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="txtEstado">Estado</label>
                            <select id="txtEstado" name="txtEstado" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btnActualizarProducto" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <?php IncludeJS(); ?>
    <script src="../assets/js/variantes.js"></script>

</body>

</html>
