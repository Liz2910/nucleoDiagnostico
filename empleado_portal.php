<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: index.php");
    exit();
}

$nombre = htmlspecialchars($_SESSION['usuario']);
$codigo = htmlspecialchars($_SESSION['codigo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Personal - La salud es primero</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {font-family: 'Poppins',sans-serif;background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:30px;color:#123044;}
        .card {width:100%;max-width:960px;background:#fff;border-radius:28px;padding:36px;box-shadow:0 20px 60px rgba(0,0,0,.2);}
        .header {display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:18px;}
        .badge {padding:10px 16px;border-radius:14px;background:#eef2ff;font-weight:600;color:#3949ab;}
        .grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-top:28px;}
        .tile {border-radius:18px;border:2px dashed rgba(57,73,171,.25);padding:18px;text-decoration:none;color:#3949ab;font-weight:600;display:flex;flex-direction:column;gap:6px;transition:.2s;}
        .tile:hover {border-color:#3949ab;background:rgba(57,73,171,.08);}
        .tile i {font-size:1.4rem;}
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <div>
            <p style="margin:0;color:#6c7a89;">La salud es primero</p>
            <h1 style="margin:4px 0 0;">Hola, <?= $nombre; ?></h1>
            <p>Gestiona pacientes, citas y diagnósticos desde aquí.</p>
        </div>
        <span class="badge"><i class="fas fa-id-badge"></i> Código <?= $codigo; ?></span>
    </div>

    <div class="grid">
        <a class="tile" href="Insertar/insertar_paciente.php"><i class="fas fa-user-plus"></i> Registrar paciente</a>
        <a class="tile" href="Consultar/consultar_pacientes.php"><i class="fas fa-users"></i> Ver pacientes</a>
        <a class="tile" href="Insertar/insertar_cita.php"><i class="fas fa-calendar-plus"></i> Agendar cita</a>
        <a class="tile" href="Consultar/consultar_citas.php"><i class="fas fa-calendar-check"></i> Consultar citas</a>
        <a class="tile" href="Consultar/disponibilidad.php"><i class="fas fa-clock"></i> Disponibilidad doctores</a>
        <a class="tile" href="Consultar/consultar_diagnosticos.php"><i class="fas fa-file-medical"></i> Ver diagnósticos PDF</a>
        <a class="tile" href="Consultar/consultar_empleados.php"><i class="fas fa-user-tie"></i> Empleados</a>
        <a class="tile" href="Consultar/consultar_doctores.php"><i class="fas fa-user-md"></i> Doctores</a>
        <a class="tile" href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </div>
</div>
</body>
</html>
