<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Menú del Administrador</title>
  <link rel="stylesheet" href="Styles/menu.css">
</head>
<body>
  <div class="card">
    <h2>Menú del Administrador</h2>

    <!-- === MENÚ EMPLEADOS === -->
    <div class="menu-section">
      <button id="empleadosBtn" class="menu-btn">Menú de Empleados</button>
      <div id="submenuEmpleados" class="submenu">
        <a href="insertar_empleado.php">Insertar Empleado</a>
        <a href="consultar_empleados.php">Consultas Generales</a>
      </div>
    </div>

    <!-- === MENÚ DOCTORES === -->
    <div class="menu-section">
      <button id="doctoresBtn" class="menu-btn">Menú de Doctores</button>
      <div id="submenuDoctores" class="submenu">
        <a href="insertar_doctor.php">Insertar Doctor</a>
        <!-- En un futuro: <a href="consultar_doctores.php">Consultas Generales</a> -->
      </div>
    </div>

  </div>

  <script>
    const empleadosBtn = document.getElementById("empleadosBtn");
    const doctoresBtn = document.getElementById("doctoresBtn");
    const submenuEmpleados = document.getElementById("submenuEmpleados");
    const submenuDoctores = document.getElementById("submenuDoctores");

    // Mostrar/ocultar menús independientemente
    empleadosBtn.addEventListener("click", () => {
      submenuEmpleados.style.display =
        submenuEmpleados.style.display === "flex" ? "none" : "flex";
    });

    doctoresBtn.addEventListener("click", () => {
      submenuDoctores.style.display =
        submenuDoctores.style.display === "flex" ? "none" : "flex";
    });
  </script>
</body>
</html>