<?php 
// ** l-2-ajuste-lote.php **
///session_start();
//accion
$LOT = [];
$SKUD = null;
$producto_categoria_id = null; // Variable para guardar la ID de categoría del producto
$producto_sku_lote = null; 

// --- 1. OBTENER DATOS DEL LOTE A EDITAR ---
if (isset($_POST['butEDIT'])) {
    $SKUD = $_POST['EDITLOL'];
    
    $sql = "SELECT L.*, P.categoria_id 
            FROM lotes L 
            INNER JOIN productos P ON L.producto_sku = P.sku
            WHERE L.activo = 1 AND L.lote_sku = ?";

    $LOT = $base->one_query($sql,$SKUD);

    if (!empty($LOT)) {
        // Guardamos los valores del lote para usarlos en el formulario
        $producto_categoria_id = $LOT['categoria_id'];
        $producto_sku_lote = $LOT['producto_sku'];
    }
}

// Si no hay lote, no continuamos con la carga de Modals.
if (empty($LOT)) {
    // Si llegamos sin lote, podemos redirigir o simplemente no mostrar nada.
    return; // Detiene la ejecución si no hay datos.
}
$sql_prod_data = "SELECT P.nombre, P.marca, M.simbolo AS moneda, P.precio_venta 
FROM productos P 
JOIN monedas M ON M.id = P.moneda_venta_id 
WHERE P.sku = ?";

$producto_data = $base->one_query($sql_prod_data,$producto_sku_lote);
?>

<?php if(!empty($LOT)):?>
<div class="modal fade" id="modalAjuste" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
<div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <form action="modulos/lotes/procesar_lotes.php" method="POST">
                        <input type="hidden" name="accion" value="ajuste_stock">
                        <input type="hidden" name="lote_id" value="<?= htmlspecialchars($LOT['lote_sku']) ?>">
                        <input type="hidden" name="empleado_id" value="<?= $_SESSION['empleado_cedula'] ?? '123456' ?>">
                            
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">Ajustar Lote ID: <?= htmlspecialchars($LOT['lote_sku']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button> 
                        </div>
                        <div class="modal-body">
                            <p>Producto: **<?= htmlspecialchars($LOT['producto_sku']) ?>**</p>
                            <p>Stock Actual: **<?= htmlspecialchars($LOT['stock_actual']) ?>**</p>
                            
                            <div class="mb-3">
                                <label for="nueva_cantidad" class="form-label">Nueva Cantidad Disponible</label>
                                <input type="number" class="form-control" name="nueva_cantidad" min="0" value="<?= htmlspecialchars($LOT['stock_actual']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="motivo" class="form-label">Motivo del Ajuste (Obligatorio)</label>
                                <textarea class="form-control" name="motivo" required></textarea>
                                <small class="text-muted">Ej: Daño por transporte, Conteo Físico, Pérdida.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning">Aplicar Ajuste</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function(){
            const modalAjuste = document.getElementById('modalAjuste');
            if(modalAjuste) {
                const miModal = new bootstrap.Modal(modalAjuste);
                miModal.show();
                
                // Si cierran el modal, forzamos una redirección para salir del estado de edición
               
            }
        });
        </script>

<?php endif; // Fin de la verificación if(!empty($LOT)) 


