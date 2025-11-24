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

$hoy = date("Y-m-d");

// Validar fecha pasada
if (strtotime($fecha) < strtotime($hoy)) {
    $tipo = "warning";
    $titulo = "No se puede actualizar";
    $msg = "La fecha ingresada ya pasó.";

    include("resultado_editar.php");
    exit();
}

// Validar disponibilidad
$check = "
    SELECT 1 FROM citas
    WHERE id_doctor = $id_doctor
      AND fecha = '$fecha'
      AND hora = '$hora'
      AND id_cita <> $id_cita
    LIMIT 1
";

$exist = pg_query($conexion, $check);

if ($exist && pg_num_rows($exist) > 0) {
    $tipo = "warning";
    $titulo = "Horario no disponible";
    $msg = "El doctor ya tiene una cita en ese horario.";

    include("resultado_editar.php");
    exit();
}

$query = "
    UPDATE citas
    SET id_paciente = $id_paciente,
        id_doctor = $id_doctor,
        fecha = '$fecha',
        hora = '$hora'
    WHERE id_cita = $id_cita
";

$ok = pg_query($conexion, $query);

$tipo = $ok ? "success" : "error";
$titulo = $ok ? "Cita actualizada correctamente" : "Error al actualizar";
$msg = $ok ? "La cita se modificó exitosamente." : "Ocurrió un problema.";

// Pasar datos a la vista
$id_cita = $id_cita;
$id_paciente = $id_paciente;
$id_doctor = $id_doctor;
$fecha = $fecha;
$hora = $hora;

include("resultado_editar.php");
exit();
?>