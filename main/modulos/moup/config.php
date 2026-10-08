<?php
/// CALL
require_once '../config/connect.php';
require_once '../config/alert.php';
$base = new MYSQL;


error_log("					CONFIG				");
/// T A B L E S
$em = $base->query_simple("SELECT * FROM empresa");
$backup_lately = $base->query_simple("SELECT * FROM registro_backup ORDER BY CONCAT(fechay, '-', fecham, '-', fechad, ' ', fechah, ':', fechami) DESC LIMIT 1") ?? [];
$backup = $base->query_all("SELECT * FROM registro_backup LIMIT 5")?? [];
$configuraciones = $base->query_simple("SELECT * FROM configuraciones_sistema");
$backup_date = $base->query_simple("SELECT * FROM copias_automaticas WHERE estado = 0") ?? null;
/////VARIABLES
$_SESSION['reinicio'] = false;


if($_SESSION['reinicio'] === true){
	alert("INFORMACION","Al volver a iniciar session vera todos los cambios","info");
	$_SESSION['reinicio'] = false;
	$_SESSION['configR'] = $base->query_simple("SELECT * FROM configuraciones_sistema");
}

$hourtimes = null;
if($backup_date !== false){
$hourtimes = $backup_date['fecha_setH'].':'.$backup_date['fecha_setM'];
///$datetime = DateTime::createFromFormat('h:i a',$cvi);
///$hourtimes = $datetime->format('h:i a');
}


?>

<div class="overlay" id="loading-overlay" style="display: none;"> <!-- Oculto inicialmente -->
    <div class="spinner"></div>
    <div class='loadtext'><h3>Esto puede tardar unos minutos.</h3></div>
</div>

<div class="container-fluid">
<!-- Mensajes -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
<h3 class="mb-4"><i class="bi bi-gear-fill"></i> Configuración del Sistema</h3>

<div class="row">
	
<!-- 			ISIDEBAR					 -->
<div class="col-md-3">
<div class="card">
<div class="card-body p-0">
<div class="nav flex-column nav-pills">
	
<label class="nav-link" onclick="cambiarSETTINGS('p1');">
<i class="bi bi-building"></i> Información de Empresa
</label>

<label class="nav-link" onclick="cambiarSETTINGS('p2');">
<i class="bi bi-palette"></i> Apariencia del Sistema
</label>

<label class="nav-link" onclick="cambiarSETTINGS('p3');">
<i class="bi bi-cloud-arrow-down"></i> Copias de Seguridad
</label>

<label class="nav-link" onclick="cambiarSETTINGS('p4');">
<i class="bi bi-cpu"></i> Información del Sistema
</label>


<label class="nav-link" onclick="cambiarSETTINGS('p5');">
<i class="bi bi-shield-lock"></i> Seguridad
</label>


<label class="nav-link" onclick="cambiarSETTINGS('p6');">
<i class="bi bi-whatsapp"></i> Contacto
</label>


</div>

<!-----PILLS ENDS HERE ------>
<div>
<img src="<?= '../assets/multimedia/logos/empresa.jpg' ?? '../assets/multimedia/pfp/default.png' ?>" class="pictureEM2">
</div>
</div>
</div>

</div>
<!-- 			ISIDEBAR END HERE					 -->



<!--			 Contenido de configuración 		-->
<div class="col-md-9">




<!-- Información de Empresa -->
<div class="config-section" id="wshow1">
<div class="card">
<div class="card-header">
<h5><i class="bi bi-building"></i> Información de la Empresa</h5>
</div>
<div class="card-body">

<!-------->
<form method="POST" name="POST2025" action="modulos/moup/config_save.php" enctype="multipart/form-data">
<input type="hidden" name="action" value="empresa_info">
<div class="row mb-3">
<div class="col-md-6">
<label class="form-label">Nombre de la Empresa</label>
<input type="text" name="Ename" class="form-control" value="<?= $em['nombre'] ?? 'Ninguno' ?>">
</div>
<div class="col-md-6">
<label class="form-label">RIF/Identificación</label>
<input type="text" name="Erif" class="form-control" value="<?= $em['rif'] ?? 'Ninguno' ?>">
</div>
</div>

