<?php
if (!isset($conexion) || !is_resource($conexion)) {
    include_once __DIR__ . '/conecta.php';
}

$pacienteInfo = null;
$doctorInfo = null;

if (!empty($id_paciente_reg)) {
    $resPaciente = pg_query_params(
        $conexion,
        "SELECT nombre FROM paciente WHERE codigo = $1 LIMIT 1",
        [$id_paciente_reg]
    );
    if ($resPaciente) {
        $pacienteInfo = pg_fetch_assoc($resPaciente);
    }
}

if (!empty($id_doctor_reg)) {
    $resDoctor = pg_query_params(
        $conexion,
        "SELECT nombre FROM doctor WHERE codigo = $1 LIMIT 1",
        [$id_doctor_reg]
    );
    if ($resDoctor) {
        $doctorInfo = pg_fetch_assoc($resDoctor);
    }
}

$theme = $theme ?? 'theme-citas';

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$baseUrl = preg_replace('#/(Actions|Insertar|Consultar)$#i', '', $scriptDir);
$baseUrl = ($baseUrl === '' ? '/' : $baseUrl . '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>

    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl . 'Styles/resultado.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?= htmlspecialchars($theme); ?>">

<div class="result-container">
    <div class="result-card">

        <?php if ($tipo == "success"): ?>
            <div class="success-icon"><i class="fas fa-check"></i></div>
        <?php elseif ($tipo == "warning"): ?>
            <div class="warning-icon"><i class="fas fa-exclamation"></i></div>
        <?php else: ?>
            <div class="error-icon"><i class="fas fa-times"></i></div>
        <?php endif; ?>

        <h2 class="result-title"><?php echo $titulo; ?></h2>
        <p class="result-message"><?php echo $msg; ?></p>

        <?php if ($tipo == "success"): ?>
        <div class="info-box">
            <p>
                <i class="fas fa-user"></i>
                <span><strong>Paciente:</strong> <?php echo $pacienteInfo['nombre'] ?? ''; ?></span>
            </p>
            <p>
                <i class="fas fa-user-md"></i>
                <span><strong>Doctor:</strong> <?php echo $doctorInfo['nombre'] ?? ''; ?></span>
            </p>
            <p>
                <i class="fas fa-calendar"></i>
                <span><strong>Fecha:</strong> <?php echo $fecha_reg; ?></span>
            </p>
            <p>
                <i class="fas fa-clock"></i>
                <span><strong>Hora:</strong> <?php echo $hora_reg; ?></span>
            </p>
        </div>
        <?php endif; ?>

        <div class="button-group">
            <a href="<?= htmlspecialchars($baseUrl . 'Insertar/insertar_cita.php'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Cita
            </a>
            <a href="<?= htmlspecialchars($baseUrl . 'menu.php'); ?>" class="btn btn-secondary">
                <i class="fas fa-home"></i> Volver al Menú
            </a>
        </div>

    </div>
</div>

</body>
</html>