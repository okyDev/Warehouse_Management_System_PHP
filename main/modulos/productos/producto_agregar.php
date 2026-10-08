<?php if(verify("crear_productos")):?>
<div class="modal fade" id="modalCrearProducto">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="modulos/productos/procesar_productos.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
									
                    <div class="row">
											<!--1---->
                        <div class="col-md-6"> <!------>
													
													
                            <div class="mb-3">
                                <label class="form-label">SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($skuGenerado) ?>" readonly>
                                <div class="form-text">SKU generado automáticamente</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>

                        </div>
                        <!--1---->

<!---2--->        
<div class="col-md-6">
<div class="mb-3">
<label class="form-label">Precio Venta <span class="text-danger">*</span></label>

<div class="form-inline">                              
<select name="moneda_venta_id" required>
<?php foreach ($monedas as $mon): ?>
<option value="<?= $mon['id'] ?>"><?= htmlspecialchars($mon['codigo']) ?></option>
<?php endforeach; ?>
</select>
                               
<input type="text" placeholder="3.567,64" name="precio_venta" required>                        
</div>
</div>

<br>
<div class="mb-3">
<label class="form-label">Marca</label>
<input name="marca" class="form-control" type="text" placeholder="Ejem: LOS ANDES">
</div>

                                

</div>
</div>
<!---MANO TERMINA AQUI 2--->


<div class="row">
<!---3-->
<div class="col-md-6">
<div class="mb-3">
<label class="form-label">Minimo aceptado</label>
<input name="stock_minimo" class="form-control" type="number" step="0" value="5" min="1">
<small class="text-muted">Ingrese un minimo para que el sistema notifique cuando un producto escasea</small>
</div>         
</div>
<!---MANO TERMINA AQUI 3--->




<div class="col-md-6"><!------>
                            <div class="mb-3">
                                <label class="form-label">Categoría</label>
                                <select name="categoria_id" class="form-select">
                                    <option value="">Sin categoría</option>
                                    <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                          
     
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="EJEM: 900GR, 2LT, 1KG"></textarea>
                    </div>
</div>

<!------>
</div>   
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </div>
            </form>
        </div>
</div>
</div>
</div>
<?php endif;?>
