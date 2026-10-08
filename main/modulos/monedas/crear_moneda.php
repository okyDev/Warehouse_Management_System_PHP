<div class="modal fade" id="modalCrearMoneda" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
					
            <form action="modulos/monedas/procesar_moneda.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Nueva Moneda
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="codigo" 
                               placeholder="Ej: USD, EUR, VES" maxlength="3" required>
                        <div class="form-text">Código de 3 letras (ISO 4217)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Símbolo</label>
                        <input type="text" class="form-control" name="simbolo" 
                               placeholder="Ej: $, €, Bs.">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tasa de Cambio <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" class="form-control" name="tasa_cambio" 
                               placeholder="Bs 1.0000" required>
                    </div>
                    
                    
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar Moneda
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
