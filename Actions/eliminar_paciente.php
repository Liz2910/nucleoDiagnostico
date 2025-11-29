<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'empleado'])) {
    header("Location: ../index.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: ../Consultar/consultar_pacientes.php?error=idinvalido");
    exit();
}

include("../conecta.php");

// Verificar si tiene citas asociadas
$citas = pg_query_params($conexion, "SELECT 1 FROM citas WHERE id_paciente = $1 LIMIT 1", [$id]);
if ($citas && pg_num_rows($citas) > 0) {
    $tipo = "warning";
    $titulo = "No se puede eliminar";
    $msg = "El paciente tiene citas asociadas. Primero elimine las citas.";
    include("../resultado_insertar.php");
    exit();
}

$resultado = pg_query_params($conexion, "DELETE FROM paciente WHERE codigo = $1", [$id]);

pg_close($conexion);

if ($resultado && pg_affected_rows($resultado) > 0) {
    $tipo = "success";
    $titulo = "Paciente Eliminado";
    $msg = "El paciente fue eliminado correctamente del sistema.";
} else {
    $tipo = "error";
    $titulo = "Error al Eliminar";
    $msg = "No fue posible eliminar el paciente.";
}

include("../resultado_insertar.php");
