<?php
$ROLedit = null;
///$pe = null;
$permisos_asignados = [];

// ... (código existente)
if(isset($_POST['IDROL'])){
    $idEDIT = $_POST['IDROL'] ?? null;
    $sql = "SELECT * FROM roles WHERE rol_id = ?";
    $ROLedit = $base->one_query($sql, $idEDIT);
    
    // CORRECCIÓN CLAVE: Usar query_all para obtener TODOS los permisos del rol.
    $permisos_rol = $base->some_query("SELECT permiso_id FROM rol_permisos WHERE rol_id = ?", [$idEDIT]);
    
    // Convertir el array de resultados a un array de IDs para búsqueda rápida
    
    if (!empty($permisos_rol)) {
        foreach ($permisos_rol as $p) {
            // Guarda el ID del permiso como la clave del array para búsqueda O(1)
            $permisos_asignados[$p['permiso_id']] = true; 
        }
    }
}
?>

<?php if(isset($ROLedit)): ?>
<!-- Modal para crear rol -->
<div class="modal fade" id="editMODALROL" tabindex="-1" aria-labelledby="editMODALROL" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Editar Rol</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">

<form action="modulos/roles/procesar.php" method="POST">
    <input type="hidden" name="accion" value="editar">
    <input type="hidden" name="id" value="<?= $ROLedit['rol_id'] ?>">

<div class="form-group mb-3">
<label class="form-label">Nombre del Rol *</label>
<input type="text" class="form-control" name="nombre" value="<?= $ROLedit['nombre_rol']?>">
</div>


<!-- Panel de permisos simplificado -->
<div class="form-group mb-3">
<div class="d-flex justify-content-between align-items-center mb-2">
<label class="form-label mb-0">Permisos</label>
<button type="button" class="btn btn-sm btn-outline" id="seleccionarTodos">
<i class="bi bi-check-all"></i> Seleccionar todos
</button>
</div>

<div class="permisos-simple" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 4px;">
    <?php foreach ($allow as $permiso): 
        // Verificar si el ID del permiso está en el array de IDs asignados
        $is_checked = isset($permisos_asignados[$permiso['id']]);
    ?>

    <div class="form-check mb-2">
        <input class="form-check-input permiso-checkbox" type="checkbox" 
               name="permisos[]" 
               value="<?= $permiso['id'] ?>" 
               id="permiso-<?= $permiso['nombre'] ?>"
               <?= $is_checked ? 'checked' : '' ?> > 
               
        <label class="form-check-label" for="permiso-<?= $permiso['nombre'] ?>">
            <?= htmlspecialchars($permiso['nombre']) ?>
        </label>
    </div>

    <?php endforeach; ?>
</div>



<div class="modal-footer">
<button type="submit"  class="btn btn-primary">Guardar Cambios</button>
</div>


</form>
</div>

</div>
</div>
</div>
</div>
<?php endif;?>



<script>
document.addEventListener('DOMContentLoaded', function() {
// Botón para seleccionar todos los permisos
const btnSeleccionarTodos = document.getElementById('seleccionarTodos');
const checkboxesPermisos = document.querySelectorAll('.permiso-checkbox');

btnSeleccionarTodos.addEventListener('click', function() {
const todosSeleccionados = Array.from(checkboxesPermisos).every(cb => cb.checked);

checkboxesPermisos.forEach(cb => {
cb.checked = !todosSeleccionados;
});

this.innerHTML = todosSeleccionados ? 
'<i class="bi bi-check-all"></i> Seleccionar todos' : 
'<i class="bi bi-x-circle"></i> Deseleccionar todos';
});

// Limpiar formulario al cancelar
const btnCancelar = document.getElementById('btnCancelar');
//const formCrearRol = document.getElementById('formCrearRol');

btnCancelar.addEventListener('click', function() {
// Limpiar campos de texto
document.getElementById('nombreRol').value = '';
document.getElementById('descripcionRol').value = '';

// Desmarcar todos los checkboxes
checkboxesPermisos.forEach(cb => {
cb.checked = false;
});

// Restablecer botón de seleccionar todos
btnSeleccionarTodos.innerHTML = '<i class="bi bi-check-all"></i> Seleccionar todos';

// Marcar el checkbox de activo como checked (por defecto)
document.getElementById('rol-activo').checked = true;
});

// También limpiar cuando se cierra el modal directamente con la X
const modal = document.getElementById('modalCrearRol');
modal.addEventListener('hidden.bs.modal', function() {
// Limpiar campos de texto
document.getElementById('nombreRol').value = '';
document.getElementById('descripcionRol').value = '';

// Desmarcar todos los checkboxes
checkboxesPermisos.forEach(cb => {
cb.checked = false;
});

// Restablecer botón de seleccionar todos
btnSeleccionarTodos.innerHTML = '<i class="bi bi-check-all"></i> Seleccionar todos';

// Marcar el checkbox de activo como checked (por defecto)
document.getElementById('rol-activo').checked = true;
});
});


document.addEventListener('DOMContentLoaded', function(){
const modal = document.getElementById('editMODALROL');
if(modal) {
const miModal = new bootstrap.Modal(modal);
miModal.show();

modal.addEventListener('hidden.bs.modal', function () {
//window.location.href = 'menu.php?pagina=empleados';
miModal.hide();
});
}
});
</script>
