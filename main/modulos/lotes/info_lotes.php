<?php 
// modulos/lotes/l-3-ver-lote.php (Nombre sugerido)

$nVER = [];
$historial = [];
if(isset($_POST['VERMAS'])){
    $SKUD = $_POST['CODVER'];

    // --- 1. Consulta Principal del Lote ---
    $sql1 = "SELECT 
        L.*, -- Selecciona todas las columnas de lotes (incluye fechas, costos, etc.)
        T.codigo as RIF, 
        M.simbolo as monedas, 
        P.nombre as producto, 
        PRO.nombre_contacto as nombrePRO
    FROM lotes L    
    INNER JOIN productos P ON P.sku = L.producto_sku    
    INNER JOIN proveedores as PRO ON PRO.id = L.proveedor_id    
    INNER JOIN monedas as M ON M.id = L.moneda_compra_id    
    INNER JOIN tipos_identificacion T ON T.id = PRO.id_tipo
    WHERE L.activo = true AND L.lote_sku = ?";

    $nVER =$base->one_query($sql1,$SKUD);


    
    // --- 2. Consulta de Historial (CORREGIDO: fetch_all) ---
    if($nVER){
        $sql2 = "SELECT * FROM cambios_lotes WHERE lote_id = ? ORDER BY id DESC"; // Añadir ORDER BY para ver lo más reciente primero
        $historial = $base->one_query_all($sql2,$SKUD);
    }
}

?>


<?php if(!empty($nVER)): ?>
<div class="modal fade" id="SEELOT" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header bg-dark text-white">
<h5 class="modal-title" id="modalLabel">Detalles del Lote **<?= $nVER['lote_sku']?>**</h5> 
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>


<div class="modal-body">
    <div class="row mb-4">
        <div class="col-md-6">
            <p><strong>SKU y Producto:</strong> <?=$nVER['producto_sku'] ?> - <span class="fw-bold"><?= $nVER['producto']?></span></p>
            <p><strong>Proveedor:</strong> [<?= $nVER['RIF']?>- <?= $nVER['proveedor_id']?>] <?= $nVER['nombrePRO']?></p>
            <p><strong>Costo Unitario:</strong> <span class="fw-bold text-success"><?= $nVER['monedas']?> <?= number_format($nVER['costo_unitario'], 2, ',', '.') ?></span></p> 
        </div>
        <div class="col-md-6">
            <p><strong>Cantidad Inicial:</strong> <?= $nVER['cantidad_inicial']?></p>
            <p><strong>Stock Actual:</strong> <span class="fw-bold text-danger"><?=$nVER['stock_actual'] ?></span></p>
            <p><strong>Fecha de Ingreso:</strong> <?= $nVER['fecha_entrada_d']?>/<?= $nVER['fecha_entrada_m']?>/<?= $nVER['fecha_entrada_y']?> <?= $nVER['fecha_entrada_H']?>:<?= $nVER['fecha_entrada_mi']?></p>
            
            <?php if(!empty($nVER['fecha_vencimiento_d']) && !empty($nVER['fecha_vencimiento_y'])):?>
            <p><strong>Fecha de Vencimiento:</strong> <span class="text-warning fw-bold"><?= $nVER['fecha_vencimiento_d']?>/<?= $nVER['fecha_vencimiento_m']?>/<?= $nVER['fecha_vencimiento_y']?></span></p>
            <?php else:?>
            <p><strong>Fecha de Vencimiento:</strong> <span class="text-muted">No aplica</span></p>
            <?php endif;?>
        </div>
    </div>
    
    <hr>
    
    <h6><i class="bi bi-clock-history me-1"></i> Historial de Cambios (<?= count($historial) ?> registros)</h6>
    <div class="history-box" style="max-height: 300px; overflow-y: auto;">
        <ul class="list-group list-group-flush">
        <?php if (!empty($historial)): ?>
            <?php foreach($historial as $h):?>
            <li class="list-group-item list-group-item-light small">
                <span class="text-muted">[<?= $h['fecha_cambioD']?>/<?= $h['fecha_cambioM']?>/<?=$h['fecha_cambioY'] ?> <?=$h['fecha_cambioH'] ?>:<?=$h['fecha_cambioMI'] ?>]</span> 
                **Empleado <?= $h['empleado_id'] ?>** realizó un cambio en el campo `<?= $h['campo_afectado'] ?>`.
                <br>
                Motivo: *<?= htmlspecialchars($h['razon'])?>*
                <br>
                De: <span class="badge bg-danger"><?= htmlspecialchars($h['valor_anterior'] ?? 'ninguno') ?? 'ninguno'?></span> 
                A: <span class="badge bg-success"><?= htmlspecialchars($h['valor_nuevo'])?></span> 
                <span class="text-info float-end"><?= htmlspecialchars($h['tipo_cambio']) ?></span>
            </li>
            <?php endforeach;?>
        <?php else: ?>
            <li class="list-group-item text-center text-muted">No hay registros de cambios para este lote.</li>
        <?php endif; ?>
        </ul>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
</div>
</div>
</div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function(){
    const modalElement = document.getElementById('SEELOT');
    if(modalElement) {
        // Usar la clase de Bootstrap 5 para el modal
        const miModal = new bootstrap.Modal(modalElement); 
        miModal.show();
        
        modalElement.addEventListener('hidden.bs.modal', function () {
             // Opcional: Redirigir o limpiar el estado si es necesario.
             // window.location.href = '../../menu.php?pagina=lotes'; 
        });
    }
});
</script>
<?php endif;?>
