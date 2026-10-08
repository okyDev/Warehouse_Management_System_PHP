<?php
require_once '../config/connect.php';
require_once '../config/auth.php';
$base = new MYSQL;

// 1. Obtener la lista de Roles
$allow = $base->query_all("SELECT * FROM permisos");
$roles = $base->query_all("SELECT * FROM roles WHERE activo = 1");
$permisostotal = $base->query_all("SELECT * FROM rol_permisos");
$total_usuarios =1;
$total_roles = 2;
$total_permisos = 2;
if(verify("crear_lotes") == true){include "agregar.php";}
if(verify("editar_roles") == true){include "editar.php";}
?>

<style>
:root {
--primary: #0078d4;
--primary-hover: #106ebe;
--success: #107c10;
--warning: #ffb900;
--danger: #d13438;
--dark: #323130;
--light: #faf9f8;
--gray: #8a8886;
--border: #edebe9;
--surface: #ffffff;
--shadow: 0 1.6px 3.6px 0 rgba(0, 0, 0, 0.132), 0 0.3px 0.9px 0 rgba(0, 0, 0, 0.108);
--shadow-hover: 0 3.2px 7.2px 0 rgba(0, 0, 0, 0.132), 0 0.6px 1.8px 0 rgba(0, 0, 0, 0.108);
}

/* Estilos generales */
* {
box-sizing: border-box;
}

body {
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
background-color: #f3f2f1;
color: var(--dark);
line-height: 1.4;
}

.container-fluid {
max-width: 1400px;
margin: 0 auto;
padding: 20px;
}

/* Header de página estilo sistema */
.page-header {
background: var(--surface);
border: 1px solid var(--border);
border-radius: 2px;
padding: 24px 32px;
margin-bottom: 24px;
box-shadow: var(--shadow);
display: flex;
justify-content: space-between;
align-items: center;
}

.page-header-content h1 {
font-size: 24px;
font-weight: 600;
margin: 0 0 8px 0;
color: var(--dark);
}

.page-header-content p {
color: var(--gray);
margin: 0;
font-size: 14px;
}

.page-header-actions {
display: flex;
gap: 12px;
}

/* Botones estilo sistema */
.btn {
padding: 8px 16px;
border: 1px solid transparent;
border-radius: 2px;
font-size: 14px;
font-weight: 600;
cursor: pointer;
transition: all 0.2s ease;
display: inline-flex;
align-items: center;
gap: 6px;
text-decoration: none;
}

.btn:hover {
text-decoration: none;
}

.btn-primary {
background-color: var(--primary);
color: white;
border-color: var(--primary);
}

.btn-primary:hover {
background-color: var(--primary-hover);
border-color: var(--primary-hover);
box-shadow: var(--shadow-hover);
}

.btn-outline {
background-color: transparent;
color: var(--primary);
border: 1px solid var(--primary);
}

.btn-outline:hover {
background-color: rgba(0, 120, 212, 0.1);
}

.btn-sm {
padding: 4px 8px;
font-size: 13px;
}

/* Grid de estadísticas */
.stats-grid {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
gap: 16px;
margin-bottom: 24px;
}

.stat-card {
background: var(--surface);
border: 1px solid var(--border);
border-radius: 2px;
padding: 20px;
box-shadow: var(--shadow);
transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
transform: translateY(-2px);
box-shadow: var(--shadow-hover);
}

.stat-value {
font-size: 28px;
font-weight: 600;
color: var(--primary);
margin: 8px 0;
}

.stat-label {
font-size: 14px;
color: var(--gray);
text-transform: uppercase;
letter-spacing: 0.5px;
}

/* Panel principal */
.main-panel {
background: var(--surface);
border: 1px solid var(--border);
border-radius: 2px;
box-shadow: var(--shadow);
margin-bottom: 24px;
overflow: hidden;
}

.panel-header {
padding: 20px 24px;
border-bottom: 1px solid var(--border);
display: flex;
justify-content: space-between;
align-items: center;
}

.panel-header h3 {
margin: 0;
font-size: 18px;
font-weight: 600;
color: var(--dark);
}

.panel-actions {
display: flex;
gap: 8px;
}

.panel-body {
padding: 0;
}

/* Tabla estilo sistema */
.table {
width: 100%;
border-collapse: collapse;
}

.table thead {
background-color: #faf9f8;
border-bottom: 2px solid var(--border);
}

