<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$codigo = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($codigo <= 0) {
    header("Location: consultar_empleados.php?error=1");
    exit();
}

$resultado = pg_query_params($conexion, 
    "SELECT codigo, nombre, direccion, telefono, fecha_nac, sexo, sueldo, turno FROM empleado WHERE codigo = $1", 
    [$codigo]
);

$empleado = $resultado ? pg_fetch_assoc($resultado) : null;
pg_close($conexion);

if (!$empleado) {
    header("Location: consultar_empleados.php?error=notfound");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Empleado - Núcleo Diagnóstico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Styles/consultas.css">
    <style>
        .detail-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .detail-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .detail-header i {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 10px;
        }
        .detail-header h2 {
            color: #2c3e50;
            margin: 0;
        }
        .detail-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .detail-list li {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-list li:last-child {
            border-bottom: none;
        }
        .detail-list .label {
            font-weight: 600;
            color: #555;
            width: 150px;
            flex-shrink: 0;
        }
        .detail-list .value {
            color: #2c3e50;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .btn-edit { background: #f39c12; color: white; }
        .btn-edit:hover { background: #d68910; }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-delete:hover { background: #c0392b; }
        .btn-back { background: #95a5a6; color: white; }
        .btn-back:hover { background: #7f8c8d; }
    </style>
</head>
<body class="theme-empleados">
    <div class="container">
        <div class="detail-card">
            <div class="detail-header">
                <i class="fas fa-user-tie"></i>
                <h2><?= htmlspecialchars($empleado['nombre']) ?></h2>
            </div>
            
            <ul class="detail-list">
                <li>
                    <span class="label"><i class="fas fa-id-badge"></i> Código:</span>
                    <span class="value"><?= htmlspecialchars($empleado['codigo']) ?></span>
                </li>
                <li>
                    <span class="label"><i class="fas fa-map-marker-alt"></i> Dirección:</span>
                    <span class="value"><?= htmlspecialchars($empleado['direccion']) ?></span>
                </li>
                <li>
                    <span class="label"><i class="fas fa-phone"></i> Teléfono:</span>
                    <span class="value"><?= htmlspecialchars($empleado['telefono']) ?></span>
                </li>
                <li>
                    <span class="label"><i class="fas fa-calendar"></i> Fecha Nac.:</span>
                    <span class="value"><?= htmlspecialchars($empleado['fecha_nac']) ?></span>
                </li>
                <li>
                    <span class="label"><i class="fas fa-venus-mars"></i> Sexo:</span>
                    <span class="value"><?= $empleado['sexo'] === 'M' ? 'Masculino' : 'Femenino' ?></span>
                </li>
                <li>
                    <span class="label"><i class="fas fa-dollar-sign"></i> Sueldo:</span>
                    <span class="value">$<?= number_format($empleado['sueldo'], 2) ?></span>
                </li>
                <li>
                    <span class="label"><i class="fas fa-clock"></i> Turno:</span>
                    <span class="value"><?= htmlspecialchars($empleado['turno']) ?></span>
                </li>
            </ul>
            
            <div class="action-buttons">
                <a href="editar_empleado.php?id=<?= $empleado['codigo'] ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i> Modificar
                </a>
                <a href="../Actions/eliminar_empleado.php?id=<?= $empleado['codigo'] ?>" class="btn btn-delete" 
                   onclick="return confirm('¿Está seguro de eliminar este empleado?');">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
                <a href="consultar_empleados.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</body>
</html>
