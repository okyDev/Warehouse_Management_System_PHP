<?php
// modulos/productos/productos.php
require_once '../config/connect.php';
require_once '../config/auth.php';
$base = new MYSQL;

$step_one = verify("ver_productos");

///// TABLAS
$productos = $base->query_all("SELECT *,P.nombre, P.sku, P.stock_actual, M.simbolo AS moneda, P.marca, P.precio_venta, C.nombre AS categoria FROM productos P JOIN categorias C ON C.id = P.categoria_id JOIN monedas M ON M.id = P.moneda_venta_id WHERE P.activo = 1");
$categorias = $base->query_all("SELECT DISTINCT C.id, C.nombre FROM categorias C JOIN proveedor_categoria PC ON C.id = PC.categoria_id");
$monedas = $base->query_all("SELECT * FROM monedas WHERE activo = 1");
$PC = $base->query_all("SELECT * FROM proveedor_categoria");



// Función para generar SKU
function generarSKU($base) {
$letra = 'P';
$numero = 1;

do {
$sku = $letra . str_pad($numero, 4, '0', STR_PAD_LEFT);
$query = "SELECT COUNT(*) AS count FROM productos WHERE sku = ?";
$resultado = $base->one_query($query,$sku);
$numero++;
} while ($resultado['count'] > 0);

return $sku;
}

$skuGenerado = generarSKU($base);


//include "producto_edit.php";
include "producto_agregar.php";
include "producto_edit.php";
?>

<?php if($step_one === true): ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-box-seam"></i> Gestión de Productos</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearProducto">
            <i class="bi bi-plus-circle"></i> Nuevo Producto
        </button>
    </div>



    <!-- Barra de Búsqueda -->
    <div class="mb-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Buscar productos..." aria-label="Buscar productos">
    </div>
   <!-----BARRA ENDS HERE -->

    <!-- Mensajes -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= count($productos) ?></h4>
                            <small>Total Productos</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-box fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">0</h4>
                            <small>Stock Bajo</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">
                                <?= array_reduce($productos, function($carry, $producto) {
                                    return $carry + ($producto['stock_actual'] == 0 ? 1 : 0);
                                }, 0) ?>
                            </h4>
                            <small>Sin Stock</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-x-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    

<!-- Tabla de Productos -->
<div class="card shadow">
<div class="card-header bg-dark text-white">
<h5 class="card-title mb-0"><i class="bi bi-list"></i> Lista de Productos</h5>
</div>
<div class="card-body">
<div class="table-responsive">
<table class="table table-striped table-hover">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Producto</th>

<th>Precio Venta</th>
<th>Stock Minimo</th>
<th>Stock Actual</th>

<th>Categoría</th>

<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php if (empty($productos)): ?>
<tr>
<td colspan="8" class="text-center text-muted py-4">
<i class="bi bi-inbox fs-1 d-block mb-2"></i>
No hay productos registrados
</td>
</tr>

<?php else: ?>
<?php foreach ($productos as $prod): ?>


<?php 
$stock_class = '';
if ($prod['stock_actual'] == 0) {
$stock_class = 'bg-danger';
} elseif ($prod['stock_actual'] <= $prod['stock_minimo']) {
$stock_class = 'bg-warning text-dark';
} else {
$stock_class = 'bg-success';
}
?>
<tr data-sku="<?= $prod['sku'] ?>" data-nombre="<?= $prod['nombre'] ?>" data-descripcion="<?= $prod['descripcion'] ?>" data-marca="<?= $prod['marca'] ?>">

<!--SKU-->
<td>
<strong><?= $prod['sku'] ?></strong>
</td>



<!--NOMBRE-->
<td>
<strong><?= htmlspecialchars($prod['nombre']) ?> <?= htmlspecialchars($prod['marca'])?></strong>
<?php if (!empty($prod['descripcion'])): ?>
<br><small class="text-muted"><?= htmlspecialchars(substr($prod['descripcion'], 0, 50)) . (strlen($prod['descripcion']) > 50 ? '...' : '') ?></small>
<?php endif; ?>
</td>

<!--PRECIO-->
<td>
<strong><?= $prod['moneda'] ?> <?= number_format($prod['precio_venta'], 2) ?></strong>
</td>


<!--STOCK-->
<td>
<?= $prod['stock_minimo']?> 
</td>                           
<td>
<?= $prod['stock_actual']?> 
</td>




<!--CATEGORIA-->
<td>
<span class="badge bg-info"><?= htmlspecialchars($prod['categoria']) ?></span>
</td>

<!---->
<td>
<span class="badge bg-success">Activo</span>
</td>

<!--ACCIONES--->
<td>
<div class="btn-group btn-group-sm">
<!-- Editar Producto -->
<?php if(verify("editar_productos")):?>
<form method="POST" style="display: inline;">
<input type="hidden" name="EDITPRODUCTO" value="<?= $prod['sku'] ?>">
<button type="submit" class="btn btn-warning" name="GETIT">
Editar
</button>
</form>
<?php endif;?>


<!-- Desactivar -->
<?php if(verify("drop_productos")):?>
<form method="POST" action="procesar_producto.php" style="display: inline;">
<input type="hidden" name="accion" value="desactivar">
<input type="hidden" name="codigo_producto" value="<?= $prod['sku'] ?>">
<button type="submit" class="btn btn-danger" 
onclick="return confirm('¿Está seguro de desactivar este producto?')">
Eliminar
</button>
</form>
<?php endif;?>
</div>
</td><!--ACCIONES ENDS HERE--->



</tr>
<?php endforeach; ?>
<?php endif; ?>
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
    $("#searchInput").on("keyup", function() {
        var inputValue = $(this).val().toLowerCase(); // Obtiene el valor ingresado
        $("table tbody tr").filter(function() {
            // Verifica si coincide con SKU, nombre, descripción o marca
            $(this).toggle(
                $(this).data("sku").toLowerCase().indexOf(inputValue) > -1 ||
                $(this).data("nombre").toLowerCase().indexOf(inputValue) > -1 ||
                $(this).data("descripcion").toLowerCase().indexOf(inputValue) > -1 ||
                $(this).data("marca").toLowerCase().indexOf(inputValue) > -1
            );
        });
    });
    
if(activo == false){
	window.location.href = './modulos/logout.php?razon={producto}';
}
});
</script>
