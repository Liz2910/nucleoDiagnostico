<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: ../Consultar/consultar_empleados.php?error=idinvalido");
    exit();
}

include("../conecta.php");

$resultado = pg_query_params($conexion, "DELETE FROM empleado WHERE codigo = $1", [$id]);

pg_close($conexion);

if ($resultado && pg_affected_rows($resultado) > 0) {
    $tipo = "success";
    $titulo = "Empleado Eliminado";
    $msg = "El empleado fue eliminado correctamente del sistema.";
} else {
    $tipo = "error";
    $titulo = "Error al Eliminar";
    $msg = "No fue posible eliminar el empleado.";
}

include("../resultado_insertar.php");
