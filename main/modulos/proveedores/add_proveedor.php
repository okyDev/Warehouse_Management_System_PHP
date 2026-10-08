<div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
					
					
					
            <form action="modulos/proveedores/procesar_proveedor.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearLabel">Registrar Nuevo Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tus campos actuales aquí -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="id_tipo" class="form-label">Tipo ID</label>
                            <select class="form-select" id="id_tipo" name="id_tipo" required>
                                <?php echo get_id_options($tipos_id); ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="id" class="form-label">RIF / Documento (ID)</label>
                            <input type="text" class="form-control" id="id" name="id" required>
                            <small class="text-muted">Este será el ID principal del proveedor.</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="empresa_proveedora" class="form-label">Nombre de la Empresa</label>
                        <input type="text" class="form-control" id="empresa_proveedora" name="empresa_proveedora" required>
                    </div>
                    
                    <div class="mb-3">
                            <label for="rifE" class="form-label">RIF de la empresa (Opcional)</label>
                            <input type="text" class="form-control" id="rifE" name="rifE"  placeholder="J-123456789">
                            <small class="text-muted">Este será el ID principal del proveedor.</small>
                        </div>
                        
                    <div class="mb-3">
                        <label for="nombre_contacto" class="form-label">Nombre del Contacto</label>
                        <input type="text" class="form-control" id="nombre_contacto" name="nombre_contacto" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono">
                        </div>
                    </div>
                   
                   <label for="categorias" class="form-label fw-bold"><i class="bi bi-tags"></i> Categorías que Suministra</label>
                       <?php if (!empty($categorias)):?>
                       <?php foreach($categorias as $cat):?>
                       <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categorias[]" 
                                       value="<?= $cat['id'] ?>" id="cat_<?= $cat['id'] ?>">
                                <label class="form-check-label" for="cat_<?= $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </label>
                            </div>
                        <?php endforeach;?>
                         <?php else:?>
<div class="text-muted">No hay categorías disponibles</div>
                         <?php endif;?>
                       
                        <small class="text-muted">Selecciona las categorías de productos que este proveedor puede suministrar. Usa CTRL/CMD para seleccionar múltiples.</small>
                </div>
<div class="mb-4">

 <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripcion (Opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    
                    
                        
                    </div>
                </div> <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
                </div>
                </div>
            </form>
        </div>
    </div>
