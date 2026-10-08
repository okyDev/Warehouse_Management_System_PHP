<!-- Botón para abrir el modal de notificaciones -->
<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#notificacionModal">
  <i class="fas fa-bell me-2"></i>Nueva Notificación
</button>

<!-- Modal de notificaciones -->
<div class="modal fade" id="notificacionModal" tabindex="-1" aria-labelledby="notificacionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-gradient-info text-white">
        <h5 class="modal-title" id="notificacionModalLabel">
          <i class="fas fa-bell me-2"></i>Crear Notificación
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formNotificacion">
          <div class="row">
            <!-- Columna izquierda: Información básica -->
            <div class="col-md-8">
              <!-- Título -->
              <div class="mb-3">
                <label for="tituloNotificacion" class="form-label fw-bold">
                  <i class="fas fa-heading me-1"></i>Título de la notificación
                </label>
                <input type="text" class="form-control" id="tituloNotificacion" placeholder="Ej: Actualización del sistema" required>
              </div>
              
              <!-- Mensaje -->
              <div class="mb-3">
                <label for="mensajeNotificacion" class="form-label fw-bold">
                  <i class="fas fa-comment-alt me-1"></i>Mensaje
                </label>
                <textarea class="form-control" id="mensajeNotificacion" rows="4" placeholder="Escribe aquí el contenido de la notificación..." required></textarea>
                <div class="form-text">Máximo 500 caracteres.</div>
              </div>
              
              <!-- Remitente -->
              <div class="mb-3">
                <label for="remitenteNotificacion" class="form-label fw-bold">
                  <i class="fas fa-user me-1"></i>Remitente
                </label>
                <select class="form-select" id="remitenteNotificacion">
                  <option value="sistema">Sistema</option>
                  <option value="administrador">Administrador</option>
                  <option value="usuario">Usuario</option>
                  <option value="personalizado">Personalizado</option>
                </select>
                <div class="mt-2" id="remitentePersonalizadoContainer" style="display: none;">
                  <input type="text" class="form-control" id="remitentePersonalizado" placeholder="Nombre del remitente">
                </div>
              </div>
            </div>
            
            <!-- Columna derecha: Prioridad y vista previa -->
            <div class="col-md-4">
              <!-- Selector de prioridad -->
              <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                  <h6 class="mb-0 fw-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>Prioridad
                  </h6>
                </div>
                <div class="card-body">
                  <!-- Rango numérico -->
                  <div class="mb-3">
                    <label for="prioridadRange" class="form-label fw-bold">Nivel de prioridad (1-3)</label>
                    <input type="range" class="form-range" min="1" max="3" step="1" id="prioridadRange" value="2">
                    <div class="d-flex justify-content-between mt-2">
                      <span class="badge bg-secondary">Baja (1)</span>
                      <span class="badge bg-warning">Media (2)</span>
                      <span class="badge bg-danger">Alta (3)</span>
                    </div>
                  </div>
                  
                  <!-- Indicadores visuales -->
                  <div class="mb-4">
                    <h6 class="fw-bold mb-3">Indicador visual:</h6>
                    <div class="d-flex justify-content-around text-center">
                      <!-- Prioridad 1 -->
                      <div class="prioridad-option" data-prioridad="1">
                        <div class="priority-indicator priority-low mb-2">
                          <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="prioridadVisual" id="prioridad1" value="1" checked>
                          <label class="form-check-label" for="prioridad1">
                            Informativa
                          </label>
                        </div>
                      </div>
                      
                      <!-- Prioridad 2 -->
                      <div class="prioridad-option" data-prioridad="2">
                        <div class="priority-indicator priority-medium mb-2">
                          <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="prioridadVisual" id="prioridad2" value="2">
                          <label class="form-check-label" for="prioridad2">
                            Advertencia
                          </label>
                        </div>
                      </div>
                      
                      <!-- Prioridad 3 -->
                      <div class="prioridad-option" data-prioridad="3">
                        <div class="priority-indicator priority-high mb-2">
                          <i class="fas fa-skull-crossbones fa-2x"></i>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="prioridadVisual" id="prioridad3" value="3">
                          <label class="form-check-label" for="prioridad3">
                            Crítica
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Vista previa -->
                  <div class="card border preview-card" id="previewNotificacion">
                    <div class="card-body">
                      <div class="d-flex align-items-start">
                        <div class="priority-preview me-3" id="priorityPreview">
                          <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div>
                          <h6 class="fw-bold mb-1" id="previewTitulo">Título de ejemplo</h6>
                          <p class="small text-muted mb-1" id="previewRemitente">Sistema · Ahora</p>
                          <p class="mb-0 small" id="previewMensaje">Mensaje de la notificación...</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Fecha -->
              <div class="mb-3">
                <label for="fechaNotificacion" class="form-label fw-bold">
                  <i class="fas fa-calendar-alt me-1"></i>Fecha
                </label>
                <div class="input-group">
                  <input type="date" class="form-control" id="fechaNotificacion" value="<?php echo date('Y-m-d'); ?>">
                  <input type="time" class="form-control" id="horaNotificacion" value="<?php echo date('H:i'); ?>">
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardarNotificacion">
          <i class="fas fa-save me-2"></i>Guardar Notificación
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Estilos personalizados -->
<style>
/* Indicadores de prioridad */
.priority-indicator {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  transition: all 0.3s ease;
  cursor: pointer;
}

