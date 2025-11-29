<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'empleado'])) {
    header("Location: ../index.php");
    exit();
}

include("../conecta.php");

$codigo = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($codigo <= 0) {
    header("Location: consultar_medicamento.php?error=1");
    exit();
}

$resultado = pg_query_params($conexion, 
    "SELECT codigo, nombre, via_adm, presentacion, fecha_cad FROM medicamento WHERE codigo = $1", 
    [$codigo]
);

$medicamento = $resultado ? pg_fetch_assoc($resultado) : null;

if (!$medicamento) {
    pg_close($conexion);
    header("Location: consultar_medicamento.php?error=notfound");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Medicamento - Núcleo Diagnóstico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Styles/form.css">
</head>
<body class="theme-medicamentos">
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <div class="header-icon" style="background: linear-gradient(135deg, #f39c12 0%, #d68910 100%);">
                    <i class="fas fa-pills"></i>
                </div>
                <h2>Modificar Medicamento</h2>
                <p class="subtitle">Actualice la información del medicamento</p>
            </div>

            <form action="../Actions/editar_medicamento_action.php" method="post">
                <input type="hidden" name="codigo" value="<?= $medicamento['codigo'] ?>">
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-pills"></i> Nombre del medicamento <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-pills input-icon"></i>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?= htmlspecialchars($medicamento['nombre']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-syringe"></i> Vía de administración <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-syringe input-icon"></i>
                            <select name="via_adm" class="form-control" required>
                                <option value="Oral" <?= $medicamento['via_adm'] === 'Oral' ? 'selected' : '' ?>>Oral</option>
                                <option value="Intravenosa" <?= $medicamento['via_adm'] === 'Intravenosa' ? 'selected' : '' ?>>Intravenosa</option>
                                <option value="Intramuscular" <?= $medicamento['via_adm'] === 'Intramuscular' ? 'selected' : '' ?>>Intramuscular</option>
                                <option value="Subcutánea" <?= $medicamento['via_adm'] === 'Subcutánea' ? 'selected' : '' ?>>Subcutánea</option>
                                <option value="Tópica" <?= $medicamento['via_adm'] === 'Tópica' ? 'selected' : '' ?>>Tópica</option>
                                <option value="Inhalatoria" <?= $medicamento['via_adm'] === 'Inhalatoria' ? 'selected' : '' ?>>Inhalatoria</option>
                                <option value="Rectal" <?= $medicamento['via_adm'] === 'Rectal' ? 'selected' : '' ?>>Rectal</option>
                                <option value="Oftálmica" <?= $medicamento['via_adm'] === 'Oftálmica' ? 'selected' : '' ?>>Oftálmica</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-box"></i> Presentación <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-box input-icon"></i>
                            <input type="text" name="presentacion" class="form-control" 
                                   value="<?= htmlspecialchars($medicamento['presentacion']) ?>" 
                                   placeholder="Ej: Tabletas 500mg" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar-times"></i> Fecha de caducidad <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar-times input-icon"></i>
                            <input type="date" name="fecha_cad" class="form-control" 
                                   value="<?= htmlspecialchars($medicamento['fecha_cad']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #f39c12 0%, #d68910 100%);">
                        <i class="fas fa-save"></i>
                        <span>Guardar Cambios</span>
                    </button>
                    <a href="consultar_medicamento.php" class="btn btn-secondary">
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
