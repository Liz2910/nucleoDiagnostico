<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Menú del Administrador</title>
  <link rel="stylesheet" href="Styles/menu.css">
</head>
<body>
  <!-- Barra superior -->
  <div class="barra-superior">
    <div class="info-usuario">
      <span>Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
      <p class="codigo">Código: <?php echo htmlspecialchars($_SESSION['codigo']); ?></p>
    </div>
    <form action="logout.php" method="post">
      <button type="submit" class="logout-btn">Cerrar sesión</button>
    </form>
  </div>


  <!-- Contenedor principal -->
  <div class="card">
    <h2>MENU DEL ADMINISTRADOR</h2>

    <!-- Menú Empleados -->
    <button class="menu-btn" id="empleadosBtn">Menú Empleados</button>
    <div class="submenu" id="empleadosSubmenu">
      <a href="insertar_empleado.php">Insertar Empleado</a>
      <a href="consultar_empleados.php">Consultas Generales</a>
    </div>

    <!-- Menú Doctores -->
    <button class="menu-btn" id="doctoresBtn">Menú Doctores</button>
    <div class="submenu" id="doctoresSubmenu">
      <a href="insertar_doctor.php">Insertar Doctor</a>
      <a href="consultar_doctores.php">Consultas Generales</a>
    </div>

    <!-- Menú Pacientes -->
    <button class="menu-btn" id="pacientesBtn">Menú Pacientes</button>
    <div class="submenu" id="pacientesSubmenu">
      <a href="insertar_paciente.php">Insertar Paciente</a>
    </div>
  </div>

  <!-- Script para abrir/cerrar submenús -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const empleadosBtn = document.getElementById("empleadosBtn");
      const doctoresBtn = document.getElementById("doctoresBtn");
      const empleadosSubmenu = document.getElementById("empleadosSubmenu");
      const doctoresSubmenu = document.getElementById("doctoresSubmenu");
      const pacientesBtn = document.getElementById("pacientesBtn");
      const pacientesSubmenu = document.getElementById("pacientesSubmenu");

      pacientesBtn.addEventListener("click", () => {
        pacientesSubmenu.style.display =
          pacientesSubmenu.style.display === "flex" ? "none" : "flex";
      });

      empleadosBtn.addEventListener("click", () => {
        empleadosSubmenu.style.display =
          empleadosSubmenu.style.display === "flex" ? "none" : "flex";
      });

      doctoresBtn.addEventListener("click", () => {
        doctoresSubmenu.style.display =
          doctoresSubmenu.style.display === "flex" ? "none" : "flex";
      });
    });
  </script>
</body>
</html>