<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'empleado'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Consultar/consultar_medicamento.php");
    exit();
}

include("../conecta.php");

$codigo = isset($_POST['codigo']) ? intval($_POST['codigo']) : 0;
$nombre = trim($_POST['nombre'] ?? '');
$via_adm = trim($_POST['via_adm'] ?? '');
$presentacion = trim($_POST['presentacion'] ?? '');
$fecha_cad = $_POST['fecha_cad'] ?? '';

if ($codigo <= 0 || empty($nombre) || empty($via_adm) || empty($presentacion) || empty($fecha_cad)) {
    $tipo = "error";
    $titulo = "Datos incompletos";
    $msg = "Todos los campos obligatorios deben ser completados.";
    include("../resultado_insertar.php");
    exit();
}

$query = "UPDATE medicamento SET nombre=$1, via_adm=$2, presentacion=$3, fecha_cad=$4 WHERE codigo=$5";
$params = [$nombre, $via_adm, $presentacion, $fecha_cad, $codigo];

$resultado = pg_query_params($conexion, $query, $params);
pg_close($conexion);

if ($resultado) {
    $tipo = "success";
    $titulo = "Medicamento Actualizado";
    $msg = "Los datos del medicamento fueron actualizados correctamente.";
} else {
    $tipo = "error";
    $titulo = "Error al Actualizar";
    $msg = "No fue posible actualizar los datos del medicamento.";
}

include("../resultado_insertar.php");
