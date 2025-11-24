<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$cita_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($cita_id <= 0) {
    header("Location: ../consultar_citas.php?error=id");
    exit();
}

// Intentar eliminar la cita
$query = "DELETE FROM citas WHERE id_cita = $cita_id";
$success = pg_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita eliminada</title>

    <link rel="stylesheet" href="../Styles/resultado.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="theme-citas">

<div class="result-container">
    <div class="result-card">

        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <h2 class="result-title">Cita Eliminada Correctamente</h2>
        <p class="result-message">La cita fue removida exitosamente del sistema.</p>

        <div class="info-box">
            <p>
                <i class="fas fa-info-circle"></i>
                <span>El registro ya no aparecerá en la lista de citas.</span>
            </p>
        </div>

        <div class="button-group">
            <a href="../consultar_citas.php" class="btn btn-primary">
                <i class="fas fa-list"></i> Volver a la lista
            </a>
            <a href="../menu.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Volver al menú
            </a>
        </div>

    </div>
</div>

</body>
</html>