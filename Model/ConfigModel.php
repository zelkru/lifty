<?php
/*
 * ConfigModel: conexion a la base de datos y utilidades comunes del Model.
 *
 * En XAMPP funciona sin configurar nada (127.0.0.1:3307, root, sin clave).
 * Con Docker los datos se toman de las variables de entorno DB_*.
 */

function Conectar()
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $servidor = getenv('DB_HOST') ?: '127.0.0.1';
    $puerto   = (int) (getenv('DB_PORT') ?: 3307);
    $usuario  = getenv('DB_USER') ?: 'root';
    $clave    = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $baseDatos = getenv('DB_NAME') ?: 'lifty_bd';

    $context = new mysqli($servidor, $usuario, $clave, $baseDatos, $puerto);
    $context -> set_charset('utf8mb4');
    // Misma collation que las tablas, para que MySQL 8 y MariaDB (XAMPP) comparen igual.
    $context -> query("SET collation_connection = 'utf8mb4_general_ci'");
    return $context;
}

function Cerrar($context)
{
    $context -> close();
}

/*
 * Convierte un valor PHP a un literal SQL seguro para armar el CALL.
 * Los numeros pasan tal cual, null es NULL y el texto se escapa.
 */
function ParametroSql($context, $valor)
{
    if ($valor === null) {
        return 'NULL';
    }
    if (is_int($valor) || is_float($valor)) {
        return (string) $valor;
    }
    return "'" . $context -> real_escape_string((string) $valor) . "'";
}

/*
 * Ejecuta un procedimiento almacenado y devuelve todas sus filas.
 * Uso: Procedimiento($context, 'spConsultarUsuario', [$consecutivo]);
 */
function Procedimiento($context, $nombre, $parametros = [])
{
    $lista = [];
    foreach ($parametros as $valor) {
        $lista[] = ParametroSql($context, $valor);
    }

    $sql = "CALL $nombre(" . implode(',', $lista) . ")";
    $response = $context -> query($sql);

    $data = [];
    if ($response instanceof mysqli_result) {
        while ($row = $response -> fetch_assoc()) {
            $data[] = $row;
        }
        $response -> free();
    }

    // Se libera cualquier resultado adicional para poder seguir usando la conexion.
    while ($context -> more_results() && $context -> next_result()) {
        $extra = $context -> use_result();
        if ($extra instanceof mysqli_result) {
            $extra -> free();
        }
    }

    return $data;
}

/* Igual que Procedimiento, pero devuelve solo la primera fila (o null). */
function ProcedimientoFila($context, $nombre, $parametros = [])
{
    $data = Procedimiento($context, $nombre, $parametros);
    return count($data) > 0 ? $data[0] : null;
}

/* Para los procedimientos que responden con una sola columna booleana (Actualizado, Pagado...). */
function ProcedimientoBooleano($context, $nombre, $parametros = [])
{
    $fila = ProcedimientoFila($context, $nombre, $parametros);
    return $fila !== null && (bool) reset($fila);
}

function RegistrarError($exc, $accion)
{
    try {
        $context = Conectar();

        $idUsuario = isset($_SESSION['ConsecutivoUsuario']) ? (int) $_SESSION['ConsecutivoUsuario'] : 0;
        Procedimiento($context, 'spRegistrarError', [$exc -> getMessage(), $accion, $idUsuario]);

        Cerrar($context);
    }
    catch (Exception $e) {
        error_log('Lifty: no se pudo registrar el error de ' . $accion . ': ' . $exc -> getMessage());
    }
}