/*

<?php if($_SESSION['user_rol'] === 'Administrador' || $_SESSION['user_rol'] === "programador"):?>
<div class="modal fade modal-lote" id="modalEditarAvanzado" tabindex="-1" aria-labelledby="modalEditarLoteLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header bg-danger text-white">
<h5 class="modal-title" id="modalEditarLoteLabel"><i class="bi bi-pencil-square me-2"></i>Edición Avanzada de Lote: <?= htmlspecialchars($LOT['lote_sku']) ?></h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>


<form method="POST" action="modulos/lotes/procesar_lotes.php">
<input type="hidden" name="accion" value="editar_lote_avanzado">
<input type="hidden" name="lote_sku" value="<?= htmlspecialchars($LOT['lote_sku']) ?>">

<input type="hidden" name="empleado_id" value="<?php echo $_SESSION['user_cedula']; ?>">

<div class="modal-body">
<div class="form-section">
<h6 class="section-title"><i class="bi bi-tag"></i> Producto y Lote <?= $_SESSION['user_cedula']?></h6>

<!------->
<div class="row g-4">
<!------->
<div class="col-md-4">
<label class="form-label">Categoría Actual</label>
<?php 
// Buscar el nombre de la categoría del lote
$cat_name = array_filter($categorias, fn($c) => $c['id'] == $producto_categoria_id);
$cat_name = reset($cat_name)['nombre'] ?? 'Desconocida';
?>

<input type="text" class="form-control" value="<?= htmlspecialchars($cat_name) ?>" disabled>
<input type="hidden" name="categoria_id" value="<?= htmlspecialchars($producto_categoria_id) ?>">

</div>

<!------->

<div class="col-md-8">
<label class="form-label">Producto Asociado</label>
<input type="text" class="form-control"value="[<?= htmlspecialchars($LOT['producto_sku']) ?>] <?= htmlspecialchars($producto_data['nombre'] ?? '') ?> - <?= htmlspecialchars($producto_data['marca'] ?? '') ?>" disabled>

<input type="hidden" name="producto_sku" value="<?= htmlspecialchars($LOT['producto_sku']) ?>">
<small class="form-text text-danger">El Producto y la Categoría **no se pueden cambiar** en un lote existente.</small>

</div>
</div>
</div>

<!------->              
<div class="form-section">
<h6 class="section-title"><i class="bi bi-gear"></i> Datos Editables (Requiere Auditoría)</h6>


<!------->
<div class="row g-4 mb-4">

<!------->
<div class="col-md-6">
<label class="form-label required-field">Proveedor</label>
<select class="form-select" name="proveedor_id" required>
<option value="">Elija un proveedor</option>
<?php 
$sql_prov = "SELECT P.nombre_contacto AS nombre, P.id, T.codigo AS ctype 
FROM proveedores P 
INNER JOIN proveedor_categoria PC ON PC.proveedor_id = P.id 
INNER JOIN tipos_identificacion T ON P.id_tipo = T.id 
WHERE PC.categoria_id = ?";

$proveedores_lote = $base->one_query($sql_prov,$producto_categoria_id);
//$query_prov->close();
                                            
                                            foreach($proveedores_lote as $p): 
                                                $selected = ($p['id'] === $LOT['proveedor_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $p['id']?>" <?= $selected ?>>
                                                    [<?= $p['ctype']?>-<?= $p['id']?>] - <?= $p['nombre']?>
                                                </option>
                                            <?php endforeach;?>
                                        </select>
                                    </div>
                                    

<!------->
<div class="col-md-6">
<label class="form-label required-field">Costo Unitario</label>
<div class="input-group">
<select class="form-select input-group-text" name="moneda_compra_id" required style="max-width: 80px;">
<?php foreach ($monedas as $mon): 
$selected = ($mon['id'] === $LOT['moneda_compra_id']) ? 'selected' : '';?>
<option value="<?= $mon['id'] ?>" <?= $selected ?>><?= htmlspecialchars($mon['simbolo']) ?></option>
<?php endforeach; ?>
                                            </select>
<input type="number" step="0.01" class="form-control" name="costo_unitario" 
                                                value="<?= htmlspecialchars($LOT['costo_unitario']) ?>" required min="0.01">
                                        </div>
                                        <small class="form-text text-danger">Cambiar el costo afecta la valoración del inventario.</small>
                                    </div>
                                </div>
                                
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <label class="form-label">Fecha de Vencimiento (Opcional)</label>
                                        <div class="date-group">
                                            <input type="number" class="form-control" placeholder="Día" name="fecha_vencimiento_d" min="1" max="31"
                                                value="<?= htmlspecialchars($LOT['fecha_vencimiento_d'] ?? '') ?>">
                                            <input type="number" class="form-control" placeholder="Mes" name="fecha_vencimiento_m" min="1" max="12"
                                                value="<?= htmlspecialchars($LOT['fecha_vencimiento_m'] ?? '') ?>">
                                            <input type="number" class="form-control" placeholder="Año" name="fecha_vencimiento_y" min="<?= date('Y') ?>"
                                                value="<?= htmlspecialchars($LOT['fecha_vencimiento_y'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <label for="motivo_edicion" class="form-label required-field">Razón del Cambio Avanzado</label>
                                <textarea class="form-control" name="motivo" rows="2" required></textarea>
                                <small class="text-muted">Describa por qué está cambiando estos valores (Ej: Corrección de error de factura, Ajuste de moneda).</small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger"><i class="bi bi-save me-2"></i> Guardar Cambios Avanzados</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            const modalAvanzado = document.getElementById('modalEditarAvanzado');
            if(modalAvanzado) {
                const miModal = new bootstrap.Modal(modalAvanzado);
                miModal.show();
            }
        });
        </script>


    <?php else: // Si no es Admin/Programador, solo puede hacer un AJUSTE SIMPLE ?>
*/
?>
