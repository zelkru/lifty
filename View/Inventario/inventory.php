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
                    <?php foreach ($todasCategorias as $categoria) { ?><option value="<?php echo Texto($categoria['Nombre']); ?>"><?php echo Texto($categoria['Nombre']); ?></option><?php } ?>
                </select>
                <select class="form-select ft" data-tabla="#tablaProductos" data-col="2" aria-label="Marca">
                    <option value="">Todas las marcas</option>
                    <?php foreach ($todasMarcas as $marca) { ?><option value="<?php echo Texto($marca['Nombre']); ?>"><?php echo Texto($marca['Nombre']); ?></option><?php } ?>
                </select>
                <div class="btn-group pestanas-estado" role="group" aria-label="Estado" data-tabla="#tablaProductos" data-col="6">
                    <button type="button" class="btn btn-outline-secondary active" data-estado="activo">Activos <span class="badge text-bg-light border"></span></button>
                    <button type="button" class="btn btn-outline-secondary" data-estado="inactivo">Inactivos <span class="badge text-bg-light border"></span></button>
                </div>
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
                        <?php foreach ($productos as $producto) {
                            $datos = ['txtConsecutivo' => $producto['Consecutivo'], 'txtNombre' => $producto['Nombre'], 'txtDescripcion' => $producto['Descripcion'],
                                      'txtImagen' => $producto['Imagen'], 'txtCategoria' => $producto['ConsecutivoCategoria'], 'txtMarca' => $producto['ConsecutivoMarca'],
                                      'txtEstado' => $producto['Estado'], 'txtDestacado' => $producto['Destacado']]; ?>
                            <tr>
                                <td data-valor="<?php echo Texto($producto['Nombre']); ?>">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="cart-thumb">
                                            <?php if ($producto['Imagen'] != '') { ?><img src="<?php echo Texto(ImagenUrl($producto['Imagen'])); ?>" alt=""><?php } else { ?><?php echo Texto(mb_strtoupper(mb_substr($producto['Nombre'], 0, 1))); ?><?php } ?>
                                        </span>
                                        <div>
                                            <a href="variants.php?id=<?php echo $producto['Consecutivo']; ?>" class="fw-semibold text-decoration-none"><?php echo Texto($producto['Nombre']); ?></a>
                                            <?php if ($producto['Destacado'] == 1) { ?><span class="badge text-bg-dark ms-1"><i class="ti ti-star-filled"></i> Portada</span><?php } ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo Texto($producto['NombreCategoria']); ?></td>
                                <td><?php echo Texto($producto['NombreMarca']); ?></td>
                                <td class="text-center"><?php echo $producto['Variantes']; ?></td>
                                <td class="text-end" data-valor="<?php echo (float) $producto['PrecioDesde']; ?>"><?php echo $producto['PrecioDesde'] !== null ? Dinero($producto['PrecioDesde']) : '-'; ?></td>
                                <td class="text-center" data-valor="<?php echo (int) $producto['StockTotal']; ?>"><span class="badge text-bg-<?php echo $producto['StockTotal'] == 0 ? 'danger' : ($producto['StockTotal'] <= 10 ? 'warning' : 'light border'); ?>"><?php echo $producto['StockTotal']; ?></span></td>
                                <td data-valor="<?php echo $producto['Estado'] == 1 ? 'Activo' : 'Inactivo'; ?>"><span class="badge text-bg-<?php echo $producto['Estado'] == 1 ? 'success' : 'secondary'; ?>"><?php echo $producto['Estado'] == 1 ? 'Activo' : 'Inactivo'; ?></span></td>
                                <td class="text-end text-nowrap">
                                    <a href="variants.php?id=<?php echo $producto['Consecutivo']; ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-stack-2"></i> Presentaciones</a>
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
                                <?php foreach ($categorias as $categoria) { ?><option value="<?php echo $categoria['Consecutivo']; ?>"><?php echo Texto($categoria['Nombre']); ?></option><?php } ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="txtMarca">Marca</label>
                            <select id="txtMarca" name="txtMarca" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($marcas as $marca) { ?><option value="<?php echo $marca['Consecutivo']; ?>"><?php echo Texto($marca['Nombre']); ?></option><?php } ?>
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
    <script src="../assets/js/inventario.js"></script>

</body>

</html>
