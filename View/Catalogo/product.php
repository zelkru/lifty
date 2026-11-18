<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/CatalogoController.php';
include_once __DIR__ . '/../../Controller/PedidosController.php';

$producto = ConsultarDetalleProducto((int) ($_GET['id'] ?? 0));

if (!$producto) {
    AsignarMensaje('El producto no existe o ya no está disponible.', 'warning');
    Redirigir('catalog.php');
}
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS(Texto($producto['Nombre']) . ' - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content py-10">
        <div class="container-fluid">

            <?php MostrarMensaje(); ?>

            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="catalog.php">Catálogo</a></li>
                    <li class="breadcrumb-item"><a href="catalog.php?categoria=<?php echo $producto['ConsecutivoCategoria']; ?>"><?php echo Texto($producto['NombreCategoria']); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo Texto($producto['Nombre']); ?></li>
                </ol>
            </nav>

            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="escenario-producto">
                        <?php echo ArteProducto($producto['Imagen'], $producto['NombreCategoria'], $producto['NombreMarca'], $producto['Nombre']); ?>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="l-card l-card-body">
                        <span class="product-meta"><?php echo Texto($producto['NombreMarca']); ?> &middot; <?php echo Texto($producto['NombreCategoria']); ?></span>
                        <h1 class="h3 mt-1 mb-3"><?php echo Texto($producto['Nombre']); ?></h1>
                        <p class="text-secondary"><?php echo nl2br(Texto($producto['Descripcion'])); ?></p>

                        <?php if (count($producto['Variantes']) == 0 || !$producto['HayStock']) { ?>
                            <div class="alert alert-secondary mb-0"><i class="ti ti-info-circle"></i> Este producto está agotado por el momento.</div>
                        <?php } else { ?>
                            <form action="" method="POST" class="validar" novalidate>
                                <?php CampoCsrf(); ?>
                                <input type="hidden" name="txtProducto" value="<?php echo $producto['Consecutivo']; ?>">

                                <h2 class="h6 mt-4 mb-2">Presentación</h2>
                                <div class="row g-2 mb-3">
                                    <?php foreach ($producto['Variantes'] as $posicion => $variante) { ?>
                                        <div class="col-12 col-sm-6">
                                            <input class="visually-hidden variant-radio" type="radio" name="txtVariante" id="variante<?php echo $variante['Consecutivo']; ?>"
                                                   value="<?php echo $variante['Consecutivo']; ?>" data-stock="<?php echo (int) $variante['Stock']; ?>" <?php echo $variante['Stock'] < 1 ? 'disabled' : ''; ?> required>
                                            <label for="variante<?php echo $variante['Consecutivo']; ?>" class="variant-option d-block w-100 <?php echo $variante['Stock'] < 1 ? 'disabled' : ''; ?>">
                                                <div class="d-flex justify-content-between">
                                                    <strong><?php echo Texto($variante['Presentacion']); ?></strong>
                                                    <span class="price"><?php echo Dinero($variante['Precio']); ?></span>
                                                </div>
                                                <div class="small <?php echo $variante['Stock'] < 1 ? 'text-danger' : ($variante['Stock'] <= 5 ? 'text-warning' : 'text-secondary'); ?>">
                                                    <?php echo $variante['Stock'] < 1 ? 'Agotado' : ($variante['Stock'] <= 5 ? 'Quedan ' . $variante['Stock'] : $variante['Stock'] . ' disponibles'); ?>
                                                    &middot; SKU <?php echo Texto($variante['Sku']); ?>
                                                </div>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div id="errorVariante"></div>

                                <div class="d-flex flex-wrap gap-3 align-items-end mt-3">
                                    <div>
                                        <label for="txtCantidad" class="form-label small text-secondary mb-1">Cantidad</label>
                                        <input id="txtCantidad" name="txtCantidad" type="number" class="form-control qty-input" value="1" min="1" max="99" required>
                                    </div>
                                    <button type="submit" name="btnAgregarCarrito" class="btn btn-primary px-4">
                                        <i class="ti ti-shopping-cart-plus"></i> <?php echo EstaAutenticado() ? 'Agregar al carrito' : 'Inicie sesión para comprar'; ?>
                                    </button>
                                </div>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <?php IncludeJS(); ?>
    <script src="../assets/js/producto.js"></script>

</body>

</html>
