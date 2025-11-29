<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$codigo = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($codigo <= 0) {
    header("Location: consultar_empleados.php?error=1");
    exit();
}

$resultado = pg_query_params($conexion, 
    "SELECT codigo, nombre, direccion, telefono, fecha_nac, sexo, sueldo, turno FROM empleado WHERE codigo = $1", 
    [$codigo]
);

$empleado = $resultado ? pg_fetch_assoc($resultado) : null;

if (!$empleado) {
    pg_close($conexion);
    header("Location: consultar_empleados.php?error=notfound");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Empleado - Núcleo Diagnóstico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Styles/form.css">
</head>
<body class="theme-empleados">
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <div class="header-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h2>Modificar Empleado</h2>
                <p class="subtitle">Actualice la información del empleado</p>
            </div>

            <form action="../Actions/editar_empleado_action.php" method="post">
                <input type="hidden" name="codigo" value="<?= $empleado['codigo'] ?>">
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Nombre completo <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?= htmlspecialchars($empleado['nombre']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Dirección <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <input type="text" name="direccion" class="form-control" 
                                   value="<?= htmlspecialchars($empleado['direccion']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i> Teléfono <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" name="telefono" class="form-control" 
                                   value="<?= htmlspecialchars($empleado['telefono']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar"></i> Fecha de nacimiento <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar input-icon"></i>
                            <input type="date" name="fecha_nac" class="form-control" 
                                   value="<?= htmlspecialchars($empleado['fecha_nac']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-venus-mars"></i> Sexo <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-venus-mars input-icon"></i>
                            <select name="sexo" class="form-control" required>
                                <option value="M" <?= $empleado['sexo'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= $empleado['sexo'] === 'F' ? 'selected' : '' ?>>Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-dollar-sign"></i> Sueldo <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-dollar-sign input-icon"></i>
                            <input type="number" name="sueldo" class="form-control" step="0.01" min="0"
                                   value="<?= htmlspecialchars($empleado['sueldo']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-clock"></i> Turno <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-clock input-icon"></i>
                            <select name="turno" class="form-control" required>
                                <option value="Matutino" <?= $empleado['turno'] === 'Matutino' ? 'selected' : '' ?>>Matutino</option>
                                <option value="Vespertino" <?= $empleado['turno'] === 'Vespertino' ? 'selected' : '' ?>>Vespertino</option>
                                <option value="Nocturno" <?= $empleado['turno'] === 'Nocturno' ? 'selected' : '' ?>>Nocturno</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock"></i> Nueva contraseña (opcional)
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="contrasena" class="form-control" 
                                   placeholder="Dejar vacío para no cambiar">
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span>Guardar Cambios</span>
                    </button>
                    <a href="consultar_empleados.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        <span>Cancelar</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php pg_close($conexion); ?>
</body>
</html>
