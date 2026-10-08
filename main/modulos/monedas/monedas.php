<?php
// modulos/monedas/monedas.php
require_once '../config/connect.php';
require_once '../config/auth.php';

$base = new MYSQL;

$ver = verify("ver_monedas");

// Obtener monedas
$monedasTABLA = $base->query_all("SELECT * FROM monedas WHERE activo = 1 AND base = false");
?>

<?php if($ver === true):?>
<div class="container-fluid">
<h3><i class="bi bi-currency-exchange"></i> Monedas</h3>

<!-- Mensajes -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
<?= $_SESSION['success'] ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
<?= $_SESSION['error'] ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Botón Agregar -->
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalCrearMoneda">
<i class="bi bi-plus-circle"></i> Nueva Moneda
</button>



<!-- Tabla -->
<div class="card shadow">
<div class="card-body">
<div class="table-responsive">
<table class="table table-striped table-hover">
<thead class="table-dark">
<tr>
<th>Código</th>
<th>Símbolo</th>
<th>Tasa de Cambio</th>
<th>Moneda Base</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach ($monedasTABLA as $moneda): ?>
<tr>
<!----->

<td><strong><?= $moneda['codigo'] ?></strong></td>

<td><?= $moneda['simbolo'] ?></td>

<td>Bs <?= number_format($moneda['tasa_cambio'], 2) ?></td>

<td>
<span class="badge bg-<?= $moneda['base'] ? 'success' : 'secondary' ?>">
<?= $moneda['base'] ? 'SÍ' : 'No' ?>
</span>
</td>


<td>
<span class="badge bg-success">Activa</span>
</td>



<?php if($moneda['base']? true:false): ?>
<td>

</td>

<?php else: ?>
<td>
<form method="POST">
<input type="hidden" name="idPROVE" value="<?php echo $moneda['id'];?>">

<button type="submit" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Editar</button>
</form>                       

<div></div>
<button class="btn btn-sm btn-danger" onclick="confirmarEliminar(<?= $moneda['id'] ?>, '<?= $moneda['codigo'] ?>')"><i class="bi bi-trash"></i> Eliminar </button>

</td>
<?php endif;?>

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

<!-- Incluir Modales -->
<?php 
if(verify("crear_monedas") === true){include 'crear_moneda.php'; }
?>
<?php 
if(verify("editar_monedas") === true){include 'editar_moneda.php';} ?>

<script>
function confirmarEliminar(id, codigo) {
if (confirm(`¿Estás seguro de eliminar la moneda ${codigo}?`)) {
window.location.href = `procesar_moneda.php?accion=eliminar&id=${id}`;
}
}






/*
data-bs-toggle="modal" data-bs-target="#modalEditarMoneda"

onsubmit="guardarModo();"
function guardarModo() {
//sessionStorage.setItem('modo', 'true'); // Guardar el estado en sessionStorage
localStorage.setItem('modo','true');
}


document.addEventListener('DOMContentLoaded',function(){

if (localStorage.getItem('modo') === 'true') {
const mimodal = document.getElementById('modalEditarMoneda');
const miModal = new bootstrap.Modal(mimodal);
miModal.show();
localStorage.removeItem('modo');
}
});
*/
// Verificación del estado al cargar la página

</script>
