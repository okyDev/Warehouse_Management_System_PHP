<?php
// modulos/categorias/categorias.php
require_once '../config/connect.php';
require_once '../config/auth.php';
$base = new MYSQL;
$categorias = $base->query_all("SELECT * FROM categorias WHERE activo = 1");
$allo = verify("ver_categorias");
if(verify("editar_categorias") === true){include "categoria_edit.php";}
?>

<style>
:root {
    --primary: #2c7be5;
    --success: #00d97e;
    --warning: #f6c343;
    --danger: #e63757;
    --dark: #1e2a38;
    --light: #f9fafd;
    --gray: #95aac9;
}

/* Estilos generales */
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    margin-bottom: 1.5rem;
    border-radius: 0.5rem;
}

.card-header {
    background-color: var(--light);
    border-bottom: 1px solid #e3ebf6;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.btn-primary {
    background-color: var(--primary);
    border-color: var(--primary);
}

.btn-warning {
    background-color: var(--warning);
    border-color: var(--warning);
    color: #fff;
}

.btn-danger {
    background-color: var(--danger);
    border-color: var(--danger);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: var(--dark);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.8em;
    font-size: 0.85em;
}

/* Header de página */
.page-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e3ebf6;
}

.page-title {
    color: var(--dark);
    font-weight: 600;
    margin: 0;
}

.page-title i {
    color: var(--primary);
    margin-right: 0.5rem;
}

/* Barra de herramientas */
.toolbar {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1.5rem;
    gap: 1rem;
}

.search-box {
    position: relative;
    flex: 1;
    max-width: 400px;
}

.search-box input {
    padding-left: 2.5rem;
    border-radius: 0.5rem;
}

.search-box i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
}

/* Estilos para la tabla */
.table-responsive {
    border-radius: 0.5rem;
    overflow: hidden;
}

.table thead {
    background-color: var(--dark) !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(44, 123, 229, 0.05);
}

/* Estilos para los botones de acción */
.btn-action-group {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

/* Estilos para las alertas */
.alert {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Estilos para el modal */
.modal-header {
    background-color: var(--light);
    border-bottom: 1px solid #e3ebf6;
}

.modal-footer {
    border-top: 1px solid #e3ebf6;
}

/* Badge para ID */
.badge-id {
    background-color: var(--light);
    color: var(--dark);
    font-family: 'Courier New', monospace;
    font-weight: 600;
}

/* Estado vacío */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--gray);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Contador de categorías */
.categories-count {
    background: linear-gradient(135deg, var(--primary) 0%, #1e2a38 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.count-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.count-label {
    font-size: 0.875rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>


<?php if($allo === true):?>
<div class="container-fluid">
	
<!-- Mensajes -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Header de página -->
    <div class="page-header">
        <div>
            <h3 class="page-title"><i class="bi bi-tags"></i> Gestión de Categorías</h3>
            <p class="text-muted mb-0">Administra las categorías de productos de tu inventario</p>
        </div>
    </div>

    <!-- Contador de categorías -->
    <div class="categories-count">
        <div class="count-number"><?= count($categorias) ?></div>
        <div class="count-label">Categorías Registradas</div>
    </div>

   
    
        
    


<!-- Tabla de categorías -->
<div class="card">
	
	
 <!-- Barra de herramientas -->
<div class="mb-3">
<div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar Categoria...">
        </div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
            <i class="bi bi-plus-circle me-2"></i> Nueva Categoría
        </button>
</div>
<!----BUSCAR---->



<div class="card-header">

<h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Lista de Categorías</h5>
</div>
<div class="card-body">
<?php if (!empty($categorias)): ?>
<div class="table-responsive">
	

<!----TABLA---->
<div class="card shadow">
<table class="table table-striped table-hover">
<thead>
<tr>
<th width="100">ID</th>
<th>Nombre de Categoría</th>
<th width="200">Acciones</th>
</tr>
</thead>

<tbody>

<?php foreach ($categorias as $cat): ?>
<tr data-idT="<?= $cat['id'] ?>" data-nombreT="<?= $cat['nombre'] ?>" id="buscarHRE">

<td>
<span class="badge badge-id">#<?= $cat['id'] ?></span>
</td>

<td>
<div class="d-flex align-items-center">
<div class="bg-primary rounded-circle me-3" style="width: 12px; height: 12px;"></div>
<strong><?= htmlspecialchars($cat['nombre']) ?></strong>
</div>
</td>


<td>
<div class="btn-action-group">
<!-- Form para Editar -->
<form method="POST" class="d-inline">
<input type="hidden" name="GETID" value="<?= $cat['id'] ?>">
<button type="submit" class="btn btn-warning btn-sm">
<i class="bi bi-pencil"></i> Editar
</button>
</form>

<!-- Form para Eliminar -->
<form method="POST" action="modulos/categorias/procesar_categorias.php" class="d-inline">
<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id" value="<?= $cat['id'] ?>">
<button type="submit" class="btn btn-danger btn-sm" 
onclick="return confirm('¿Estás seguro de eliminar la categoría \"<?= htmlspecialchars($cat['nombre']) ?>\"?')">
<i class="bi bi-trash"></i> Eliminar
</button>
</form>
</div>
</td>


</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!----tabla ENDS HERE---->

</div>
<?php else: ?>
<div class="empty-state">
<i class="bi bi-tags"></i>
<h4>No hay categorías registradas</h4>
<p class="text-muted">Comienza creando tu primera categoría de productos</p>
<button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
<i class="bi bi-plus-circle me-2"></i> Crear Primera Categoría
</button>
</div>
<?php endif; ?>
</div>
</div>
</div>


<?php if(verify("crear_categorias") === true):?>
<!-- Modal Crear Categoría -->
<div class="modal fade" id="modalCrearCategoria" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Nueva Categoría</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form action="modulos/categorias/procesar_categorias.php" method="POST">
<input type="hidden" name="accion" value="crear">
<div class="modal-body">
<div class="mb-3">
<label class="form-label">Nombre de Categoría</label>
<input type="text" name="nombre" class="form-control" placeholder="Ingresa el nombre de la categoría" required>
<div class="form-text">Ej: Electrónicos, Ropa, Hogar, etc.</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
<button type="submit" class="btn btn-primary">
<i class="bi bi-check-lg me-2"></i> Guardar Categoría
</button>
</div>
</form>
</div>
</div>
</div>
<?php endif;?>

<script>
$(document).ready(function() {
    $("#searchInput").on("keyup", function() {
        var inputValue = $(this).val().toLowerCase(); // Obtiene el valor ingresado
        $("table tbody tr").filter(function() {
            // Verifica si coincide con SKU, nombre, descripción o marca
            $(this).toggle(
                $(this).data("idT").toLowerCase().indexOf(inputValue) > -1 ||
                $(this).data("nombreT").toLowerCase().indexOf(inputValue) > -1 
            );
        });
    });
});


// Mejorar la confirmación de eliminación
function confirmDelete(categoryName) {
    return confirm(`¿Estás seguro de eliminar la categoría "${categoryName}"?\n\nEsta acción no se puede deshacer.`);
}
</script>
<?php else:?>
<div class="alert alert-danger" role="alert">
  <h4 class="alert-heading">Error de Permisos</h4>
  <p>El usuario no cuenta con los permisos necesarios para acceder a esta función.</p>
</div>
<?php endif;?>
