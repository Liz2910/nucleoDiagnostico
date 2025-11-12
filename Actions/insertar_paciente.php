<?php
include("conecta.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = $_POST['nombre'];
  $direccion = $_POST['direccion'];
  $telefono = $_POST['telefono'];
  $fecha_nac = $_POST['fecha_nac'];
  $sexo = $_POST['sexo'];
  $edad = $_POST['edad'];
  $estatura = $_POST['estatura'];

  $query = "INSERT INTO paciente (nombre, direccion, telefono, fecha_nac, sexo, edad, estatura)
            VALUES ($1, $2, $3, $4, $5, $6, $7)";
  $result = pg_query_params($conexion, $query, array($nombre, $direccion, $telefono, $fecha_nac, $sexo, $edad, $estatura));

  if ($result) {
    echo "<h3>Paciente registrado correctamente</h3>";
    echo "<a href='menu.php'>Volver al menú</a>";
  } else {
    echo "Error al registrar: " . pg_last_error($conexion);
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Insertar Paciente</title>
  <link rel="stylesheet" href="Styles/form.css">
</head>
<body>
  <div class="form-container">
    <h2>Registrar Nuevo Paciente</h2>
    <form method="POST">
      <input type="text" name="nombre" placeholder="Nombre completo" required>
      <input type="text" name="direccion" placeholder="Dirección" required>
      <input type="text" name="telefono" placeholder="Teléfono" required>
      <input type="date" name="fecha_nac" required>
      <input type="text" name="sexo" maxlength="1" placeholder="M/F" required>
      <input type="number" name="edad" placeholder="Edad" required>
      <input type="number" step="0.01" name="estatura" placeholder="Estatura (m)" required>
      <button type="submit">Guardar Paciente</button>
    </form>
  </div>
</body>
</html>