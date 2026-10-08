<style>
.modal-lote .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    overflow-x:auto;
}

.modal-lote .modal-header {
    background: linear-gradient(135deg, #1e2a38, #2d3b4e);
    color: white;
    border-bottom: none;
    border-radius: 12px 12px 0 0;
    padding: 1.5rem;
}

.modal-lote .modal-body {
    padding: 2rem;
    overflow-x:auto;
}

.modal-lote .modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 1.5rem 2rem;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #f1f3f4;
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e2a38;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #4361ee;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #4361ee;
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}

.date-group {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.75rem;
}

.required-field::after {
    content: " *";
    color: #dc3545;
}

.help-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    font-size: 0.9rem;
}

.btn-primary {
    background-color: #4361ee;
    border-color: #4361ee;
    padding: 0.75rem 2rem;
    font-weight: 500;
    border-radius: 6px;
}

.btn-primary:hover {
    background-color: #3a56d4;
    border-color: #3a56d4;
    transform: translateY(-1px);
}

.btn-outline-secondary {
    border-radius: 6px;
    padding: 0.75rem 2rem;
}

.categoria-badge {
    font-size: 0.75rem;
    margin-left: 0.5rem;
    background-color: #e9ecef;
    color: #495057;
}

.proveedor-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.proveedor-info {
    flex: 1;
}

