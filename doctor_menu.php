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

// Si es admin y no ha seleccionado doctor, mostrar picker
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

// Obtener datos del doctor
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

// CITAS - Todas las citas del doctor
$citasQuery = pg_query_params(
    $conexion,
    "SELECT c.id_cita,
           c.fecha,
           c.hora,
           p.codigo AS paciente_codigo,
           p.nombre AS paciente_nombre,
           CASE 
               WHEN c.fecha > CURRENT_DATE THEN 'proxima'
               WHEN c.fecha = CURRENT_DATE AND c.hora::time > CURRENT_TIME THEN 'hoy'
               WHEN c.fecha = CURRENT_DATE AND c.hora::time <= CURRENT_TIME AND (c.hora::time + interval '1 hour') > CURRENT_TIME THEN 'en-curso'
               ELSE 'pasada'
           END AS estado
     FROM citas c
     INNER JOIN paciente p ON p.codigo = c.id_paciente
     WHERE c.id_doctor = $1
     ORDER BY c.fecha DESC, c.hora DESC
     LIMIT 20",
    [$doctorCodigo]
);
$todasCitas = pg_fetch_all($citasQuery) ?: [];

// Próximas citas
$proximasCitas = array_filter($todasCitas, fn($c) => in_array($c['estado'], ['proxima', 'hoy', 'en-curso']));
$proximasCitas = array_slice(array_values($proximasCitas), 0, 4);
$proximaCita = $proximasCitas[0] ?? null;

// Datos del paciente de la próxima cita
$pacienteDetalle = null;
if ($proximaCita) {
    $pacienteDetalle = pg_fetch_assoc(pg_query_params(
        $conexion,
        "SELECT codigo, nombre, telefono, direccion, fecha_nac, sexo, edad, estatura
         FROM paciente WHERE codigo = $1 LIMIT 1",
        [$proximaCita['paciente_codigo']]
    ));
}

// MEDICAMENTOS DISPONIBLES
$medicamentos = pg_fetch_all(pg_query(
    $conexion,
    "SELECT codigo, nombre, via_adm, presentacion, fecha_cad FROM medicamento ORDER BY nombre ASC"
)) ?: [];

$medicamentosJson = htmlspecialchars(json_encode($medicamentos, JSON_UNESCAPED_UNICODE), ENT_QUOTES);
$medOptions = '';
foreach ($medicamentos as $med) {
    $medOptions .= '<option value="' . htmlspecialchars($med['codigo']) . '">' . htmlspecialchars($med['nombre']) . '</option>';
}

// PACIENTES DISPONIBLES
$pacientes = pg_fetch_all(pg_query(
    $conexion,
    "SELECT codigo, nombre, telefono, sexo, edad FROM paciente ORDER BY nombre ASC"
)) ?: [];

// CONSULTAS REALIZADAS (historial)
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
        SELECT string_agg(m.nombre || COALESCE(' - ' || NULLIF(cr.instrucciones, ''), ''), E'\n') AS listado
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

function formatFecha(string $fecha): string {
    $dt = DateTime::createFromFormat('Y-m-d', $fecha);
    return $dt ? $dt->format('d/m/Y') : $fecha;
}

function formatHora(string $hora): string {
    return substr($hora, 0, 5);
}

function getEstadoClass(string $estado): string {
    return match($estado) {
        'proxima' => 'proxima',
        'hoy' => 'hoy',
        'en-curso' => 'en-curso',
        default => 'pasada'
    };
}

