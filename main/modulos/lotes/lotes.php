<?php 
require_once '../config/connect.php';
date_default_timezone_set('America/Caracas');
//session_start();
$opciones = 'activos';
$base = new MYSQL;
//// tablas

$productos = $base->query_all("SELECT * FROM productos WHERE activo = 1");
$lotes = $base->query_all("SELECT * FROM lotes WHERE activo = 1");
$monedas = $base->query_all("SELECT * FROM monedas WHERE activo = 1");

$categorias = $base->query_all("SELECT DISTINCT C.id, C.nombre FROM categorias C JOIN proveedor_categoria PC ON C.id = PC.categoria_id WHERE C.activo = true");


if($opciones === 'activo'){
	$lotes = $base->query_all("SELECT * FROM lotes WHERE activo = 1");
	}
elseif ($opciones === 'ven'){
	
	}
elseif($opciones ==='agot'){
	
	
	}



function generarSKU($base) {
$ano = date('Y');
$letra = 'LOTE-'.$ano;
$numero = 1;

    do {
        $sku = $letra . str_pad($numero, 4, '0', STR_PAD_LEFT);
        $query = "SELECT COUNT(*) AS count FROM lotes WHERE lote_sku = ?";
        $fila = $base->one_query($query,$sku);
        $numero++;
    } while ($fila['count'] > 0);

    return $sku;
}
$AUTOID = generarSKU($base);


include "agregar.php";
include "editar.php";
include "info_lotes.php";

?>


<style>
:root {
    --sidebar-color: #1e2a38;
    --primary-color: #4361ee;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --light-bg: #f8f9fa;
    --dark-text: #343a40;
    --muted-text: #6c757d;
}

	.scale-table {
    transform: scale(0.9);  /* Escala al 80% del tamaño original */
    transform-origin: top left;  /* Mantiene la posición original */
    width: auto; /* Ajusta el ancho de la tabla según su contenido */
}

.lotes-container {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px black;
    overflow: hidden;
}

.lotes-header {
    background-color: var(--light-bg);
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    color: var(--dark-text);
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.page-description {
    color: var(--muted-text);
    margin-bottom: 0;
}

.search-container {
    background-color: white;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.search-box {
    position: relative;
}

.search-box .form-control {
    padding-left: 2.5rem;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}

.search-box .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted-text);
}

.stats-container {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.stat-card {
    flex: 1;
    background: white;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border-left: 4px solid var(--primary-color);
}

.stat-card.warning {
    border-left-color: var(--warning-color);
}

.stat-card.danger {
    border-left-color: var(--danger-color);
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--muted-text);
    margin-bottom: 0;
}

.table-container {
    padding: 0;
}

.table {
    margin-bottom: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted-text);
    background-color: var(--light-bg);
    padding: 12px 15px;
    vertical-align: middle;
}

.table td {
    padding: 12px 15px;
    vertical-align: middle;
    border-color: #f1f3f4;
}

.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
    border-radius: 50px;
    font-weight: 500;
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
    background-color: var(--success-color);
}

.status-warning {
    background-color: var(--warning-color);
}

.status-expired {
    background-color: var(--danger-color);
}

.priority-high {
    background-color: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
}

.priority-medium {
    background-color: rgba(255, 193, 7, 0.1);
    color: #856404;
}

.priority-low {
    background-color: rgba(40, 167, 69, 0.1);
    color: var(--success-color);
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
    border-radius: 4px;
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background-color: #3a56d4;
    border-color: #3a56d4;
}

.expiry-warning {
    color: var(--danger-color);
    font-weight: 500;
}

.expiry-close {
    color: var(--warning-color);
    font-weight: 500;
}

.table-hover tbody tr:hover {
    background-color: rgba(67, 97, 238, 0.03);
}

.lote-id {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: var(--dark-text);
}

.filter-buttons {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.filter-btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.75rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--muted-text);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #dee2e6;
}

.pagination-container {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: var(--light-bg);
}

.page-info {
    font-size: 0.875rem;
    color: var(--muted-text);
}

@media (max-width: 768px) {
    .stats-container {
        flex-direction: column;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}



/* */
.modal-lotes .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.modal-lotes .modal-header {
    background: linear-gradient(135deg, #1e2a38, #2d3b4e);
    color: white;
    border-bottom: none;
    border-radius: 12px 12px 0 0;
    padding: 1.5rem;
}

.modal-lotes .modal-title {
    font-weight: 600;
    font-size: 1.25rem;
}

.modal-lotes .modal-body {
    padding: 2rem;
}

.modal-lotes .modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 1.5rem 2rem;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #f1f3f4;
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e2a38;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #4361ee;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #4361ee;
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

.date-time-group {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.75rem;
}

.time-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.stock-info {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 0.5rem;
}

.stock-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e2a38;
}

.cost-display {
    background-color: #e8f5e8;
    border-radius: 6px;
    padding: 0.75rem;
    margin-top: 0.5rem;
    text-align: center;
}

.cost-amount {
    font-size: 1.2rem;
    font-weight: 700;
    color: #28a745;
}

.currency-symbol {
    font-size: 0.9rem;
    color: #6c757d;
    margin-right: 0.25rem;
}

.btn-primary {
    background-color: #4361ee;
    border-color: #4361ee;
    padding: 0.75rem 2rem;
    font-weight: 500;
    border-radius: 6px;
}

.btn-primary:hover {
    background-color: #3a56d4;
    border-color: #3a56d4;
    transform: translateY(-1px);
}

.btn-outline-secondary {
    border-radius: 6px;
    padding: 0.75rem 2rem;
}

.required-field::after {
    content: " *";
    color: #dc3545;
}

.help-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    font-size: 0.9rem;
}

