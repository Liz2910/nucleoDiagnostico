<?php
$host = "localhost";
$port = "5432";
$dbname = "nucleodiagnotico";
$user = "postgres";
$password = "12345"; // contraseña para acceder a postgresql

$conexion = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");