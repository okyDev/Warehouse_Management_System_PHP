<?php 
// modulos/empleados/empleado_edit.php
//require_once "../../../config/connect.php";7/
$empEDIT = null;
///$empleadoEDIT = null;

if(isset($_POST['EDITPAUP'])){
$empleadoEDIT = $_POST['cedula_editar'];
$sql = "SELECT E.*, T.codigo FROM empleados_datos E JOIN tipos_identificacion T ON E.id_tipo = T.id WHERE E.cedula = ?";
$empEDIT = $base->one_query($sql,$empleadoEDIT);
}
?>

<?php if($empEDIT !== null): ?>
<div class="modal fade" tabindex="-1" id="modalEditarEmpleado" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
			
			<div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                
            <form action="modulos/empleados/procesar_empleados.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="cedula" value="<?= $empEDIT['cedula'] ?>">
                
                

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tipo Documento</label>
                                <select name="id_tipo" class="form-select" required>
                                    <?php foreach ($tipos_id as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>" 
                                        <?= $tipo['id'] == $empEDIT['id_tipo'] ? 'selected' : '' ?>>
                                        <?= $tipo['codigo'] ?> - <?= $tipo['tipo'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Cedula (Obligatorio)</label>
                                <input type="text" class="form-control" value="<?= $empEDIT['cedula'] ?>" disabled>
                                <div class="form-text">La cédula no se puede modificar</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Primer Nombre (Obligatorio)</label>
                                <input type="text" name="nombre1" class="form-control" 
                                       value="<?= htmlspecialchars($empEDIT['nombre1']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Segundo Nombre</label>
                                <input type="text" name="nombre2" class="form-control" 
                                       value="<?= htmlspecialchars($empEDIT['nombre2'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Apellido (Obligatorio)</label>
                                <input type="text" name="apellido" class="form-control" 
                                       value="<?= htmlspecialchars($empEDIT['apellido']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Telefono</label>
                                <input type="text" name="telefono" class="form-control" 
                                       value="<?= htmlspecialchars($empEDIT['telefono'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Direccion</label>
                        <textarea name="direccion" class="form-control" rows="3"><?= htmlspecialchars($empEDIT['direccion'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
<button type="submit" class="btn btn-warning">Actualizar Empleado</button>
</div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('modalEditarEmpleado');
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
<?php endif; ?>
