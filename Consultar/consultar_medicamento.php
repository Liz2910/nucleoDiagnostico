<?php
// Proteger la página - verificar sesión
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");
$resultado = pg_query($conexion, "SELECT codigo, nombre, via_adm, presentacion, fecha_cad FROM medicamento ORDER BY codigo ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Pacientes - Nucleo Diagnóstico</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../Styles/consultas.css">
</head>
<body class="theme-medicamentos">
  <div class="container">
    <h2>Inventario de medicamentos</h2>

    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th><i class="fas fa-hashtag"></i> Código</th>
            <th><i class="fas fa-prescription-bottle-alt"></i> Nombre</th>
            <th><i class="fas fas fa-syringe"></i> Vía Administración</th>
            <th><i class="fas fa-pills"></i> Presentación</th>
            <th><i class="fas fa-calendar-times"></i> Fecha Caducidad</th>
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
              echo "<td>" . htmlspecialchars($fila['via_adm']) . "</td>";
              echo "<td>" . htmlspecialchars($fila['presentacion']) . "</td>";
              echo "<td>" . htmlspecialchars($fila['fecha_cad']) . "</td>";
              echo "<td class='actions-cell'>
                      <a href='ver_medicamento.php?id=" . $fila['codigo'] . "' class='btn-action btn-view' title='Ver'><i class='fas fa-eye'></i></a>
                      <a href='editar_medicamento.php?id=" . $fila['codigo'] . "' class='btn-action btn-edit' title='Editar'><i class='fas fa-edit'></i></a>
                      <a href='../Actions/eliminar_medicamento.php?id=" . $fila['codigo'] . "' class='btn-action btn-delete' title='Eliminar' onclick='return confirm(\"¿Eliminar este medicamento?\");'><i class='fas fa-trash'></i></a>
                    </td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='6'>No hay medicamentos en el inventario</td></tr>";
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