.priority-low {
  background-color: rgba(13, 110, 253, 0.1);
  border: 3px solid #0d6efd;
  color: #0d6efd;
}

.priority-medium {
  background-color: rgba(255, 193, 7, 0.1);
  border: 3px solid #ffc107;
  color: #ffc107;
}

.priority-high {
  background-color: rgba(220, 53, 69, 0.1);
  border: 3px solid #dc3545;
  color: #dc3545;
}

.prioridad-option:hover .priority-indicator {
  transform: scale(1.1);
}

.prioridad-option.active .priority-indicator {
  box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
  transform: scale(1.05);
}

/* Vista previa */
.preview-card {
  border-left-width: 5px !important;
  transition: all 0.3s;
}

.preview-card.priority-low {
  border-left-color: #0d6efd !important;
}

.preview-card.priority-medium {
  border-left-color: #ffc107 !important;
}

.preview-card.priority-high {
  border-left-color: #dc3545 !important;
}

.priority-preview {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

/* Efectos de selección */
.form-range:focus {
  box-shadow: none;
}

.form-range::-webkit-slider-thumb {
  background-color: #0d6efd;
}

.form-range::-moz-range-thumb {
  background-color: #0d6efd;
}

/* Animaciones */
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.priority-high .priority-indicator {
  animation: pulse 2s infinite;
}

/* Responsive */
@media (max-width: 768px) {
  .d-flex.justify-content-around {
    flex-direction: column;
    gap: 15px;
  }
  
  .prioridad-option {
    display: flex;
    align-items: center;
    gap: 15px;
  }
  
  .priority-indicator {
    width: 50px;
    height: 50px;
    margin: 0;
  }
}
</style>

<!-- JavaScript/jQuery -->
<script>
$(document).ready(function() {
  // Inicializar valores
  actualizarVistaPrevia();
  
  // Sincronizar rango con radio buttons
  $('#prioridadRange').on('input', function() {
    let prioridad = $(this).val();
    $(`input[name="prioridadVisual"][value="${prioridad}"]`).prop('checked', true);
    $('.prioridad-option').removeClass('active');
    $(`.prioridad-option[data-prioridad="${prioridad}"]`).addClass('active');
    actualizarVistaPrevia();
  });
  
  // Sincronizar radio buttons con rango
  $('input[name="prioridadVisual"]').on('change', function() {
    let prioridad = $(this).val();
    $('#prioridadRange').val(prioridad);
    $('.prioridad-option').removeClass('active');
    $(`.prioridad-option[data-prioridad="${prioridad}"]`).addClass('active');
    actualizarVistaPrevia();
  });
  
  // Click en los indicadores visuales
  $('.prioridad-option').on('click', function() {
    let prioridad = $(this).data('prioridad');
    $(`input[name="prioridadVisual"][value="${prioridad}"]`).prop('checked', true).trigger('change');
  });
  
  // Mostrar/ocultar campo de remitente personalizado
  $('#remitenteNotificacion').on('change', function() {
    if ($(this).val() === 'personalizado') {
      $('#remitentePersonalizadoContainer').slideDown();
    } else {
      $('#remitentePersonalizadoContainer').slideUp();
    }
    actualizarVistaPrevia();
  });
  
  // Actualizar vista previa cuando cambian los campos
  $('#tituloNotificacion, #mensajeNotificacion').on('input', function() {
    actualizarVistaPrevia();
  });
  
  // Guardar notificación
  $('#guardarNotificacion').on('click', function() {
    if (validarFormulario()) {
      guardarNotificacion();
    }
  });
  
  // Inicializar estado activo
  $('.prioridad-option[data-prioridad="2"]').addClass('active');
});

// Función para actualizar la vista previa
function actualizarVistaPrevia() {
  let prioridad = $('input[name="prioridadVisual"]:checked').val();
  let titulo = $('#tituloNotificacion').val() || 'Título de ejemplo';
  let mensaje = $('#mensajeNotificacion').val().substring(0, 100) || 'Mensaje de la notificación...';
  let remitenteVal = $('#remitenteNotificacion').val();
  
  // Determinar remitente
  let remitente = '';
  switch(remitenteVal) {
    case 'sistema':
      remitente = 'Sistema';
      break;
    case 'administrador':
      remitente = 'Administrador';
      break;
    case 'usuario':
      remitente = 'Usuario';
      break;
    case 'personalizado':
      remitente = $('#remitentePersonalizado').val() || 'Personalizado';
      break;
    default:
      remitente = 'Sistema';
  }
  
  let fecha = $('#fechaNotificacion').val();
  let hora = $('#horaNotificacion').val();
  
  // Formatear fecha
  let fechaFormateada = 'Ahora';
  if (fecha) {
    let fechaObj = new Date(fecha + ' ' + hora);
    let ahora = new Date();
    let diffHoras = Math.abs(ahora - fechaObj) / 36e5;
    
    if (diffHoras < 24 && fecha === new Date().toISOString().split('T')[0]) {
      fechaFormateada = 'Hoy a las ' + hora;
    } else {
      fechaFormateada = fecha + ' ' + hora;
    }
  }
  
  // Actualizar vista previa
  $('#previewTitulo').text(titulo);
  $('#previewMensaje').text(mensaje + (mensaje.length >= 100 ? '...' : ''));
  $('#previewRemitente').text(`${remitente} · ${fechaFormateada}`);
  
  // Actualizar estilos según prioridad
  $('#previewNotificacion').removeClass('priority-low priority-medium priority-high');
  $('#priorityPreview').removeClass('priority-low priority-medium priority-high');
  
  let icono = '';
  switch(prioridad) {
    case '1':
      $('#previewNotificacion').addClass('priority-low');
      $('#priorityPreview').addClass('priority-low');
      icono = 'info-circle';
      break;
    case '2':
      $('#previewNotificacion').addClass('priority-medium');
      $('#priorityPreview').addClass('priority-medium');
      icono = 'exclamation-circle';
      break;
    case '3':
      $('#previewNotificacion').addClass('priority-high');
      $('#priorityPreview').addClass('priority-high');
      icono = 'skull-crossbones';
      break;
  }
  
  $('#priorityPreview').html(`<i class="fas fa-${icono}"></i>`);
}

// Función para validar el formulario
function validarFormulario() {
  let titulo = $('#tituloNotificacion').val().trim();
  let mensaje = $('#mensajeNotificacion').val().trim();
  
  if (!titulo) {
    alert('Por favor, ingresa un título para la notificación');
    $('#tituloNotificacion').focus();
    return false;
  }
  
  if (!mensaje) {
    alert('Por favor, ingresa un mensaje para la notificación');
    $('#mensajeNotificacion').focus();
    return false;
  }
  
  if ($('#remitenteNotificacion').val() === 'personalizado' && !$('#remitentePersonalizado').val().trim()) {
    alert('Por favor, ingresa el nombre del remitente personalizado');
    $('#remitentePersonalizado').focus();
    return false;
  }
  
  return true;
}

// Función para guardar la notificación (ejemplo)
function guardarNotificacion() {
  let notificacion = {
    titulo: $('#tituloNotificacion').val().trim(),
    mensaje: $('#mensajeNotificacion').val().trim(),
    prioridad: $('input[name="prioridadVisual"]:checked').val(),
    remitente: $('#remitenteNotificacion').val(),
    remitentePersonalizado: $('#remitentePersonalizado').val().trim(),
    fecha: $('#fechaNotificacion').val(),
    hora: $('#horaNotificacion').val(),
    timestamp: new Date().toISOString()
  };
  
  // Aquí iría tu lógica para guardar la notificación
  console.log('Notificación a guardar:', notificacion);
  
  // Mostrar mensaje de éxito
  alert('Notificación guardada exitosamente!');
  
  // Cerrar modal
  $('#notificacionModal').modal('hide');
  
  // Resetear formulario
  $('#formNotificacion')[0].reset();
  $('input[name="prioridadVisual"][value="2"]').prop('checked', true).trigger('change');
  $('#remitentePersonalizadoContainer').hide();
  actualizarVistaPrevia();
}

// Función para cargar una notificación existente (opcional)
function cargarNotificacion(notificacion) {
  $('#tituloNotificacion').val(notificacion.titulo);
  $('#mensajeNotificacion').val(notificacion.mensaje);
  $(`input[name="prioridadVisual"][value="${notificacion.prioridad}"]`).prop('checked', true).trigger('change');
  $('#remitenteNotificacion').val(notificacion.remitente);
  
  if (notificacion.remitente === 'personalizado') {
    $('#remitentePersonalizado').val(notificacion.remitentePersonalizado);
    $('#remitentePersonalizadoContainer').show();
  }
  
  if (notificacion.fecha) {
    $('#fechaNotificacion').val(notificacion.fecha);
    $('#horaNotificacion').val(notificacion.hora);
  }
  
  actualizarVistaPrevia();
}
</script>