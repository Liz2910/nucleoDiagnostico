<?php
// Recibe:
// $tipo, $titulo, $msg, $id_cita, $id_paciente, $id_doctor, $fecha, $hora

include("../conecta.php");

// Obtener nombre del paciente
$q1 = pg_query($conexion, "SELECT nombre FROM paciente WHERE codigo = $id_paciente");
$paciente = pg_fetch_assoc($q1)['nombre'] ?? "Paciente desconocido";

// Obtener nombre del doctor
$q2 = pg_query($conexion, "SELECT nombre FROM doctor WHERE codigo = $id_doctor");
$doctor = pg_fetch_assoc($q2)['nombre'] ?? "Doctor desconocido";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>

    <link rel="stylesheet" href="../Styles/resultado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="theme-citas">

<div class="result-container">
    <div class="result-card">

        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <h2 class="result-title"><?php echo $titulo; ?></h2>
        <p class="result-message"><?php echo $msg; ?></p>

        <div class="info-box">
            <p>
                <i class="fas fa-hashtag"></i>
                <span><strong>ID Cita:</strong> <?php echo $id_cita; ?></span>
            </p>
            <p>
                <i class="fas fa-user"></i>
                <span><strong>Paciente:</strong> <?php echo $paciente; ?></span>
            </p>
            <p>
                <i class="fas fa-user-md"></i>
                <span><strong>Doctor:</strong> <?php echo $doctor; ?></span>
            </p>
            <p>
                <i class="fas fa-calendar"></i>
                <span><strong>Fecha:</strong> <?php echo $fecha; ?></span>
            </p>
            <p>
                <i class="fas fa-clock"></i>
                <span><strong>Hora:</strong> <?php echo $hora; ?></span>
            </p>
        </div>

        <div class="btn-container">
            <a href="../consultar_citas.php" class="btn-return">
                <i class="fas fa-list"></i> Volver a consultar citas
            </a>

            <a href="../menu.php" class="btn-menu">
                <i class="fas fa-home"></i> Volver al menú
            </a>
        </div>

    </div>
</div>

</body>
</html>