.table th {
padding: 16px 20px;
text-align: left;
font-weight: 600;
font-size: 13px;
color: var(--dark);
text-transform: uppercase;
letter-spacing: 0.5px;
border-bottom: 1px solid var(--border);
}

.table td {
padding: 16px 20px;
border-bottom: 1px solid var(--border);
font-size: 14px;
}

.table tbody tr {
transition: background-color 0.2s ease;
}

.table tbody tr:hover {
background-color: rgba(0, 120, 212, 0.04);
}

.table tbody tr:last-child td {
border-bottom: none;
}

/* Badges */
.badge {
display: inline-block;
padding: 4px 8px;
border-radius: 2px;
font-size: 12px;
font-weight: 600;
text-transform: uppercase;
letter-spacing: 0.5px;
}

.badge-success {
background-color: rgba(16, 124, 16, 0.1);
color: var(--success);
}

.badge-secondary {
background-color: rgba(138, 136, 134, 0.1);
color: var(--gray);
}

/* Acciones en fila */
.row-actions {
display: flex;
gap: 8px;
}

/* Estados vacíos */
.empty-state {
text-align: center;
padding: 48px 24px;
color: var(--gray);
}

.empty-state-icon {
font-size: 48px;
margin-bottom: 16px;
opacity: 0.5;
}

.empty-state h4 {
margin: 0 0 8px 0;
color: var(--dark);
}

.empty-state p {
margin: 0 0 16px 0;
font-size: 14px;
}

/* Alertas */
.alert {
padding: 12px 16px;
border-radius: 2px;
margin-bottom: 16px;
border-left: 4px solid transparent;
display: flex;
align-items: flex-start;
gap: 8px;
font-size: 14px;
}

.alert-success {
background-color: rgba(16, 124, 16, 0.1);
border-left-color: var(--success);
color: var(--success);
}

.alert-danger {
background-color: rgba(209, 52, 56, 0.1);
border-left-color: var(--danger);
color: var(--danger);
}

.alert i {
flex-shrink: 0;
margin-top: 2px;
}

/* Modal estilo sistema */
.modal {
background: rgba(0, 0, 0, 0.4);
}

.modal-content {
border: 1px solid var(--border);
border-radius: 2px;
box-shadow: 0 6.4px 14.4px 0 rgba(0, 0, 0, 0.132), 0 1.2px 3.6px 0 rgba(0, 0, 0, 0.108);
}

.modal-header {
padding: 20px 24px;
border-bottom: 1px solid var(--border);
background: var(--surface);
}

.modal-title {
font-size: 18px;
font-weight: 600;
margin: 0;
color: var(--dark);
}

.modal-body {
padding: 24px;
background: var(--surface);
}

.modal-footer {
padding: 20px 24px;
border-top: 1px solid var(--border);
background: #faf9f8;
display: flex;
justify-content: flex-end;
gap: 8px;
}

/* Formularios */
.form-group {
margin-bottom: 20px;
}

.form-label {
display: block;
margin-bottom: 8px;
font-weight: 600;
color: var(--dark);
font-size: 14px;
}

