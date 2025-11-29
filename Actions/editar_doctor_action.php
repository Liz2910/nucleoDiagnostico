<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Consultar/consultar_doctores.php");
    exit();
}

include("../conecta.php");

$codigo = isset($_POST['codigo']) ? intval($_POST['codigo']) : 0;
$nombre = trim($_POST['nombre'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$fecha_nac = $_POST['fecha_nac'] ?? '';
$sexo = $_POST['sexo'] ?? '';
$especialidad = trim($_POST['especialidad'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');

if ($codigo <= 0 || empty($nombre) || empty($direccion) || empty($telefono) || 
    empty($fecha_nac) || empty($sexo) || empty($especialidad)) {
    $tipo = "error";
    $titulo = "Datos incompletos";
    $msg = "Todos los campos obligatorios deben ser completados.";
    include("../resultado_insertar.php");
    exit();
}

// Construir query
if (!empty($contrasena)) {
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $query = "UPDATE doctor SET nombre=$1, direccion=$2, telefono=$3, fecha_nac=$4, sexo=$5, especialidad=$6, contrasena=$7 WHERE codigo=$8";
    $params = [$nombre, $direccion, $telefono, $fecha_nac, $sexo, $especialidad, $hash, $codigo];
} else {
    $query = "UPDATE doctor SET nombre=$1, direccion=$2, telefono=$3, fecha_nac=$4, sexo=$5, especialidad=$6 WHERE codigo=$7";
    $params = [$nombre, $direccion, $telefono, $fecha_nac, $sexo, $especialidad, $codigo];
}

$resultado = pg_query_params($conexion, $query, $params);
pg_close($conexion);

if ($resultado) {
    $tipo = "success";
    $titulo = "Doctor Actualizado";
    $msg = "Los datos del doctor fueron actualizados correctamente.";
} else {
    $tipo = "error";
    $titulo = "Error al Actualizar";
    $msg = "No fue posible actualizar los datos del doctor.";
}

include("../resultado_insertar.php");
