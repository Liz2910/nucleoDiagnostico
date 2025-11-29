<?php
// Proteger la página - verificar sesión
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");
$resultado = pg_query($conexion, "SELECT codigo, nombre, direccion, telefono, fecha_nac, sexo, sueldo, turno FROM empleado ORDER BY codigo ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Empleados - Nucleo Diagnóstico</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../Styles/consultas.css">
</head>
<body class="theme-empleados">
  <div class="container">
    <h2>Lista de Empleados Registrados</h2>

    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th><i class="fas fa-hashtag"></i> Código</th>
            <th><i class="fas fa-user"></i> Nombre</th>
            <th><i class="fas fa-map-marker-alt"></i> Dirección</th>
            <th><i class="fas fa-phone"></i> Teléfono</th>
            <th><i class="fas fa-calendar"></i> Fecha Nac.</th>
            <th><i class="fas fa-venus-mars"></i> Sexo</th>
            <th><i class="fas fa-dollar-sign"></i> Sueldo</th>
            <th><i class="fas fa-clock"></i> Turno</th>
            <th><i class="fas fa-cogs"></i> Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($resultado && pg_num_rows($resultado) > 0) {
            while ($fila = pg_fetch_assoc($resultado)) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($fila['codigo']) . "</td>";
              echo "<td>" . htmlspecialchars($fila['nombre']) . "</td>";
              echo "<td>" . htmlspecialchars($fila['direccion']) . "</td>";
              echo "<td>" . htmlspecialchars($fila['telefono']) . "</td>";
              echo "<td>" . htmlspecialchars($fila['fecha_nac']) . "</td>";
              echo "<td>" . htmlspecialchars($fila['sexo']) . "</td>";
              echo "<td>$" . number_format($fila['sueldo'], 2) . "</td>";
              echo "<td>" . htmlspecialchars($fila['turno']) . "</td>";
              echo "<td class='actions-cell'>
                      <a href='ver_empleado.php?id=" . $fila['codigo'] . "' class='btn-action btn-view' title='Ver'><i class='fas fa-eye'></i></a>
                      <a href='editar_empleado.php?id=" . $fila['codigo'] . "' class='btn-action btn-edit' title='Editar'><i class='fas fa-edit'></i></a>
                      <a href='../Actions/eliminar_empleado.php?id=" . $fila['codigo'] . "' class='btn-action btn-delete' title='Eliminar' onclick='return confirm(\"¿Eliminar este empleado?\");'><i class='fas fa-trash'></i></a>
                    </td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='9'>No hay empleados registrados</td></tr>";
          }
          pg_close($conexion);
          ?>
        </tbody>
      </table>
    </div>

    <div class="btn-container">
      <a href="../menu.php" class="back-btn">
        <span>Volver al Menú</span>
      </a>
    </div>
  </div>
</body>
</html>