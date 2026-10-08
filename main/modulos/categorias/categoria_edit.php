<?php 
// modulos/categorias/categoria_edit.php
//require_once "../../../config/connect.php";

$idCATEDIT = $_POST['GETID'] ?? null;
$catEDIT = null;

if($idCATEDIT){
$sql = "SELECT * FROM categorias WHERE activo = 1 AND id = ?";
$catEDIT = $base->one_query($sql,$idCATEDIT);
}
?>

<?php if($catEDIT !== null): ?>
<div class="modal fade" tabindex="-1" id="modalEditarCategoria" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="modulos/categorias/procesar_categorias.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" value="<?= $catEDIT['id'] ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Editar Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nombre de Categoría</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($catEDIT['nombre']) ?>" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>



<script>
document.addEventListener('DOMContentLoaded',function(){
const mimodal = document.getElementById('modalEditarCategoria');
       const miModal = new bootstrap.Modal(mimodal);
       miModal.show();
});
</script>
