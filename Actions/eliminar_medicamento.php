<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'empleado'])) {
    header("Location: ../index.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: ../Consultar/consultar_medicamento.php?error=idinvalido");
    exit();
}

include("../conecta.php");

// Verificar si está siendo usado en alguna consulta
$consultas = pg_query_params($conexion, "SELECT 1 FROM consulta WHERE id_medicamento = $1 LIMIT 1", [$id]);
if ($consultas && pg_num_rows($consultas) > 0) {
    $tipo = "warning";
    $titulo = "No se puede eliminar";
    $msg = "El medicamento está siendo usado en consultas registradas.";
    include("../resultado_insertar.php");
    exit();
}

$resultado = pg_query_params($conexion, "DELETE FROM medicamento WHERE codigo = $1", [$id]);

pg_close($conexion);

if ($resultado && pg_affected_rows($resultado) > 0) {
    $tipo = "success";
    $titulo = "Medicamento Eliminado";
    $msg = "El medicamento fue eliminado correctamente del sistema.";
} else {
    $tipo = "error";
    $titulo = "Error al Eliminar";
    $msg = "No fue posible eliminar el medicamento.";
}

include("../resultado_insertar.php");
