<?php
include("conecta.php");

$nombre = $_POST['nombre'];
$direccion = $_POST['direccion'];
$telefono = $_POST['telefono'];
$sexo = $_POST['sexo'];
$sueldo = $_POST['sueldo'];
$turno = $_POST['turno'];
$contrasena = $_POST['contrasena'];

$query = "INSERT INTO empleado 
    (nombre, direccion, telefono, sexo, sueldo, turno, fecha_nac, contrasena)

$resultado = pg_query($conexion, $query);

if ($resultado) {
    echo "<h3>Empleada insertada correctamente</h3>";
    echo "<a href='menu.php'>Volver al menú</a>";
} else {
    echo "Error al insertar: " . pg_last_error($conexion);
}
pg_close($conexion);
?>