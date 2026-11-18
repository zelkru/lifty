<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/CatalogoController.php';

$datos = ConsultarPaginaCatalogo();
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS(($datos['NombreCategoria'] != '' ? $datos['NombreCategoria'] . ' - ' : '') . 'Catálogo - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content">
        <div class="container-fluid">

            <div class="cat-cabeza catalogo-cabeza">
                <div>
                    <h1 class="display titulo-catalogo"><?php echo $datos['NombreCategoria'] != '' ? Texto($datos['NombreCategoria']) : ($datos['HayFiltros'] ? 'Resultados' : 'Catálogo'); ?></h1>
                </div>
                <a href="home.php" class="ver-todo"><i class="ti ti-arrow-left"></i> Volver al inicio</a>
            </div>

            <?php MostrarMensaje(); ?>

            <form method="GET" action="catalog.php" class="herramientas">
                <input type="hidden" name="categoria" value="<?php echo $datos['Categoria']; ?>">
                <input name="q" type="search" class="form-control" placeholder="Buscar producto" aria-label="Buscar producto" value="<?php echo Texto($datos['Busqueda']); ?>">
                <select name="marca" class="form-select" aria-label="Marca" data-autoenvio>
                    <option value="0">Todas las marcas</option>
                    <?php foreach ($datos['Marcas'] as $marca) { ?>
                        <option value="<?php echo $marca['Consecutivo']; ?>" <?php echo $datos['Marca'] == $marca['Consecutivo'] ? 'selected' : ''; ?>><?php echo Texto($marca['Nombre']); ?></option>
                    <?php } ?>
                </select>
                <button class="btn btn-primary" type="submit"><i class="ti ti-search"></i> Buscar</button>
                <?php if ($datos['HayFiltros']) { ?><a href="catalog.php" class="btn btn-outline-secondary"><i class="ti ti-filter-off"></i> Limpiar</a><?php } ?>
            </form>

            <div class="tabs-cat">
                <a href="<?php echo UrlCatalogo(['categoria' => 0]); ?>" class="tab-cat <?php echo $datos['Categoria'] == 0 ? 'activo' : ''; ?>">Todo</a>
                <?php foreach ($datos['Categorias'] as $categoria) { ?>
                    <a href="<?php echo UrlCatalogo(['categoria' => $categoria['Consecutivo']]); ?>" class="tab-cat <?php echo $datos['Categoria'] == $categoria['Consecutivo'] ? 'activo' : ''; ?>"><i class="ti <?php echo IconoCategoria($categoria['Nombre']); ?>"></i> <?php echo Texto($categoria['Nombre']); ?></a>
                <?php } ?>
            </div>

            <?php if (count($datos['Productos']) == 0) { ?>
                <div class="l-card empty-state">
                    <i class="ti ti-mood-empty"></i>
                    <p class="mb-3 fw-bold">No encontramos productos con esos filtros.</p>
                    <a href="catalog.php" class="btn btn-primary btn-sm px-4">Ver todo el catálogo</a>
                </div>
            <?php } else { ?>
                <div class="rejilla" id="productos">
                    <?php foreach ($datos['Productos'] as $producto) { echo TarjetaProducto($producto); } ?>
                </div>

                <?php if ($datos['Paginas'] > 1) { ?>
                    <nav class="paginador" aria-label="Páginas del catálogo">
                        <a href="<?php echo UrlCatalogo(['pagina' => $datos['Pagina'] - 1]); ?>" class="<?php echo $datos['Pagina'] == 1 ? 'inactivo' : ''; ?>" aria-label="Anterior"><i class="ti ti-chevron-left"></i></a>
                        <?php for ($numeroPagina = 1; $numeroPagina <= $datos['Paginas']; $numeroPagina++) { ?>
                            <?php if ($numeroPagina == $datos['Pagina']) { ?>
                                <span class="actual" aria-current="page"><?php echo $numeroPagina; ?></span>
                            <?php } else { ?>
                                <a href="<?php echo UrlCatalogo(['pagina' => $numeroPagina]); ?>"><?php echo $numeroPagina; ?></a>
                            <?php } ?>
                        <?php } ?>
                        <a href="<?php echo UrlCatalogo(['pagina' => $datos['Pagina'] + 1]); ?>" class="<?php echo $datos['Pagina'] == $datos['Paginas'] ? 'inactivo' : ''; ?>" aria-label="Siguiente"><i class="ti ti-chevron-right"></i></a>
                    </nav>
                    <p class="paginador-info">Mostrando <?php echo $datos['Desde']; ?> a <?php echo $datos['Hasta']; ?> de <?php echo $datos['Total']; ?></p>
                <?php } ?>
            <?php } ?>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <?php IncludeJS(); ?>

</body>

</html>
