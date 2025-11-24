<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$paciente = $_POST['id_paciente'];
$doctor   = $_POST['id_doctor'];
$fecha    = $_POST['fecha'];
$hora     = $_POST['hora'];

// Validar duplicado
$check = pg_query($conexion, "
    SELECT 1 FROM citas 
    WHERE id_doctor = $doctor 
      AND fecha = '$fecha'
      AND hora = '$hora'
    LIMIT 1
");

if (pg_num_rows($check) > 0) {
    $tipo = "warning";
    $titulo = "Horario No Disponible";
    $msg = "El doctor ya tiene una cita en ese horario.";

    include("resultado_insertar.php");
    exit();
}

// Insertar cita
$query = "
INSERT INTO citas (id_paciente, id_doctor, fecha, hora) 
VALUES ($paciente, $doctor, '$fecha', '$hora')
";

$resultado = pg_query($conexion, $query);

// --- VARIABLES para usar en resultado_insertar.php ---
$id_paciente_reg = $paciente;
$id_doctor_reg   = $doctor;
$fecha_reg       = $fecha;
$hora_reg        = $hora;

if ($resultado) {

    // Viene desde disponibilidad (botón de horarios)
    if (isset($_POST['desde_disponibilidad'])) {

        $tipo = "success";
        $titulo = "Cita Registrada Correctamente";
        $msg = "La cita fue creada exitosamente.";

        include("resultado_insertar.php");
        exit();
    }

    // Viene desde insertar_cita.php (normal)
    $tipo = "success";
    $titulo = "Cita Registrada Correctamente";
    $msg = "La cita fue creada exitosamente.";

    include("resultado_insertar.php");
    exit();
}

// Error general
$tipo = "error";
$titulo = "Error al Registrar";
$msg = "No fue posible registrar la cita.";

include("resultado_insertar.php");
exit();

?>