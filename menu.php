<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Menú del Administrador</title>
  <link rel="stylesheet" href="Styles\menu.css">
</head>
<body>
  <div class="card">
    <h2>Menú del Administrador</h2>
    <button id="menuBtn" class="menu-btn">Menú de Empleados</button>

    <div id="submenu" class="submenu">
      <button id="insertarBtn">Insertar Empleado</button>
      <button id="consultarBtn">Consultas Generales</button>
    </div>

    <div id="formulario" class="formulario">
      <h3 class="titulo-form">Insertar Nueva Empleada</h3>
      <form action="insertar_empleado_action.php" method="post">
        <label>Nombre completo:</label>
        <input type="text" name="nombre" placeholder="Nombre completo" required>

        <label>Dirección:</label>
        <input type="text" name="direccion" placeholder="Dirección" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" placeholder="Teléfono" required>

        <label>Sexo (M/F):</label>
        <input type="text" name="sexo" maxlength="1" placeholder="M o F" required>

        <label>Sueldo:</label>
        <input type="number" name="sueldo" step="0.01" placeholder="Sueldo" required>

        <label>Turno:</label>
        <input type="text" name="turno" placeholder="Turno" required>

        <label>Fecha de nacimiento:</label>
        <input type="date" name="fecha_nacimiento" required>

        <label>Contraseña:</label>
        <input type="password" name="contrasena" placeholder="Contraseña" required>

        <button type="submit">Guardar Empleada</button>
      </form>
    </div>
  </div>

  <script>
    const menuBtn = document.getElementById("menuBtn");
    const submenu = document.getElementById("submenu");
    const insertarBtn = document.getElementById("insertarBtn");
    const formulario = document.getElementById("formulario");
    const consultarBtn = document.getElementById("consultarBtn");

    consultarBtn.addEventListener("click", () => {
        window.location.href = "consultar_empleados.php";
    });

    menuBtn.addEventListener("click", () => {
      const activo = menuBtn.classList.toggle("active");
      submenu.style.display = activo ? "flex" : "none";
    });

    insertarBtn.addEventListener("click", () => {
      formulario.style.display =
        (formulario.style.display === "none" || formulario.style.display === "")
        ? "block"
        : "none";
    });
  </script>
</body>
</html>