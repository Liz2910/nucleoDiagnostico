<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'empleado'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$codigo = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($codigo <= 0) {
    header("Location: consultar_pacientes.php?error=1");
    exit();
}

$resultado = pg_query_params($conexion, 
    "SELECT codigo, nombre, direccion, telefono, fecha_nac, sexo, edad, estatura FROM paciente WHERE codigo = $1", 
    [$codigo]
);

$paciente = $resultado ? pg_fetch_assoc($resultado) : null;

if (!$paciente) {
    pg_close($conexion);
    header("Location: consultar_pacientes.php?error=notfound");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Paciente - Núcleo Diagnóstico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Styles/form.css">
</head>
<body class="theme-pacientes">
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <div class="header-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                    <i class="fas fa-user-injured"></i>
                </div>
                <h2>Modificar Paciente</h2>
                <p class="subtitle">Actualice la información del paciente</p>
            </div>

            <form action="../Actions/editar_paciente_action.php" method="post">
                <input type="hidden" name="codigo" value="<?= $paciente['codigo'] ?>">
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Nombre completo <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?= htmlspecialchars($paciente['nombre']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Dirección <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <input type="text" name="direccion" class="form-control" 
                                   value="<?= htmlspecialchars($paciente['direccion']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i> Teléfono <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" name="telefono" class="form-control" 
                                   value="<?= htmlspecialchars($paciente['telefono']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar"></i> Fecha de nacimiento <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar input-icon"></i>
                            <input type="date" name="fecha_nac" class="form-control" 
                                   value="<?= htmlspecialchars($paciente['fecha_nac']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-venus-mars"></i> Sexo <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-venus-mars input-icon"></i>
                            <select name="sexo" class="form-control" required>
                                <option value="M" <?= $paciente['sexo'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= $paciente['sexo'] === 'F' ? 'selected' : '' ?>>Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user-clock"></i> Edad <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-user-clock input-icon"></i>
                            <input type="number" name="edad" class="form-control" min="0" max="150"
                                   value="<?= htmlspecialchars($paciente['edad']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-ruler-vertical"></i> Estatura (metros) <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-ruler-vertical input-icon"></i>
                            <input type="number" name="estatura" class="form-control" step="0.01" min="0" max="3"
                                   value="<?= htmlspecialchars($paciente['estatura']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <i class="fas fa-save"></i>
                        <span>Guardar Cambios</span>
                    </button>
                    <a href="consultar_pacientes.php" class="btn btn-secondary">
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
