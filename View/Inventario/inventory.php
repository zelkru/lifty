<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/InventarioController.php';

ValidarAdmin();

$productos = ConsultarProductosAdmin();
$categorias = ConsultarCategorias(true);
$marcas = ConsultarMarcas(true);
$todasCategorias = ConsultarCategorias();
$todasMarcas = ConsultarMarcas();
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Inventario - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content py-10">
        <div class="container-fluid">

            <div class="page-head">
                <div>
                    <h1>Inventario</h1>
                    <p>Administre los productos, sus presentaciones y existencias</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto" data-nuevo>
                    <i class="ti ti-plus"></i> Nuevo producto
                </button>
            </div>

            <?php MostrarMensaje(); ?>

            <div class="barra-filtros">
                <input type="search" class="form-control ft ft-buscar" data-tabla="#tablaProductos" placeholder="Buscar producto, categoría o marca..." aria-label="Buscar">
                <select class="form-select ft" data-tabla="#tablaProductos" data-col="1" aria-label="Categoría">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($todasCategorias as $c) { ?><option value="<?php echo Texto($c['Nombre']); ?>"><?php echo Texto($c['Nombre']); ?></option><?php } ?>
                </select>
                <select class="form-select ft" data-tabla="#tablaProductos" data-col="2" aria-label="Marca">
                    <option value="">Todas las marcas</option>
                    <?php foreach ($todasMarcas as $m) { ?><option value="<?php echo Texto($m['Nombre']); ?>"><?php echo Texto($m['Nombre']); ?></option><?php } ?>
                </select>
                <select class="form-select ft" data-tabla="#tablaProductos" data-col="6" aria-label="Estado">
                    <option value="">Todos los estados</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
                <button type="button" class="btn btn-outline-secondary limpiar-filtros" data-tabla="#tablaProductos"><i class="ti ti-filter-off"></i> Limpiar</button>
            </div>

            <div class="l-card">
                <div class="table-responsive">
                    <table class="table l-table table-hover tabla-apilada tabla-datos" id="tablaProductos" data-pagina="12">
                        <thead>
                            <tr>
                                <th data-orden="texto">Producto</th>
                                <th data-orden="texto">Categoría</th>
                                <th data-orden="texto">Marca</th>
                                <th data-orden="numero" class="text-center">Presentaciones</th>
                                <th data-orden="numero" class="text-end">Desde</th>
                                <th data-orden="numero" class="text-center">Stock</th>
                                <th data-orden="texto">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($productos as $p) {
                            $datos = ['txtConsecutivo' => $p['Consecutivo'], 'txtNombre' => $p['Nombre'], 'txtDescripcion' => $p['Descripcion'],
                                      'txtImagen' => $p['Imagen'], 'txtCategoria' => $p['ConsecutivoCategoria'], 'txtMarca' => $p['ConsecutivoMarca'],
                                      'txtEstado' => $p['Estado'], 'txtDestacado' => $p['Destacado']]; ?>
                            <tr>
                                <td data-valor="<?php echo Texto($p['Nombre']); ?>">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="cart-thumb">
                                            <?php if ($p['Imagen'] != '') { ?><img src="<?php echo Texto(ImagenUrl($p['Imagen'])); ?>" alt=""><?php } else { ?><?php echo Texto(mb_strtoupper(mb_substr($p['Nombre'], 0, 1))); ?><?php } ?>
                                        </span>
                                        <div>
                                            <a href="variants.php?id=<?php echo $p['Consecutivo']; ?>" class="fw-semibold text-decoration-none"><?php echo Texto($p['Nombre']); ?></a>
                                            <?php if ($p['Destacado'] == 1) { ?><span class="badge text-bg-dark ms-1"><i class="ti ti-star-filled"></i> Portada</span><?php } ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo Texto($p['NombreCategoria']); ?></td>
                                <td><?php echo Texto($p['NombreMarca']); ?></td>
                                <td class="text-center"><?php echo $p['Variantes']; ?></td>
                                <td class="text-end" data-valor="<?php echo (float) $p['PrecioDesde']; ?>"><?php echo $p['PrecioDesde'] !== null ? Dinero($p['PrecioDesde']) : '-'; ?></td>
                                <td class="text-center" data-valor="<?php echo (int) $p['StockTotal']; ?>"><span class="badge text-bg-<?php echo $p['StockTotal'] == 0 ? 'danger' : ($p['StockTotal'] <= 10 ? 'warning' : 'light border'); ?>"><?php echo $p['StockTotal']; ?></span></td>
                                <td data-valor="<?php echo $p['Estado'] == 1 ? 'Activo' : 'Inactivo'; ?>"><span class="badge text-bg-<?php echo $p['Estado'] == 1 ? 'success' : 'secondary'; ?>"><?php echo $p['Estado'] == 1 ? 'Activo' : 'Inactivo'; ?></span></td>
                                <td class="text-end text-nowrap">
                                    <a href="variants.php?id=<?php echo $p['Consecutivo']; ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-stack-2"></i> Presentaciones</a>
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProducto"
                                            data-fill="<?php echo Texto(json_encode($datos)); ?>"><i class="ti ti-edit"></i> Editar</button>
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

    <div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="tituloProducto" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="" method="POST" class="modal-content validar" novalidate>
                <?php CampoCsrf(); ?>
                <input type="hidden" name="txtConsecutivo" value="">
                <div class="modal-header">
                    <h2 class="modal-title" id="tituloProducto">Producto</h2>
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
                        <div class="col-12 col-md-4" id="grupoEstado">
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
                    <button type="submit" name="btnRegistrarProducto" class="btn btn-primary" id="btnGuardarProducto"><i class="ti ti-check"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <?php IncludeJS(); ?>
    <script>
        $('#modalProducto form').on('submit', function () {
            var editando = $(this).find('[name="txtConsecutivo"]').val() !== '';
            $('#btnGuardarProducto').attr('name', editando ? 'btnActualizarProducto' : 'btnRegistrarProducto');
        });
        document.getElementById('modalProducto').addEventListener('show.bs.modal', function (e) {
            var editando = e.relatedTarget && e.relatedTarget.dataset.fill;
            $('#grupoEstado, #grupoDestacado').toggle(!!editando);
            $('#tituloProducto').text(editando ? 'Editar producto' : 'Nuevo producto');
        });
    </script>

</body>

</html>
