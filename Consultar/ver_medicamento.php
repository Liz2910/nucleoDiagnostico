<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'empleado'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$codigo = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($codigo <= 0) {
    header("Location: consultar_medicamento.php?error=1");
    exit();
}

$resultado = pg_query_params($conexion, 
    "SELECT codigo, nombre, via_adm, presentacion, fecha_cad FROM medicamento WHERE codigo = $1", 
    [$codigo]
);

$medicamento = $resultado ? pg_fetch_assoc($resultado) : null;
pg_close($conexion);

if (!$medicamento) {
    header("Location: consultar_medicamento.php?error=notfound");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Medicamento - Núcleo Diagnóstico</title>
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
            color: #f39c12;
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
            width: 180px;
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
<body class="theme-medicamentos">
    <div class="container">
        <div class="detail-card">
            <div class="detail-header">
                <i class="fas fa-pills"></i>
                <h2><?= htmlspecialchars($medicamento['nombre']) ?></h2>
            </div>
            
            <ul class="detail-list">
                <li>
                    <span class="label"><i class="fas fa-id-badge"></i> Código:</span>
                    <span class="value"><?= htmlspecialchars($medicamento['codigo']) ?></span>
                </li>
                <li>
                    <span class="label"><i class="fas fa-syringe"></i> Vía de administración:</span>
                    <span class="value"><?= htmlspecialchars($medicamento['via_adm']) ?></span>
                </li>
                <li>
                    <span class="label"><i class="fas fa-box"></i> Presentación:</span>
                    <span class="value"><?= htmlspecialchars($medicamento['presentacion']) ?></span>
                </li>
                <li>
                    <span class="label"><i class="fas fa-calendar-times"></i> Fecha de caducidad:</span>
                    <span class="value"><?= htmlspecialchars($medicamento['fecha_cad']) ?></span>
                </li>
            </ul>
            
            <div class="action-buttons">
                <a href="editar_medicamento.php?id=<?= $medicamento['codigo'] ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i> Modificar
                </a>
                <a href="../Actions/eliminar_medicamento.php?id=<?= $medicamento['codigo'] ?>" class="btn btn-delete" 
                   onclick="return confirm('¿Está seguro de eliminar este medicamento?');">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
                <a href="consultar_medicamento.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</body>
</html>
