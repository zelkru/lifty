<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/CatalogoController.php';

$secciones = ConsultarSeccionesInicio();
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Lifty | Suplementos, ropa deportiva y snacks'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content con-hero">

        <section class="hero-x" id="hero">
            <div class="hero-grid">
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

                <a href="catalog.php" class="hero-foto" aria-label="Ver el catálogo de Lifty">
                    <img src="../assets/img/hero-set.png" alt="Set de productos Lifty: proteína, creatina, shaker, camiseta y guantes">
                </a>
            </div>
        </section>

        <div class="cinta-caja" aria-hidden="true">
            <div class="cinta">
                <div class="cinta-pista" id="cintaPista">
                    <?php for ($repeticion = 0; $repeticion < 6; $repeticion++) { foreach ($secciones as $seccion) { $categoria = $seccion['Categoria']; ?>
                        <span><?php echo Texto($categoria['Nombre']); ?></span><span><i class="ti <?php echo IconoCategoria($categoria['Nombre']); ?>"></i></span>
                    <?php } } ?>
                </div>
            </div>
        </div>

        <?php MostrarMensaje(); ?>

        <section class="seccion">
            <div class="cat-rapidas">
                <?php foreach ($secciones as $seccion) { $categoria = $seccion['Categoria']; ?>
                    <a href="catalog.php?categoria=<?php echo $categoria['Consecutivo']; ?>" class="cat-r rv">
                        <span class="arriba">
                            <i class="ti <?php echo IconoCategoria($categoria['Nombre']); ?> ico"></i>
                            <i class="ti ti-arrow-up-right f"></i>
                        </span>
                        <span class="abajo">
                            <span class="t"><?php echo Texto($categoria['Nombre']); ?></span>
                            <span class="c">Ver productos</span>
                        </span>
                    </a>
                <?php } ?>
            </div>
        </section>

        <div class="container-fluid p-0">

            <?php foreach ($secciones as $seccion) { $categoria = $seccion['Categoria']; if (count($seccion['Productos']) == 0) { continue; } ?>
                <section class="seccion seccion-cat">
                    <div class="cat-cabeza rv">
                        <div>
                            <h2 class="display"><?php echo Texto($categoria['Nombre']); ?></h2>
                        </div>
                        <a href="catalog.php?categoria=<?php echo $categoria['Consecutivo']; ?>" class="ver-todo">Ver todo en <?php echo Texto($categoria['Nombre']); ?> <i class="ti ti-arrow-right"></i></a>
                    </div>
                    <div class="rejilla">
                        <?php foreach ($seccion['Productos'] as $producto) { echo TarjetaProducto($producto); } ?>
                    </div>
                </section>
            <?php } ?>

            <section class="seccion">
                <div class="valores">
                    <div class="valor rv"><i class="ti ti-truck-delivery ico"></i><h3>Envío fijo</h3><p>Envío a todo el país por ₡2.500.</p></div>
                    <div class="valor rv"><i class="ti ti-adjustments-horizontal ico"></i><h3>Tu presentación</h3><p>Sabor, tamaño o talla: cada una con su precio y existencias reales.</p></div>
                    <div class="valor rv"><i class="ti ti-shield-check ico"></i><h3>Pago simulado</h3><p>Tarjeta o SINPE Móvil. Nunca guardamos datos de pago.</p></div>
                </div>
            </section>

            <section class="revela" id="revela">
                <div class="revela-stage">
                    <div class="revela-blob"></div>
                    <h2 class="revela-t"><span class="l1">Deja de adivinar,</span><span class="l2">elige tu dosis.</span></h2>
                    <p class="revela-sub">Cada producto con sus presentaciones, precio y existencias.</p>
                    <a href="catalog.php" class="btn-ember">Ir al catálogo completo</a>
                </div>
            </section>

            <?php MostrarFooter(); ?>
        </div>
    </main>

    <?php IncludeJS(); ?>

</body>

</html>
