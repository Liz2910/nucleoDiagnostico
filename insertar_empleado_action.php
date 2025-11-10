<?php
include("conecta.php");

$nombre = $_POST['nombre'];
$direccion = $_POST['direccion'];
$telefono = $_POST['telefono'];
$fecha_nac = $_POST['fecha_nacimiento'];
$sexo = $_POST['sexo'];
$sueldo = $_POST['sueldo'];
$turno = $_POST['turno'];
$contrasena = $_POST['contrasena'];

$query = "INSERT INTO empleado 
(nombre, direccion, telefono, fecha_nac, sexo, sueldo, turno, contrasena)
VALUES 
('$nombre', '$direccion', '$telefono', '$fecha_nac', '$sexo', $sueldo, '$turno', '$contrasena')";

$resultado = pg_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Resultado de Inserción</title>
  <link rel="stylesheet" href="Styles\menu.css">
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background-color: #f2f5fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .card {
      background: white;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.08);
      text-align: center;
      width: 400px;
      animation: fadeIn 0.5s ease;
    }
    h3 {
      color: #004a98;
      font-size: 20px;
      margin-bottom: 15px;
    }
    a {
      text-decoration: none;
      color: white;
      background-color: #004a98;
      padding: 10px 20px;
      border-radius: 8px;
      transition: 0.3s;
      display: inline-block;
      margin-top: 10px;
    }
    a:hover {
      background-color: #003570;
      transform: scale(1.05);
    }
    .error {
      color: #d32f2f;
      font-weight: 500;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="card">
    <?php
    if ($resultado) {
        echo "<h3>Empleada insertada correctamente</h3>";
        echo "<a href='menu.php'>Volver al menú</a>";
    } else {
        echo "<h3 class='error'>Error al insertar</h3>";
        echo "<p class='error'>" . pg_last_error($conexion) . "</p>";
        echo "<a href='menu.php'>Intentar nuevamente</a>";
    }
    pg_close($conexion);
    ?>
  </div>
</body>
</html>