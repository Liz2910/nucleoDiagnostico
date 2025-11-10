<?php
include("conecta.php");
$resultado = pg_query($conexion, "SELECT codigo, nombre, direccion, telefono, fecha_nac, sexo, sueldo, turno FROM empleado ORDER BY codigo ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consultas Generales - Empleadas</title>
  <link rel="stylesheet" href="Styles\consultar.css">
</head>
<body>
  <div class="container">
    <div class="card">
      <h2>Lista de Empleadas Registradas</h2>

      <table>
        <thead>
          <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Fecha de Nacimiento</th>
            <th>Sexo</th>
            <th>Sueldo</th>
            <th>Turno</th>
          </tr>
        </thead>
        <tbody>
          <?php
          while ($fila = pg_fetch_assoc($resultado)) {
              echo "<tr>";
              echo "<td>" . $fila['codigo'] . "</td>";
              echo "<td>" . $fila['nombre'] . "</td>";
              echo "<td>" . $fila['direccion'] . "</td>";
              echo "<td>" . $fila['telefono'] . "</td>";
              echo "<td>" . $fila['fecha_nac'] . "</td>";
              echo "<td>" . $fila['sexo'] . "</td>";
              echo "<td>$" . number_format($fila['sueldo'], 2) . "</td>";
              echo "<td>" . $fila['turno'] . "</td>";
              echo "</tr>";
          }
          pg_close($conexion);
          ?>
        </tbody>
      </table>

      <div class="btn-container">
        <a href="menu.php" class="back-btn">Volver al Menú</a>
      </div>
    </div>
  </div>
</body>
</html>