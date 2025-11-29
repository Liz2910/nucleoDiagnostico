<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$codigoEntrada = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

if ($codigoEntrada === '' || $contrasena === '') {
    header("Location: ../index.php?error=empty");
    exit();
}

if (!ctype_digit($codigoEntrada)) {
    header("Location: ../index.php?error=invalid");
    exit();
}

include("../conecta.php");
$codigo = intval($codigoEntrada);

function matchesPassword(string $plain, string $stored): bool
{
    $info = password_get_info($stored);
    if (!empty($info['algo'])) {
        return password_verify($plain, $stored);
    }
    return hash_equals($stored, $plain);
}

function fetchRow($conexion, string $sql): ?array
{
    $result = pg_query($conexion, $sql);
    return ($result && pg_num_rows($result) > 0) ? pg_fetch_assoc($result) : null;
}

// Admin
$admin = fetchRow($conexion, "SELECT codigo, contrasena FROM admini WHERE codigo = $codigo LIMIT 1");
if ($admin && matchesPassword($contrasena, $admin['contrasena'])) {
    $_SESSION['rol'] = 'admin';
    $_SESSION['codigo'] = $admin['codigo'];
    $_SESSION['usuario'] = 'Administrador';
    pg_close($conexion);
    header("Location: ../menu.php");
    exit();
}

// Doctor
$doctor = fetchRow($conexion, "SELECT codigo, nombre, contrasena FROM doctor WHERE codigo = $codigo LIMIT 1");
if ($doctor && matchesPassword($contrasena, $doctor['contrasena'])) {
    $_SESSION['rol'] = 'doctor';
    $_SESSION['codigo'] = $doctor['codigo'];
    $_SESSION['usuario'] = $doctor['nombre'];
    $_SESSION['doctor_codigo'] = $doctor['codigo'];
    $_SESSION['doctor_nombre'] = $doctor['nombre'];
    pg_close($conexion);
    header("Location: ../doctor_menu.php");
    exit();
}

// Empleado
$empleado = fetchRow($conexion, "SELECT codigo, nombre, contrasena FROM empleado WHERE codigo = $codigo LIMIT 1");
if ($empleado && matchesPassword($contrasena, $empleado['contrasena'])) {
    $_SESSION['rol'] = 'empleado';
    $_SESSION['codigo'] = $empleado['codigo'];
    $_SESSION['usuario'] = $empleado['nombre'];
    pg_close($conexion);
    header("Location: ../empleado_portal.php");
    exit();
}

pg_close($conexion);
header("Location: ../index.php?error=invalid");
exit();
