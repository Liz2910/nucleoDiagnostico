<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}
include("../conecta.php");

$id_cita = isset($_POST['id_cita']) ? intval($_POST['id_cita']) : 0;
$diagnostico = isset($_POST['diagnostico']) ? trim($_POST['diagnostico']) : '';
$id_medicamento = isset($_POST['id_medicamento']) ? intval($_POST['id_medicamento']) : 0;

if ($id_cita <= 0 || $id_medicamento <= 0 || $diagnostico === '' || strlen($diagnostico) > 300) {
    header("Location: ../generar_diagnostico.php?id_cita={$id_cita}&error=validacion");
    exit();
}

// Inserta la consulta
$qInsert = "INSERT INTO consulta (id_cita, diagnostico, id_medicamento) VALUES ($id_cita, $1, $id_medicamento)";
pg_query_params($conexion, $qInsert, [$diagnostico]);

// Obtiene datos para el PDF-like
$cita = pg_fetch_assoc(pg_query($conexion, "
SELECT c.id_cita, c.fecha, c.hora,
       p.codigo AS id_paciente, p.nombre AS paciente, p.direccion AS dir_pac, p.telefono AS tel_pac, p.edad, p.estatura,
       d.codigo AS id_doctor, d.nombre AS doctor, d.especialidad, d.telefono AS tel_doc
FROM citas c
JOIN paciente p ON p.codigo = c.id_paciente
JOIN doctor d   ON d.codigo = c.id_doctor
WHERE c.id_cita = $id_cita"));

$med = pg_fetch_assoc(pg_query($conexion, "SELECT codigo, nombre FROM medicamento WHERE codigo = $id_medicamento"));

pg_close($conexion);

// Render imprimible
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Diagnóstico - La salud es primero</title>
  <link rel="stylesheet" href="../Styles/resultado.css">
  <link rel="stylesheet" href="../Styles/form.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Estilos para formato impresión */
    @media print {
      .btn, .button-group { display: none !important; }
      .result-card, .form-card { box-shadow: none !important; }
    }
    .brand-header {
      display:flex; align-items:center; gap:12px; justify-content:center; margin-bottom: 10px;
    }
    .brand-logo {
      width:60px; height:60px; border-radius:50%; background:#4a6cf7; display:flex; align-items:center; justify-content:center; color:#fff;
      box-shadow: 0 4px 12px rgba(0,0,0,.2);
    }
    .signature-box {
      border-top: 1px solid #ccc; margin-top: 50px; padding-top: 30px; text-align: center; color:#555;
    }
  </style>
</head>
<body class="theme-consultas">
  <div class="container result-container">
    <div class="form-card result-card">
      <div class="brand-header">
        <div class="brand-logo"><i class="fas fa-hospital-user"></i></div>
        <div>
          <h2 class="form-title" style="margin:0;">La salud es primero</h2>
          <p class="subtitle-purple" style="margin:0;">Núcleo de Diagnóstico</p>
        </div>
      </div>

      <div class="info-box">
        <p><i class="fas fa-hashtag"></i><strong>ID Cita:</strong> <?= $cita['id_cita'] ?></p>
        <p><i class="fas fa-calendar"></i><strong>Fecha/Hora:</strong> <?= htmlspecialchars($cita['fecha']) ?> <?= htmlspecialchars(substr($cita['hora'],0,5)) ?></p>
      </div>

      <div class="success-details">
        <p><i class="fas fa-user"></i><strong>Paciente:</strong> <?= htmlspecialchars($cita['paciente']) ?> — Tel: <?= htmlspecialchars($cita['tel_pac']) ?></p>
        <p><i class="fas fa-map-marker-alt"></i><strong>Dirección:</strong> <?= htmlspecialchars($cita['dir_pac']) ?></p>
        <p><i class="fas fa-user-clock"></i><strong>Edad/Estatura:</strong> <?= intval($cita['edad']) ?> años, <?= number_format((float)$cita['estatura'], 2) ?> m</p>
      </div>

      <div class="info-box">
        <p><i class="fas fa-user-md"></i><strong>Doctor:</strong> <?= htmlspecialchars($cita['doctor']) ?></p>
        <p><i class="fas fa-stethoscope"></i><strong>Especialidad:</strong> <?= htmlspecialchars($cita['especialidad']) ?></p>
        <p><i class="fas fa-phone"></i><strong>Tel:</strong> <?= htmlspecialchars($cita['tel_doc']) ?></p>
      </div>

      <div class="error-detail" style="background: #f8f9fa; border-left-color:#4a6cf7;">
        <strong><i class="fas fa-notes-medical"></i> Diagnóstico</strong>
        <p style="color:#333;"><?= nl2br(htmlspecialchars($diagnostico)) ?></p>
      </div>

      <div class="success-details">
        <p><i class="fas fa-pills"></i><strong>Medicamento recetado:</strong> <?= htmlspecialchars($med['nombre']) ?></p>
      </div>

      <div class="signature-box">
        <div style="height:80px;"></div>
        <p>Firma del doctor</p>
      </div>

      <div class="button-group">
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-file-pdf"></i> Imprimir/Guardar PDF</button>
        <a href="../consultas_proximas.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
      </div>
    </div>
  </div>
</body>
</html>
