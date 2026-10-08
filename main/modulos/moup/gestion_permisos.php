<?php 
require_once '../config/connect.php';
$base = new MYSQL;

////TABLAS
$sql = "SELECT e.*, p.image_name FROM empleados_datos e JOIN usuarios_acceso u ON u.cedula_empleado = e.cedula JOIN usuario_profile p ON p.usuario_id = u.id";
$pffi = $base->query_all($sql);
$empleados = $base->query_all("SELECT * FROM empleados_datos");
$permisostotal = $base->query_all("SELECT * FROM permisos");
//$permisos = null;
$permisos_empleado = null;
$un = null;
$id_us = null;




if($_POST['empleadoSelect']){
	$cd = $_POST['empleadoSelect'] ?? null;

	$id = $base->one_query("SELECT id,username,rol_id FROM usuarios_acceso WHERE cedula_empleado = ? ",$cd);
	//$permisos_empleado = $base->some_query('SELECT * FROM usuario_permisos WHERE usuario_id = ?',[$id['id']]);

	$un = $id['username'];
	$rol_id = $id['rol_id'];
	$id_us = $id['id'];
	
	$permisos1 = $base->some_query("SELECT permiso_id FROM usuario_permisos WHERE usuario_id = ?", [$id_us]);
    
    // Convertir el array de resultados a un array de IDs para búsqueda rápida
    
    if (!empty($permisos1)) {
        foreach ($permisos1 as $p) {
            // Guarda el ID del permiso como la clave del array para búsqueda O(1)
            $permisos_defautl[$p['permiso_id']] = true; 
}
}

 $permisos2 = $base->some_query("SELECT permiso_id FROM rol_permisos WHERE rol_id = ?", [$rol_id]);
    
    // Convertir el array de resultados a un array de IDs para búsqueda rápida
    
    if (!empty($permisos2)) {
        foreach ($permisos2 as $p) {
            // Guarda el ID del permiso como la clave del array para búsqueda O(1)
            $permisos_default_rol[$p['permiso_id']] = true; 
        }
    }
}



if(isset($_POST['bsumit'])){
$allow_list = $_POST['permisos'] ?? [];
$us = $_POST['id_US'];
$countP = 0;
$countN = 0;
try{
	if(empty($us)){throw new Exception("Error usuario vacio");}
	if(empty($allow_list)){throw new Exception("Error permiso vacio");}
	
	foreach($allow_list as $n){
		$sql = "INSERT INTO usuario_permisos (usuario_id,permiso_id) VALUES(?,?)";
		//$id = ;
		$result = $base->some_query($sql,[$us,$n]);
		
		if($result === false){
			$countN++;
		}
		else{
			$countP++;
		}
	}
///

$_SESSION['success'] = "Se agregaron ".$countP." permisos / faltaron ".$countN." al usuario ".$un. "";
}
catch(Exception $e){
$_SESSION['error'] = $e->showMessage();
}
	
}


?>

<!---CONTENIDO START HERE---->
<div class="container-fluid">
	
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success">
<i class="bi bi-check-circle"></i>
<div><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger">
<i class="bi bi-exclamation-triangle"></i>
<div><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
</div>
<?php endif; ?>


    <!-- Header de página -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="bi bi-shield-check me-2"></i> Gestión de Permisos del Sistema</h2>
                <p class="mb-0">Configura los permisos de acceso para cada usuario del sistema</p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-light text-dark">
                    <i class="bi bi-people me-1"></i>
                    <span id="totalUsers">0</span> usuarios
                </span>
            </div>
        </div>
    </div>



<!-- Contenedor principal -->
<div class="permissions-container">
<!-- Sidebar de empleados -->

<div class="employees-sidebar" style="width:auto;">

<div class="search-box">
<i class="bi bi-search"></i>
<input type="text" class="form-input" id="employeeSearch" placeholder="Buscar...">
</div>