<div class="row mb-3">
<div class="col-md-6">
<label class="form-label">Teléfono</label>
<input type="text" name="Ephone" class="form-control" value="<?= $em['telefono'] ?? 'Ninguno' ?>">
</div>
<div class="col-md-6">
<label class="form-label">Email</label>
<input type="email" name="Eemail" class="form-control" value="<?= $em['email'] ?? 'Ninguno' ?>">
</div>
</div>

<div class="mb-3">
<label class="form-label">Dirección</label>
<textarea class="form-control" rows="3" name="Edirecc">
<?= $em['direccion'] ?? 'Ninguno' ?></textarea>
</div>

<div class="mb-3">
<img src="<?= '../assets/multimedia/logos/empresa.png' ?? '../assets/multimedia/pfp/default.png' ?>" class="pictureEM" id="pfp">
<label class="form-label">Logo de la Empresa</label>
<input type="file" class="form-control" accept="image/*" name="logo" id="logo">
<div class="form-text">Tamaño recomendado: 200x60 píxeles. Formatos: PNG, JPG, SVG.</div>
</div>

<div class="action-buttons">
<button type="button" class="btn btn-secondary">Cancelar</button>
<button type="submit" class="btn btn-primary">Guardar Cambios</button>
</div>
</form>

<!-------->

</div>
</div>
</div>



<!-- Apariencia del Sistema -->
<div class="config-section" id="wshow2">
<div class="card">
<div class="card-header">
<h5 class="mb-0"><i class="bi bi-palette"></i> Apariencia del Sistema</h5>
</div>
<div class="card-body">
<div class="row mb-4">
<div class="col-md-6">
<label class="form-label">Tema de Color</label>
<div class="row">


<!---THEMA: CKARO--->
<div class="col-6">
	
<div class="theme-option"  id="themesofpos1">
<div class="theme-preview" style="background: linear-gradient(135deg, #2c7be5 0%, #1e2a38 100%);"></div>
<div class="theme-name">Modo Claro</div>
<div class="theme-description">Tema predeterminado</div>
</div>
</div>
<!------>

<!--THEME: DARK---->
<div class="col-6">
	
<button class="theme-option" id="themesofpos2" type="button">
<div class="theme-preview" style="background: linear-gradient(135deg, #3a3b3c 0%, #1e2a38 100%);"></div>
<div class="theme-name">Modo Oscuro</div>
<div class="theme-description">Para uso prolongado</div>
</button>

</div>
<!------>

</div>
</div>

</div>
</div>
</div>
</div>


<!-- Copias de Seguridad -->
<div class="config-section" id="wshow3">
<div class="card">
<div class="card-header">
<h5 class="mb-0"><i class="bi bi-cloud-arrow-down"></i> Copias de Seguridad</h5>
</div>
<div class="card-body">
<div class="info-card">
<h6><i class="bi bi-info-circle text-primary"></i> Información de Respaldo</h6>
<?php if(isset($backup_lately)):?>
<p class="mb-0">Última copia de seguridad: <strong><?= $backup_lately['fechad']?>/<?= $backup_lately['fecham']?>/<?= $backup_lately['fechay']?> [<?= $backup_lately['fechah']?>:<?= $backup_lately['fechami']?> <?= $backup_lately['tipohora']?>]</strong></p>
<p class="mb-0">Tamaño total de respaldos: <strong><?= $backup_lately['peso']?> MB</strong></p>
<p class="mb-0">Próxima copia automática: <strong>16/03/2023 02:00</strong></p>
<?php else:?>
<p>Ninguna</p>

<?php endif;?>
</div>

