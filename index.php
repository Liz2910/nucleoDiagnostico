<!DOCTYPE html>
<!-- login del administrador -->
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - Nucleo Diagnóstico</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h2>Acceso Administrador</h2>
    <form action="menu.php" method="post">
      <label>Usuario:</label>
      <input type="text" name="usuario" required>
      <label>Contraseña:</label>
      <input type="password" name="contrasena" required>
      <button type="submit">Entrar</button>
    </form>
  </div>
</body>
</html>