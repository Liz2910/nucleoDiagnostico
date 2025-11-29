<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['doctor', 'admin'], true)) {
    header("Location: index.php");
    exit();
}

$isAdmin = $_SESSION['rol'] === 'admin';
$doctorCodigo = $isAdmin ? intval($_GET['doctor'] ?? 0) : intval($_SESSION['doctor_codigo'] ?? $_SESSION['codigo']);

include("conecta.php");

$doctorListado = pg_fetch_all(pg_query($conexion, "SELECT codigo, nombre FROM doctor ORDER BY nombre ASC")) ?: [];

if ($isAdmin && $doctorCodigo <= 0) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Seleccionar doctor - La salud es primero</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="Styles/doctor_menu.css">
    </head>
    <body class="doctor-portal">
    <div class="doctor-picker-card">
        <i class="fas fa-user-md"></i>
        <h1>Selecciona un doctor para gestionar sus consultas</h1>
        <form method="get">
            <select name="doctor" required>
                <option value="">Elige un doctor</option>
                <?php foreach ($doctorListado as $doc): ?>
                    <option value="<?= $doc['codigo']; ?>"><?= htmlspecialchars($doc['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><i class="fas fa-arrow-right"></i> Ir al panel</button>
        </form>
    </div>
    </body>
    </html>
    <?php
    exit();
}

$doctorData = pg_fetch_assoc(pg_query_params(
    $conexion,
    "SELECT nombre, COALESCE(especialidad, 'General') AS especialidad FROM doctor WHERE codigo = $1 LIMIT 1",
    [$doctorCodigo]
));

if (!$doctorData) {
    header("Location: doctor_menu.php");
    exit();
}

$doctorNombre = $doctorData['nombre'];
$doctorEspecialidad = $doctorData['especialidad'] ?? 'General';
$doctorPrimerNombre = explode(' ', trim($doctorNombre))[0];
$doctorPrimerNombre = $isAdmin ? $doctorPrimerNombre : $doctorPrimerNombre;

$upcomingQuery = pg_query_params(
    $conexion,
    "SELECT c.id_cita,
           c.fecha,
           c.hora,
           p.codigo AS paciente_codigo,
           p.nombre AS paciente_nombre
     FROM citas c
     INNER JOIN paciente p ON p.codigo = c.id_paciente
     WHERE c.id_doctor = $1
       AND c.fecha >= CURRENT_DATE
     ORDER BY c.fecha ASC, c.hora ASC
     LIMIT 4",
    [$doctorCodigo]
);

$proximasCitas = pg_fetch_all($upcomingQuery) ?: [];

$proximaCita = $proximasCitas[0] ?? null;

$pacienteDetalle = null;
if ($proximaCita) {
    $pacienteDetalle = pg_fetch_assoc(pg_query_params(
        $conexion,
        "SELECT codigo, nombre, identificacion, telefono, correo, direccion
         FROM paciente WHERE codigo = $1 LIMIT 1",
        [$proximaCita['paciente_codigo']]
    ));
}

$medicamentos = pg_fetch_all(pg_query(
    $conexion,
    "SELECT codigo, nombre FROM medicamento ORDER BY nombre ASC"
)) ?: [];
$medicamentosJson = htmlspecialchars(json_encode($medicamentos, JSON_UNESCAPED_UNICODE), ENT_QUOTES);
$medOptions = '';entos as $med) {
foreach ($medicamentos as $med) {="' . htmlspecialchars($med['codigo']) . '">' . htmlspecialchars($med['nombre']) . '</option>';
    $medOptions .= '<option value="' . htmlspecialchars($med['codigo']) . '">' . htmlspecialchars($med['nombre']) . '</option>';
}medicamentosJson = htmlspecialchars(json_encode($medicamentos, JSON_UNESCAPED_UNICODE), ENT_QUOTES);

$consultas = pg_fetch_all(pg_query_params(
    $conexion,
    "SELECT con.id_consulta,
            con.diagnostico,
            p.nombre AS paciente_nombre,
            c.fecha,
            c.hora,
            COALESCE(meds.listado, 'Sin medicamentos') AS medicamentos_detalle
     FROM consulta con
     INNER JOIN citas c ON c.id_cita = con.id_cita
     INNER JOIN paciente p ON p.codigo = c.id_paciente
     LEFT JOIN LATERAL (
        SELECT string_agg(m.nombre || COALESCE(' · ' || NULLIF(cr.instrucciones, ''), ''), E'\n') AS listado
        FROM consulta_recetas cr
        INNER JOIN medicamento m ON m.codigo = cr.id_medicamento
        WHERE cr.id_consulta = con.id_consulta
     ) meds ON true
     WHERE c.id_doctor = $1
     ORDER BY con.id_consulta DESC
     LIMIT 6",
    [$doctorCodigo]
)) ?: [];

pg_close($conexion);

function formatFecha(string $fecha): string
{
    $dt = DateTime::createFromFormat('Y-m-d', $fecha);
    return $dt ? $dt->format('d/m/Y') : $fecha;
}

function formatHora(string $hora): string
{
    return substr($hora, 0, 5);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Doctor - La salud es primero</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Styles/doctor_menu.css">
</head>
<body class="doctor-portal">
<div class="doctor-container">
    <header class="doctor-header">
        <div class="branding">
            <div class="brand-icon">
                <i class="fas fa-hospital-user"></i>
            </div>
            <div>
                <p class="tagline">La salud es primero</p>
                <h1>Bienvenido, Dr. <?= htmlspecialchars($doctorPrimerNombre) ?></h1>
                <p class="subline"><?= htmlspecialchars($doctorEspecialidad) ?></p>
            </div>
        </div>
        <div class="header-actions">
            <span class="doctor-code"><i class="fas fa-id-badge"></i> Código <?= htmlspecialchars($doctorCodigo) ?></span>
            <a href="logout.php" class="header-link">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </header>

    <section class="cards-grid">
        <article class="panel upcoming-panel">
            <div class="panel-title">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h2>Próximas citas</h2>
            <?php if ($proximaCita): ?>
                <div class="next-appointment">
                    <strong><?= htmlspecialchars($proximaCita['paciente_nombre']) ?></strong>
                    <p><i class="fas fa-calendar-alt"></i> <?= formatFecha($proximaCita['fecha']); ?></p>
                    <p><i class="fas fa-clock"></i> <?= formatHora($proximaCita['hora']); ?></p>
                    <p><i class="fas fa-hashtag"></i> Cita #<?= htmlspecialchars($proximaCita['id_cita']); ?></p>
                </div>
            <?php else: ?>
                <p class="empty-state">No hay citas agendadas.</p>
            <?php endif; ?>

            <?php if (count($proximasCitas) > 1): ?>
                <ul class="appointments-list">
                    <?php foreach ($proximasCitas as $index => $cita): if ($index === 0) continue; ?>
                        <li>
                            <span><?= formatFecha($cita['fecha']); ?> · <?= formatHora($cita['hora']); ?></span>
                            <strong><?= htmlspecialchars($cita['paciente_nombre']); ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>

        <article class="panel quick-links">
            <div class="panel-title">
                <i class="fas fa-calendar-day"></i>
            </div>
            <h2>Menú Citas</h2>
            <a href="Consultar/consultar_citas.php?doctor=<?= $doctorCodigo ?>" class="panel-link">
                <i class="fas fa-list"></i> Ver citas asignadas
            </a>
            <a href="Consultar/disponibilidad.php" class="panel-link">
                <i class="fas fa-calendar-plus"></i> Disponibilidad
            </a>
        </article>

        <article class="panel quick-links">
            <div class="panel-title">
                <i class="fas fa-user-injured"></i>
            </div>
            <h2>Menú Pacientes</h2>
            <a href="Consultar/consultar_pacientes.php" class="panel-link">
                <i class="fas fa-users"></i> Ver pacientes
            </a>
            <?php if ($proximaCita): ?>
                <a href="Consultar/consultar_pacientes.php?codigo=<?= htmlspecialchars($proximaCita['paciente_codigo']); ?>" class="panel-link">
                    <i class="fas fa-user-md"></i> Paciente de la siguiente consulta
                </a>
            <?php endif; ?>
        </article>
    </section>

    <?php if ($proximaCita && $pacienteDetalle): ?>
        <section class="patient-diagnosis">
            <article class="panel patient-card">
                <div class="panel-title">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h2>Paciente a atender</h2>
                <div class="patient-grid">
                    <div>
                        <span>Nombre</span>
                        <strong><?= htmlspecialchars($pacienteDetalle['nombre']); ?></strong>
                    </div>
                    <div>
                        <span>Identificación</span>
                        <strong><?= htmlspecialchars($pacienteDetalle['identificacion'] ?? 'No registrada'); ?></strong>
                    </div>
                    <div>
                        <span>Teléfono</span>
                        <strong><?= htmlspecialchars($pacienteDetalle['telefono'] ?? 'No registrado'); ?></strong>
                    </div>
                    <div>
                        <span>Correo</span>
                        <strong><?= htmlspecialchars($pacienteDetalle['correo'] ?? 'No registrado'); ?></strong>
                    </div>
                    <div class="full-row">
                        <span>Dirección</span>
                        <strong><?= htmlspecialchars($pacienteDetalle['direccion'] ?? 'No registrada'); ?></strong>
                    </div>
                </div>
            </article>

            <article class="panel diagnosis-panel">
                <div class="panel-title">
                    <i class="fas fa-file-medical"></i>
                </div>
                <h2>Elaborar diagnóstico</h2>
                <form class="diagnostico-form" action="Actions/generar_receta.php" method="post">
                    <input type="hidden" name="doctor_codigo" value="<?= $doctorCodigo; ?>">
                    <label>
                        Consulta / diagnóstico
                        <textarea name="diagnostico" required placeholder="Describe la valoración médica realizada"></textarea>
                    </label>
                    <div class="medications-group"
                         data-med-manager data-meds="<?= $medicamentosJson; ?>">
                        <div class="med-row">
                            <select name="medicamentos_ids[]" required>
                                <option value="">Selecciona un medicamento</option>
                                <?= $medOptions; ?>
                            </select>
                            <input type="text" name="medicamentos_horarios[]" placeholder="Dosis / horario (ej. 1 tableta cada 8h)" required>
                            <button type="button" class="btn-remove-med" aria-label="Quitar medicamento">&times;</button>
                        </div>
                        <button type="button" class="btn-add-med" type="button">
                            <i class="fas fa-plus"></i> Agregar medicamento
                        </button>
                    </div>

                    <label>
                        Instrucciones generales
                        <textarea name="medicamentos" required placeholder="Describe cualquier indicación adicional para el paciente (reposo, cuidados, etc.)"></textarea>
                    </label>
                    <?php if (empty($medicamentos)): ?>
                        <p class="medication-warning"><i class="fas fa-exclamation-triangle"></i> Registra medicamentos antes de generar una consulta.</p>
                    <?php endif; ?>
                    <input type="hidden" name="id_cita" value="<?= $selectedCita['id_cita']; ?>">
                    <input type="hidden" name="doctor_codigo" value="<?= $doctorCodigo; ?>">
                    <button type="submit" class="btn-receta" <?= empty($medicamentos) ? 'disabled' : ''; ?>>
                        <i class="fas fa-file-download"></i> Generar receta PDF
                    </button>
                </form>
            </article>
        </section>
    <?php endif; ?>

    <section class="panel history-panel">
        <div class="panel-title">
            <i class="fas fa-history"></i>
        </div>
        <h2>Consultas realizadas</h2>
        <?php if ($consultas): ?>
            <div class="history-list">
                <?php foreach ($consultas as $con): ?>
                    <div class="history-item">
                        <div>
                            <strong><?= htmlspecialchars($con['paciente_nombre']); ?></strong>
                            <p><?= formatFecha($con['fecha']); ?> · <?= formatHora($con['hora']); ?></p>
                            <span><?= nl2br(htmlspecialchars($con['medicamentos_detalle'])); ?></span>
                        </div>
                        <a class="panel-link compact" href="Recetas/consulta_<?= $con['id_consulta']; ?>.pdf" target="_blank" rel="noopener">
                            <i class="fas fa-file-pdf"></i> Consultar diagnóstico
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-state">Aún no se han generado diagnósticos.</p>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
<script>
(function () {
    document.querySelectorAll('[data-med-manager]').forEach(group => {
        const addBtn = group.querySelector('.btn-add-med');
        const meds = JSON.parse(group.dataset.meds || '[]');
        const buildOptions = () => {
            let template = '<option value="">Selecciona un medicamento</option>';
            meds.forEach(m => {
                template += `<option value="${m.codigo}">${m.nombre}</option>`;
            });
            return template;
        };

        const updateRemovers = () => {
            const removes = group.querySelectorAll('.btn-remove-med');
            removes.forEach(btn => {
                btn.style.display = removes.length === 1 ? 'none' : 'inline-flex';
            });
        };

        const createRow = () => {
            const row = document.createElement('div');
            row.className = 'med-row';
            row.innerHTML = `
                <select name="medicamentos_ids[]" required>
                    ${buildOptions()}
                </select>
                <input type="text" name="medicamentos_horarios[]" placeholder="Dosis / horario (ej. 1 tableta cada 8h)" required>
                <button type="button" class="btn-remove-med" aria-label="Quitar medicamento">&times;</button>
            `;
            return row;
        };

        group.addEventListener('click', (ev) => {
            if (ev.target.closest('.btn-remove-med')) {
                const rows = group.querySelectorAll('.med-row');
                if (rows.length === 1) return;
                ev.target.closest('.med-row').remove();
                updateRemovers();
            }
        });

        addBtn.addEventListener('click', () => {
            addBtn.before(createRow());
            updateRemovers();
        });

        updateRemovers();
    });
})();
</script>