<div class="row mb-4">
<div class="col-md-6">
<div class="card h-200">
	
	
<div class="card-body">
<h6 class="card-title bi-usb-drive"> Crear Copia de Seguridad</h6>
<p class="card-text">Genera una copia manual de toda la información del sistema.</p>
<!-----
<div class="form-check mb-3">
<input class="form-check-input" type="checkbox" id="incluirArchivos" checked>
<label class="form-check-label" for="incluirArchivos">Incluir archivos adjuntos</label>
</div>
----->


<button type="button" class="btn btn-primary w-100" id="SUBMITCREATEOSCOWY">
<i class="bi bi-download"></i> Generar Copia
</button>

</div>


</div>
</div>


<div class="col-md-6 <?php if(!isset($_SESSION['is_above']) and $_SESSION['is_above'] !== true){echo "dsh";}?>">
<div class="card h-100">
<div class="card-body">
<h6 class="card-title bi-database-add"> Crear Copia de Base de Datos</h6>
<p>Advertencia, mantenga el archivo protegido en todo momento, no lo envie a nadie, ni lo exponga.</p>
<button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#SEGURIDPASS">
<i class="bi bi-arrow-clockwise"></i> Realizar Copia de seguridad
</button>
</div>
</div>
</div>





</div>

<h6 class="mb-3">Configuración Automática</h6>


<form method="POST" action="modulos/moup/config_save.php">
<input type="hidden" name="action" value="copias_settings">
<div class="row mb-4">
<div class="col-md-4">
<label class="form-label">Frecuencia</label>
<!--selected--->
<select class="form-select" name="frecuencia" <?= ($_SESSION['backupaudate'] or isset($_SESSION['backupaudate']) or $_SESSION['backupaudate'] > 0 or $_SESSION['backupaudate'] == true)? htmlspecialchars('disabled'):''?>>
<option value="Diaria" <?= ($backup_date !== false && $backup_date['frecuencia'] == 'Diaria')? 'selected':'' ?>>Diariamente</option>
<option value="Semanal" <?= ($backup_date !== false && $backup_date['frecuencia'] == 'Semanal')? 'selected':'' ?>>Semanalmente</option>
<option value="Mensual" <?= ($backup_date !== false && $backup_date['frecuencia'] == 'Mensual')? 'selected':'' ?>>Mensualmente</option>
</select>

<p class="muted">Desde que momento le gustaria que empience.</p>
</div>

<div class="col-md-4">
<label class="form-label">Día de la Semana</label>
<select class="form-select" name="day" <?= ($_SESSION['backupaudate'] or isset($_SESSION['backupaudate']) or $_SESSION['backupaudate'] > 0 or $_SESSION['backupaudate'] == true)? htmlspecialchars('disabled'):''?>>
<option value="1" <?= ($backup_date !== false && $backup_date['week_day'] == 1)? 'selected':'' ?>>Lunes</option>
<option value="2" <?= ($backup_date !== false && $backup_date['week_day'] == 2)? 'selected':'' ?>>Martes</option>
<option value="3" <?= ($backup_date !== false && $backup_date['week_day'] == 3)? 'selected':'' ?>>Miercoles</option>
<option value="4" <?= ($backup_date !== false && $backup_date['week_day'] == 4)? 'selected':'' ?>>Jueves</option>
<option value="5" <?= ($backup_date !== false && $backup_date['week_day'] == 5)? 'selected':'' ?>>Viernes</option>
<option value="6" <?= ($backup_date !== false && $backup_date['week_day'] == 6)? 'selected':'' ?>>Sabado</option>
<option value="7" <?= ($backup_date !== false && $backup_date['week_day'] == 7)? 'selected':'' ?>>Domingo</option>
</select>
<p class="muted">Desde que dia le gustaria que empience.</p>
</div>


<div class="col-md-4">
<label class="form-label">Hora</label>
<input type="time" name="hourTO" class="form_time" value="<?= ($hourtimes !== null)? htmlspecialchars($hourtimes): ''?>" <?= ($_SESSION['backupaudate'] or isset($_SESSION['backupaudate']) or $_SESSION['backupaudate'] > 0 or $_SESSION['backupaudate'] == true)? htmlspecialchars('disabled'):''?>>
<p class="muted">Desde que hora le gustaria que empience.</p>
</div>
</div>

