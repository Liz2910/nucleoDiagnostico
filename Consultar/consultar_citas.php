<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");
date_default_timezone_set('America/Mexico_City'); // <-- Asegura fecha/hora correctas

$hoy = date("Y-m-d");

$query = "
    SELECT 
        c.id_cita,
        p.nombre AS paciente,
        d.nombre AS doctor,
        c.fecha,
        c.hora
    FROM citas c
    JOIN paciente p ON c.id_paciente = p.codigo
    JOIN doctor d ON c.id_doctor = d.codigo
    ORDER BY c.fecha DESC, c.hora DESC
";
$resultado = pg_query($conexion, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consultar Citas</title>
    <link rel="stylesheet" href="../Styles/consultas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .action-btn {
            padding: 6px 14px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s ease;
            font-size: 0.85rem;
        }
        .edit-btn { background: #3498db; margin-right: 6px; }
        .edit-btn:hover { background: #2c81ba; }

        .delete-btn { background: #e74c3c; }
        .delete-btn:hover { background: #c0392b; }
    </style>
</head>

<body class="theme-citas">

<div class="container">

    <h2>Listado de Citas</h2>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID Cita</th>
                    <th>Paciente</th>
                    <th>Doctor</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!$resultado) {
                    echo "<tr><td colspan='6'>Error al consultar citas.</td></tr>";
                } elseif (pg_num_rows($resultado) === 0) {
                    echo "<tr><td colspan='6'>No hay citas registradas.</td></tr>";
                } else {
                    $now = time();
                    while ($row = pg_fetch_assoc($resultado)) {

                        $inicioTs = strtotime($row['fecha'].' '.substr($row['hora'],0,5).':00');
                        $finTs    = $inicioTs + 3600; // cada cita dura 1h

                        echo "<tr>
                                <td>{$row['id_cita']}</td>
                                <td>{$row['paciente']}</td>
                                <td>{$row['doctor']}</td>
                                <td>{$row['fecha']}</td>
                                <td>{$row['hora']}</td>
                                <td>";

                        // Mostrar Editar solo si la cita no ha terminado
                        if ($now < $finTs) {
                            echo "
                                <a class='action-btn edit-btn' href='../editar_cita.php?id={$row['id_cita']}'>
                                    <i class='fas fa-edit'></i> Editar
                                </a>";
                        }

                        echo "
                            <a class='action-btn delete-btn' href='../Actions/cancelar_cita.php?id={$row['id_cita']}'>
                                <i class='fas fa-trash'></i> Eliminar
                            </a>
                        </td>
                        </tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="btn-container">
        <a href="../menu.php" class="back-btn">Volver al menú</a>
    </div>

</div>

<?php pg_close($conexion); ?>
</body>
</html>