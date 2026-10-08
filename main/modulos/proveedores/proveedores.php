<?php 
require_once '../config/connect.php'; 
require_once '../config/auth.php';
$base = new MYSQL;

// 1. OBTENER TIPOS DE IDENTIFICACIÓN y PROVEEDORES
$tipos_id = $base->query_all("SELECT id, codigo, tipo FROM tipos_identificacion");
$proveedores = $base->query_all("SELECT P.*, T.codigo FROM proveedores P JOIN tipos_identificacion T ON P.id_tipo = T.id WHERE P.activo = TRUE");

$categorias = $base->query_all("SELECT * FROM categorias WHERE activo = 1");

// Función para obtener opciones de select (ej. para Tipos de ID)
function get_id_options($tipos, $selected_id = null) {
$options = '';
foreach ($tipos as $tipo) {
$selected = ($tipo['id'] == $selected_id) ? 'selected' : '';
$options .= "<option value='{$tipo['id']}' {$selected}>{$tipo['codigo']} - {$tipo['tipo']}</option>";
}
return $options;
}
$step_one = verify("ver_proveedores");


/////
if(verify("crear_proveedores") == true){
include('add_proveedor.php'); 
}
include('editar_proveedor.php');
?>

<style>
/* Estilos adicionales para mejorar la apariencia */
.card {
border: none;
border-radius: 12px;
box-shadow: 0 4px 12px rgba(0,0,0,0.05);
transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
transform: translateY(-2px);
box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

.table-responsive {
border-radius: 8px;
overflow: hidden;
}

.table th {
border-top: none;
font-weight: 600;
font-size: 0.85rem;
text-transform: uppercase;
letter-spacing: 0.5px;
color: #6c757d;
background-color: #f8f9fa;
padding: 12px 15px;
}

.table td {
padding: 12px 15px;
vertical-align: middle;
}

.btn {
border-radius: 6px;
font-weight: 500;
transition: all 0.2s ease;
}

.btn-primary {
background-color: #4361ee;
border-color: #4361ee;
}

.btn-primary:hover {
background-color: #3a56d4;
border-color: #3a56d4;
transform: translateY(-1px);
}

.btn-sm {
padding: 0.375rem 0.75rem;
font-size: 0.8rem;
}

.badge {
font-size: 0.75rem;
padding: 0.35em 0.65em;
border-radius: 50px;
}

.page-header {
display: flex;
justify-content: space-between;
align-items: center;
margin-bottom: 1.5rem;
padding-bottom: 1rem;
border-bottom: 1px solid #e9ecef;
}

.page-title {
margin: 0;
color: #343a40;
font-weight: 600;
}

.page-description {
color: #6c757d;
margin-bottom: 0;
}

.action-buttons {
display: flex;
gap: 8px;
}

.avatar {
width: 36px;
height: 36px;
border-radius: 50%;
background-color: #4361ee;
color: white;
display: flex;
align-items: center;
justify-content: center;
font-weight: 600;
font-size: 0.9rem;
margin-right: 10px;
}

.proveedor-id {
display: flex;
align-items: center;
font-family: 'Courier New', monospace;
font-weight: 600;
}

.email-cell {
max-width: 180px;
overflow: hidden;
text-overflow: ellipsis;
white-space: nowrap;
}

.status-badge {
display: inline-flex;
align-items: center;
gap: 5px;
}

.status-dot {
width: 8px;
height: 8px;
border-radius: 50%;
display: inline-block;
}

.status-active {
background-color: #28a745;
}

.status-inactive {
background-color: #dc3545;
}

.table-hover tbody tr:hover {
background-color: rgba(67, 97, 238, 0.05);
}

.alert {
border-radius: 8px;
border: none;
box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.alert-success {
background-color: #d4edda;
color: #155724;
}

.alert-danger {
background-color: #f8d7da;
color: #721c24;
}
</style>


<?php if($step_one === true):?>
<div class="container-fluid">
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

<div class="page-header">
<div>
<h3 class="page-title"><i class="bi bi-person-badge-fill me-2"></i>Gestión de Proveedores</h3>
<p class="page-description">Maestros de soporte para las Órdenes de Compra.</p>
</div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
<i class="bi bi-plus-circle me-1"></i> Nuevo Proveedor
</button>
</div>

<div class="card shadow">
<div class="card-body">
<div class="table-responsive">
<table class="table table-striped table-hover"  id="tablaProveedores">
<thead>
<tr>
<th>ID/RIF</th>
<th>Empresa</th>
<th>Contacto</th>
<th>Categorías</th>
<th>Email</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach ($proveedores as $p): ?>
<tr>
<td>
<div class="proveedor-id">
<div class="avatar">
<?= substr($p['empresa_proveedora'], 0, 1) ?>
</div>
<strong><?= $p['id'] ?></strong>
</div>
</td>
<td><?= htmlspecialchars($p['empresa_proveedora']) ?></td>
<td><?= htmlspecialchars($p['nombre_contacto']) ?></td>
<td>
<?php
$sql = "SELECT * FROM proveedor_categoria WHERE proveedor_id = ?";
$result = $base->one_query($sql,$p['id']);

// Contar el total de filas
$total = count($result);

// Mostrar el total
echo '<span class="badge bg-secondary">' . $total . ' categoría(s)</span>';
?>
</td>
<td class="email-cell"><?= htmlspecialchars($p['email']) ?></td>
<td>
<span class="status-badge">
<span class="status-dot status-<?php echo $p['activo'] ? 'active' : 'inactive'; ?>"></span>
<span class="badge bg-<?php echo $p['activo'] ? 'success' : 'danger'; ?>">
<?php echo $p['activo'] ? 'Activo' : 'Inactivo'; ?>
</span>
</span>
</td>
<td >
<div class="form-inline">
<form method="POST" style="display: inline;">
<input type="hidden" name="GETID" value="<?= $p['id'] ?>">
<button type="submit" class="btn btn-sm btn-warning" name="pemge">
EDITAR
</button>
</form>

<form method="POST" action="modulos/proveedores/procesar_proveedor.php" style="display: inline;">
<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id" value="<?= $p['id'] ?>">
<button type="submit" class="btn btn-sm btn-danger" 
onclick="return confirm('¿Está seguro de que desea eliminar este proveedor?')">
ELIMINAR
</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

</div>
</div>
</div>

<?php else:?>
<div class="alert alert-danger" role="alert">
  <h4 class="alert-heading">Error de Permisos</h4>
  <p>El usuario no cuenta con los permisos necesarios para acceder a esta función.</p>
</div>

<?php endif;?>


<script>
$(document).ready(function() {
var activo = <?= json_encode($step_one)?>;
if(activo == false){
	window.location.href = './modulos/logout.php?razon=${"ver_proveedores"}';
}
});

</script>
