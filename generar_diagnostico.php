<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: index.php");
    exit();
}
include("conecta.php");

$id_cita = isset($_GET['id_cita']) ? intval($_GET['id_cita']) : 0;
if ($id_cita <= 0) {
  header("Location: consultas_proximas.php?error=idcita");
  exit();
}

$cita = pg_fetch_assoc(pg_query($conexion, "
SELECT c.id_cita, c.fecha, c.hora, p.codigo AS id_paciente, p.nombre AS paciente,
       d.codigo AS id_doctor, d.nombre AS doctor, d.especialidad
FROM citas c
JOIN paciente p ON p.codigo = c.id_paciente
JOIN doctor d   ON d.codigo = c.id_doctor
WHERE c.id_cita = $id_cita"));

if (!$cita) {
  header("Location: consultas_proximas.php?error=nocita");
  exit();
}

$meds = pg_query($conexion, "SELECT codigo, nombre FROM medicamento ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Generar diagnóstico</title>
  <link rel="stylesheet" href="Styles/form.css">
  <link rel="stylesheet" href="Styles/resultado.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="theme-consultas">
  <div class="container">
    <div class="form-card">
      <div class="header-icon"><i class="fas fa-file-medical"></i></div>
      <h2>Generar diagnóstico</h2>
      <p class="subtitle">Complete el diagnóstico y seleccione el medicamento.</p>

      <div class="info-box">
        <p><i class="fas fa-hashtag"></i><strong>ID Cita:</strong> <?= $cita['id_cita'] ?></p>
        <p><i class="fas fa-user"></i><strong>Paciente:</strong> <?= htmlspecialchars($cita['paciente']) ?></p>
        <p><i class="fas fa-user-md"></i><strong>Doctor:</strong> <?= htmlspecialchars($cita['doctor']) ?> (<?= htmlspecialchars($cita['especialidad']) ?>)</p>
        <p><i class="fas fa-calendar"></i><strong>Fecha/Hora:</strong> <?= htmlspecialchars($cita['fecha']) ?> <?= htmlspecialchars(substr($cita['hora'],0,5)) ?></p>
      </div>

      <form action="Actions/generar_diagnostico_action.php" method="post">
        <input type="hidden" name="id_cita" value="<?= $cita['id_cita'] ?>">

        <div class="form-grid">
          <div class="form-group full-width">
            <label class="form-label"><i class="fas fa-notes-medical"></i> Diagnóstico <span class="required">*</span></label>
            <div class="input-wrapper">
              <textarea class="form-control" name="diagnostico" maxlength="300" rows="4" placeholder="Describa el diagnóstico (máx. 300 caracteres)" required></textarea>
            </div>
            <div class="form-help"><i class="fas fa-info-circle"></i> Máximo 300 caracteres como en la tabla consulta.</div>
          </div>

          <div class="form-group full-width">
            <label class="form-label"><i class="fas fa-pills"></i> Medicamento <span class="required">*</span></label>
            <div class="input-wrapper">
              <select class="form-control" name="id_medicamento" required>
                <option value="">Seleccione medicamento</option>
                <?php while ($m = pg_fetch_assoc($meds)): ?>
                  <option value="<?= intval($m['codigo']) ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="form-help"><i class="fas fa-info-circle"></i> El medicamento se guarda asociado a la consulta.</div>
          </div>
        </div>

        <div class="button-group">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar y generar PDF</button>
          <a href="consultas_proximas.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
