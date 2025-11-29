<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$query = "SELECT * FROM doctor ORDER BY codigo ASC";
$resultado = pg_query($conexion, $query);
$isAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consulta General de Doctores</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../Styles/consultas.css">
</head>
<body class="theme-doctores">
  <div class="container">
    <h2>Consulta General de Doctores</h2>
    <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th><i class="fas fa-hashtag"></i> Código</th>
          <th><i class="fas fa-user"></i> Nombre</th>
          <th><i class="fas fa-map-marker-alt"></i> Dirección</th>
          <th><i class="fas fa-phone"></i> Teléfono</th>
          <th><i class="fas fa-stethoscope"></i> Especialidad</th>
          <th><i class="fas fa-calendar"></i> Fecha Nac.</th>
          <th><i class="fas fa-venus-mars"></i> Sexo</th>
          <?php if ($isAdmin): ?><th><i class="fas fa-cogs"></i> Acciones</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($resultado && pg_num_rows($resultado) > 0) {
          while ($row = pg_fetch_assoc($resultado)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['codigo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($row['direccion']) . "</td>";
            echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
            echo "<td>" . htmlspecialchars($row['especialidad']) . "</td>";
            echo "<td>" . htmlspecialchars($row['fecha_nac']) . "</td>";
            echo "<td>" . htmlspecialchars($row['sexo']) . "</td>";
            if ($isAdmin) {
              echo "<td class='actions-cell'>
                      <a href='ver_doctor.php?id=" . $row['codigo'] . "' class='btn-action btn-view' title='Ver'><i class='fas fa-eye'></i></a>
                      <a href='editar_doctor.php?id=" . $row['codigo'] . "' class='btn-action btn-edit' title='Editar'><i class='fas fa-edit'></i></a>
                      <a href='../Actions/eliminar_doctor.php?id=" . $row['codigo'] . "' class='btn-action btn-delete' title='Eliminar' onclick='return confirm(\"¿Eliminar este doctor?\");'><i class='fas fa-trash'></i></a>
                    </td>";
            }
            echo "</tr>";
          }
        } else {
          $cols = $isAdmin ? 8 : 7;
          echo "<tr><td colspan='$cols'>No hay doctores registrados</td></tr>";
        }
        pg_close($conexion);
        ?>
      </tbody>
    </table>
    </div>

    <div class="btn-container">
      <a href="../menu.php" class="back-btn">Volver al menú</a>
    </div>
  </div>
</body>
</html>