<div class="employees-list" id="employeesList">
<?php if(!empty($pffi)):?>
<?php foreach($pffi as $n):?>
<?php 
$profilepicturepathfinalboos = '../../assets/multimedia/pfp/';
if(file_exists($profilepicturepathfinalboos.$n['image_name'])){
	$profilepicturepathfinalboos .= $n['image_name'];
}
else{
	$profilepicturepathfinalboos = '../../assets/multimedia/pfp/default.png';
}  
?>


<div class="employee-item" data-user-id="">
<div class="employee-avatar"> 
<img width="60" style="border-radius:50px;" src="<?= $profilepicturepathfinalboos ?>"></div>
<div class="employee-name"><?= $n['nombre1']?> <?= $n['apellido']?></div>
</div>


<?php endforeach;?>
<?php else:?>
<div><h4>No hay</h4></div>
<?php endif;?>
</div>
</div>




<!-- Contenido principal -->
<div class="permissions-content">
<!-- Selector de usuario -->
<div class="user-selector">
	
<label class="form-label fw-bold mb-3">
<i class="bi bi-person-badge me-2"></i>Seleccionar Empleado
</label>

<form method="POST" id="pickone" name="formulario_pickone">
<select name="empleadoSelect" class="form-select" onchange="document.getElementById('pickone').submit()">
<option value="">-- Seleccione un empleado --</option>
<?php foreach($empleados as $n):?>
<option value="<?=$n['cedula']?>"><?= $n['nombre1'] ?> <?= $n['nombre2'] ?> - <?= $n['apellido'] ?></option>
<?php endforeach;?>
</select>
</form>
</div>




<!-- Panel de permisos -->
<div class="permissions-panel" id="permissionsPanel">

<?php if($permisos_empleado === null and $un === null):?>
<div class="empty-state">
<i class="bi bi-shield-lock"></i>

<h4>Seleccione un empleado</h4>
<p class="text-muted">Elija un usuario de la lista para configurar sus permisos</p>
</div>
<?php else:?>
<form method="POST">
<h4>Usuario <?= $un?></h4>
<?php foreach($permisostotal as $n):
$is_checked = isset($permisos_defautl[$n['id']]);
$is_there = isset($permisos_default_rol[$n['id']]);
?>

<?php if($is_there !== true):?>
<div class="form-check mb-2">
<input type="hidden" name="id_US" value="<?= $id_us?>">
        <input class="form-check-input permiso-checkbox" type="checkbox" 
               name="permisos[]" 
               value="<?= $n['id'] ?>" 
               id="permiso-<?= $n['nombre'] ?>"
               <?= $is_checked ? 'checked' : '' ?> > 
               
        <label class="form-check-label" for="permiso-<?= $n['nombre'] ?>">
            <?= htmlspecialchars($n['nombre']) ?>
        </label>
    </div>
<?php endif;?>

<?php endforeach;?>
<button type="submit" name="bsumit">Guardar Cambios</button>
</form>


<?php endif;?>


</div>
</div>
</div>
</div>
<!--Contenedor ENDS HERE---->


<!-- Template para la sección de permisos (se usará desde JavaScript) -->
<template id="permissionTemplate">
    <div class="permission-section fade-in">
        <div class="section-header">
            <div class="section-number"></div>
            <h3 class="section-title"></h3>
        </div>
        <p class="section-subtitle"></p>
        <div class="permission-group">
            <!-- Los permisos se agregarán aquí -->
        </div>
    </div>
</template>

<!-- Template para un ítem de permiso -->
<template id="permissionItemTemplate">
    <div class="permission-item">
        <div class="permission-checkbox">
            <input type="checkbox" class="form-check-input permission-check">
        </div>
        <div class="permission-details">
            <div class="permission-name"></div>
            <div class="permission-description"></div>
        </div>
    </div>
</template>

<!-- Template para un empleado en el sidebar -->

