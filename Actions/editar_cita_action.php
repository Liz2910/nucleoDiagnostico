<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$id_cita = intval($_POST['id_cita']);
$id_paciente = intval($_POST['id_paciente']);
$id_doctor = intval($_POST['id_doctor']);
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];

date_default_timezone_set('America/Mexico_City');

// Verificar cita existente
$orig = pg_query_params($conexion, "SELECT fecha, hora FROM citas WHERE id_cita = $1", [$id_cita]);
if (!$orig || pg_num_rows($orig) === 0) {
    $tipo="error"; $titulo="No existe"; $msg="La cita no fue encontrada.";
    include("../resultado_editar.php"); exit();
}
$origRow = pg_fetch_assoc($orig);
$origInicio = strtotime($origRow['fecha'].' '.substr($origRow['hora'],0,5).':00');
$origFin    = $origInicio + 3600;
if (time() >= $origFin) {
    $tipo="warning"; $titulo="Cita finalizada"; $msg="La cita original ya terminó y no puede editarse.";
    include("../resultado_editar.php"); exit();
}

// Validar nueva fecha/hora
$horaNormalizada = substr($hora,0,5);
$nuevaInicio = strtotime($fecha.' '.$horaNormalizada.':00');
$nuevoFin    = $nuevaInicio + 3600;
if ($nuevoFin <= time()) {
    $tipo="warning"; $titulo="Fecha/Hora inválidas"; $msg="La nueva programación ya está en pasado.";
    include("../resultado_editar.php"); exit();
}

// Disponibilidad
$exist = pg_query_params(
  $conexion,
  "SELECT 1 FROM citas WHERE id_doctor=$1 AND fecha=$2 AND hora=$3 AND id_cita<>$4 LIMIT 1",
  [$id_doctor, $fecha, $hora, $id_cita]
);
if ($exist && pg_num_rows($exist)>0) {
    $tipo="warning"; $titulo="Horario ocupado"; $msg="El doctor tiene otra cita en ese horario.";
    include("../resultado_editar.php"); exit();
}

// Update
$ok = pg_query_params(
  $conexion,
  "UPDATE citas SET id_paciente=$1, id_doctor=$2, fecha=$3, hora=$4 WHERE id_cita=$5",
  [$id_paciente,$id_doctor,$fecha,$hora,$id_cita]
);

$tipo   = $ok ? "success" : "error";
$titulo = $ok ? "Cita actualizada correctamente" : "Error al actualizar";
$msg    = $ok ? "La cita se modificó exitosamente." : "Ocurrió un problema.";

// Pasar datos a la vista
$id_cita = $id_cita;
$id_paciente = $id_paciente;
$id_doctor = $id_doctor;
$fecha = $fecha;
$hora = $hora;

include("../resultado_editar.php");
exit();
?>