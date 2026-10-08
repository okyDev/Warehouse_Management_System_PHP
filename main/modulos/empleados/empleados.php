<?php
require_once '../config/connect.php';

$base = new MYSQL;
////////////// TABLAS
$empleados = $base->query_all("
    SELECT E.*, T.codigo, T.tipo 
    FROM empleados_datos E 
    JOIN tipos_identificacion T ON E.id_tipo = T.id 
    ORDER BY E.cedula
");
$tipos_id = $base->query_all("SELECT * FROM tipos_identificacion");
$nuevoU = $base->query_all("SELECT * FROM empleados_datos LEFT JOIN usuarios_acceso U ON empleados_datos.cedula = U.cedula_empleado WHERE U.cedula_empleado IS NULL");
$roles = $base->query_all("SELECT * FROM roles WHERE activo = 1");

$path_img = '../assets/multimedia/';
//include "gestionar_empleado.php";
include "crear_empleado.php";
include "empleado_edit.php";
include "crear_usuario.php";
include "gestionar_empleado.php";

?>

<style>
.empleados-container {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.empleados-header {
    background: linear-gradient(135deg, #1e2a38, #2d3b4e);
    color: white;
    padding: 1.5rem;
    border-radius: 12px 12px 0 0;
}

.page-title {
    color: white;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.page-description {
    color: rgba(255,255,255,0.8);
    margin-bottom: 0;
}

.actions-container {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.btn-primary {
    background-color: #4361ee;
    border-color: #4361ee;
    border-radius: 8px;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #3a56d4;
    border-color: #3a56d4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.btn-outline-primary {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
}

.table-container {
    padding: 0;
}

.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 1rem 1.25rem;
    vertical-align: middle;
    border-bottom: 2px solid #e9ecef;
}

.table td {
    padding: 1rem 1.25rem;
    vertical-align: middle;
    border-color: #f1f3f4;
    transition: background-color 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(67, 97, 238, 0.04);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
    border-radius: 50px;
    font-weight: 500;
}

.employee-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4361ee, #3a56d4);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    margin-right: 12px;
}

.employee-info {
    display: flex;
    align-items: center;
}

.employee-name {
    font-weight: 600;
    color: #343a40;
    margin-bottom: 2px;
}

.employee-id {
    font-size: 0.8rem;
    color: #6c757d;
    font-family: 'Courier New', monospace;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    color: #6c757d;
}

.contact-item i {
    width: 16px;
    color: #4361ee;
}

.address-truncate {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-sm {
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-sm:hover {
    transform: translateY(-1px);
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #e0a800;
    color: #000;
}

.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #138496;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #5a6268;
}

.alert {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border-left: 4px solid #4361ee;
    text-align: center;
}

.stat-card.warning {
    border-left-color: #ffc107;
}

.stat-card.success {
    border-left-color: #28a745;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #343a40;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #dee2e6;
}

@media (max-width: 768px) {
    .actions-container {
        text-align: center;
    }
    
    .action-buttons {
        justify-content: center;
    }
    
    .employee-info {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .employee-avatar {
        margin-bottom: 8px;
        margin-right: 0;
    }
    
    .stats-container {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container-fluid py-3">
	
	<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
<i class="bi bi-check-circle-fill me-2"></i>
<?php echo $_SESSION['success']; ?>
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
<i class="bi bi-exclamation-triangle-fill me-2"></i>
<?php echo $_SESSION['error']; ?>
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>


    <div class="empleados-container">
        <!-- Header -->
        <div class="empleados-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="page-title"><i class="bi bi-people me-2"></i>Gestión de Empleados</h3>
                    <p class="page-description">Administre el personal y los accesos al sistema de almacén</p>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="stats-container" style="padding: 1.5rem 1.5rem 0;">
            <div class="stat-card">
                <div class="stat-value"><?= count($empleados) ?></div>
                <div class="stat-label">Total Empleados</div>
            </div>
            <div class="stat-card success">
                <div class="stat-value">
                    <?php
                    $con_usuarios = 0;
                    foreach ($empleados as $emp) {
                        $sql = "SELECT COUNT(*) as total FROM usuarios_acceso WHERE cedula_empleado = ?";
                        $stmt = $base->one_query($sql,$emp['cedula']);
                        $tiene_usuario = $stmt['total'];
                        if ($tiene_usuario) $con_usuarios++;
                    }
                    echo $con_usuarios;
                    ?>
                </div>
                <div class="stat-label">Con Acceso al Sistema</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-value"><?= count($empleados) - $con_usuarios ?></div>
                <div class="stat-label">Sin Acceso al Sistema</div>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Acciones -->
        <div class="actions-container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearEmpleado">
                        <i class="bi bi-person-plus me-1"></i> Nuevo Empleado
                    </button>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                        <i class="bi bi-person-check me-1"></i> Asignar Acceso
                    </button>
                </div>
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    <?= count($empleados) ?> empleados registrados
                </div>
            </div>
        </div>

<!-- Barra de Búsqueda -->
    <div class="mb-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Buscar empleados..." aria-label="Buscar empleados">
    </div>
   <!-----BARRA ENDS HERE -->
   
        <!-- Tabla -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Contacto</th>
                            <th>Dirección</th>
                            <th>Estado de Acceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($empleados)): ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="bi bi-people"></i>
                                    <h5 class="mt-2">No hay empleados registrados</h5>
                                    <p class="mb-0">Comience agregando el primer empleado</p>
                                </td>
                            </tr>
					<?php else: ?>
<?php foreach ($empleados as $emp): ?>
<?php 
$sql_usuario = "SELECT username, activo FROM usuarios_acceso WHERE cedula_empleado = ?";
$usuario = $base->one_query($sql_usuario,$emp['cedula']);

$iniciales = substr($emp['nombre1'], 0, 1) . substr($emp['apellido'], 0, 1);
?>
<tr data-ci="<?= $emp['cedula'] ?>" data-nombre="<?= htmlspecialchars($emp['nombre1']) ?>" data-usuario="<?= $usuario['username'] ?>">
<td>
<div class="employee-info">
<div class="employee-avatar">
<?= strtoupper($iniciales) ?>
</div>
<div>
<div class="employee-name">
<?= htmlspecialchars($emp['nombre1']) ?> 
<?= !empty($emp['nombre2']) ? htmlspecialchars($emp['nombre2']) : '' ?> 
<?= htmlspecialchars($emp['apellido']) ?>
</div>
<div class="employee-id">
<?= $emp['codigo'] ?>-<?= $emp['cedula'] ?>
</div>
</div>
</div>
</td>
<td>
<div class="contact-info">
<div class="contact-item">
<i class="bi bi-telephone"></i>
<?= $emp['telefono'] ?? 'No registrado' ?>
</div>
<div class="contact-item">
<i class="bi bi-envelope"></i>
<?= $emp['email'] ?? 'No registrado' ?>
</div>
</div>
</td>
<td>
<div class="address-truncate" title="<?= htmlspecialchars($emp['direccion'] ?? '') ?>">
<?= !empty($emp['direccion']) ? htmlspecialchars($emp['direccion']) : 'No registrada' ?>
</div>
</td>
<td>
<?php if ($usuario): ?>
<div class="d-flex align-items-center gap-2">
<span class="badge bg-<?= $usuario['activo'] ? 'success' : 'secondary' ?>">
<i class="bi bi-<?= $usuario['activo'] ? 'check-circle' : 'x-circle' ?> me-1"></i>
<?= $usuario['username'] ?>
</span>
<small class="text-muted">
(<?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>)
</small>
</div>
<?php else: ?>
<span class="badge bg-warning">
<i class="bi bi-exclamation-triangle me-1"></i>
Sin acceso
</span>
<?php endif; ?>
</td>
<td>
<div class="action-buttons">
<!-- Editar Empleado -->
<form method="POST" style="display: inline;" name="EDITPAUP">
<input type="hidden" name="cedula_editar" value="<?= $emp['cedula'] ?>">
<button type="submit" class="btn btn-sm btn-warning" name="EDITPAUP">
<i class="bi bi-pencil me-1"></i> Editar
</button>
</form>
                                        
<!-- Gestionar Usuario -->
<?php if (!$usuario): ?>
<button class="btn btn-sm btn-info" 
data-bs-toggle="modal" 
data-bs-target="#modalCrearUsuario">
<i class="bi bi-person-plus me-1"></i> Crear Acceso
</button>
<?php else: ?>
<form method="POST">
<input type="hidden" name="cedula_edita2r" value="<?= $emp['cedula'] ?>">
<button type="submit" class="btn btn-sm btn-secondary" name="EDITPRO">
<i class="bi bi-gear me-1"></i> Gestionar
</button>
</form>
<?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<script>
//// BUSQUEDA
$(document).ready(function() {
    $("#searchInput").on("keyup", function() {
        var inputValue = $(this).val().toLowerCase(); // Obtiene el valor ingresado
        $("table tbody tr").filter(function() {
            // Verifica si coincide con SKU, nombre, descripción o marca
            $(this).toggle(
                $(this).data("ci").toLowerCase().indexOf(inputValue) > -1 ||
                $(this).data("nombre").toLowerCase().indexOf(inputValue) > -1 ||
                $(this).data("usuario").toLowerCase().indexOf(inputValue) > -1 
            );
        });
    });
});


document.getElementById('imageInput')?.addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('pfp');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = "../assets/icons/user.png";
    }
});


// Configurar previsualización de imagen para editar
document.getElementById('imageInputEdit')?.addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('pfpEdit');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
