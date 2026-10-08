<?php
$editMONEDA = null;
if($_POST){
$valorID = $_POST['idPROVE'];


if(isset($valorID)){
$sqlscript = "SELECT * FROM monedas WHERE activo = 1 AND id = ? LIMIT 1";
$editMONEDA = $base->one_query($sqlscript,$valorID);
}
}
?>


<?php if($editMONEDA !== null): ?>
<div class="modal fade" tabindex="-1" id="modalEditarMoneda">
<div class="modal-dialog">

<div class="modal-content">

<form action="modulos/monedas/procesar_moneda.php" method="POST">
<input type="hidden" name="accion" value="editar">
                
<div class="modal-header bg-warning text-dark">
<h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Moneda</h5>

<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<input type="hidden" value="<?php echo $editMONEDA['id'];?>" name="idEDIT">

<div class="modal-body">
<div class="mb-3">
<label class="form-label">Código <span class="text-danger">*</span></label>
<input type="text" class="form-control" name="codigo" maxlength="3" 
value="<?= $editMONEDA['codigo']?>" required>

</div>
                    

<div class="mb-3">
<label class="form-label">Símbolo</label>
<input type="text" class="form-control" name="simbolo" value="<?= $editMONEDA['simbolo']?>">
</div>
                    

<div class="mb-3">
<label class="form-label">Tasa de Cambio <span class="text-danger">*</span></label>
<input type="number" step="0.0001" class="form-control" name="tasa_cambio" value="<?= $editMONEDA['tasa_cambio']?>" required>
                    </div>
                    
                </div>
                

<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancelar</button>

<button type="submit" class="btn btn-warning"><i class="bi bi-check-circle"></i> Actualizar Moneda</button>

</div>

</form>
        </div>
    </div>
</div>


<?php endif; ?>






<script>
document.addEventListener('DOMContentLoaded',function(){
const mimodal = document.getElementById('modalEditarMoneda');
       const miModal = new bootstrap.Modal(mimodal);
       miModal.show();
});
</script>
