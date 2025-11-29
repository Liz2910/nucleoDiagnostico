<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

// Obtener doctor y fecha seleccionados
$doctor = isset($_GET['doctor']) ? intval($_GET['doctor']) : 0;
$fecha  = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Obtener lista de doctores
$doctores = pg_query($conexion, "SELECT codigo, nombre FROM doctor ORDER BY nombre ASC");

// Obtener citas ocupadas del doctor seleccionado
$horas_ocupadas = [];

if ($doctor > 0 && $fecha != "") {
    $query = "
        SELECT hora FROM citas 
        WHERE id_doctor = $doctor 
          AND fecha = '$fecha'
    ";
    $result = pg_query($conexion, $query);

    while ($row = pg_fetch_assoc($result)) {
        $horas_ocupadas[] = substr($row['hora'], 0, 5);
    }
}

// Horarios disponibles (8 AM – 8 PM)
$horarios = [];
for ($h = 8; $h <= 20; $h++) {
    $horarios[] = sprintf("%02d:00", $h);
}

// Obtener lista de pacientes
$pacientes = pg_query($conexion, "SELECT codigo, nombre FROM paciente ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Disponibilidad de Citas</title>

    <link rel="stylesheet" href="../Styles/dispo.css">
    <link rel="stylesheet" 
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>

<div class="container">

    <h1><i class="fas fa-sitemap"></i> Disponibilidad de Citas</h1>
    <p class="description">Consulta horarios disponibles y agenda directamente.</p>

    <!-- FORM SELECCIÓN DOCTOR Y FECHA -->
    <form method="GET" class="filters-card">
        <div>
            <label>Doctor:</label>
            <select name="doctor" class="select-field" required>
                <option value="">Seleccione un doctor</option>
                <?php 
                pg_result_seek($doctores, 0); // Resetear puntero
                while ($d = pg_fetch_assoc($doctores)): 
                ?>
                    <option value="<?php echo $d['codigo']; ?>"
                        <?php echo ($doctor == $d['codigo']) ? "selected" : ""; ?>>
                        <?php echo $d['nombre']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label>Fecha:</label>
            <input type="date" name="fecha" class="date-field" value="<?php echo $fecha; ?>" required>
        </div>

        <div>
            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </form>

    <?php if ($doctor > 0): ?>
    
    <!-- SELECCIÓN DE PACIENTE -->
    <div class="patient-selector">
        <label><i class="fas fa-user"></i> Seleccione el paciente:</label>
        <select id="pacienteSeleccionado" class="patient-select" required>
            <option value="">-- Seleccione un paciente --</option>
            <?php while ($p = pg_fetch_assoc($pacientes)): ?>
                <option value="<?php echo $p['codigo']; ?>"><?php echo $p['nombre']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="slots-card">
        <div class="slots-header">
            <h3>Horarios Disponibles</h3>
        </div>

        <div class="slots-grid">

            <?php foreach ($horarios as $h): ?>

                <?php if (in_array($h, $horas_ocupadas)): ?>
                    <div class="slot slot-busy">
                        <?php echo $h; ?>
                        <span>Ocupado</span>
                    </div>

                <?php else: ?>
                    <form action="../Actions/insertar_cita_action.php" method="POST" class="slot-form" onsubmit="return validarPaciente()">
                        <input type="hidden" name="desde_disponibilidad" value="1">
                        <input type="hidden" name="id_doctor" value="<?php echo $doctor; ?>">
                        <input type="hidden" name="fecha" value="<?php echo $fecha; ?>">
                        <input type="hidden" name="hora" value="<?php echo $h; ?>">
                        <input type="hidden" name="id_paciente" class="paciente-hidden">

                        <button type="submit" class="slot slot-free">
                            <strong><?php echo $h; ?></strong>
                            <span>Disponible</span>
                            <i class="fas fa-calendar-check"></i>
                        </button>
                    </form>
                <?php endif; ?>

            <?php endforeach; ?>

        </div>
    </div>
    <?php endif; ?>

    <div class="back-container">
        <a href="../menu.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Volver al Menú
        </a>
    </div>

</div>

<script>
// Validar que se haya seleccionado un paciente
function validarPaciente() {
    const pacienteSelect = document.getElementById('pacienteSeleccionado');
    if (!pacienteSelect.value) {
        alert('Por favor, seleccione un paciente antes de agendar la cita.');
        pacienteSelect.focus();
        return false;
    }
    return true;
}

// Sincronizar el paciente seleccionado con todos los formularios
document.addEventListener('DOMContentLoaded', function() {
    const pacienteSelect = document.getElementById('pacienteSeleccionado');
    
    if (pacienteSelect) {
        pacienteSelect.addEventListener('change', function() {
            // Actualizar todos los inputs hidden de paciente
            const hiddenInputs = document.querySelectorAll('.paciente-hidden');
            hiddenInputs.forEach(input => {
                input.value = this.value;
            });
        });
    }
});
</script>

</body>
</html>