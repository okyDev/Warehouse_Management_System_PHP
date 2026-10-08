<?php
$editID = $_POST['cedula_edita2r'] ?? null;
$wekk1 = null;
if(isset($_POST['EDITPRO'])){
///$int = 1;
$editID = $_POST['cedula_edita2r'] ?? null;
$wekk1 = $base->one_query("SELECT cedula_empleado,username,password,id FROM usuarios_acceso WHERE cedula_empleado = ?",$editID);
$profile = $base->one_query("SELECT image_name FROM usuario_profile WHERE usuario_id = ?",$wekk1['id']);
if($wekk1 == false){
$editID = null;
$wekk1 = null;
$_SESSION['error'] = "Error editando usuario";
}
}
?>


<?php if($wekk1 !== null):?>
<div class="modal fade" tabindex="-1" id="modalGestionarUsuario" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
					<div class="modal-header bg-warning text-dark">
 <h5 class="modal-title"><i class="bi bi-gear"></i> Gestionar Usuario</h5>
<button class="btn-close" data-bs-dismiss="modal">X</button>
                </div>
                

<form class="caja_singIN2" method="post" action="modulos/empleados/procesar_usuario.php" enctype="multipart/form-data">


<!---PROFILE_PICTURE---->
<div class="image-upload-section">
<input type="file" name="imageInputEdit" id="imageInputEdit" accept="image/*" style="display:none">
<label class="inputAA" for="imageInputEdit">
<img src="<?= htmlspecialchars($path_img) ?>pfp/<?= $profile['image_name'] ?? "default.png"?>" id="pfpEdit">
</label>
<span>Cambiar imagen (opcional)</span>
</div>
<!------->


<!----->
<input type="hidden" name="accion" value="gestionar">
<input type="hidden" name="cedula_empleadoedit" value="<?= $wekk1['cedula_empleado'] ?>">
<!----->
                
                
<div class="modal-body">
<div class="mb-3">
<label class="form-label">Username</label>
<input type="text" class="form-control" name="namedit" value="<?=$wekk1['username'] ?>" readonly>
</div>
                    

<div class="mb-3">
<label class="form-label">Nueva Contraseña (opcional)</label>
<input type="password" name="nueva_password" class="form-control" value="">
<div class="form-text">Dejar vacío para mantener la actual</div>
</div>
</div>

<div class="modal-footer">
<button type="submit" class="btn btn-warning">Actualizar Usuario</button>
</div>
            </form>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('modalGestionarUsuario');
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
<?php endif;?>
