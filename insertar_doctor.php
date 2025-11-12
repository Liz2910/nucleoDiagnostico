<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Insertar Nuevo Doctor</title>
  <link rel="stylesheet" href="Styles/form.css">
</head>
<body>
  <div class="card">
    <h2>Insertar Nuevo Doctor</h2>
    <form action="acciones/insertar_doctor_action.php" method="post">
      <label>Nombre completo:</label>
      <input type="text" name="nombre" required>

      <label>Dirección:</label>
      <input type="text" name="direccion" required>

      <label>Teléfono:</label>
      <input type="text" name="telefono" required>

      <label>Especialidad:</label>
      <input type="text" name="especialidad" required>

      <label>Fecha de nacimiento:</label>
      <input type="date" name="fecha_nac" required>

      <label>Sexo (M/F):</label>
      <input type="text" name="sexo" maxlength="1" required>

      <label>Contraseña:</label>
      <input type="password" name="contrasena" required>

      <button type="submit">Guardar Doctor</button>
      <a href="menu.php" class="back-btn">Volver al menú</a>
    </form>
  </div>
</body>
</html>