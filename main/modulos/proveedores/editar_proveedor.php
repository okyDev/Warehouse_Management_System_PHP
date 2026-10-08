<?php 
$idPROVEDIT = null;
$provEDIT = null;
$tipos_id = [];
$categorias_asociadas = [];

if(isset($_POST['pemge'])){
error_log("Entramos");
$idPROVEDIT = $_POST['GETID'];

// Obtener datos del proveedor
$sqle = "SELECT P.*, T.codigo 
FROM proveedores P 
JOIN tipos_identificacion T ON P.id_tipo = T.id 
WHERE P.id = ? AND P.activo = TRUE";

$provEDIT = $base->one_query($sqle, $idPROVEDIT);


// Obtener tipos de ID para el select
$tipos_id_result = $base->query_all("SELECT * FROM tipos_identificacion");
if ($tipos_id_result !== false) {
$tipos_id = $tipos_id_result;
}

// Obtener categorías asociadas al proveedor
$sql_cats = "SELECT categoria_id FROM proveedor_categoria WHERE proveedor_id = ?";
$result_cats = $base->some_query($sql_cats, [$idPROVEDIT]);

if ($result_cats !== false && is_array($result_cats)) {
foreach ($result_cats as $row) {
if (isset($row['categoria_id'])) {
$categorias_asociadas[] = $row['categoria_id'];
}
}
}
}
?>

<?php if($provEDIT !== null): ?>
<div class="modal fade" tabindex="-1" id="modalEditarProveedor" data-bs-backdrop="static">
<div class="modal-dialog modal-lg">
<div class="modal-content">


<form action="modulos/proveedores/procesar_proveedor.php" method="POST">
<input type="hidden" name="accion" value="editar">
<input type="hidden" name="id" value="<?= $provEDIT['id'] ?>">

<div class="modal-header">
<h5 class="modal-title">Editar Proveedor</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<div class="row">
<div class="col-md-4">
<div class="mb-3">
<label class="form-label">Tipo ID</label>
<select name="id_tipo" class="form-select" required>
<?php foreach ($tipos_id as $tipo): ?>
<option value="<?= $tipo['id'] ?>" 
<?= $tipo['id'] == $provEDIT['id_tipo'] ? 'selected' : '' ?>>
<?= $tipo['codigo'] ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="col-md-8">
<div class="mb-3">
<label class="form-label">RIF/ID</label>
<input type="text" class="form-control" value="<?= $provEDIT['id'] ?>" disabled>
<div class="form-text">El ID no se puede modificar</div>
</div>
</div>
</div>

<div class="mb-3">
<label class="form-label">Empresa</label>
<input type="text" name="empresa_proveedora" class="form-control" 
value="<?= htmlspecialchars($provEDIT['empresa_proveedora']) ?>"required>
</div>


<div class="mb-3">
<label class="form-label">Empresa RIF</label>
<input type="text" name="rifE" class="form-control" 
value="<?= htmlspecialchars($provEDIT['rif_empresa_proveedora']) ?>"required>
</div>

<div class="mb-3">
<label class="form-label">Contacto</label>
<input type="text" name="nombre_contacto" class="form-control" 
value="<?= htmlspecialchars($provEDIT['nombre_contacto']) ?>" required>
</div>

<div class="row">
<div class="col-md-6">
<div class="mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" 
value="<?= htmlspecialchars($provEDIT['email']) ?>"required>
</div>
</div>
<div class="col-md-6">
<div class="mb-3">
<label class="form-label">Teléfono</label>
<input type="text" name="telefono" class="form-control" 
value="<?= htmlspecialchars($provEDIT['telefono']) ?>" required>
</div>
</div>
</div>


<div class="mb-3">
<label class="form-label">Descripcion (Opcional)</label>
<textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($provEDIT['descripcion']) ?></textarea>
</div>

<div class="mb-4">

<div class="mb-3">
<label class="form-label">Categorias</label>
<div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
<?php if(!empty($categorias)):?>
<?php foreach ($categorias as $cat): ?>
<?php $selected = in_array($cat['id'], $categorias_asociadas) ? 'checked' : '';?>
<div class="form-check">

<input class="form-check-input" type="checkbox" name="categorias[]" 
value="<?= $cat['id'] ?>" id="cat_<?= $cat['id'] ?>" <?= $selected?>>

<label class="form-check-label" for="cat_<?= $cat['id'] ?>">
<?= htmlspecialchars($cat['nombre']) ?>
</label>
</div>
<?php endforeach; ?>

<?php else: ?>
<div class="text-muted">No hay categorías disponibles</div>
<?php endif; ?>
</div>
<small class="text-muted">Seleccione las categorías de productos que provee</small>
</div>
</div>

</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
<button type="submit" class="btn btn-warning">Actualizar Proveedor</button>
</div>
</form>


</div>
</div>
</div>

<?php endif; ?>


<script>
$(document).ready(function() {
const modalAjuste = document.getElementById('modalEditarProveedor');
if(modalAjuste) {
const miModal = new bootstrap.Modal(modalAjuste);
miModal.show();
}
});
</script>