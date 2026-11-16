<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/CatalogoController.php';

const PRODUCTOS_POR_PAGINA = 12;

$busqueda = trim($_GET['q'] ?? '');
$categoria = (int) ($_GET['categoria'] ?? 0);
$marca = (int) ($_GET['marca'] ?? 0);
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));

$categorias = ConsultarFiltrosCategorias();
$marcas = ConsultarFiltrosMarcas();

$total = ContarCatalogo($categoria, $marca, $busqueda);
$paginas = max(1, (int) ceil($total / PRODUCTOS_POR_PAGINA));
$pagina = min($pagina, $paginas);
$productos = ConsultarCatalogo($categoria, $marca, $busqueda, PRODUCTOS_POR_PAGINA, ($pagina - 1) * PRODUCTOS_POR_PAGINA);

$nombreCategoria = '';
foreach ($categorias as $c) {
    if ($c['Consecutivo'] == $categoria) {
        $nombreCategoria = $c['Nombre'];
    }
}
$hayFiltros = $busqueda != '' || $categoria > 0 || $marca > 0;

function UrlCatalogo($cambios)
{
    $parametros = array_merge(['q' => $_GET['q'] ?? '', 'categoria' => $_GET['categoria'] ?? 0, 'marca' => $_GET['marca'] ?? 0, 'pagina' => 1], $cambios);
    $parametros = array_filter($parametros, function ($v) { return $v !== '' && $v !== 0 && $v !== '0'; });
    if (isset($parametros['pagina']) && $parametros['pagina'] == 1) {
        unset($parametros['pagina']);
    }
    return 'catalog.php' . (count($parametros) ? '?' . http_build_query($parametros) : '');
}
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS(($nombreCategoria != '' ? $nombreCategoria . ' - ' : '') . 'Catálogo - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content">
        <div class="container-fluid">

            <div class="cat-cabeza catalogo-cabeza">
                <div>
                    <h1 class="display" style="font-size: clamp(3rem, 8vw, 6.5rem); margin: 0;"><?php echo $nombreCategoria != '' ? Texto($nombreCategoria) : ($hayFiltros ? 'Resultados' : 'Catálogo'); ?></h1>
                </div>
                <a href="home.php" class="ver-todo"><i class="ti ti-arrow-left"></i> Volver al inicio</a>
            </div>

            <?php MostrarMensaje(); ?>

            <form method="GET" action="catalog.php" class="herramientas">
                <input type="hidden" name="categoria" value="<?php echo $categoria; ?>">
                <input name="q" type="search" class="form-control" placeholder="Buscar producto" aria-label="Buscar producto" value="<?php echo Texto($busqueda); ?>">
                <select name="marca" class="form-select" aria-label="Marca" onchange="this.form.submit()">
                    <option value="0">Todas las marcas</option>
                    <?php foreach ($marcas as $m) { ?>
                        <option value="<?php echo $m['Consecutivo']; ?>" <?php echo $marca == $m['Consecutivo'] ? 'selected' : ''; ?>><?php echo Texto($m['Nombre']); ?></option>
                    <?php } ?>
                </select>
                <button class="btn btn-primary" type="submit"><i class="ti ti-search"></i> Buscar</button>
                <?php if ($hayFiltros) { ?><a href="catalog.php" class="btn btn-outline-secondary"><i class="ti ti-filter-off"></i> Limpiar</a><?php } ?>
            </form>

            <div class="tabs-cat">
                <a href="<?php echo UrlCatalogo(['categoria' => 0]); ?>" class="tab-cat <?php echo $categoria == 0 ? 'activo' : ''; ?>">Todo</a>
                <?php foreach ($categorias as $c) { ?>
                    <a href="<?php echo UrlCatalogo(['categoria' => $c['Consecutivo']]); ?>" class="tab-cat <?php echo $categoria == $c['Consecutivo'] ? 'activo' : ''; ?>"><i class="ti <?php echo IconoCategoria($c['Nombre']); ?>"></i> <?php echo Texto($c['Nombre']); ?></a>
                <?php } ?>
            </div>

            <?php if (count($productos) == 0) { ?>
                <div class="l-card empty-state">
                    <i class="ti ti-mood-empty"></i>
                    <p class="mb-3 fw-bold">No encontramos productos con esos filtros.</p>
                    <a href="catalog.php" class="btn btn-primary btn-sm px-4">Ver todo el catálogo</a>
                </div>
            <?php } else { ?>
                <div class="rejilla" id="productos">
                    <?php foreach ($productos as $p) { echo TarjetaProducto($p); } ?>
                </div>

                <?php if ($paginas > 1) { ?>
                    <nav class="paginador" aria-label="Páginas del catálogo">
                        <a href="<?php echo UrlCatalogo(['pagina' => $pagina - 1]); ?>" class="<?php echo $pagina == 1 ? 'inactivo' : ''; ?>" aria-label="Anterior"><i class="ti ti-chevron-left"></i></a>
                        <?php for ($n = 1; $n <= $paginas; $n++) { ?>
                            <?php if ($n == $pagina) { ?>
                                <span class="actual" aria-current="page"><?php echo $n; ?></span>
                            <?php } else { ?>
                                <a href="<?php echo UrlCatalogo(['pagina' => $n]); ?>"><?php echo $n; ?></a>
                            <?php } ?>
                        <?php } ?>
                        <a href="<?php echo UrlCatalogo(['pagina' => $pagina + 1]); ?>" class="<?php echo $pagina == $paginas ? 'inactivo' : ''; ?>" aria-label="Siguiente"><i class="ti ti-chevron-right"></i></a>
                    </nav>
                    <p class="paginador-info">Mostrando <?php echo ($pagina - 1) * PRODUCTOS_POR_PAGINA + 1; ?> a <?php echo ($pagina - 1) * PRODUCTOS_POR_PAGINA + count($productos); ?> de <?php echo $total; ?></p>
                <?php } ?>
            <?php } ?>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <?php IncludeJS(); ?>

</body>

</html>