.alert-warning {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
}

@media (max-width: 768px) {
    .date-time-group {
        grid-template-columns: 1fr;
    }
    
    .time-group {
        grid-template-columns: 1fr 1fr;
    }
    
    .modal-body {
        padding: 1.5rem;
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
    



<!---HTML------>
<div class="lotes-container">
<div class="lotes-header">
<div class="d-flex justify-content-between align-items-start mb-3">

<div>
<h3 class="page-title"><i class="bi bi-box-seam me-2"></i>Gestión de Lotes de Almacén</h3>
<p class="page-description">Control y seguimiento de inventario por lotes</p>
</div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CREAR">
<i class="bi bi-plus-circle me-1"></i> Nuevo Ingreso </button>
            </div>
            
<div class="search-container">
<div class="row">
<div class="col-md-8">
<div class="search-box">
<i class="bi bi-search search-icon"></i>
<input type="text" class="form-control" placeholder="Buscar por ID de lote, producto o ubicación...">
</div>
                        
<div class="filter-buttons">
<button class="btn btn-outline-secondary filter-btn active">Todos</button>
<button class="btn btn-outline-secondary filter-btn" type="submit" name="xvencer">Por vencer</button>
<button class="btn btn-outline-secondary filter-btn">Agotados</button>
</div>
</div>

<div class="col-md-4">
<div class="stats-container">

<?php if(!empty($lotes)):?>
<div class="stat-card">
<div class="stat-value"><?= count($lotes) ?></div>
<div class="stat-label">Lotes activos</div>
</div>

<div class="stat-card warning">
<div class="stat-value">5</div>
<div class="stat-label">Por vencer</div>
</div>

<div class="stat-card danger">
<div class="stat-value">2</div>
<div class="stat-label">Vencidos</div>
</div>


<?php else:?>
<span>
<i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay lotes registrados aun
</span>
<?php endif;?>

</div>
</div>
</div>
</div>
</div>
        
<div class="table-container">
<div class="table-responsive">


<table class="table table-hover scale-table" border="1">
<thead>
<tr>
<th>Fecha de Creacion</th> <th>ID</th> <th>Descripción</th> <th>Cantidad Inicial</th> <th>Cantidad Actual</th> <th>Fecha de Vencimiento</th> <th>MAS</th>
</tr>
</thead>
<tbody>
<!----<th>Proveedor</th> <th>Fecha Ingreso</th>
<th>Fecha Vencimiento</th> <th>Estado</th> <th>Prioridad</th> <th>Acciones</th>---->
<!---FOREACH SHOW SALLL THE CONTENT------>
<?php if(!empty($lotes)):?>
<?php foreach($lotes as $n):?>
<tr>

<!------>
<td>
<table border="1">
<tr>
<td>Fecha</td>
<td>Hora</td>
</tr>
<tr>
<td><?=$n['fecha_entrada_d']?>/<?=$n['fecha_entrada_m']?>/<?=$n['fecha_entrada_y']?></td>
<td><?=$n['fecha_entrada_H']?>:<?=$n['fecha_entrada_mi']?></td>
</tr>
</table>
</td>
<!------>


<!------>
<td><?= $n['lote_sku']?></td>
<!------>

<!------>
<td><?= $n['producto_sku']?></td>
<!------>

<!------>
<td><?= $n['cantidad_inicial']?></td>
<!------>

<!------>
<td><?= $n['stock_actual']?></td>
<!------>


<!------>
<td>
<?php if(!empty($n['fecha_vencimiento_d'])):?>
<?=$n['fecha_vencimiento_d']?>/<?=$n['fecha_vencimiento_m']?>/<?=$n['fecha_vencimiento_y']?>
<?php else:?>
<span>Ninguna</span>
<?php endif;?>
</td>
<!------>


<td>
<div class="btn-group btn-group-sm">
<!-- Editar LOTE -->
<form method="POST" style="display: inline;">
<input type="hidden" name="EDITLOL" value="<?= $n['lote_sku'] ?>">
<button type="submit" class="btn btn-warning" name="butEDIT">
Editar
</button>
</form>
                                        
<!-- Desactivar -->
<form method="POST" action="modulos/lotes/procesar_lotes.php" style="display: inline;">
<input type="hidden" name="accion" value="desactivar">
<input type="hidden" name="lote_sku_eliminar" value="<?= $n['lote_sku'] ?>">
<button type="submit" class="btn btn-danger" 
onclick="return confirm('¿Está seguro de desactivar este LOTE?')">Eliminar</button>
</form>


<!-- KNOW mORE -->
<form method="POST" style="display: inline;">
<input type="hidden" name="CODVER" value="<?= $n['lote_sku'] ?>">
<button type="submit" name="VERMAS" class="btn btn-info">Ver</button>
</form>
</div>
</td>
<!------>
</tr>
<?php endforeach;?>
<!------>
<?php else:?>
<tr>
<h4>Ingrese nuevo lote</h4>
</tr>
<?php endif;?>
</tbody>
</table>
</div>
            

<!------
<div class="pagination-container">
<div class="page-info">Mostrando 5 de 24 lotes</div>
<nav>
<ul class="pagination pagination-sm mb-0">
<li class="page-item disabled">
<a class="page-link" href="#" tabindex="-1">Anterior</a>
</li>
<li class="page-item active"><a class="page-link" href="#">1</a></li>
<li class="page-item"><a class="page-link" href="#">2</a></li>
<li class="page-item"><a class="page-link" href="#">3</a></li>
<li class="page-item">
<a class="page-link" href="#">Siguiente</a>
</li>
</ul>
</nav>
</div>
<!------>

</div>
</div>
</div>
