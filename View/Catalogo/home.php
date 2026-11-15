<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/CatalogoController.php';

/* Inicio de la tienda: vista general. Pocos productos por categoria y enlace al catalogo completo. */
const PRODUCTOS_POR_CATEGORIA = 4;

$categorias = ConsultarFiltrosCategorias();
$marcas = ConsultarFiltrosMarcas();

/* Producto destacado de la portada: el marcado como destacado (la consulta lo trae primero) o el primero con existencias. */
$destacado = null;
foreach (ConsultarCatalogo(0, 0, '', 6, 0) as $p) {
    if ($p['StockTotal'] > 0) {
        $destacado = $p;
        break;
    }
}

/* Vista previa de cada categoria */
$secciones = [];
foreach ($categorias as $c) {
    $secciones[] = [
        'Categoria' => $c,
        'Total' => ContarCatalogo($c['Consecutivo'], 0, ''),
        'Productos' => ConsultarCatalogo($c['Consecutivo'], 0, '', PRODUCTOS_POR_CATEGORIA, 0),
    ];
}

/* Cada palabra del titular entra por separado (la animacion esta en el CSS). */
function Palabras($texto, $claseUltima = '', $clasePrimera = '')
{
    $palabras = preg_split('/\s+/', trim($texto));
    $html = '';
    foreach ($palabras as $i => $palabra) {
        $clase = ($i == count($palabras) - 1 ? $claseUltima : '') . ($i == 0 ? ' ' . $clasePrimera : '');
        $html .= '<span class="pal ' . $clase . '"><span style="--d:' . (150 + $i * 110) . 'ms">' . Texto($palabra) . '</span></span> ';
    }
    return $html;
}
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Lifty | Suplementos, ropa deportiva y snacks'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content con-hero">

        <section class="hero-x" id="hero">
            <div class="hero-grid <?php echo ($destacado && $destacado['Imagen'] != '') ? '' : 'solo'; ?>">
                <div>
                    <p class="hero-meta">Envío fijo ₡2.500 &middot; Pago simulado</p>
                    <h1 class="display">
                        <span class="linea"><?php echo Palabras('Rinde más.', 'ember'); ?></span>
                        <span class="linea"><?php echo Palabras('Recupera mejor.', '', 'contorno'); ?></span>
                    </h1>
                    <p class="hero-lead">Suplementos, ropa deportiva y snacks para tu rutina. Elige la presentación exacta que necesitas y recíbela en casa.</p>
                    <div class="hero-actions">
                        <a href="catalog.php" class="btn-ember">Ver el catálogo</a>
                        <?php if (!EstaAutenticado()) { ?><a href="../Inicio/register.php" class="btn-ghost">Crear cuenta</a><?php } ?>
                    </div>
                </div>

                <?php if ($destacado && $destacado['Imagen'] != '') { ?>
                    <a href="product.php?id=<?php echo $destacado['Consecutivo']; ?>" class="hero-foto" aria-label="<?php echo Texto($destacado['Nombre']); ?>">
                        <img src="<?php echo Texto(ImagenUrl($destacado['Imagen'])); ?>" alt="<?php echo Texto($destacado['Nombre']); ?>">
                    </a>
                <?php } ?>
            </div>
        </section>

        <div class="cinta-caja" aria-hidden="true">
            <div class="cinta">
                <div class="cinta-pista" id="cintaPista">
                    <?php for ($r = 0; $r < 6; $r++) { foreach ($categorias as $c) { ?>
                        <span><?php echo Texto($c['Nombre']); ?></span><span><i class="ti <?php echo IconoCategoria($c['Nombre']); ?>"></i></span>
                    <?php } } ?>
                </div>
            </div>
        </div>

        <?php MostrarMensaje(); ?>

        <section class="seccion">
            <div class="cat-rapidas">
                <?php foreach ($secciones as $s) { $c = $s['Categoria']; ?>
                    <a href="catalog.php?categoria=<?php echo $c['Consecutivo']; ?>" class="cat-r rv">
                        <span class="arriba">
                            <i class="ti <?php echo IconoCategoria($c['Nombre']); ?> ico"></i>
                            <i class="ti ti-arrow-up-right f"></i>
                        </span>
                        <span class="abajo">
                            <span class="t"><?php echo Texto($c['Nombre']); ?></span>
                            <span class="c">Ver productos</span>
                        </span>
                    </a>
                <?php } ?>
            </div>
        </section>

        <div class="container-fluid p-0">

            <?php foreach ($secciones as $s) { $c = $s['Categoria']; if (count($s['Productos']) == 0) { continue; } ?>
                <section class="seccion seccion-cat">
                    <div class="cat-cabeza rv">
                        <div>
                            <h2 class="display"><?php echo Texto($c['Nombre']); ?></h2>
                        </div>
                        <a href="catalog.php?categoria=<?php echo $c['Consecutivo']; ?>" class="ver-todo">Ver todo en <?php echo Texto($c['Nombre']); ?> <i class="ti ti-arrow-right"></i></a>
                    </div>
                    <div class="rejilla">
                        <?php foreach ($s['Productos'] as $p) { echo TarjetaProducto($p); } ?>
                    </div>
                </section>
            <?php } ?>

            <section class="seccion">
                <div class="valores">
                    <div class="valor rv"><i class="ti ti-truck-delivery ico"></i><h3>Envío fijo</h3><p>₡2.500 a todo el país, sin sorpresas al pagar.</p></div>
                    <div class="valor rv"><i class="ti ti-adjustments-horizontal ico"></i><h3>Tu presentación</h3><p>Sabor, tamaño o talla: cada una con su precio y existencias reales.</p></div>
                    <div class="valor rv"><i class="ti ti-shield-check ico"></i><h3>Pago simulado</h3><p>Tarjeta o SINPE Móvil. Nunca guardamos datos de pago.</p></div>
                </div>
            </section>

            <section class="revela" id="revela">
                <div class="revela-stage">
                    <div class="revela-blob"></div>
                    <h2 class="revela-t"><span class="l1">Deja de adivinar,</span><span class="l2">elige tu dosis.</span></h2>
                    <p class="revela-sub">Cada producto con sus presentaciones, precio y existencias reales. Tú decides, nosotros lo llevamos a casa.</p>
                    <a href="catalog.php" class="btn-ember">Ir al catálogo completo</a>
                </div>
            </section>

            <?php MostrarFooter(); ?>
        </div>
    </main>

    <?php IncludeJS(); ?>

</body>

</html>
