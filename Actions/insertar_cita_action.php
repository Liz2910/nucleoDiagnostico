<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$id_paciente_reg = null;
$id_doctor_reg = null;
$fecha_reg = '';
$hora_reg = '';

$paciente = isset($_POST['id_paciente']) ? intval($_POST['id_paciente']) : 0;
$doctor   = isset($_POST['id_doctor']) ? intval($_POST['id_doctor']) : 0;
$fecha    = $_POST['fecha'] ?? '';
$hora     = $_POST['hora'] ?? '';

if ($paciente <= 0 || $doctor <= 0 || empty($fecha) || empty($hora)) {
    $tipo = "error";
    $titulo = "Datos incompletos";
    $msg = "Selecciona paciente, doctor, fecha y hora antes de registrar.";

    include("../resultado_insertar.php");
    exit();
}

// ========== VALIDACIÓN DE HORARIO ==========
// Solo Lunes a Viernes
$diaSemana = date('N', strtotime($fecha)); // 1=Lunes, 7=Domingo
if ($diaSemana > 5) {
    $tipo = "warning";
    $titulo = "Día No Válido";
    $msg = "Solo se pueden agendar citas de Lunes a Viernes.";
    include("../resultado_insertar.php");
    exit();
}

// Solo de 9:00 a 20:00 (última cita a las 19:00 para terminar a las 20:00)
$horaInt = intval(substr($hora, 0, 2));
if ($horaInt < 9 || $horaInt > 19) {
    $tipo = "warning";
    $titulo = "Hora No Válida";
    $msg = "El horario de citas es de 9:00 a 20:00 hrs. La última cita disponible es a las 19:00.";
    include("../resultado_insertar.php");
    exit();
}

// Intervalos de 1 hora (minutos deben ser 00)
$minutos = intval(substr($hora, 3, 2));
if ($minutos !== 0) {
    $tipo = "warning";
    $titulo = "Hora No Válida";
    $msg = "Las citas deben ser en intervalos de 1 hora (ej: 9:00, 10:00, 11:00...).";
    include("../resultado_insertar.php");
    exit();
}

// Validar duplicado (mismo doctor, misma fecha y hora)
$check = pg_query_params(
    $conexion,
    "SELECT 1 FROM citas WHERE id_doctor = $1 AND fecha = $2 AND hora = $3 LIMIT 1",
    [$doctor, $fecha, $hora]
);

if (pg_num_rows($check) > 0) {
    $tipo = "warning";
    $titulo = "Horario No Disponible";
    $msg = "El doctor ya tiene una cita en ese horario.";

    include("../resultado_insertar.php");
    exit();
}

// Insertar cita
$query = "INSERT INTO citas (id_paciente, id_doctor, fecha, hora) VALUES ($1, $2, $3, $4)";
$resultado = pg_query_params($conexion, $query, [$paciente, $doctor, $fecha, $hora]);

if ($resultado) {
    $id_paciente_reg = $paciente;
    $id_doctor_reg   = $doctor;
    $fecha_reg       = $fecha;
    $hora_reg        = $hora;

    // Viene desde disponibilidad (botón de horarios)
    if (isset($_POST['desde_disponibilidad'])) {

        $tipo = "success";
        $titulo = "Cita Registrada Correctamente";
        $msg = "La cita fue creada exitosamente.";

        include("../resultado_insertar.php");
        exit();
    }

    // Viene desde insertar_cita.php (normal)
    $tipo = "success";
    $titulo = "Cita Registrada Correctamente";
    $msg = "La cita fue creada exitosamente.";

    include("../resultado_insertar.php");
    exit();
}

// Error general
$tipo = "error";
$titulo = "Error al Registrar";
$msg = "No fue posible registrar la cita.";

include("../resultado_insertar.php");
exit();