.form-control {
width: 100%;
padding: 8px 12px;
border: 1px solid var(--border);
border-radius: 2px;
font-size: 14px;
transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus {
outline: none;
border-color: var(--primary);
box-shadow: 0 0 0 2px rgba(0, 120, 212, 0.2);
}

.form-select {
width: 100%;
padding: 8px 12px;
border: 1px solid var(--border);
border-radius: 2px;
font-size: 14px;
background-color: white;
cursor: pointer;
}

.form-select:focus {
outline: none;
border-color: var(--primary);
box-shadow: 0 0 0 2px rgba(0, 120, 212, 0.2);
}

.checkbox-group {
display: grid;
grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
gap: 12px;
margin-top: 8px;
}

.form-check {
display: flex;
align-items: center;
gap: 8px;
}

.form-check-input {
margin: 0;
}

.form-check-label {
font-size: 14px;
color: var(--dark);
cursor: pointer;
}

/* Botón de acción flotante */
.floating-action {
position: fixed;
bottom: 24px;
right: 24px;
z-index: 1000;
box-shadow: 0 6.4px 14.4px 0 rgba(0, 0, 0, 0.132), 0 1.2px 3.6px 0 rgba(0, 0, 0, 0.108);
border-radius: 50%;
width: 56px;
height: 56px;
display: flex;
align-items: center;
justify-content: center;
background-color: var(--primary);
color: white;
border: none;
cursor: pointer;
transition: all 0.2s ease;
}

.floating-action:hover {
background-color: var(--primary-hover);
transform: scale(1.05);
box-shadow: 0 9.6px 21.6px 0 rgba(0, 0, 0, 0.132), 0 1.8px 5.4px 0 rgba(0, 0, 0, 0.108);
}

/* Responsive */
@media (max-width: 768px) {
.container-fluid {
padding: 12px;
}

.page-header {
flex-direction: column;
align-items: flex-start;
gap: 16px;
padding: 20px;
}

.page-header-actions {
width: 100%;
}

.stats-grid {
grid-template-columns: 1fr;
}

.panel-header {
flex-direction: column;
align-items: flex-start;
gap: 16px;
}

.row-actions {
flex-direction: column;
}

.checkbox-group {
grid-template-columns: 1fr;
}
}
</style>


<div class="container-fluid">
<!-- Mensajes -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success">
<i class="bi bi-check-circle"></i>
<div><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger">
<i class="bi bi-exclamation-triangle"></i>
<div><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
</div>
<?php endif; ?>

<!-- Header de la página -->
<div class="page-header">
<div class="page-header-content">
<h1>Roles del Sistema</h1>
<p>Administra los roles y permisos de acceso de los usuarios</p>
</div>
<div class="page-header-actions">
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearRol">
<i class="bi bi-plus-lg"></i>
Nuevo Rol
</button>
</div>
</div>

<!-- Estadísticas -->
<div class="stats-grid">
<div class="stat-card">
<div class="stat-label">Total de Roles</div>
<div class="stat-value"><?= count($roles) ?></div>
</div>

<div class="stat-card">
<div class="stat-label">Permisos Configurados</div>
<div class="stat-value"><?= count($permisostotal)?></div>
</div>
</div>

<!-- Panel principal -->
<div class="main-panel">
<div class="panel-header">
<h3>Lista de Roles</h3>
<div class="panel-actions">
<button class="btn btn-outline btn-sm">
<i class="bi bi-funnel"></i>
Filtrar
</button>
<button class="btn btn-outline btn-sm">
<i class="bi bi-download"></i>
Exportar
</button>
</div>
</div>
<div class="panel-body">
<?php if (!empty($roles)): ?>
<table class="table">
<thead>
<tr>
<th>ID</th>
<th>Nombre del Rol</th>
<th>Permisos</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach ($roles as $rol): ?>
<tr>
<td>
<span style="font-family: monospace; color: var(--gray);">#<?= htmlspecialchars($rol['rol_id']) ?></span>
</td>
<td>
<strong><?= htmlspecialchars($rol['nombre_rol']) ?></strong>
</td>
<td>
<span style="color: var(--gray); font-size: 13px;">
<?php 
$total = 0;
foreach($permisostotal as $n){
	if($n['rol_id'] === $rol['rol_id']){
	$total++;
}
}
echo $total;
?>
</span>
</td>
<td>
<span class="badge badge-<?= $rol['activo'] == 1 ? 'success' : 'secondary' ?>">
<?= $rol['activo'] == 1 ? 'ACTIVO' : 'INACTIVO' ?>
</span>
</td>
<td>
<div class="row-actions">
	
<?php if(verify("editar_roles") == true):?>
<form method="POST" class="d-inline">
<input type="hidden" name="IDROL" value="<?= $rol['rol_id']?>">
<button type="submit" class="btn btn-outline btn-sm">
<i class="bi bi-pencil"></i>
Editar
</button>
</form>
<?php endif;?>

<form method="POST" action="modulos/roles/procesar.php" class="d-inline">
<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id" value="<?= $rol['rol_id']?>">
<button type="submit" class="btn btn-outline btn-sm" onclick="return confirmarEliminarRol(<?= $rol['rol_id'] ?>)">
<i class="bi bi-trash"></i>
Eliminar
</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<div class="empty-state">
<div class="empty-state-icon">
<i class="bi bi-people"></i>
</div>
<h4>No hay roles configurados</h4>
<p>Comienza creando el primer rol para tu sistema</p>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearRol">
<i class="bi bi-plus-circle me-2"></i>
Crear Primer Rol
</button>
</div>
<?php endif; ?>
</div>
</div>

<!-- Botón flotante -->
<button class="floating-action" data-bs-toggle="modal" data-bs-target="#modalCrearRol">
<i class="bi bi-plus-lg">+</i>
</button>
</div>

<script>
function confirmarEliminarRol(id) {
return confirm(`¿Estás seguro de eliminar el rol #${id}?\nEsta acción no se puede deshacer.`);
}
</script>