<div class="form-check form-switch mb-4">
<input class="form-check-input" type="checkbox" id="trueaubackup" name="trueaubackup" <?= ($configuraciones !== false && $configuraciones['copias_automaticas'] == true)? 'checked':'' ?> <?= ($_SESSION['backupaudate'] or isset($_SESSION['backupaudate']) or $_SESSION['backupaudate'] > 0 or $_SESSION['backupaudate'] == true)? htmlspecialchars('disabled'):''?>>
<label class="form-check-label" for="trueaubackup">Habilitar copias de seguridad automáticas</label>
</div>

<button type="submit" class="btn btn-primary w-100" <?= ($_SESSION['backupaudate'] or isset($_SESSION['backupaudate']) or $_SESSION['backupaudate'] > 0 or $_SESSION['backupaudate'] == true)? htmlspecialchars('disabled'):''?>>
<i class="bi bi-download"></i> Guardar Cambios
</button>


</form>


<h6 class="mb-3">Copias de Seguridad Recientes</h6>
<div class="table-responsive">
<?php if(isset($backup)):?>
<table class="table table-hover">
<thead>
<tr>
<th>Fecha</th>
<th>Tamaño</th>
<th>Tipo</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>

<?php foreach($backup as $n):?>

<tr>
<td><?= $n['fechad']?>/<?= $n['fecham']?>/<?= $n['fechay']?> [<?= $n['fechah']?>:<?= $n['fechami']?> <?= $n['tipohora']?>]</td>
<td><?= $n['peso'] ?> MB</td>
<td><span class="badge bg-primary">Completa</span></td>
<td>
<button class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></button>
<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
</td>
</tr>

<?php endforeach;?>
</tbody>
</table>

<?php else:?>
<p>No hay ninguna copia registrada, ingrese la primera.</p>
<?php endif;?>
</div>
</div>
</div>
</div>


<!-- Información del Sistema -->
<div class="config-section" id="wshow4">
<div class="card">
<div class="card-header">
<h5 class="mb-0"><i class="bi bi-cpu"></i> Información del Sistema</h5>
</div>
<div class="card-body">
<div class="row mb-4">
<div class="col-md-6">
<h6>Especificaciones Técnicas</h6>
<table class="table table-sm">
<tr>
<td class="fw-bold" width="40%">Versión del Sistema:</td>
<td>v0.0.5</td>
</tr>
<tr>
<td class="fw-bold">PHP:</td>
<td><?=  phpversion() ?></td>
</tr>
<tr>
<td class="fw-bold">Base de Datos:</td>
<td>MySQL <?= $base->version()  ?></td>
</tr>
<tr>
<td class="fw-bold">Servidor Web:</td>
<td>Localhost</td>
</tr>
<tr>
<td class="fw-bold">Sistema Operativo:</td>
<td><?= PHP_OS ?></td>
</tr>
</table>
</div>

</div>

<!------
<div class="info-card warning">
<h6><i class="bi bi-exclamation-triangle text-warning"></i> Actualización Disponible</h6>
<p class="mb-0">Hay una nueva versión del sistema disponible (v2.6.0). Se recomienda realizar la actualización durante un horario de bajo uso del sistema.</p>
<button type="button" class="btn btn-warning btn-sm mt-2">Ver Detalles de Actualización</button>
</div>
------>

<div class="action-buttons mt-4">
<button type="button" class="btn btn-warning">Limpiar Caché</button>
<button type="button" class="btn btn-danger" onclick="location.reload();">Reiniciar Sistema</button>
</div>
</div>
</div>
</div>


<!-- Seguridad -->
<div class="config-section" id="wshow5">
<div class="card">
<div class="card-header">
<h5 class="mb-0"><i class="bi bi-shield-lock"></i> Configuración de Seguridad</h5>
</div>


