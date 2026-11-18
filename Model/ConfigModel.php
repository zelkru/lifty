<?php

function CargarEntorno()
{
    $archivo = __DIR__ . '/../.env';

    if (!is_readable($archivo)) {
        return;
    }

    foreach (file($archivo, FILE_IGNORE_NEW_LINES) as $linea) {
        $linea = trim($linea);

        if ($linea == '' || $linea[0] == '#' || strpos($linea, '=') === false) {
            continue;
        }

        list($clave, $valor) = explode('=', $linea, 2);
        $clave = trim($clave);

        if (getenv($clave) === false) {
            putenv($clave . '=' . trim($valor, " 	\"'"));
        }
    }
}

CargarEntorno();

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
    $context -> query("SET collation_connection = 'utf8mb4_general_ci'");
    return $context;
}

function Cerrar($context)
{
    $context -> close();
}

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

    while ($context -> more_results() && $context -> next_result()) {
        $extra = $context -> use_result();
        if ($extra instanceof mysqli_result) {
            $extra -> free();
        }
    }

    return $data;
}

function ProcedimientoFila($context, $nombre, $parametros = [])
{
    $data = Procedimiento($context, $nombre, $parametros);
    return count($data) > 0 ? $data[0] : null;
}

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
    catch (Exception $excepcion) {
        error_log('Lifty: no se pudo registrar el error de ' . $accion . ': ' . $exc -> getMessage());
    }
}
