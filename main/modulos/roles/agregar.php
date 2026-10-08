<!-- Modal para crear rol -->
<div class="modal fade" id="modalCrearRol" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCrearRol" action="modulos/roles/procesar.php" method="POST">
                    <input type="hidden" name="accion" value="crear">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Nombre del Rol *</label>
                        <input type="text" class="form-control" name="nombre_rol" required 
                               placeholder="Ej: Administrador, Vendedor, Almacén" id="nombreRol">
                    </div>



                    <!-- Panel de permisos simplificado -->
                    <div class="form-group mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Permisos</label>
                            <button type="button" class="btn btn-sm btn-outline" id="seleccionarTodos">
                                <i class="bi bi-check-all"></i> Seleccionar todos
                            </button>
                        </div>
                        
                        <div class="permisos-simple" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 4px;">
                            <?php foreach ($allow as $permiso): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input permiso-checkbox" type="checkbox" 
                                       name="permisos[]" value="<?= $permiso['nombre'] ?>" 
                                       id="permiso-<?= $permiso['nombre'] ?>">
                                <label class="form-check-label" for="permiso-<?= $permiso['nombre'] ?>">
                                    <?= htmlspecialchars($permiso['nombre']) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="btnCancelar" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formCrearRol" class="btn btn-primary">Crear Rol</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Botón para seleccionar todos los permisos
    const btnSeleccionarTodos = document.getElementById('seleccionarTodos');
    const checkboxesPermisos = document.querySelectorAll('.permiso-checkbox');
    
    btnSeleccionarTodos.addEventListener('click', function() {
        const todosSeleccionados = Array.from(checkboxesPermisos).every(cb => cb.checked);
        
        checkboxesPermisos.forEach(cb => {
            cb.checked = !todosSeleccionados;
        });
        
        this.innerHTML = todosSeleccionados ? 
            '<i class="bi bi-check-all"></i> Seleccionar todos' : 
            '<i class="bi bi-x-circle"></i> Deseleccionar todos';
    });
    
    // Limpiar formulario al cancelar
    const btnCancelar = document.getElementById('btnCancelar');
    const formCrearRol = document.getElementById('formCrearRol');
    
    btnCancelar.addEventListener('click', function() {
        // Limpiar campos de texto
        document.getElementById('nombreRol').value = '';
        document.getElementById('descripcionRol').value = '';
        
        // Desmarcar todos los checkboxes
        checkboxesPermisos.forEach(cb => {
            cb.checked = false;
        });
        
        // Restablecer botón de seleccionar todos
        btnSeleccionarTodos.innerHTML = '<i class="bi bi-check-all"></i> Seleccionar todos';
        
        // Marcar el checkbox de activo como checked (por defecto)
        document.getElementById('rol-activo').checked = true;
    });
    
    // También limpiar cuando se cierra el modal directamente con la X
    const modal = document.getElementById('modalCrearRol');
    modal.addEventListener('hidden.bs.modal', function() {
        // Limpiar campos de texto
        document.getElementById('nombreRol').value = '';
        document.getElementById('descripcionRol').value = '';
        
        // Desmarcar todos los checkboxes
        checkboxesPermisos.forEach(cb => {
            cb.checked = false;
        });
        
        // Restablecer botón de seleccionar todos
        btnSeleccionarTodos.innerHTML = '<i class="bi bi-check-all"></i> Seleccionar todos';
        
        // Marcar el checkbox de activo como checked (por defecto)
        document.getElementById('rol-activo').checked = true;
    });
});
</script>
