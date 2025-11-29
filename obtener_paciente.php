<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']) || !isset($_SESSION['codigo'])) {
    echo json_encode(['ok'=>false,'error'=>'unauthorized']); exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['ok'=>false,'error'=>'invalid_id']); exit();
}

require_once __DIR__.'/conecta.php';

$res = pg_query_params($conexion,
  "SELECT codigo, nombre, direccion, telefono, fecha_nac, sexo, edad, estatura
   FROM paciente WHERE codigo = $1 LIMIT 1", [$id]);

if ($res && pg_num_rows($res) === 1) {
    $data = pg_fetch_assoc($res);
    echo json_encode(['ok'=>true,'data'=>$data]);
} else {
    echo json_encode(['ok'=>false,'error'=>'not_found']);
}

pg_close($conexion);
