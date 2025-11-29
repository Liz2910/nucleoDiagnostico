<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}
include("../conecta.php");

$id_cita = isset($_POST['id_cita']) ? intval($_POST['id_cita']) : 0;
$diagnostico = isset($_POST['diagnostico']) ? trim($_POST['diagnostico']) : '';

// Soportar tanto un solo medicamento como múltiples
$medicamentos_ids = [];
$medicamentos_horarios = [];

if (isset($_POST['medicamentos_ids']) && is_array($_POST['medicamentos_ids'])) {
    // Múltiples medicamentos desde doctor_menu.php
    $medicamentos_ids = array_map('intval', $_POST['medicamentos_ids']);
    $medicamentos_horarios = isset($_POST['medicamentos_horarios']) ? $_POST['medicamentos_horarios'] : [];
} elseif (isset($_POST['id_medicamento'])) {
    // Un solo medicamento desde generar_diagnostico.php
    $medicamentos_ids = [intval($_POST['id_medicamento'])];
    $medicamentos_horarios = [''];
}

// Filtrar medicamentos vacíos
$medicamentos_ids = array_filter($medicamentos_ids, fn($id) => $id > 0);

if ($id_cita <= 0 || empty($medicamentos_ids) || $diagnostico === '' || strlen($diagnostico) > 300) {
    header("Location: ../generar_diagnostico.php?id_cita={$id_cita}&error=validacion");
    exit();
}

// Obtener primer medicamento para la tabla consulta (mantener compatibilidad)
$primer_medicamento = reset($medicamentos_ids);

// Inserta la consulta
$qInsert = "INSERT INTO consulta (id_cita, diagnostico, id_medicamento) VALUES ($id_cita, $1, $primer_medicamento) RETURNING id_consulta";
$result = pg_query_params($conexion, $qInsert, [$diagnostico]);
$id_consulta = pg_fetch_result($result, 0, 'id_consulta');

// Si hay tabla consulta_recetas, insertar todos los medicamentos
$tablaRecetasExiste = pg_query($conexion, "SELECT 1 FROM information_schema.tables WHERE table_name = 'consulta_recetas'");
if ($tablaRecetasExiste && pg_num_rows($tablaRecetasExiste) > 0) {
    foreach ($medicamentos_ids as $idx => $med_id) {
        $instruccion = isset($medicamentos_horarios[$idx]) ? trim($medicamentos_horarios[$idx]) : '';
        pg_query_params($conexion, 
            "INSERT INTO consulta_recetas (id_consulta, id_medicamento, instrucciones) VALUES ($1, $2, $3)",
            [$id_consulta, $med_id, $instruccion]
        );
    }
}

