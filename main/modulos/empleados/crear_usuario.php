<div class="modal fade" tabindex="-1" id="modalCrearUsuario" data-bs-backdrop="static">
<div class="modal-dialog">
<div class="modal-content">
					<!------->
	
<form class="caja_singIN2" method="post" action="modulos/empleados/procesar_usuario.php" enctype="multipart/form-data">

<!---PROFILE_PICTURE---->
<div class="image-upload-section">
<input type="file" name="imageInput" id="imageInput" accept="image/*" style="display:none">
<label class="inputAA" for="imageInput">
<img src="<?php echo htmlspecialchars($path_img);?>pfp/default.png" id="pfp" alt="Previsualización de imagen">
</label>
<span>Ingrese imagen</span>
</div>
<!------->


<input type="hidden" name="accion" value="crear">
                
<div class="modal-header bg-info text-white">
<h5 class="modal-title"><i class="bi bi-person-check"></i> Crear Usuario</h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
                
<div class="modal-body">


<!----1--->
<div class="mb-3">
<label class="form-label">Empleado</label>
<select name="empleadoChoose" class="form-select">
<?php if(empty($nuevoU)):?>
<option>NO ah ingresado un empleado aun!</option>
<?php else:?>
<?php foreach($nuevoU as $emp):?>
<option value="<?= $emp['cedula']?>"> <?= $emp['apellido']?> - <?= $emp['cedula']?></option>
<?php endforeach;?>
<?php endif;?>
</select>
</div>




<!---2---->
<div class="mb-3">
<label class="form-label">Username <span class="text-danger">*</span></label>
<input type="text" name="username" class="form-control" required pattern="[a-zA-Z0-9_]+" title="Solo letras, números y guión bajo">
</div>
                    


<!----3--->
<div class="mb-3">
<label class="form-label">Contraseña <span class="text-danger">*</span></label>
<input type="password" name="password" class="form-control" required minlength="4">
<div class="form-text">Mínimo 4 caracteres</div>
</div>
                    

<!---4---->
<div class="mb-3">
<label class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
<input type="password" name="confirm_password" class="form-control" required>
</div>
                    

<!----5--->
<div class="mb-3">
<label class="form-label">Rol <span class="text-danger">*</span></label>
<select name="rol_id" class="form-select" required>

<?php foreach ($roles as $rol): ?>
<option value="<?= $rol['rol_id'] ?>"><?= $rol['nombre_rol'] ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
                
<!----6--->
<div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">Crear Usuario</button>
                </div>
            </form>
        
        
        

</div>
</div>
</div>