<div class="card-body">
<div class="row mb-4">
<div class="col-md-6">
<h6>Acciones logicas del sistema</h6>
<form method="POST" name="secugirity" action="modulos/moup/config_save.php">
<input type="hidden" value="segurity" name="action">
<label class="form-label">Limite de tiempo a esperar para eliminar productos caducados.</label>
<div class="form-check form-switch mb-3">
<input class="form-check-input" type="checkbox" id="permitirwaittime" name="permitirwaittime" <?php echo ($configuraciones !== false && $configuraciones['eliminar_mermaF'] == true)? 'checked' : '';?>>

<select class="form-select" id="tiempotowait" name="tiempotowait" <?php echo ($configuraciones !== false && $configuraciones['eliminar_mermaF'] == true) ? '' : 'disabled'; ?>>

<option value="" <?= ($configuraciones !== false && $configuraciones['fecha_eliminar_d'] === null)? 'selected' : '' ?>>...</option>
<option value="0" <?= ($configuraciones !== false && $configuraciones['fecha_eliminar_d'] === 0)? 'selected' : '' ?>>Al momento</option>
<option value="1" <?= ($configuraciones !== false && $configuraciones['fecha_eliminar_d'] === 1)? 'selected' : '' ?>>Esperar 1 dia</option>
<option value="5" <?= ($configuraciones !== false && $configuraciones['fecha_eliminar_d'] === 5)? 'selected' : '' ?>>Esperar 5 dias</option>
<option value="10" <?= ($configuraciones !== false && $configuraciones['fecha_eliminar_d'] === 10)? 'selected' : '' ?>>Esperar 10 dias</option>
<option value="15" <?= ($configuraciones !== false && $configuraciones['fecha_eliminar_d'] === 15)? 'selected' : '' ?>>Esperar 15 dias</option>
</select>
</div>

</div>

<div class="col-md-6">
<h6>Configuración de Sesiones</h6>

<!-----
<div class="mb-3">
<label class="form-label">Tiempo de expiración de sesión (minutos)</label>
<input type="number" class="form-control" value="60">
</div>
----->

<div class="mb-3">
<label class="form-label">Límite de intentos de acceso</label>
<input type="number" class="form-control" value="<?= ($configuraciones !== false)? $configuraciones['numero_intentos'] : '5'?>" name="blockearpor">
</div>
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" id="blockear" name="blockear" <?= ($configuraciones !== false && $configuraciones['blockear_despues'] == true)? 'checked' : '' ?>>
<label class="form-check-label" for="blockear">Bloquear usuario después de intentos fallidos</label>
</div>
</div>
</div>


<div class="action-buttons">
<button type="submit" class="btn btn-primary">Actualizar Políticas</button>
</div>

</form>


</div>
</div>
</div>



<!-- ABOUT -->
<div class="config-section" id="wshow6">
<div class="card">
<div class="card-header">
<h5 class="mb-0"><i class="bi bi-wechat"></i> Contacto </h5>
</div>
<div class="card-body">

<img src="../../assets/logos/universidad.jpg" class="logo_uni_con">
<hr>
<h5>Autor(es):</h5>
<li class="bi-envelope"> oscarsonz43@gmail.com</li>
<li class="bi-envelope"> joseenriqueaponte3@gmail.com</li>
</div>
</div>
</div>
        
        
</div>
</div>
</div>



<div class="modal fade" id="SEGURIDPASS" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Verificación de Seguridad</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        
      </div>
      <form method="POST" action="modulos/moup/config_save.php">
	<input type="hidden" name="action" value="bsUP">
	<input type="hidden" name="usID" value="<?= $_SESSION['user_cedula'] ?? $ciw23 ?>">
      <div class="modal-body">
        <p>Para proceder con la copia de seguridad de la base de datos, por favor ingresa tu clave de usuario.</p>
        <p class="text-danger font-weight-bold">Atención: Debes tener un cuidado riguroso con esta acción. Asegúrate de que la copia de seguridad se realice en un entorno seguro.</p>
        <div class="form-group">
          <label for="userKey">Clave de Usuario</label>
          <input type="password" class="form-control" name="userKey" placeholder="Ingresa tu clave">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Confirmar</button>
      </div>
      </form>
      
    </div>
  </div>
