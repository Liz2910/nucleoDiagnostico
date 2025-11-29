<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: index.php");
    exit();
}
include("conecta.php");
date_default_timezone_set('America/Mexico_City');

$hoy = date("Y-m-d");

// Solo hoy: mostrar próximas o en curso (inicio + 2h > ahora)
$sql = "
SELECT c.id_cita, c.fecha, c.hora,
       p.codigo AS id_paciente, p.nombre AS paciente,
       d.codigo AS id_doctor, d.nombre AS doctor, d.especialidad
FROM citas c
JOIN paciente p ON p.codigo = c.id_paciente
JOIN doctor d   ON d.codigo = c.id_doctor
WHERE c.fecha = CURRENT_DATE
  AND (c.hora::time + interval '2 hours') > CURRENT_TIME
ORDER BY c.hora ASC";

$rs = pg_query($conexion, $sql);
$db_error = $rs === false ? pg_last_error($conexion) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Citas de hoy (<?= htmlspecialchars($hoy) ?>)</title>
  <link rel="stylesheet" href="Styles/consultas.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="theme-consultas">
  <div class="container">
    <h2><i class="fas fa-calendar-day"></i> Citas de hoy (<?= htmlspecialchars($hoy) ?>)</h2>
    <?php if ($db_error): ?>
      <div class="info-badge">Error: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <!-- Fecha eliminada -->
            <th><i class="fas fa-clock"></i> Hora</th>
            <th><i class="fas fa-user"></i> Paciente</th>
            <th><i class="fas fa-user-md"></i> Doctor</th>
            <th><i class="fas fa-stethoscope"></i> Especialidad</th>
            <th><i class="fas fa-info-circle"></i> Estado</th>
            <th><i class="fas fa-tools"></i> Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$rs || pg_num_rows($rs) === 0): ?>
          <tr><td colspan="6">No hay citas próximas ni en curso para hoy.</td></tr>
        <?php else: ?>
          <?php
          $now = time();
          while ($row = pg_fetch_assoc($rs)):
              $inicioTs = strtotime($row['fecha'].' '.substr($row['hora'],0,5).':00');
              $finTs    = $inicioTs + 3600;

              // Estado correcto según hora actual
              if ($now < $inicioTs) {
                  $estadoTxt = '<span style="color:#2980b9;font-weight:600;">Próxima</span>';
              } elseif ($now < $finTs) {
                  $estadoTxt = '<span style="color:#ff4f81;font-weight:600;">En curso</span>';
              } else {
                  $estadoTxt = '<span style="color:#7f8c8d;font-weight:600;">Pasada</span>';
              }
          ?>
          <tr>
            <td><?= htmlspecialchars(substr($row['hora'],0,5)) ?></td>
            <td><?= htmlspecialchars($row['paciente']) ?></td>
            <td><?= htmlspecialchars($row['doctor']) ?></td>
            <td><?= htmlspecialchars($row['especialidad']) ?></td>
            <td><?= $estadoTxt ?></td>
            <td>
              <div class="actions-group">
                <button type="button"
                        class="action-btn edit-btn btn-show-paciente"
                        data-paciente-id="<?= intval($row['id_paciente']) ?>">
                  <i class="fas fa-user"></i> Ver paciente
                </button>
                <a class="action-btn delete-btn" href="generar_diagnostico.php?id_cita=<?= intval($row['id_cita']) ?>">
                  <i class="fas fa-file-medical"></i> Diagnóstico
                </a>
                <!-- Sin botón Editar en esta vista -->
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="btn-container">
      <!-- Quitar icono de casita -->
      <a href="menu.php" class="back-btn">Volver al menú</a>
    </div>
    <?php pg_close($conexion); ?>
  </div>

  <!-- Modal Paciente -->
  <div id="pacienteModal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
      <button class="modal-close" id="closePacienteModal" aria-label="Cerrar">
        <i class="fas fa-times"></i>
      </button>
      <div class="modal-hero">
        <div class="hero-avatar" aria-hidden="true">
          <img id="pacienteAvatar" alt="Paciente" />
          <i class="fas fa-user" id="pacienteAvatarIcon"></i>
        </div>
        <div class="hero-text">
          <h3 class="hero-title">Datos del Paciente</h3>
          <div class="hero-subtitle"><i class="fas fa-id-badge"></i> <span id="pacienteName">Cargando…</span></div>
        </div>
      </div>
      <div id="pacienteContent" class="modal-content">
        <!-- Se rellena dinámicamente -->
      </div>
    </div>
  </div>

  <script>
    (function() {
      const modal = document.getElementById('pacienteModal');
      const content = document.getElementById('pacienteContent');
      const closeBtn = document.getElementById('closePacienteModal');

      function abrirModal() { modal.style.display = 'flex'; }
      function cerrarModal() { modal.style.display = 'none'; content.innerHTML = ''; }

      closeBtn.addEventListener('click', cerrarModal);
      modal.addEventListener('click', e => { if (e.target === modal) cerrarModal(); });

      function renderPaciente(data) {
        const nameEl = document.getElementById('pacienteName');
        if (nameEl) nameEl.textContent = data?.nombre || 'Paciente';
        const avatarImg = document.getElementById('pacienteAvatar');
        const avatarIcon = document.getElementById('pacienteAvatarIcon');
        const fotoUrl = data?.foto_url || data?.foto || data?.fotoPerfil || data?.imagen;
        if (avatarImg && avatarIcon) {
          if (fotoUrl) {
            avatarImg.src = fotoUrl;
            avatarImg.style.display = 'block';
            avatarIcon.style.display = 'none';
          } else {
            avatarImg.removeAttribute('src');
            avatarImg.style.display = 'none';
            avatarIcon.style.display = '';
          }
        }
        content.innerHTML = `
          <ul class="paciente-detail-list">
            <li>
              <i class="fas fa-id-badge"></i>
              <div><strong>ID:</strong> <span>${data.codigo}</span></div>
            </li>
            <li>
              <i class="fas fa-phone-alt"></i>
              <div><strong>Teléfono:</strong> <span>${data.telefono}</span></div>
            </li>
            <li>
              <i class="fas fa-map-marker-alt"></i>
              <div><strong>Dirección:</strong> <span>${data.direccion}</span></div>
            </li>
            <li>
              <i class="fas fa-birthday-cake"></i>
              <div><strong>Fecha Nac:</strong> <span>${data.fecha_nac}</span></div>
            </li>
            <li>
              <i class="fas fa-venus-mars"></i>
              <div><strong>Sexo:</strong> <span>${data.sexo}</span></div>
            </li>
            <li>
              <i class="fas fa-user-clock"></i>
              <div><strong>Edad:</strong> <span>${data.edad} años</span></div>
            </li>
            <li>
              <i class="fas fa-ruler-vertical"></i>
              <div><strong>Estatura:</strong> <span>${Number(data.estatura).toFixed(2)} m</span></div>
            </li>
          </ul>
        `;
      }

      async function cargarPaciente(id) {
        try {
          const resp = await fetch('obtener_paciente.php?id=' + encodeURIComponent(id));
          if (!resp.ok) throw new Error('Error HTTP');
          const json = await resp.json();
          if (json.ok) {
            renderPaciente(json.data);
            abrirModal();
          } else {
            content.innerHTML = '<p class="error-text">No se encontró el paciente.</p>';
            abrirModal();
          }
        } catch (err) {
          content.innerHTML = '<p class="error-text">Error al cargar datos.</p>';
          abrirModal();
        }
      }

      document.querySelectorAll('.btn-show-paciente').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-paciente-id');
          cargarPaciente(id);
        });
      });
    })();
  </script>
</body>
</html>
