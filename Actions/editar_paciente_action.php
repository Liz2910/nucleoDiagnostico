<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'empleado'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Consultar/consultar_pacientes.php");
    exit();
}

include("../conecta.php");

$codigo = isset($_POST['codigo']) ? intval($_POST['codigo']) : 0;
$nombre = trim($_POST['nombre'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$fecha_nac = $_POST['fecha_nac'] ?? '';
$sexo = $_POST['sexo'] ?? '';
$edad = intval($_POST['edad'] ?? 0);
$estatura = floatval($_POST['estatura'] ?? 0);

if ($codigo <= 0 || empty($nombre) || empty($direccion) || empty($telefono) || 
    empty($fecha_nac) || empty($sexo)) {
    $tipo = "error";
    $titulo = "Datos incompletos";
    $msg = "Todos los campos obligatorios deben ser completados.";
    include("../resultado_insertar.php");
    exit();
}

$query = "UPDATE paciente SET nombre=$1, direccion=$2, telefono=$3, fecha_nac=$4, sexo=$5, edad=$6, estatura=$7 WHERE codigo=$8";
$params = [$nombre, $direccion, $telefono, $fecha_nac, $sexo, $edad, $estatura, $codigo];

$resultado = pg_query_params($conexion, $query, $params);
pg_close($conexion);

if ($resultado) {
    $tipo = "success";
    $titulo = "Paciente Actualizado";
    $msg = "Los datos del paciente fueron actualizados correctamente.";
} else {
    $tipo = "error";
    $titulo = "Error al Actualizar";
    $msg = "No fue posible actualizar los datos del paciente.";
}

include("../resultado_insertar.php");