function getEstadoTexto(string $estado): string {
    return match($estado) {
        'proxima' => 'Próxima',
        'hoy' => 'Hoy',
        'en-curso' => 'En curso',
        default => 'Pasada'
    };
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
    <!-- ========== HEADER ========== -->
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
            <?php if ($isAdmin): ?>
                <a href="menu.php" class="header-link" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                    <i class="fas fa-arrow-left"></i> Volver al panel admin
                </a>
            <?php endif; ?>
            <a href="logout.php" class="header-link">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </header>

    <!-- ========== CARDS GRID ========== -->
    <section class="cards-grid">
        <!-- Panel: Próximas Citas -->
        <article class="panel upcoming-panel">
            <div class="panel-title">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h2>Próxima cita</h2>
            <?php if ($proximaCita): ?>
                <div class="next-appointment">
                    <strong><?= htmlspecialchars($proximaCita['paciente_nombre']) ?></strong>
                    <p><i class="fas fa-calendar-alt"></i> <?= formatFecha($proximaCita['fecha']); ?></p>
                    <p><i class="fas fa-clock"></i> <?= formatHora($proximaCita['hora']); ?></p>
                    <p><i class="fas fa-hashtag"></i> Cita #<?= htmlspecialchars($proximaCita['id_cita']); ?></p>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    No hay citas próximas
                </div>
            <?php endif; ?>

            <?php if (count($proximasCitas) > 1): ?>
                <h3 style="font-size: 0.9rem; color: #666; margin: 16px 0 10px;">Siguientes citas:</h3>
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

        <!-- Panel: Menú Citas -->
        <article class="panel quick-links">
            <div class="panel-title">
                <i class="fas fa-calendar-day"></i>
            </div>
            <h2>CITAS</h2>
            <a href="#" class="panel-link" onclick="abrirModalFecha(); return false;">
                <i class="fas fa-calendar-day"></i> Ver cita (día específico)
            </a>
            <a href="#" class="panel-link" onclick="verCitasSemana(); return false;">
                <i class="fas fa-calendar-week"></i> Ver citas (por semana)
            </a>
            <a href="#" class="panel-link" onclick="verCitasMes(); return false;">
                <i class="fas fa-calendar-alt"></i> Ver citas (por mes)
            </a>
        </article>

        <!-- Panel: Menú Pacientes -->
        <article class="panel quick-links">
            <div class="panel-title">
                <i class="fas fa-user-injured"></i>
            </div>
            <h2>PACIENTES</h2>
            <a href="#tab-pacientes" class="panel-link" onclick="activarTab('pacientes'); return false;">
                <i class="fas fa-user"></i> Ver información paciente
            </a>
        </article>
    </section>

    <!-- ========== SECCIÓN: Paciente a atender + Generar diagnóstico ========== -->
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
                <div>
                    <span>Edad</span>
                    <strong><?= htmlspecialchars($pacienteDetalle['edad'] ?? '-'); ?> años</strong>
                </div>
                <div>
                    <span>Sexo</span>
                    <strong><?= htmlspecialchars($pacienteDetalle['sexo'] ?? '-'); ?></strong>
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
            <h2>Generar diagnóstico</h2>
            <form class="diagnostico-form" action="Actions/generar_diagnostico_action.php" method="post">
                <input type="hidden" name="id_cita" value="<?= $proximaCita['id_cita']; ?>">
                <input type="hidden" name="doctor_codigo" value="<?= $doctorCodigo; ?>">
                
                <label>
                    <i class="fas fa-notes-medical"></i> Diagnóstico
                    <textarea name="diagnostico" maxlength="300" rows="3" placeholder="Describa el diagnóstico del paciente (máx. 300 caracteres)" required></textarea>
                </label>

                <div class="medications-group" data-med-manager data-meds="<?= $medicamentosJson; ?>">
                    <label style="margin-bottom: 12px;"><i class="fas fa-prescription"></i> Medicamentos recetados</label>
                    <div class="med-row">
                        <select name="medicamentos_ids[]" required>
                            <option value="">Selecciona medicamento</option>
                            <?= $medOptions; ?>
                        </select>
                        <input type="text" name="medicamentos_horarios[]" placeholder="Dosis / horario" required>
                        <button type="button" class="btn-remove-med" aria-label="Quitar">&times;</button>
                    </div>
                    <button type="button" class="btn-add-med">
                        <i class="fas fa-plus"></i> Agregar otro medicamento
                    </button>
                </div>

                <?php if (empty($medicamentos)): ?>
                    <p class="medication-warning"><i class="fas fa-exclamation-triangle"></i> No hay medicamentos registrados.</p>
                <?php endif; ?>

                <button type="submit" class="btn-receta" <?= empty($medicamentos) ? 'disabled' : ''; ?>>
                    <i class="fas fa-file-pdf"></i> Guardar y generar receta PDF
                </button>
            </form>
        </article>
    </section>
    <?php endif; ?>

    <!-- ========== TABS: Citas, Pacientes ========== -->
    <div class="tabs-container">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="citas" onclick="activarTab('citas')">
                <i class="fas fa-calendar-alt"></i> Mis Citas
            </button>
            <button class="tab-btn" data-tab="pacientes" onclick="activarTab('pacientes')">
                <i class="fas fa-users"></i> Pacientes
            </button>
        </div>

        <!-- Tab: Citas -->
        <div class="tab-content active" id="tab-citas">
            <h2 style="margin-bottom: 16px; color: #333;">
                <i class="fas fa-calendar-alt" style="color: #667eea;"></i> 
                Historial de citas
                <span class="badge"><?= count($todasCitas) ?> registros</span>
            </h2>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-calendar"></i> Fecha</th>
                            <th><i class="fas fa-clock"></i> Hora</th>
                            <th><i class="fas fa-user"></i> Paciente</th>
                            <th><i class="fas fa-info-circle"></i> Estado</th>
                            <th><i class="fas fa-cogs"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($todasCitas)): ?>
                            <tr><td colspan="6" style="text-align:center; padding: 40px; color: #888;">No tienes citas registradas</td></tr>
                        <?php else: ?>
                            <?php foreach ($todasCitas as $cita): ?>
                            <tr>
                                <td><?= htmlspecialchars($cita['id_cita']) ?></td>
                                <td><?= formatFecha($cita['fecha']) ?></td>
                                <td><?= formatHora($cita['hora']) ?></td>
                                <td><?= htmlspecialchars($cita['paciente_nombre']) ?></td>
                                <td>
                                    <span class="cita-status <?= getEstadoClass($cita['estado']) ?>">
                                        <?= getEstadoTexto($cita['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="table-btn btn-view" onclick="abrirModalPaciente(<?= $cita['paciente_codigo'] ?>)">
                                            <i class="fas fa-user"></i> Paciente
                                        </button>
                                        <?php if (in_array($cita['estado'], ['hoy', 'en-curso', 'proxima'])): ?>
                                        <a href="generar_diagnostico.php?id_cita=<?= $cita['id_cita'] ?>" class="table-btn btn-diagnose">
                                            <i class="fas fa-file-medical"></i> Diagnóstico
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Pacientes -->
        <div class="tab-content" id="tab-pacientes">
            <h2 style="margin-bottom: 16px; color: #333;">
                <i class="fas fa-users" style="color: #27ae60;"></i> 
                Lista de pacientes
                <span class="badge"><?= count($pacientes) ?> registrados</span>
            </h2>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> Código</th>
                            <th><i class="fas fa-user"></i> Nombre</th>
                            <th><i class="fas fa-phone"></i> Teléfono</th>
                            <th><i class="fas fa-venus-mars"></i> Sexo</th>
                            <th><i class="fas fa-user-clock"></i> Edad</th>
                            <th><i class="fas fa-eye"></i> Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pacientes)): ?>
                            <tr><td colspan="6" style="text-align:center; padding: 40px; color: #888;">No hay pacientes registrados</td></tr>
                        <?php else: ?>
                            <?php foreach ($pacientes as $pac): ?>
                            <tr>
                                <td><?= htmlspecialchars($pac['codigo']) ?></td>
                                <td><?= htmlspecialchars($pac['nombre']) ?></td>
                                <td><?= htmlspecialchars($pac['telefono'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($pac['sexo'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($pac['edad'] ?? '-') ?> años</td>
                                <td>
                                    <button type="button" class="table-btn btn-view" onclick="abrirModalPaciente(<?= $pac['codigo'] ?>)">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========== HISTORIAL DE CONSULTAS ========== -->
    <section class="panel history-panel">
        <div class="panel-title">
            <i class="fas fa-history"></i>
        </div>
        <h2>Diagnósticos generados</h2>
        <?php if ($consultas): ?>
            <div class="history-list">
                <?php foreach ($consultas as $con): ?>
                    <div class="history-item">
                        <div>
                            <strong><?= htmlspecialchars($con['paciente_nombre']); ?></strong>
                            <p><?= formatFecha($con['fecha']); ?> · <?= formatHora($con['hora']); ?></p>
                            <span><?= nl2br(htmlspecialchars($con['medicamentos_detalle'])); ?></span>
                        </div>
                        <a class="panel-link compact" href="Recetas/consulta_<?= $con['id_consulta']; ?>.pdf" target="_blank">
                            <i class="fas fa-file-pdf"></i> Ver PDF
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-file-medical-alt"></i>
                Aún no has generado diagnósticos
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- ========== MODAL: Ver paciente ========== -->
<div id="pacienteModal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <button class="modal-close" onclick="cerrarModal()" aria-label="Cerrar">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-hero">
            <div class="hero-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="hero-text">
                <h3 class="hero-title" id="modalPacienteNombre">Cargando...</h3>
                <p class="hero-subtitle"><i class="fas fa-id-badge"></i> Información del paciente</p>
            </div>
        </div>
        <div class="modal-content" id="modalPacienteContent">
            <p style="text-align:center; padding: 20px;">Cargando datos...</p>
        </div>
    </div>
</div>

<!-- ========== MODAL: Seleccionar fecha ========== -->
<div id="fechaModal" class="modal-overlay" style="display:none;">
    <div class="modal-card" style="max-width: 400px;">
        <button class="modal-close" onclick="cerrarModalFecha()" aria-label="Cerrar">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-hero" style="padding: 20px 28px;">
            <div class="hero-avatar" style="width: 60px; height: 60px;">
                <i class="fas fa-calendar-day" style="font-size: 1.5rem;"></i>
            </div>
            <div class="hero-text">
                <h3 class="hero-title">Seleccionar fecha</h3>
                <p class="hero-subtitle">Ver citas de un día específico</p>
            </div>
        </div>
        <div class="modal-content" style="padding: 25px;">
            <form onsubmit="buscarCitasPorFecha(event)">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                    <i class="fas fa-calendar"></i> Fecha:
                </label>
                <input type="date" id="fechaBuscar" required 
                    style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 1rem; margin-bottom: 20px;">
                <button type="submit" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-search"></i> Buscar citas
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ========== MODAL: Resultados de citas ========== -->
<div id="citasResultadoModal" class="modal-overlay" style="display:none;">
    <div class="modal-card" style="max-width: 700px;">
        <button class="modal-close" onclick="cerrarModalCitasResultado()" aria-label="Cerrar">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-hero" style="padding: 20px 28px;">
            <div class="hero-avatar" style="width: 60px; height: 60px;">
                <i class="fas fa-calendar-check" style="font-size: 1.5rem;"></i>
            </div>
            <div class="hero-text">
                <h3 class="hero-title" id="citasResultadoTitulo">Citas</h3>
                <p class="hero-subtitle" id="citasResultadoSubtitulo">Resultados</p>
            </div>
        </div>
        <div class="modal-content" id="citasResultadoContent" style="padding: 20px; max-height: 400px; overflow-y: auto;">
            <p style="text-align:center; padding: 20px;">Cargando...</p>
        </div>
    </div>
</div>

<script>
// ========== DATOS DE CITAS ==========
const todasLasCitas = <?= json_encode($todasCitas, JSON_UNESCAPED_UNICODE) ?>;
const doctorCodigo = <?= $doctorCodigo ?>;

// ========== TABS ==========
function activarTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    document.getElementById(`tab-${tabName}`).classList.add('active');
    
    document.getElementById(`tab-${tabName}`).scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ========== MODAL PACIENTE ==========
const modal = document.getElementById('pacienteModal');

function abrirModalPaciente(id) {
    modal.style.display = 'flex';
    document.getElementById('modalPacienteNombre').textContent = 'Cargando...';
    document.getElementById('modalPacienteContent').innerHTML = '<p style="text-align:center; padding: 20px;">Cargando datos...</p>';
    
    fetch('obtener_paciente.php?id=' + encodeURIComponent(id))
        .then(resp => resp.json())
        .then(json => {
            if (json.ok) {
                const d = json.data;
                document.getElementById('modalPacienteNombre').textContent = d.nombre || 'Paciente';
                document.getElementById('modalPacienteContent').innerHTML = `
                    <ul class="paciente-detail-list">
                        <li><i class="fas fa-id-badge"></i><div><strong>Código</strong><span>${d.codigo}</span></div></li>
                        <li><i class="fas fa-phone-alt"></i><div><strong>Teléfono</strong><span>${d.telefono || 'No registrado'}</span></div></li>
                        <li><i class="fas fa-map-marker-alt"></i><div><strong>Dirección</strong><span>${d.direccion || 'No registrada'}</span></div></li>
                        <li><i class="fas fa-birthday-cake"></i><div><strong>Fecha de nacimiento</strong><span>${d.fecha_nac || '-'}</span></div></li>
                        <li><i class="fas fa-venus-mars"></i><div><strong>Sexo</strong><span>${d.sexo || '-'}</span></div></li>
                        <li><i class="fas fa-user-clock"></i><div><strong>Edad</strong><span>${d.edad || '-'} años</span></div></li>
                        <li><i class="fas fa-ruler-vertical"></i><div><strong>Estatura</strong><span>${d.estatura ? Number(d.estatura).toFixed(2) + ' m' : '-'}</span></div></li>
                    </ul>
                `;
            } else {
                document.getElementById('modalPacienteContent').innerHTML = '<p style="text-align:center; color: #e74c3c;">No se encontró el paciente.</p>';
            }
        })
        .catch(() => {
            document.getElementById('modalPacienteContent').innerHTML = '<p style="text-align:center; color: #e74c3c;">Error al cargar datos.</p>';
        });
}

function cerrarModal() {
    modal.style.display = 'none';
}

modal.addEventListener('click', e => {
    if (e.target === modal) cerrarModal();
});

// ========== MODAL FECHA (día específico) ==========
const fechaModal = document.getElementById('fechaModal');

function abrirModalFecha() {
    document.getElementById('fechaBuscar').value = new Date().toISOString().split('T')[0];
    fechaModal.style.display = 'flex';
}

function cerrarModalFecha() {
    fechaModal.style.display = 'none';
}

fechaModal.addEventListener('click', e => {
    if (e.target === fechaModal) cerrarModalFecha();
});

function buscarCitasPorFecha(e) {
    e.preventDefault();
    const fecha = document.getElementById('fechaBuscar').value;
    const citasFiltradas = todasLasCitas.filter(c => c.fecha === fecha);
    
    cerrarModalFecha();
    mostrarResultadosCitas(citasFiltradas, 'Citas del día', formatearFecha(fecha));
}

// ========== VER CITAS POR SEMANA ==========
function verCitasSemana() {
    const hoy = new Date();
    const inicioSemana = new Date(hoy);
    inicioSemana.setDate(hoy.getDate() - hoy.getDay() + 1); // Lunes
    const finSemana = new Date(inicioSemana);
    finSemana.setDate(inicioSemana.getDate() + 6); // Domingo
    
    const inicioStr = inicioSemana.toISOString().split('T')[0];
    const finStr = finSemana.toISOString().split('T')[0];
    
    const citasFiltradas = todasLasCitas.filter(c => c.fecha >= inicioStr && c.fecha <= finStr);
    
    mostrarResultadosCitas(citasFiltradas, 'Citas de la semana', 
        `${formatearFecha(inicioStr)} - ${formatearFecha(finStr)}`);
}

// ========== VER CITAS POR MES ==========
function verCitasMes() {
    const hoy = new Date();
    const año = hoy.getFullYear();
    const mes = hoy.getMonth();
    
    const inicioMes = new Date(año, mes, 1);
    const finMes = new Date(año, mes + 1, 0);
    
    const inicioStr = inicioMes.toISOString().split('T')[0];
    const finStr = finMes.toISOString().split('T')[0];
    
    const citasFiltradas = todasLasCitas.filter(c => c.fecha >= inicioStr && c.fecha <= finStr);
    
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    mostrarResultadosCitas(citasFiltradas, 'Citas del mes', `${meses[mes]} ${año}`);
}

// ========== MOSTRAR RESULTADOS DE CITAS ==========
const citasResultadoModal = document.getElementById('citasResultadoModal');

function mostrarResultadosCitas(citas, titulo, subtitulo) {
    document.getElementById('citasResultadoTitulo').textContent = titulo;
    document.getElementById('citasResultadoSubtitulo').textContent = subtitulo;
    
    let html = '';
    
    if (citas.length === 0) {
        html = '<p style="text-align: center; color: #888; padding: 30px;"><i class="fas fa-calendar-times" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>No hay citas en este período</p>';
    } else {
        html = '<table class="data-table" style="width: 100%;"><thead><tr><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Estado</th><th>Acción</th></tr></thead><tbody>';
        
        citas.forEach(c => {
            const estadoClass = c.estado === 'proxima' ? 'proxima' : (c.estado === 'hoy' ? 'hoy' : (c.estado === 'en-curso' ? 'en-curso' : 'pasada'));
            const estadoText = c.estado === 'proxima' ? 'Próxima' : (c.estado === 'hoy' ? 'Hoy' : (c.estado === 'en-curso' ? 'En curso' : 'Pasada'));
            
            html += `<tr>
                <td>${formatearFecha(c.fecha)}</td>
                <td>${c.hora.substring(0,5)}</td>
                <td>${c.paciente_nombre}</td>
                <td><span class="cita-status ${estadoClass}">${estadoText}</span></td>
                <td><button type="button" class="table-btn btn-view" onclick="abrirModalPaciente(${c.paciente_codigo})"><i class="fas fa-user"></i></button></td>
            </tr>`;
        });
        
        html += '</tbody></table>';
    }
    
    document.getElementById('citasResultadoContent').innerHTML = html;
    citasResultadoModal.style.display = 'flex';
}

function cerrarModalCitasResultado() {
    citasResultadoModal.style.display = 'none';
}

citasResultadoModal.addEventListener('click', e => {
    if (e.target === citasResultadoModal) cerrarModalCitasResultado();
});

// ========== UTILIDADES ==========
function formatearFecha(fechaStr) {
    const [año, mes, dia] = fechaStr.split('-');
    return `${dia}/${mes}/${año}`;
}

// ========== MEDICAMENTOS DINÁMICOS ==========
document.querySelectorAll('[data-med-manager]').forEach(group => {
    const addBtn = group.querySelector('.btn-add-med');
    const meds = JSON.parse(group.dataset.meds || '[]');
    
    const buildOptions = () => {
        let template = '<option value="">Selecciona medicamento</option>';
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
            <select name="medicamentos_ids[]" required>${buildOptions()}</select>
            <input type="text" name="medicamentos_horarios[]" placeholder="Dosis / horario" required>
            <button type="button" class="btn-remove-med" aria-label="Quitar">&times;</button>
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

    if (addBtn) {
        addBtn.addEventListener('click', () => {
            addBtn.before(createRow());
            updateRemovers();
        });
    }

    updateRemovers();
});
</script>
</body>
</html>