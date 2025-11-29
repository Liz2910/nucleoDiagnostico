<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: index.php");
    exit();
}

$nombre = htmlspecialchars($_SESSION['usuario']);
$codigo = htmlspecialchars($_SESSION['codigo']);
$primer_nombre = explode(' ', $nombre)[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Empleado - La salud es primero</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Styles/men.css">
</head>
<body>
  <!-- Header superior -->
  <div class="top-header">
    <div class="user-info">
      <div class="user-avatar">
        <i class="fas fa-user-tie"></i>
      </div>
      <div class="user-details">
        <h3>Bienvenido, <?= $primer_nombre; ?></h3>
        <p><i class="fas fa-id-badge"></i> Código: <?= $codigo; ?></p>
      </div>
    </div>
    <form action="logout.php" method="post">
      <button type="submit" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        <span>Cerrar Sesión</span>
      </button>
    </form>
  </div>

  <div class="main-container">
    <!-- Tarjeta de bienvenida -->
    <div class="welcome-card">
      <h1>
        <i class="fas fa-hospital"></i>
        Panel del Empleado
      </h1>
      <p>Gestiona pacientes, citas y medicamentos desde aquí</p>
    </div>

    <!-- Grid de menús -->
    <div class="menu-grid">
      <!-- Menú Pacientes -->
      <div class="menu-section pacientes">
        <div class="menu-header">
          <div class="menu-icon">
            <i class="fas fa-user-injured"></i>
          </div>
          <h2>Pacientes</h2>
        </div>
        <div class="menu-options">
          <a href="Insertar/insertar_paciente.php" class="menu-link">
            <i class="fas fa-user-plus"></i>
            <span>Registrar Paciente</span>
          </a>
          <a href="Consultar/consultar_pacientes.php" class="menu-link">
            <i class="fas fa-list"></i>
            <span>Ver Todos los Pacientes</span>
          </a>
        </div>
      </div>

      <!-- Menú Citas -->
      <div class="menu-section citas">
        <div class="menu-header">
          <div class="menu-icon">
            <i class="fas fa-calendar-check"></i>
          </div>
          <h2>Citas</h2>
        </div>
        <div class="menu-options">
          <a href="Insertar/insertar_cita.php" class="menu-link">
            <i class="fas fa-calendar-plus"></i>
            <span>Registrar Cita</span>
          </a>
          <a href="Consultar/consultar_citas.php" class="menu-link">
            <i class="fas fa-list"></i>
            <span>Ver Todas las Citas</span>
          </a>
          <a href="Consultar/disponibilidad.php" class="menu-link">
            <i class="fas fa-clock"></i>
            <span>Ver Disponibilidad Doctor</span>
          </a>
        </div>
      </div>

      <!-- Menú Medicamentos -->
      <div class="menu-section medicamento">
        <div class="menu-header">
          <div class="menu-icon">
            <i class="fas fa-pills"></i>
          </div>
          <h2>Medicamentos</h2>
        </div>
        <div class="menu-options">
          <a href="Insertar/insertar_medicamento.php" class="menu-link">
            <i class="fas fa-plus"></i>
            <span>Registrar Medicamento</span>
          </a>
          <a href="Consultar/consultar_medicamento.php" class="menu-link">
            <i class="fas fa-list"></i>
            <span>Ver Todos los Medicamentos</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