</div>




<script>
///// CARGAR CUANDO TODO ESTE READY
const navLinks = document.querySelectorAll('.nav-link');
const sections = document.querySelectorAll('.config-section');
const theme1 = document.getElementById('themesofpos1');
const theme2 = document.getElementById('themesofpos2');


navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remover clase active de todos los enlaces
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Añadir clase active al enlace clickeado
            this.classList.add('active');
        });
 });



//// VISUALIZAR PFP
document.getElementById('logo')?.addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('pfp');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = "../assets/icons/user.png";
    }
});





//// THEMAS
$(document).ready(function() {
    $(theme1).on("click", function() {
        theme2.classList.remove('selected');
        theme1.classList.add('selected');
    });

    $(theme2).on("click", function() {
        theme1.classList.remove('selected');
        theme2.classList.add('selected');
    });
});

//// CAMBIAR DE PAGINA
function cambiarSETTINGS(page){
$('.wshow').hide();
if(page == 'p1'){
$('#wshow1').show();
$('#wshow2').hide();
$('#wshow3').hide();
$('#wshow4').hide();
$('#wshow5').hide();
$('#wshow6').hide();

}
else if(page == 'p2'){
$('#wshow2').show();
$('#wshow1').hide();
$('#wshow3').hide();
$('#wshow4').hide();
$('#wshow5').hide();
$('#wshow6').hide();
}
else if(page == 'p3'){
$('#wshow3').show();
$('#wshow1').hide();
$('#wshow2').hide();
$('#wshow4').hide();
$('#wshow5').hide();
$('#wshow6').hide();
}
else if(page == 'p4'){
$('#wshow4').show();
$('#wshow1').hide();
$('#wshow3').hide();
$('#wshow2').hide();
$('#wshow5').hide();
$('#wshow6').hide();
}
else if(page == 'p5'){
$('#wshow5').show();
$('#wshow1').hide();
$('#wshow3').hide();
$('#wshow4').hide();
$('#wshow2').hide();
$('#wshow6').hide();
}
else if(page == 'p6'){
$('#wshow6').show();
$('#wshow1').hide();
$('#wshow3').hide();
$('#wshow4').hide();
$('#wshow2').hide();
$('#wshow5').hide();
}
else{
$('.wshow').hide();
}
}



$(document).ready(function() {
    $("#SUBMITCREATEOSCOWY").click(function() {
        $("#loading-overlay").fadeIn();
        $.ajax({
            url: 'modulos/moup/backup.php', 
            method: 'POST',
            dataType: 'json', // Asegúrate de que PHP devuelve un JSON válido
            success: function(response) {
                $("#loading-overlay").fadeOut();
                // Verifica el estado de la respuesta
                if (response.success) {
                    Swal.fire({
                        title: 'Exito',
                        text: 'Se ha creado una copia de seguridad con éxito!',
                        icon: 'success',
                        customClass: {
                            popup: 'sweety-window',
                            title: 'sweety-title',
                            content: 'sweety-content'
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'No se pudo realizar la copia de seguridad.',
                        icon: 'error',
                        customClass: {
                            popup: 'sweety-window',
                            title: 'sweety-title',
                            content: 'sweety-content'
                        }
                    });
                }
            },
            error: function() {
                $("#loading-overlay").fadeOut();
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo realizar la copia de seguridad.',
                    icon: 'error',
                    customClass: {
                        popup: 'sweety-window',
                        title: 'sweety-title',
                        content: 'sweety-content'
                    }
                });
            }
        });
    });
});



$(document).ready(function (){
$('#permitirwaittime').click(function(){
	let current = $("#tiempotowait").prop('disabled');
	$('#tiempotowait').prop('disabled',!current);
});
	});
</script>
