<div class="modal fade" id="modalCrearEmpleado">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="modulos/empleados/procesar_empleados.php" method="POST">
							
                <input type="hidden" name="accion" value="crear">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Nuevo Empleado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
									
									
<!------->
<div class="row"> 

<!------->
<div class="col-md-4">

<div class="mb-3">
<label class="form-label">Tipo Documento <span class="text-danger">*</span></label>
<select name="id_tipo" class="form-select" required>
<?php foreach ($tipos_id as $tipo): ?>
<option value="<?= $tipo['id'] ?>"><?= $tipo['codigo'] ?> - <?= $tipo['tipo'] ?></option>
<?php endforeach; ?>
</select>
</div>
</div>


<div class="col-md-8">
<div class="mb-3">
<label class="form-label">Cedula (Obligatorio) <span class="text-danger">*</span></label>
<input type="text" name="cedula" class="form-control" required pattern="[0-9]+" title="Solo numeros permitidos">

</div>
</div>
</div>


<!------->            
<div class="row">
<div class="col-md-6">

<!------->
<div class="mb-3">
<label class="form-label">Primer Nombre (Obligatorio) <span class="text-danger">*</span></label>
<input type="text" name="nombre1" class="form-control" required>
</div>

</div>


<div class="col-md-6">
<div class="mb-3">
<label class="form-label">Segundo Nombre</label>
<input type="text" name="nombre2" class="form-control">
</div>
</div>
</div>
                    

<!------->
<div class="row">
<!------->
<div class="col-md-6">
<div class="mb-3">
<label class="form-label">Apellido (Obligatorio) <span class="text-danger">*</span></label>
<input type="text" name="apellido" class="form-control" required>
</div>

</div>

<div class="col-md-6">
<div class="mb-3">
<label class="form-label">Teléfono</label>
<input type="text" name="telefono" class="form-control">
</div>
</div>
</div>
                    
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea name="direccion" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Empleado</button>
                </div>
            </form>
        </div>
    </div>
</div>
