<?php
/* Layout de las vistas publicas de acceso (login, registro, recuperar contrasena). */

function IncludeCSS($titulo = 'Lifty - Suplementos deportivos')
{
    echo '
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>' . htmlspecialchars($titulo) . '</title>
            <link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">

            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Manrope:wght@400;500;600;700;800&display=swap">
            <link rel="stylesheet" href="../assets/css/main.css">
            <link rel="stylesheet" href="../assets/css/lifty.css?v=' . filemtime(__DIR__ . '/assets/css/lifty.css') . '">
        </head>
    ';
}

function MostrarLogo()
{
    echo '<a href="../Catalogo/catalog.php" class="auth-logo">LIFTY<span>.</span></a>';
}

/*
 * Panel visual de las pantallas de acceso: filas de palabras fitness en diagonal que se
 * desplazan en sentidos alternos. Solo texto y CSS.
 */
function MostrarArteAuth()
{
    $filas = [
        ['Rinde más', 'Recupera mejor', 'Repite'],
        ['Proteína', 'Creatina', 'Pre entreno', 'Aminoácidos'],
        ['Fuerza', 'Disciplina', 'Constancia', 'Energía'],
        ['Suplementos', 'Ropa deportiva', 'Snacks', 'Accesorios'],
        ['Entrena', 'Recupera', 'Repite', 'Supera'],
        ['Resistencia', 'Masa', 'Potencia', 'Ritmo'],
        ['Hidrátate', 'Descansa', 'Progresa', 'Rinde'],
    ];
    $estilos = ['solido', 'contorno', 'ember', 'contorno', 'solido', 'ember', 'contorno'];

    echo '<div class="auth-filas" aria-hidden="true">';
    foreach ($filas as $i => $palabras) {
        $contenido = '';
        for ($r = 0; $r < 4; $r++) {
            foreach ($palabras as $palabra) {
                $contenido .= '<span>' . htmlspecialchars($palabra) . '</span><b>/</b>';
            }
        }
        echo '<div class="auth-fila ' . $estilos[$i] . ($i % 2 ? ' inv' : '') . '" style="--v:' . (34 + $i * 6) . 's"><div class="pista">' . $contenido . '</div></div>';
    }
    echo '</div>';
}

function IncludeJS()
{
    echo '
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js"></script>
        <script src="../assets/js/validacion.js"></script>
        <script type="module" src="../assets/js/main.js"></script>
    ';
}
