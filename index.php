<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
session_start();

require_once 'conecta.php';  // Debe definir $conexion = pg_connect(...)

$error = ''; // ← en GET queda vacío y NO se muestra nada

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Leer y sanear
    $codigo = isset($_POST['codigo']) ? (int)$_POST['codigo'] : 0;
    $contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

    // 2) Validación mínima
    if ($codigo === 0 || $contrasena === '') {
        $error = 'Por favor ingresa tu usuario y contraseña.';
    } else {
        // 3) Buscar usuario por codigo (serial integer)
        $sql  = 'SELECT codigo, nombre, contrasena FROM empleados WHERE codigo = $1';
        $res  = pg_query_params($conexion, $sql, [$codigo]);

        if ($res && pg_num_rows($res) === 1) {
            $row = pg_fetch_assoc($res);

            // 4) Comparar contraseña (si actualmente la guardas en texto plano)
            //    Si más adelante usas password_hash(), cámbialo por password_verify()
            if (trim((string)$row['contrasena']) === $contrasena) {
                $_SESSION['codigo'] = $row['codigo'];
                $_SESSION['nombre'] = $row['nombre'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'Usuario no encontrado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="Styles/index.css">
</head>
<body>
  <div class="login-wrapper">
    <div class="login-box">
      <h2>Iniciar Sesión</h2>

      <form method="post" action="">
        <label for="codigo">Código:</label>
        <input id="codigo" name="codigo" type="text"
               value="<?= htmlspecialchars($_POST['codigo'] ?? '') ?>" required>

        <label for="contrasena">Contraseña:</label>
        <input id="contrasena" name="contrasena" type="password" required>

        <button type="submit">Entrar</button>

        <?php if ($error !== ''): ?>
          <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
      </form>
    </div>
  </div>
</body>
</html>