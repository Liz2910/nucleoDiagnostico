<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['doctor', 'admin'], true)) {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: doctor_menu.php");
    exit();
}

include("conecta.php");

$registro = pg_fetch_assoc(pg_query_params(
    $conexion,
    "SELECT con.id_consulta,
            con.diagnostico,
            med.nombre AS medicamento_nombre,
            p.nombre AS paciente_nombre,
            d.nombre AS doctor_nombre,
            d.especialidad,
            c.fecha,
            c.hora
     FROM consulta con
     INNER JOIN citas c ON c.id_cita = con.id_cita
     INNER JOIN paciente p ON p.codigo = c.id_paciente
     INNER JOIN doctor d ON d.codigo = c.id_doctor
     INNER JOIN medicamento med ON med.codigo = con.id_medicamento
     WHERE con.id_consulta = $1
     LIMIT 1",
    [$id]
));

$medicamentos = pg_fetch_all(pg_query_params(
    $conexion,
    "SELECT m.nombre, COALESCE(cr.instrucciones, '') AS instrucciones
     FROM consulta_recetas cr
     INNER JOIN medicamento m ON m.codigo = cr.id_medicamento
     WHERE cr.id_consulta = $1",
    [$id]
)) ?: [];

pg_close($conexion);

if (!$registro) {
    header("Location: doctor_menu.php");
    exit();
}

$pdfPath = 'Recetas/consulta_' . $registro['id_consulta'] . '.pdf';
$volver = 'doctor_menu.php';
if ($_SESSION['rol'] === 'admin') {
    $volver = 'menu.php';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico generado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Styles/resultado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="theme-doctores">
<div class="result-container">
    <div class="result-card">
        <div class="success-icon"><i class="fas fa-file-prescription"></i></div>
        <h2 class="result-title">Diagnóstico listo</h2>
        <p class="result-message">El archivo PDF se generó correctamente para <?= htmlspecialchars($registro['paciente_nombre']); ?>.</p>

        <div class="success-details">
            <p><i class="fas fa-user"></i><strong>Paciente:</strong> <?= htmlspecialchars($registro['paciente_nombre']); ?></p>
            <p><i class="fas fa-user-md"></i><strong>Doctor:</strong> <?= htmlspecialchars($registro['doctor_nombre']); ?> (<?= htmlspecialchars($registro['especialidad']); ?>)</p>
            <p><i class="fas fa-prescription-bottle-medical"></i><strong>Medicamentos:</strong></p>
            <ul class="med-list">
                <?php foreach ($medicamentos as $med): ?>
                    <li><?= htmlspecialchars($med['nombre']); ?> — <?= htmlspecialchars($med['instrucciones']); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><i class="fas fa-calendar-day"></i><strong>Cita:</strong> <?= date('d/m/Y', strtotime($registro['fecha'])); ?> · <?= substr($registro['hora'], 0, 5); ?></p>
        </div>

        <div class="button-group">
            <a class="btn btn-primary" href="<?= htmlspecialchars($pdfPath); ?>" target="_blank" rel="noopener">
                <i class="fas fa-file-pdf"></i> Ver PDF
            </a>
            <a class="btn btn-secondary" href="<?= $volver; ?>">
                <i class="fas fa-arrow-left"></i> Volver al menú
            </a>
        </div>
    </div>
</div>
</body>
</html>
