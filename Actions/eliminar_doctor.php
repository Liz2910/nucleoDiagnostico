<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: ../Consultar/consultar_doctores.php?error=idinvalido");
    exit();
}

include("../conecta.php");

// Verificar si tiene citas asociadas
$citas = pg_query_params($conexion, "SELECT 1 FROM citas WHERE id_doctor = $1 LIMIT 1", [$id]);
if ($citas && pg_num_rows($citas) > 0) {
    $tipo = "warning";
    $titulo = "No se puede eliminar";
    $msg = "El doctor tiene citas asociadas. Primero elimine las citas.";
    include("../resultado_insertar.php");
    exit();
}

$resultado = pg_query_params($conexion, "DELETE FROM doctor WHERE codigo = $1", [$id]);

pg_close($conexion);

if ($resultado && pg_affected_rows($resultado) > 0) {
    $tipo = "success";
    $titulo = "Doctor Eliminado";
    $msg = "El doctor fue eliminado correctamente del sistema.";
} else {
    $tipo = "error";
    $titulo = "Error al Eliminar";
    $msg = "No fue posible eliminar el doctor.";
}

include("../resultado_insertar.php");