.proveedor-categorias {
    font-size: 0.75rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .date-group {
        grid-template-columns: 1fr;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .proveedor-option {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .proveedor-categorias {
        margin-top: 0.25rem;
    }
}
</style>

<!------------>
<div class="modal fade modal-lote" id="CREAR" tabindex="-1" aria-labelledby="modalCrearLoteLabel" aria-hidden="true">

<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="modalCrearLoteLabel"><i class="bi bi-plus-circle me-2"></i> Nuevo Lote de Producto (ID: <?= $AUTOID ?>)</h5>

<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form method="POST" action="modulos/lotes/procesar_lotes.php">
<input type="hidden" name="accion" value="crear_lote">
                
<div class="modal-body">
	
<!--HEADER -->
<div class="form-section">
<h6 class="section-title">
<i class="bi bi-info-circle"></i>Información Principal
</h6>        




<!-----ROW-1 DETALLES IDENTIFICACION------->
<input type="hidden" name="numero_lote" required readonly value="<?= $AUTOID ?>">
<div class="row g-4">
<!-- CATEGORÍA -->
<div class="col-md-4 border border-dark">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary rounded p-2 me-2">
                        <i class="bi bi-tags text-white"></i>
                    </div>
                    <h6 class="card-title mb-0">Categoría</h6>
                    <span class="badge bg-danger ms-2">Requerido</span>
                </div>
                <select class="form-select" id="selector-categoria" require>
                    <option value="">Elija una categoría</option>	
                    <?php foreach($categorias as $cats):?>
                    <option value="<?= $cats['id']?>"><?= $cats['nombre']?></option>
                    <?php endforeach;?>
                </select>
                <small class="form-text text-muted mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Seleccione la categoría del producto que desea ajustar
                </small>
            </div>
        </div>
    </div>

<!-- PRODUCTOS -->
<div class="col-md-4 border border-dark">
<div class="card h-100 border-0 shadow-sm">
<div class="card-body">
<div class="d-flex align-items-center mb-3">
<div class="bg-success rounded p-2 me-2"><i class="bi bi-box text-white"></i></div>
<h6 class="card-title mb-0">Productos</h6>
<span class="badge bg-danger ms-2">Requerido</span>
</div>

                
<?php foreach($categorias as $cat):?>
<div class="producto cat-<?php echo $cat['id']; ?>" style="display: none;">
<?php
$idnow = $cat['id'];
$sql = "select P.nombre, P.sku, P.marca, P.precio_venta,M.simbolo AS moneda FROM productos P JOIN monedas M ON M.id = P.moneda_venta_id WHERE P.categoria_id = ?";
$fila = $base->some_query($sql,[$idnow]);
///$fila = $resultado->fetch_all(MYSQLI_ASSOC);
?>

<?php if(!empty($fila)):?>
<select class="form-select" name="productoSKU" require>
<option value="">Elija un producto</option>


<?php foreach($fila as $p):?>
<option value="<?= $p['sku']?>">
[<?= $p['sku']?>] <?= $p['nombre']?> - <?= $p['marca']?> [<?= $p['moneda']?>-<?= $p['precio_venta']?>]
</option>
<?php endforeach;?>


</select>
<?php else:?>
<div class="alert alert-warning mt-2"><i class="bi bi-exclamation-triangle me-2"></i>
La categoría no está relacionada a ningún producto
</div>
<?php endif;?>
</div>
                <?php endforeach;?>
                
                <small class="form-text text-muted mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Los productos se muestran según la categoría seleccionada
                </small>
            </div>
        </div>
    </div>

<!-- PROVEE -->
<div class="col-md-4 border border-dark">
<div class="card h-100 border-0 shadow-sm">
<div class="card-body">
<div class="d-flex align-items-center mb-3">
<div class="bg-warning rounded p-2 me-2"><i class="bi bi-truck text-white"></i></div>
<h6 class="text-center card-title mb-0">Proveedores</h6>
<span class="badge bg-danger ms-2">Requerido</span>
</div>
                
<?php foreach($categorias as $cat):?>
<div class="producto cat-<?php echo $cat['id']; ?>" style="display: none;">
<?php
$idnow = $cat['id'];
$sql = "SELECT P.nombre_contacto as nombre, P.id, P.empresa_proveedora, CAT.nombre AS categoria, T.codigo as ctype FROM proveedores P INNER JOIN proveedor_categoria PC ON PC.proveedor_id = P.id INNER JOIN categorias CAT ON PC.categoria_id = CAT.id INNER JOIN tipos_identificacion T ON P.id_tipo = T.id WHERE CAT.activo = TRUE AND CAT.id = ?";
$fila = $base->some_query($sql,[$idnow]);
?>

<?php if(!empty($fila)):?>
<select class="form-select" name="proveedorID" require>
<option value="">Elija un proveedor</option>
<?php foreach($fila as $p):?>
<option value="<?= $p['id']?>">[<?= $p['ctype']?>-<?= $p['id']?>] - <?= $p['nombre']?></option>
<?php endforeach;?>
</select>
<?php else:?>
<div class="alert alert-warning mt-2"><i class="bi bi-exclamation-triangle me-2"></i>
La categoría no está relacionada a ningún proveedor</div>
<?php endif;?>
</div>
<?php endforeach;?>
<small class="form-text text-muted mt-2">
<i class="bi bi-info-circle me-1"></i>Los proveedores se muestran según la categoría seleccionada</small>
</div>

</div>
</div>

<hr class="border " style="height: 5px; color:black; background-color:black;">
</div>




<!----ROW 2------>
<div class="form-section"><h6 class="section-title"><i class="bi bi-currency-dollar"></i> Stock y Costos</h6>
                        
<div class="row g-4">
<div class="col-md-4">
<label class="form-label required-field">Cantidad Inicial (Stock)</label>
<input type="number" class="form-control" name="cantidad_inicial" required min="1" value="1">
</div>

<!--MONEDAS PLATA----->
<div class="col-md-4">
<label class="form-label required-field">Costo Unitario</label>
<div class="input-group">
<select class="form-select input-group-text" name="moneda_compra_id" required style="max-width: 80px;">
<?php foreach ($monedas as $mon): ?>
<option value="<?= $mon['id'] ?>"><?= htmlspecialchars($mon['simbolo']) ?></option>
<?php endforeach; ?>
</select>

<input type="text" step="0.01" class="form-control" name="costo_unitario" required placeholder="32.456,45" >
</div>
</div>


<!------->
</div>
</div>
<!------->

<!------->
<div class="form-section"><h6 class="section-title"><i class="bi bi-calendar-check"></i> Fechas</h6>
<!------->
               

<!----ROW 3------>        
<div class="row g-4">
<!------->
<div class="col-md-6">
<label class="form-label">Fecha y Hora de Registro</label>
<input type="text" class="form-control" value="<?= date("d/m/Y h:i")?>" disabled>
<input type="hidden" name="IND" value="<?= date('d')?>">
<input type="hidden" name="INM" value="<?= date('M')?>">
<input type="hidden" name="INY" value="<?= date('Y')?>">
<input type="hidden" name="INH" value="<?= date('h')?>">
<input type="hidden" name="INMI" value="<?= date('i')?>">
</div>
<!------->

<div class="col-md-6">
<label class="form-label">Fecha de Vencimiento (Opcional)</label>

<div class="date-group">
<input type="number" class="form-control" placeholder="Día" name="fecha_vencimiento_d" min="1" max="31">
<input type="number" class="form-control" placeholder="Mes" name="fecha_vencimiento_m" min="1" max="12">
<input type="number" class="form-control" placeholder="Año" name="fecha_vencimiento_y" min="<?= date('Y') ?>">
</div>
<div class="help-text">Dejar vacío si no aplica.</div>
</div>
</div>
                    
</div>




</div><!---------->
</div><!---------->


<!---------->
<div class="modal-footer">
<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
<button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i> Registrar Lote e Ingresar Stock</button>
</div>
<!---------->

</div>


</div>
</form>
</div>






<script>
$(document).ready(function() {
    $('#selector-categoria').on('change', function() {
        var categoriaSeleccionada = $(this).val();

        // 1. Deshabilita todos los inputs y oculta todos los productos
        $('.producto').hide().find('input, select, textarea').prop('disabled', true);

        // 2. Habilita los inputs y muestra solo los productos de la categoría seleccionada
        if (categoriaSeleccionada !== '0') {
            var $productosVisibles = $('.cat-' + categoriaSeleccionada);
            $productosVisibles.show().find('input, select, textarea').prop('disabled', false);
        }
    });

    // **IMPORTANTE:** Llama al evento al cargar la página para inicializar el estado
    // (Ocultar todos los productos y deshabilitar sus inputs al inicio).
    $('#selector-categoria').trigger('change');
});




//----
$(document).ready(function() {
    const selectorCategoria = $('#selector-categoria');
    const selectorProducto = $('#selector-producto');
    const selectorProveedor = $('#selector-proveedor');

    function actualizarFiltros() {
        const categoriaId = selectorCategoria.val();

        // Deshabilitar todos los selects
        selectorProducto.prop('disabled', true).val('');
        selectorProveedor.prop('disabled', true).val('');

        if (!categoriaId) {
            // No hay categoría seleccionada, resetear las opciones
            selectorProducto.find('option:gt(0)').hide();
            selectorProveedor.find('option:gt(0)').hide();
            return;
        }

        // Mostrar/Ocultar Opciones de Producto
        selectorProducto.find('option').hide(); // Oculta todas las opciones
        selectorProducto.find('option:first').show(); // Muestra el placeholder
        selectorProducto.find(`option[data-cat-id="${categoriaId}"]`).show(); // Muestra solo las de la categoría
        selectorProducto.prop('disabled', false); // Habilita el select de Producto

        // Mostrar/Ocultar Opciones de Proveedor
        selectorProveedor.find('option').hide(); // Oculta todas las opciones
        selectorProveedor.find('option:first').show(); // Muestra el placeholder
        selectorProveedor.find(`option[data-cat-id="${categoriaId}"]`).show(); // Muestra solo las de la categoría
        selectorProveedor.prop('disabled', false); // Habilita el select de Proveedor
    }

    // Usamos el evento change para actualizar los filtros
    selectorCategoria.on('change', actualizarFiltros);
    
    // Ejecutar al cargar para inicializar el estado
    actualizarFiltros();
});
</script>
