<?php
session_start();
if (!isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: ../Consultar/consultar_citas.php?error=idinvalido");
    exit();
}

require_once __DIR__ . '/../conecta.php';

$detalle = pg_query_params(
    $conexion,
    "SELECT id_paciente, id_doctor, fecha, hora FROM citas WHERE id_cita = $1 LIMIT 1",
    [$id]
);

if (!$detalle || pg_num_rows($detalle) === 0) {
    pg_close($conexion);
    header("Location: ../Consultar/consultar_citas.php?error=noexiste");
    exit();
}

$detalle = pg_fetch_assoc($detalle);
$id_paciente_reg = (int)$detalle['id_paciente'];
$id_doctor_reg = (int)$detalle['id_doctor'];
$fecha_reg = $detalle['fecha'];
$hora_reg = $detalle['hora'];

$result = pg_query_params(
    $conexion,
    "DELETE FROM citas WHERE id_cita = $1",
    [$id]
);

$success = $result && pg_affected_rows($result) > 0;

$theme = 'theme-citas';

if ($success) {
    $tipo = "success";
    $titulo = "Cita cancelada correctamente";
    $msg = "La cita fue eliminada del calendario.";
} else {
    $tipo = "error";
    $titulo = "No se pudo cancelar la cita";
    $msg = "Ocurrió un problema al intentar eliminarla.";
}

include("../resultado_insertar.php");
exit();
