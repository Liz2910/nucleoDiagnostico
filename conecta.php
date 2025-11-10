<?php
$host = "localhost";
$port = "5432";
$dbname = "nucleodiagnotico";
$user = "postgres";
$password = "12345"; // ccontraseña para acceder a postgresql

$conexion = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conexion) {
    die("Error al conectar con PostgreSQL: " . pg_last_error());
} else {
    echo "Conexión exitosa con PostgreSQL";
}
?>