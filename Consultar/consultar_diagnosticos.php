<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['empleado', 'admin'], true)) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$tablaExiste = false;
$tablaCheck = pg_query($conexion, "SELECT to_regclass('public.consulta') AS tabla;");
if ($tablaCheck) {
    $info = pg_fetch_assoc($tablaCheck);
    $tablaExiste = !empty($info['tabla']);
}

$tablaRecetasExiste = false;
$recetasCheck = pg_query($conexion, "SELECT to_regclass('public.consulta_recetas') AS tabla;");
if ($recetasCheck && pg_num_rows($recetasCheck) > 0) {
    $tablaRecetasExiste = !empty(pg_fetch_result($recetasCheck, 0, 'tabla'));
}
$lateralMedicamentos = $tablaRecetasExiste
    ? "LEFT JOIN LATERAL (
            SELECT string_agg(m.nombre || COALESCE(' (' || NULLIF(cr.instrucciones,'') || ')',''), ', ') AS listado
            FROM consulta_recetas cr
            INNER JOIN medicamento m ON m.codigo = cr.id_medicamento
            WHERE cr.id_consulta = con.id_consulta
       ) meds ON true"
    : "LEFT JOIN LATERAL (SELECT 'Sin medicamentos' AS listado) meds ON true";

if ($tablaExiste) {
    $result = pg_query($conexion, "
        SELECT con.id_consulta,
               con.diagnostico,
               p.nombre AS paciente_nombre,
               doc.nombre AS doctor_nombre,
               c.fecha,
               c.hora,
               COALESCE(meds.listado, 'Sin medicamentos') AS medicamentos_detalle
        FROM consulta con
        INNER JOIN citas c ON c.id_cita = con.id_cita
        INNER JOIN paciente p ON p.codigo = c.id_paciente
        INNER JOIN doctor doc ON doc.codigo = c.id_doctor
        $lateralMedicamentos
        ORDER BY con.id_consulta DESC
    ");
    $diagnosticos = $result ? pg_fetch_all($result) : [];
} else {
    $diagnosticos = [];
}
pg_close($conexion);

$volver = $_SESSION['rol'] === 'admin' ? '../menu.php' : '../empleado_portal.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnósticos - La salud es primero</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Styles/consultas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .diagnosticos-wrapper {max-width:1100px;margin:0 auto;}
        table {width:100%;border-collapse:collapse;}
        th,td {padding:12px 14px;text-align:left;}
        .actions a {display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;text-decoration:none;font-weight:600;}
        .pdf-link {background:#ff4f81;color:#fff;}
        .back-link {margin-top:20px;display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;text-decoration:none;background:#ff7aa8;color:#fff;font-weight:600;}
        @media(max-width:720px){table,thead,tbody,tr,td,th{display:block;} th{position:sticky;top:0;} td{border-bottom:1px solid #f8cfe0;}}
    </style>
</head>
<body class="theme-consultas">
<div class="container diagnosticos-wrapper">
    <h1>Consultas registradas</h1>
    <?php if (!$tablaExiste): ?>
        <p>La tabla de consultas aún no existe. Ejecute la migración correspondiente.</p>
    <?php elseif ($diagnosticos): ?>
        <table>
            <thead>
            <tr>
                <th>Paciente</th>
                <th>Doctor</th>
                <th>Medicamentos recetados</th>
                <th>Fecha cita</th>
                <th>Diagnóstico</th>
                <th>PDF</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($diagnosticos as $diag): ?>
                <tr>
                    <td><?= htmlspecialchars($diag['paciente_nombre']); ?></td>
                    <td><?= htmlspecialchars($diag['doctor_nombre']); ?></td>
                    <td><?= htmlspecialchars($diag['medicamentos_detalle']); ?></td>
                    <td><?= date('d/m/Y', strtotime($diag['fecha'])) . ' · ' . substr($diag['hora'], 0, 5); ?></td>
                    <td><?= htmlspecialchars($diag['diagnostico']); ?></td>
                    <td class="actions">
                        <a class="pdf-link" href="../Recetas/consulta_<?= $diag['id_consulta']; ?>.pdf" target="_blank" rel="noopener">
                            <i class="fas fa-file-pdf"></i> Ver PDF
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No se han generado diagnósticos.</p>
    <?php endif; ?>

    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px;">
        <a class="pdf-link" href="../doctor_menu.php">
            <i class="fas fa-notes-medical"></i> Generar nueva consulta
        </a>
        <a class="back-link" href="<?= htmlspecialchars($volver); ?>">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
</body>
</html>
