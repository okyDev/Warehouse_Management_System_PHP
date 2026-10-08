<?php 
// modulos/productos/producto_edit.php
///require_once "../../../config/connect.php";

$skuEDIT = null;
$prodEDIT = null;
$proveedoresProducto = [];
$alw = verify("editar_productos");

if(isset($_POST['GETIT']) and $alw === true){
    $skuEDIT = $_POST['EDITPRODUCTO'];
    
    // Obtener datos del producto
    $sql = "SELECT P.*, C.nombre as categoria, M.codigo as moneda
            FROM productos P 
            LEFT JOIN categorias C ON P.categoria_id = C.id 
            LEFT JOIN monedas M ON P.moneda_venta_id = M.id 
            WHERE P.sku = ? AND P.activo = 1";
	$prodEDIT = $base->one_query($sql,$skuEDIT);

    
    
    // Obtener listas para selects
    $categorias = $base->query_all("SELECT * FROM categorias WHERE activo = 1");
    $monedas = $base->query_all("SELECT * FROM monedas WHERE activo = 1");
    $proveedores = $base->query_all("SELECT * FROM proveedores WHERE activo = 1");
}
?>

<?php if($prodEDIT !== null): ?>
<div class="modal fade" tabindex="-1" id="modalEditarProducto" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
					
            <form action="modulos/productos/procesar_productos.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="sku" value="<?= $prodEDIT['sku'] ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Editar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control" value="<?= $prodEDIT['sku'] ?>" disabled>
                                <div class="form-text">El SKU no se puede modificar</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" 
                                       value="<?= htmlspecialchars($prodEDIT['nombre']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Precio Venta <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="precio_venta" class="form-control" 
                                       value="<?= $prodEDIT['precio_venta'] ?>" required>
                            </div>
                            
                            
                            <div class="mb-3">
                                <label class="form-label">Marca <span class="text-danger">*</span></label>
<input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($prodEDIT['marca']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Categoría</label>
                                <select name="categoria_id" class="form-select">
                                    <option value="">Sin categoría</option>
                                    <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                        <?= $cat['id'] == $prodEDIT['categoria_id'] ? 'selected' : '' ?>>
                                        <?= $cat['nombre'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Moneda Venta <span class="text-danger">*</span></label>
                                <select name="moneda_venta_id" class="form-select" required>
                                    <?php foreach ($monedas as $mon): ?>
                                    <option value="<?= $mon['id'] ?>" 
                                        <?= $mon['id'] == $prodEDIT['moneda_venta_id'] ? 'selected' : '' ?>>
                                        <?= $mon['codigo'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Stock Minimo</label>
                                <input type="number" name="stock_minimo" class="form-control" 
                                       value="<?= $prodEDIT['stock_minimo'] ?>" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($prodEDIT['descripcion'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function(){
const modalAjuste = document.getElementById('modalEditarProducto');
if(modalAjuste) {
const miModal = new bootstrap.Modal(modalAjuste);
miModal.show();

// Si cierran el modal, forzamos una redirección para salir del estado de edición

}
});
        </script>