// Obtiene datos para el PDF-like
$cita = pg_fetch_assoc(pg_query($conexion, "
SELECT c.id_cita, c.fecha, c.hora,
       p.codigo AS id_paciente, p.nombre AS paciente, p.direccion AS dir_pac, p.telefono AS tel_pac, p.edad, p.estatura,
       d.codigo AS id_doctor, d.nombre AS doctor, d.especialidad, d.telefono AS tel_doc
FROM citas c
JOIN paciente p ON p.codigo = c.id_paciente
JOIN doctor d   ON d.codigo = c.id_doctor
WHERE c.id_cita = $id_cita"));

// Obtener todos los medicamentos recetados
$meds = [];
foreach ($medicamentos_ids as $idx => $med_id) {
    $medData = pg_fetch_assoc(pg_query($conexion, "SELECT codigo, nombre FROM medicamento WHERE codigo = $med_id"));
    if ($medData) {
        $medData['instrucciones'] = isset($medicamentos_horarios[$idx]) ? trim($medicamentos_horarios[$idx]) : '';
        $meds[] = $medData;
    }
}

pg_close($conexion);

// Render imprimible - Receta Médica Profesional
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Receta Médica - Núcleo Diagnóstico</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
      font-family: 'Inter', 'Segoe UI', sans-serif;
      background: #e8e8e8;
      min-height: 100vh;
      padding: 30px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .prescription {
      width: 100%;
      max-width: 700px;
      background: #fff;
      box-shadow: 0 2px 20px rgba(0,0,0,0.1);
      position: relative;
      padding: 0;
    }

    /* Marca de agua sutil */
    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-25deg);
      font-size: 80px;
      color: rgba(0, 0, 0, 0.03);
      font-weight: 700;
      pointer-events: none;
      z-index: 0;
      white-space: nowrap;
      font-family: 'Crimson Text', serif;
      letter-spacing: 8px;
    }

    /* Header */
    .header {
      border-bottom: 3px double #333;
      padding: 25px 35px;
      text-align: center;
      position: relative;
      z-index: 1;
    }

    .header-top {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-bottom: 8px;
    }

    .logo-icon {
      font-size: 24px;
      color: #333;
    }

    .clinic-name {
      font-family: 'Crimson Text', serif;
      font-size: 1.6rem;
      font-weight: 700;
      color: #1a1a1a;
      letter-spacing: 3px;
      text-transform: uppercase;
    }

    .clinic-slogan {
      font-family: 'Crimson Text', serif;
      font-size: 0.85rem;
      color: #555;
      margin-top: 4px;
      letter-spacing: 1px;
    }

    .header-contact {
      margin-top: 12px;
      font-size: 0.8rem;
      color: #666;
    }

    /* Info de receta */
    .prescription-info {
      display: flex;
      justify-content: space-between;
      padding: 15px 35px;
      border-bottom: 1px solid #ddd;
      background: #fafafa;
      font-size: 0.85rem;
    }

    .info-group {
      display: flex;
      gap: 6px;
    }

    .info-label {
      color: #666;
    }

    .info-value {
      font-weight: 600;
      color: #333;
    }

    /* Cuerpo */
    .body {
      padding: 30px 35px;
      position: relative;
      z-index: 1;
      min-height: 400px;
    }

    /* Secciones */
    .section {
      margin-bottom: 25px;
    }

    .section-title {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: #888;
      margin-bottom: 8px;
      font-weight: 600;
    }

    /* Paciente */
    .patient-row {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      padding: 15px 0;
      border-bottom: 1px solid #eee;
    }

    .patient-name {
      font-size: 1.1rem;
      font-weight: 600;
      color: #1a1a1a;
      width: 100%;
    }

    .patient-detail {
      font-size: 0.8rem;
      color: #444;
    }

    .patient-detail span {
      color: #888;
      margin-right: 4px;
    }

    /* Doctor */
    .doctor-row {
      padding: 15px 0;
      border-bottom: 1px solid #eee;
    }

    .doctor-name {
      font-size: 0.95rem;
      font-weight: 600;
      color: #1a1a1a;
    }

    .doctor-specialty {
      font-size: 0.8rem;
      color: #666;
      font-style: italic;
      margin-top: 2px;
    }

    .doctor-contact {
      font-size: 0.8rem;
      color: #666;
      margin-top: 4px;
    }

    /* Diagnóstico */
    .diagnosis-section {
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #ddd;
    }

    .diagnosis-header {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: #888;
      margin-bottom: 10px;
      font-weight: 600;
    }

    .diagnosis-text {
      font-size: 0.9rem;
      line-height: 1.7;
      color: #333;
      padding: 12px;
      background: #f9f9f9;
      border-left: 3px solid #333;
      margin-bottom: 20px;
    }

    /* Medicamento */
    .medication-section {
      padding: 15px;
      border: 2px solid #333;
      margin-bottom: 15px;
    }

    .medication-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
    }

    .medication-header i {
      font-size: 1rem;
      color: #333;
    }

    .medication-label {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #666;
      font-weight: 600;
    }

    .medication-name {
      font-size: 1.15rem;
      font-weight: 700;
      color: #1a1a1a;
      font-family: 'Crimson Text', serif;
    }

    /* Firma */
    .signature-area {
      margin-top: 40px;
      display: flex;
      justify-content: flex-end;
    }

    .signature-box {
      text-align: center;
      min-width: 220px;
    }

    .signature-line {
      border-top: 1px solid #333;
      margin-bottom: 6px;
    }

    .signature-name {
      font-size: 0.85rem;
      font-weight: 600;
      color: #333;
    }

    .signature-title {
      font-size: 0.75rem;
      color: #666;
      font-style: italic;
    }

    /* Footer */
    .footer {
      border-top: 3px double #333;
      padding: 15px 35px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.75rem;
      color: #888;
    }

    .footer-left {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    /* Botones (solo pantalla) */
    .actions {
      margin-top: 25px;
      display: flex;
      gap: 15px;
      justify-content: center;
    }

    .btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      border: none;
      border-radius: 6px;
      font-size: 0.95rem;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    .btn-print {
      background: #333;
      color: #fff;
    }

    .btn-print:hover {
      background: #555;
    }

    .btn-back {
      background: #e0e0e0;
      color: #333;
    }

    .btn-back:hover {
      background: #d0d0d0;
    }

    /* Print */
    @media print {
      body {
        background: #fff;
        padding: 0;
      }
      
      .prescription {
        box-shadow: none;
        max-width: 100%;
      }
      
      .actions {
        display: none !important;
      }

      .body {
        min-height: auto;
      }
    }

    @media (max-width: 600px) {
      .header, .body, .footer, .prescription-info {
        padding-left: 20px;
        padding-right: 20px;
      }
      
      .prescription-info {
        flex-direction: column;
        gap: 8px;
      }
      
      .actions {
        flex-direction: column;
        padding: 0 20px;
      }
      
      .btn {
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="prescription">
    <!-- Marca de agua -->
    <div class="watermark">NÚCLEO DX</div>

    <!-- Header -->
    <div class="header">
      <div class="header-top">
        <i class="fas fa-staff-snake logo-icon"></i>
        <span class="clinic-name">Núcleo Diagnóstico</span>
      </div>
      <div class="clinic-slogan">La salud lo es todo</div>
      <div class="header-contact">
        Consultorio Médico Especializado
      </div>
    </div>

    <!-- Info de receta -->
    <div class="prescription-info">
      <div class="info-group">
        <span class="info-label">Receta No.</span>
        <span class="info-value"><?= str_pad($cita['id_cita'], 6, '0', STR_PAD_LEFT) ?></span>
      </div>
      <div class="info-group">
        <span class="info-label">Fecha:</span>
        <span class="info-value"><?= date('d/m/Y', strtotime($cita['fecha'])) ?></span>
      </div>
      <div class="info-group">
        <span class="info-label">Hora:</span>
        <span class="info-value"><?= htmlspecialchars(substr($cita['hora'],0,5)) ?></span>
      </div>
    </div>

    <!-- Cuerpo -->
    <div class="body">
      <!-- Paciente -->
      <div class="section">
        <div class="section-title">Paciente</div>
        <div class="patient-row">
          <div class="patient-name"><?= htmlspecialchars($cita['paciente']) ?></div>
          <div class="patient-detail"><span>Edad:</span> <?= intval($cita['edad']) ?> años</div>
          <div class="patient-detail"><span>Estatura:</span> <?= number_format((float)$cita['estatura'], 2) ?> m</div>
          <div class="patient-detail"><span>Tel:</span> <?= htmlspecialchars($cita['tel_pac']) ?></div>
          <div class="patient-detail"><span>Dirección:</span> <?= htmlspecialchars($cita['dir_pac']) ?></div>
        </div>
      </div>

      <!-- Doctor -->
      <div class="section">
        <div class="section-title">Médico</div>
        <div class="doctor-row">
          <div class="doctor-name">Dr(a). <?= htmlspecialchars($cita['doctor']) ?></div>
          <div class="doctor-specialty"><?= htmlspecialchars($cita['especialidad']) ?></div>
          <div class="doctor-contact">Tel: <?= htmlspecialchars($cita['tel_doc']) ?></div>
        </div>
      </div>

      <!-- Diagnóstico -->
      <div class="diagnosis-section">
        <div class="diagnosis-header">Diagnóstico</div>
        <div class="diagnosis-text"><?= nl2br(htmlspecialchars($diagnostico)) ?></div>

        <!-- Medicamentos -->
        <?php foreach ($meds as $med): ?>
        <div class="medication-section">
          <div class="medication-header">
            <i class="fas fa-pills"></i>
            <span class="medication-label">Medicamento Recetado</span>
          </div>
          <div class="medication-name"><?= htmlspecialchars($med['nombre']) ?></div>
          <?php if (!empty($med['instrucciones'])): ?>
          <div class="medication-instructions" style="font-size: 0.85rem; color: #555; margin-top: 6px; padding-left: 8px; border-left: 2px solid #ddd;">
            <strong>Indicaciones:</strong> <?= htmlspecialchars($med['instrucciones']) ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Firma -->
      <div class="signature-area">
        <div class="signature-box">
          <div class="signature-line"></div>
          <div class="signature-name">Dr(a). <?= htmlspecialchars($cita['doctor']) ?></div>
          <div class="signature-title"><?= htmlspecialchars($cita['especialidad']) ?></div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer">
      <div class="footer-left">
        <i class="fas fa-shield-alt"></i>
        Documento médico confidencial
      </div>
      <div><?= date('d/m/Y H:i') ?></div>
    </div>
  </div>

  <!-- Botones -->
  <div class="actions">
    <button class="btn btn-print" onclick="window.print()">
      <i class="fas fa-print"></i> Imprimir / Guardar PDF
    </button>
    <a href="../doctor_menu.php<?= isset($_POST['doctor_codigo']) ? '?doctor=' . intval($_POST['doctor_codigo']) : '' ?>" class="btn btn-back">
      <i class="fas fa-arrow-left"></i> Volver al menú
    </a>
  </div>
</body>
</html>
