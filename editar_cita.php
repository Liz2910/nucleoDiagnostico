<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: index.php");
    exit();
}

include("conecta.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: Consultar/consultar_citas.php?error=idinvalido");
    exit();
}

// Obtener cita
$query = "SELECT * FROM citas WHERE id_cita = $id";
$result = pg_query($conexion, $query);
$cita = pg_fetch_assoc($result);

if (!$cita) {
    header("Location: Consultar/consultar_citas.php?error=noexiste");
    exit();
}

date_default_timezone_set('America/Mexico_City');

$horaCruda = $cita['hora'];
$horaCita  = substr($horaCruda,0,5);
$inicioTimestamp = strtotime($cita['fecha'].' '.$horaCita.':00');
$finTimestamp    = $inicioTimestamp + 3600;

$terminada = time() >= $finTimestamp;

if ($terminada) {
  ?>
  <!DOCTYPE html>
  <html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita pasada</title>
    <link rel="stylesheet" href="Styles/resultado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  </head>
  <body class="theme-citas">
    <div class="result-container">
      <div class="result-card">
        <div class="warning-icon"><i class="fas fa-exclamation"></i></div>
        <h2 class="result-title">Esta cita ya terminó</h2>
        <p class="result-message">No se puede editar porque ya ha concluido.</p>
        <div class="button-group">
          <a href="Consultar/consultar_citas.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Volver
          </a>
        </div>
      </div>
    </div>
  </body>
  </html>
  <?php
  exit();
}

// Obtener nombre de paciente
$pac = pg_fetch_assoc(pg_query($conexion,
    "SELECT nombre FROM paciente WHERE codigo={$cita['id_paciente']}"));

// Obtener nombre del doctor
$doc = pg_fetch_assoc(pg_query($conexion,
    "SELECT nombre FROM doctor WHERE codigo={$cita['id_doctor']}"));

// Obtener listas completas
$pacientes = pg_query($conexion, "SELECT codigo, nombre FROM paciente ORDER BY nombre ASC");
$doctores = pg_query($conexion, "SELECT codigo, nombre FROM doctor ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Cita Médica</title>

    <link rel="stylesheet" href="Styles/form.css">
    <link rel="stylesheet" href="Styles/resultado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="theme-citas">

<div class="container">

    <div class="form-card">

        <div class="purple-header-circle">
            <i class="fas fa-edit"></i>
        </div>

        <h2 class="form-title">Modificar Cita Médica</h2>
        <p class="subtitle-purple">Actualice la información de la cita</p>

        <div class="info-box-purple">
            <p><i class="fas fa-hashtag"></i> <strong>ID Cita:</strong> <?= $cita['id_cita'] ?></p>
            <p><i class="fas fa-user"></i> <strong>Paciente actual:</strong> <?= $pac['nombre'] ?></p>
            <p><i class="fas fa-user-md"></i> <strong>Doctor actual:</strong> <?= $doc['nombre'] ?></p>
        </div>

        <form action="Actions/editar_cita_action.php" method="POST">

            <input type="hidden" name="id_cita" value="<?= $cita['id_cita'] ?>">

            <div class="form-grid">

                <!-- PACIENTE -->
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Paciente</label>
                    <div class="input-wrapper">
                        <select class="form-control" name="id_paciente" required>
                            <option value="">Seleccione un paciente</option>
                            <?php while ($p = pg_fetch_assoc($pacientes)): ?>
                                <option value="<?= $p['codigo'] ?>" 
                                <?= ($p['codigo']==$cita['id_paciente']) ? 'selected' : '' ?>>
                                    <?= $p['nombre'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- DOCTOR -->
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user-md"></i> Doctor</label>
                    <div class="input-wrapper">
                        <select class="form-control" name="id_doctor" required>
                            <option value="">Seleccione un doctor</option>
                            <?php while ($d = pg_fetch_assoc($doctores)): ?>
                                <option value="<?= $d['codigo'] ?>"
                                <?= ($d['codigo']==$cita['id_doctor']) ? 'selected' : '' ?>>
                                    <?= $d['nombre'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- FECHA -->
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar"></i> Nueva Fecha</label>
                    <div class="input-wrapper">
                        <input type="date" class="form-control" name="fecha" value="<?= $cita['fecha'] ?>" required>
                        <div class="form-help">
                            <i class="fas fa-info-circle"></i> Seleccione nueva fecha
                        </div>
                    </div>
                </div>

                <!-- HORA -->
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-clock"></i> Nueva Hora</label>
                    <div class="input-wrapper">
                        <input type="time" class="form-control" name="hora" value="<?= substr($cita['hora'],0,5) ?>" required>
                        <div class="form-help"><i class="fas fa-info-circle"></i> Cada cita dura 1 hora</div>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>

                <a href="Consultar/consultar_citas.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>

        </form>

    </div>
</div>

</body>
</html>