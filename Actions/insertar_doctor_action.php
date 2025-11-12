<?php
include("../conecta.php");

$nombre = $_POST['nombre'];
$direccion = $_POST['direccion'];
$telefono = $_POST['telefono'];
$fecha_nac = $_POST['fecha_nacimiento'];
$sexo = $_POST['sexo'];
$sueldo = $_POST['sueldo'];
$turno = $_POST['turno'];
$contrasena = $_POST['contrasena'];

$query = "INSERT INTO empleado (nombre, direccion, telefono, fecha_nac, sexo, sueldo, turno, contrasena)
VALUES ('$nombre', '$direccion', '$telefono', '$fecha_nac', '$sexo', $sueldo, '$turno', '$contrasena')";
$resultado = pg_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Resultado - Empleada</title>
  <link rel="stylesheet" href="../Styles/form.css">
</head>
<body>
  <div class="card msg">
    <?php
    if ($resultado) {
      echo "<h2>Empleada insertada correctamente</h2>";
    } else {
      echo "<h2 class='error'>Error al insertar</h2>";
      echo "<p class='error'>" . pg_last_error($conexion) . "</p>";
    }
    pg_close($conexion);
    ?>
    <a href="../menu.php" class="back-btn">Volver al menú</a>
  </div>
</body>
</html>