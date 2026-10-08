<?php
session_start();
error_log("				DESDE AQUI									");
date_default_timezone_set('America/Caracas');

require_once(__DIR__ . '/../config/auth.php');
require_once(__DIR__ . '/../config/connect.php');


/// T A B L A
$con = new MYSQL;
/// var de men
$paginas_validas = ['gestion_permisos','dashboard','productos', 'empleados', 'proveedores', 'categorias','lotes','gestionv', 'monedas','ventas','roles','reportes','config'];
$globalMessage = null;
$tipo_message = 'error';
$ciw23 = $_SESSION['cedula'] ?? $_COOKIE['respaldoCI'];

/// PFP
if (!isset($_SESSION['idOTU'])){
$_SESSION['idOTU'] = $con->one_query("SELECT id FROM usuarios_acceso WHERE cedula_empleado = ?",$ciw23);
}


$path = $con->one_query("SELECT image_name FROM usuario_profile WHERE usuario_id = ?",$_SESSION['idOTU']['id']);
//
setcookie('pagina','', 0, '/');
$pagina = $_COOKIE['pagina'] ?? $_GET['pagina'] ?? null;
$tasas_de_cambio = $con->query_all("SELECT * FROM monedas WHERE activo = 1");
///$archivos = null;



// CLIENTE O BUENO SI :)
if (isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
$pagina = $_GET['pagina'] ?? $_COOKIE['pagina'] ?? null;
$_COOKIE['pagina'] = $pagina ?? $_GET['pagina'];
$paginas_validas = ['gestion_permisos','dashboard','productos', 'empleados', 'proveedores','monedas','lotes','categorias','ventas','roles','reportes','gestionv','config'];

if (!in_array($pagina, $paginas_validas)) {
$pagina = 'dashboard';
}

if($_POST){
$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$ciw23,"Ingreso a ".$pagina,$pagina,"Ingreso a la pagina",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);
}
// Cargar contenido vía AJAX
cargarContenido($pagina);
exit();
}


/// FUNCION PARA CARGAR INCLUDE '-|-'
function cargarContenido($pagina) {
if($pagina !== null){
$archivos = [
"modulos/inicio/{$pagina}.php",
"modulos/productos/{$pagina}.php",
"modulos/empleados/{$pagina}.php",
"modulos/proveedores/{$pagina}.php",
"modulos/monedas/{$pagina}.php",
"modulos/roles/{$pagina}.php",
"modulos/categorias/{$pagina}.php",
"modulos/lotes/{$pagina}.php",
"modulos/ventas/{$pagina}.php",
"modulos/reportes/{$pagina}.php",
"modulos/nav_user/{$pagina}.php",
"modulos/moup/{$pagina}.php"
];

try {
$archivoIncluido = false; 
foreach ($archivos as $archivo) { 
if (file_exists($archivo)) {
include $archivo; 
$archivoIncluido = true; 
if($_POST)
$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$usuario['cedula'],"Ingreso a ".$pagina,$pagina,"Ingreso a la pagina",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);
break; 
} }
if (!$archivoIncluido) 
{ echo "<div class='alert alert-warning'>No se encontró la página.</div>";
} 
} catch (Exception $e) {
	error_log("Error al incluir archivo: " . $e->getMessage());
	echo "<div class='alert alert-danger'>Error al cargar la página.</div>";
}
}
else{
	echo '<h3>HOLA</h3>';
}
}


///			SESSION
if(!isset($_SESSION['username'])){
header("Location: modulos/logout.php");
}

function salirSESSION(){
header("Location: modulos/logout.php");
}




/// notificaciones
if(!isset($_SESSION['lote']) AND !isset($_SESSION['configR'])){
	$_SESSION['lote'] = $con->query_all("SELECT l.*,p.nombre as nombre,p.marca as marca FROM lotes l JOIN productos p ON p.sku = l.producto_sku WHERE l.activo = TRUE");
	$_SESSION['configR'] = $con->query_simple("SELECT * FROM configuraciones_sistema");	
}

if(!isset($_SESSION['readed'])){
$_SESSION['readed'] = $con->one_query_all("SELECT al.* FROM alertas_leidas al JOIN usuarios_acceso u ON u.id = al.usuario_id WHERE u.cedula_empleado = ?",$ciw23);
}


$lote2 = $_SESSION['lote'] ?? false;
$config = $_SESSION['configR'] ?? false;
$lista = null;
$listafinal = null;
$fallidos = 0;


if(!isset($lista)){
if($lote2 !== false){
foreach($lote2 as $n){
/// FECHA
if (
$n['fecha_vencimiento_y'] <= date('Y') or 
($n['fecha_vencimiento_y'] == date('Y') && $n['fecha_vencimiento_m'] < date('m')) or 
($n['fecha_vencimiento_y'] == date('Y') && $n['fecha_vencimiento_m'] >= date('m') && $n['fecha_vencimiento_d'] < date('d'))
) {
    $lista[] = [ // 
        'sku' => $n['lote_sku'],
        'nombre' => $n['nombre'],
        'marca' => $n['marca'],
        'fecha' => $n['fecha_vencimiento_d'].'/'.$n['fecha_vencimiento_m'].'/'.$n['fecha_vencimiento_y'],
        'dia' => $n['fecha_vencimiento_d'],
        'cantidad' => $n['stock_actual'],
        'sefue' => 'si'
    ];

    
}

elseif($n['fecha_vencimiento_y'] == date('Y') and ($n['fecha_vencimiento_m'] - date('m') == 1) and $n['fecha_vencimiento_d'] > date('d')){
 $lista[] = [ // 
        'sku' => $n['lote_sku'],
        'nombre' => $n['nombre'],
        'marca' => $n['marca'],
        'fecha' => $n['fecha_vencimiento_d'].'/'.$n['fecha_vencimiento_m'].'/'.$n['fecha_vencimiento_y'],
        'cantidad' => $n['stock_actual'],
        'sefue' => 'no'
    ];
}
}


if(isset($lista)){

if($config !== false and $config['eliminar_mermaF'] === true){
foreach($lista as $n){
$day = $n['dia'];

for($i =0; $i == $config['fecha_eliminar_d']; $i++ ){
	if($day > 31){$day = 1;}
	else{$day += $i;}
	}

if($day == date('d') AND $n['sefue'] === 'si'){
	//error_log("LISTA FINAL : ".$n['sku']);
	$listafinal[] = [
	'sku' => $n['sku'],
        'nombre' => $n['nombre'],
        'marca' => $n['marca'],
        'fecha' => $n['fecha'],
        'dia' => $n['dia'],
        'cantidad' => $n['cantidad'],
        'sefue' => 'si'
	];
}
}


if(isset($listafinal)){

foreach($listafinal as $n){
$mensaje = 'Se ha perdido ('.$n['cantidad'].') del producto ['.$n['sku'].'] '.$n['nombre'].'_'.$n['marca'].', en la fecha ['.$n['fecha'].']';
$dateNOW = date('Y_m_d [h:i:s a]');

$stmt = $con->some_query("INSERT INTO alertas (tipo, titulo, mensaje, fecha_creacion, prioridad) VALUES (?, ?, ?, ?, ?)",['Notificacion','Perdida de producto',$mensaje, $dateNOW, 3]);

if($stmt === false){
$fallidos++;
}
}
}

}


else{
foreach($lista as $n){
///error_log("LISTA FINAL : ".$n['sku']);
$mensaje = 'Se ha perdido ('.$n['cantidad'].') del producto ['.$n['sku'].'] '.$n['nombre'].'_'.$n['marca'].', en la fecha ['.$n['fecha'].']';
$dateNOW = date('Y_m_d [h:i:s a]');
///error_log("LA FECHA:		".$dateNOW);

//// EXISTE?
$exist = $con->one_query("SELECT * FROM alertas WHERE mensaje = ?",$mensaje);
if($exist === false){
$stmt = $con->some_query("INSERT INTO alertas (tipo, titulo, mensaje, fecha_creacion, prioridad) VALUES (?, ?, ?, ?, ?)",['Notificacion','Perdida de producto',$mensaje, $dateNOW, 3]);

//// ALGUN FALLO
if($stmt === false){
$fallidos++;
}
}


}


}


}


if($fallidos > 0){
$_SESSION['error'] = 'Error interno localizando notificaciones numero de fallos: '.$fallidos;
}

}
}



///// pfp
$profilepicturepath = "../assets/multimedia/pfp/default.png";
if(isset($path) and !empty($path) and file_exists('../assets/multimedia/pfp/' . $path['image_name'])){
	$profilepicturepath = '../assets/multimedia/pfp/' . $path['image_name'];

}

else{
	$profilepicturepath = '../assets/multimedia/pfp/default.png';
}

//// T A B L A ///
$notificaciones = $con->one_query_all("SELECT a.* FROM alertas AS a LEFT JOIN alertas_leidas AS al ON a.id_A = al.id_al AND al.usuario_id = ? WHERE (a.fecha_programada_y IS NULL OR (a.fecha_programada_y = YEAR(CURDATE()) AND a.fecha_programada_m = MONTH(CURDATE()) AND a.fecha_programada_d = DAY(CURDATE()))) AND al.id_al IS NULL",$_SESSION['idOTU']['id']);
?>


<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../assets/multimedia/icons/icons8-store-50.png" type="image/png">
<title>Menu</title>
<link rel="stylesheet" href="../assets/css/style2.css">
<link rel="stylesheet" href="../assets/css/style2_sidebar.css">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/caljs.css">
<link rel="stylesheet" href="../assets/css/bootstrap-icons/bootstrap-icons.min.css">

<!-- Bootstrap Icons -->
<script src="../assets/js/boostrapjs/bootstrap.bundle.min.js"></script>

<script src="../assets/js/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="../assets/js/jquery-3.7.1.js"></script>
<script src="../assets/js/chart-js/dist/chart.umd.js"></script>


<script src="menu.js"></script>
<script src="logic.js"></script>
</head>


<body>

<?php if(isset($_SESSION['errorGLOBAL']) and $_SESSION['errorGLOBAL'] !== null):?>
<script>
Swal.fire({title: 'Error',text: '<?= $_SESSION['errorGLOBAL'] ?>',icon: 'warning',customClass: {popup: 'sweety-window',title: 'sweety-title',content: 'sweety-content'}});
</script>
<?php endif; $_SESSION['errorGLOBAL'] = null;?>
<?php if(isset($_SESSION['succesGLOBAL']) and $_SESSION['succesGLOBAL'] !== null):?>
<script>
Swal.fire({title: 'Exito',text: '<?= $_SESSION['succesGLOBAL'] ?>',icon: 'success',customClass: {popup: 'sweety-window',title: 'sweety-title',content: 'sweety-content'}});
</script>
<?php endif; $_SESSION['succesGLOBAL'] = null;?>


<!---TOOOLBAR--->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: #212529;">
<div class="container-fluid">
	
<!----SIN ESTO EL CONTENIDO DEL TOOLBAR SE DESPLAZA A LA DERECHA --->
<br>

<div>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUserMenu"><span class="navbar-toggler-icon"></span></button>


<div class="collapse navbar-collapse" id="navbarUserMenu">
<!----			TOOLBARS STUFF SATAR 		----->
<div class="navbar-nav ms-auto">
<!-- Notificaciones -->


<div class="dropdown me-3">
<button class="btn btn-black position-relative" type="button" data-bs-toggle="dropdown">
<i class="bi bi-bell fs-5"></i>
<?php if($notificaciones !== false):?>
<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
<!---NOTIFICACION NUMERO AQUI [5]--->
<?= count($notificaciones)?>
<span class="visually-hidden">notificaciones</span>
</span>
</button>



<!-- 					NOTIFICACIONES VIISIBLES						-->
<ul class="dropdown-menu dropdown-menu-end notificaciones" style="width: 400px; max-height: 400px; overflow-y: auto;">
<li><h6 class="dropdown-header">Notificaciones</h6></li>

<!----------------->
<form method="POST" action="remove.php">
<input type="hidden" name="Wmove" value="dropALL">
<li><button type="submit" class="dropdown-item">Limpiar todas las notificaciones</button></li>
</form>
<!----------------->


<!-- Contenedor con scroll -->
<div class="notificaciones-container" style="padding: 0 10px;">
<?php $freid = 0; foreach($notificaciones as $n): ?>
<?php 
// Determinar clase según el rango
$clasN = '';
if(isset($n['prioridad'])) {
if($n['prioridad'] == 2) {
$clasN = 'alert-warning';
} elseif($n['prioridad'] >= 3) {
$clasN = 'alert-danger';
} elseif($n['prioridad'] <= 1) {
$clasN = 'rounded';
}
}

$freid++;
?>

<!----------------->  
<label class="dropdown-item notificacion-item alert <?php echo $clasN; ?> mb-2 p-2 rounded" style="margin-bottom: 5px !important;" data-bs-toggle="modal" data-bs-target="#notif<?php echo $freid;?>">
<div class="d-flex flex-column">
<div class="d-flex justify-content-between align-items-start">
<strong class="me-2"><?= htmlspecialchars($n['titulo']) ?></strong>
<?php if(isset($n['rango'])): ?>
<span class="badge bg-danger" style="font-size: 0.7em;">

</span>
<?php endif; ?>
</div>
<small class="text-muted mt-1"><?= htmlspecialchars($n['mensaje']) ?></small>
<?php if(isset($n['fecha_creacion'])): ?>
<small class="text-end mt-1" style="font-size: 0.75em; color: #666;">
<i><?= htmlspecialchars($n['fecha_creacion']) ?></i>
</small>
<?php endif; ?>
</div>
</label>
<!----------------->

<?php endforeach; ?>


        
<?php if(empty($notificaciones)): ?>
<li class="dropdown-item text-center py-3">
<small class="text-muted">No hay notificaciones</small>
</li>
<?php endif; ?>
</div>
</ul>
<?php endif;?>
<!-- 					NOTIFICACIONES VIISIBLES ENDS HERE			-->
</div>


<li class="nav-item dropdown">
<img src="<?= $profilepicturepath?>" width="60" class="toolbarPFP">
</li>





<!-- 			SUB VARRA PARTE DEL PERFIL						-->
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle text-white" role="button" data-bs-toggle="dropdown" aria-expanded="false">
<?=htmlspecialchars($_SESSION['username'])?> 
</a>
<div>
<p class="text-white">
<?php if(isset($_SESSION['is_above']) and $_SESSION['is_above'] === true){ echo "<img src='../assets/logos/admin.svg' class='admin_privileg'>"; }?>
<?=htmlspecialchars($_SESSION['user_ROL'])?></p>
</div>
<ul class="dropdown-menu dropdown-menu-end notificaciones">
	
	
	
<!-- Información del usuario -->
<li>
<div class="dropdown-header">
<div class="d-flex align-items-center">
	
<div class="flex-grow-1 ms-2">
<h6 class="mb-0"><?= htmlspecialchars($_SESSION['username']) ?></h6>
</div>

</div>
</div>
</li>
			
			

<li><hr class="dropdown-divider"></li>

<!-- Enlaces de perfil -->
<li>
<button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#perfilintagram">
<i class="bi bi-person me-2"></i>
Mi Perfil
</button>
</li>
<li>
<label class="dropdown-item" for="butondivaedti">
<i class="bi bi-pencil-square me-2"></i>Editar Perfil
</label>
<button type="button" data-bs-toggle="modal" data-bs-target="#edit_soft" style="display:none;" id="butondivaedti"></button>
</li>
<li>
<a class="dropdown-item" href="#">
<i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña
</a>
</li>

<!-- Cambiar de usuario -->
<li><hr class="dropdown-divider"></li>


<li><hr class="dropdown-divider"></li>
<li>
<button class="dropdown-item" type="button" id="colorTHEMA">
<i class="bi bi-arrow-left-right me-2"></i>Modo Oscuro
</button>
</li>

<!-- Cerrar sesión -->
<li><hr class="dropdown-divider"></li>

<li>
<button class="dropdown-item" type="button" onclick='window.location.href = "../login.php";'>
<i class="bi bi-arrow-left-right me-2"></i>Reinciar Session
</button>
</li>

<li>
<a class="dropdown-item text-danger" href="modulos/logout.php" onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?');">
<i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
</a>
</li>
</ul>
</li>

<!-- 			SUB VARRA PARTE DEL PERFIL END HERE				-->

</ul>
<!-- 			TOOLBARS STUFF SATAR  ENS HERE		-->
</div>


</div>

</div>

</nav> 
<!--TOOLBAR ENDS HERE BROOOO--->



<!---MODULOS--->
<div class="box">  
<!--ASIDE SIDEBAR WHATRECVER---->
<aside class="sidebar">
<?php include 'modulos/nav_user/sidebar.php';?>
</aside>


<!--PAGE CARGAA ANQUI -->

<!-----ACCIONES RAPIDAS------->
<div class="ar_class">
<label for="calcbuton" class="ar_class_iccons"><img src="../assets/logos/calc.svg"></label>
<button type="button" class="form-button" data-bs-toggle="modal" data-bs-target="#caljs" id="calcbuton"></button>

<label for="moneyexchange" class="ar_class_iccons"><img src="../assets/logos/money.svg"></label>
<button type="button" class="form-button" id="moneyexchange" data-bs-toggle="modal" data-bs-target="#caltakegreen"></button>
</div>



<!------CONTENIDO DE PAGINA------>
<div id="contenido-dinamico" class="box-main">
<?php 
$pagina = $_GET['pagina'] ?? $_COOKIE['pagina'] ?? null;
if($pagina !== null){
$archivos = [
"modulos/inicio/{$pagina}.php",
"modulos/productos/{$pagina}.php",
"modulos/empleados/{$pagina}.php",
"modulos/proveedores/{$pagina}.php",
"modulos/monedas/{$pagina}.php",
"modulos/roles/{$pagina}.php",
"modulos/categorias/{$pagina}.php",
"modulos/lotes/{$pagina}.php",
"modulos/ventas/{$pagina}.php",
"modulos/reportes/{$pagina}.php",
"modulos/nav_user/{$pagina}.php",
"modulos/moup/{$pagina}.php"
];


try {
$archivoIncluido = false; 
foreach ($archivos as $archivo) {
if (file_exists($archivo)) {
include $archivo; 
$archivoIncluido = true; 
$_COOKIE['pagina'] = $_GET['pagina'] ?? $pagina;
break; } 
} 
if (!$archivoIncluido) { 
echo "<div class='alert alert-warning'>No se encontró la página.</div>";
} 
} 
catch (Exception $e) {
error_log("Error al incluir archivo: " . $e->getMessage());
echo "<div class='alert alert-danger'>Error al cargar la página.</div>";
}

}
else{
$path_choosed = '';
if(file_exists(realpath('../assets/multimedia/logos/empresa.jpg'))){
	$path_choosed = '../assets/multimedia/logos/empresa.jpg';
}
else{
	$path_choosed =  '../assets/multimedia/pfp/default.png';
}
echo " <div class='vanishmaindiv'> <img src='".$path_choosed."' class='pictureEMvanish'> </div> ";
}
?>


</div>

<!--PAGE CARGAA ANQUI END HERE -->




</div>
<!--MODULOS ENDS HERE---->



<!-- 		PERFIL 			-->
<div class="modal fade" id="perfilintagram" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
	
<div class="modal-header profile-header">
<h5 class="modal-title">Perfil de Usuario</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>


<div class="modal-body text-center profile-containe">
<img src="<?= $profilepicturepath ?>" 
alt="Foto de perfil" 
class="rounded-circle mb-3" 
style="width: 250px; height: 250px; object-fit: cover;">

                <h4><?= htmlspecialchars($_SESSION['username']) ?></h4>
            </div>
            
           <div class="modal-footer profile-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-----------PERFIL END HERE------------->






<!-- 		EDIT INSTAGRAM 			-->
<div class="modal fade" id="edit_soft" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header bg-info text-white">
<h5 class="modal-title"><i class="bi bi-person-check"></i> Editar Usuario</h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<form class="caja_singIN2" method="POST" action="upf.php" enctype="multipart/form-data">
<input type="hidden" name="current" value="<? $pagina ?>">
<input type="hidden" name="motivos" value="actua_us"> 
<input type="hidden" name="ekw" value="<?= $ciw23 ?>">
<!---PROFILE_PICTURE---->
<div class="image-upload-section">
<input type="file" name="instapicture" id="froistapgp" accept="image/*" style="display:none">
<label class="inputAA" for="froistapgp">
<img src="<?= htmlspecialchars($profilepicturepath)?>" id="swdlpgp" alt="Previsualización de imagen">
</label>
<span>Ingrese imagen</span>
</div>
<!------->
<div class="modal-body">


<!---2---->
<div class="mb-3">
<label class="form-label">Username <span class="text-danger">*</span></label>
<input type="text" name="edit_name" class="form-control" required pattern="[a-zA-Z0-9_]+" title="Solo letras, números y guión bajo" value="<?= htmlspecialchars($_SESSION['username']) ?>">
</div>
                    


<!----3--->


<!---4---->
<div class="mb-3">
<label class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
<input type="password" name="passwor_configrm" class="form-control" required>
</div>
                    

<!----5--->


</div>
                
<!----6--->
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
<button type="submit" class="btn btn-info">Guardar Cambios</button>
</div>
</form>
        
        
        

</div>
</div>
</div>
<!-----------INSTAGRAM END HERE------------->








<!-------------CALCJS-------------------->
<div class="modal fade" id="caljs" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="caltitle">
<h1>Calculadora</h1>
</div>

<div class="calmain">
<table>
<tr>
<th colspan="5">
<input id="displayBox" type="text" readonly>
</th>
</tr>
<tr>
<td>
<div class="caldisplaytable-cell" id="bMClear">
<button class="calbutton calgreybutton" type="button" onclick="calculadora.clearMemory()">MC</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bMRead">
<button class="calbutton calgreybutton" type="button" onclick="calculadora.readMemory()">MR</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bMAdd">
<button class="calbutton calgreybutton" type="button" onclick="calculadora.addToMemory()">M+</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bMSub">
<button class="calbutton calgreybutton" type="button" onclick="calculadora.subtractFromMemory()">M-</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bMSave">
<button class="calbutton calgreybutton" type="button" onclick="calculadora.saveToMemory()">MS</button>
</div>
</td>
</tr>
<tr>
<td>
<div class="caldisplaytable-cell" id="bSquare">
<button class="calbutton calblackbutton" type="button" onclick="calculadora.square()">x^2</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bNthPowerOfTenOr">
<button class="calbutton calblackbutton" type="button" onclick="calculadora.nthTenPower()">10^x</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bSine">
<button class="calbutton calblackbutton" type="button" onclick="calculadora.writeMathFunction('sin(')">sin</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bCosine">
<button class="calbutton calblackbutton" type="button" onclick="calculadora.writeMathFunction('cos(')">cos</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bTangent">
<button class="calbutton calblackbutton" type="button" onclick="calculadora.writeMathFunction('tan(')">tan</button>
</div>
</td>
</tr>
<tr>
<td>
<div class="caldisplaytable-cell" id="bCube">
<button class="calbutton calblackbutton" type="button" onclick="calculadora.cube()">x^3</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bInverseNumber">
<button class="calbutton calblackbutton" type="button" onclick="calculadora.inverseNumber()">x^-1</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bSqrt">
<button class="calbutton calblackbutton" type="button" onclick="calculadora.writeMathFunction('sqrt(')">sqrt</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bLogOr">
<button class="calbutton calblackbutton" type="button" onclick="calculadora.writeMathFunction('log(')">log</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bNaturalLog">
<button class="calbutton calblackbutton" type="button" onclick="calculadora.writeMathFunction('ln(')">ln</button>
</div>
</td>
</tr>
<tr>
<td>
<div class="caldisplaytable-cell" id="bPercentage">
<button class="calbutton" type="button" onclick="calculadora.writeMathFunction('e')">e</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bClearDisplayCE">
<button class="calbutton calredbutton" type="button" onclick="calculadora.clearDisplay()">CE</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bClearDisplayC">
<button class="calbutton calredbutton" type="button" onclick="calculadora.clearDisplay()">C</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bDelete">
<button class="calbutton calredbutton" type="button" onclick="calculadora.eraseLastInput()">Del</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bDivide">
<button class="calbutton calgreenbutton" type="button" onclick="calculadora.writeOperatorToDisplay('/')">/</button>
</div>
</td>
</tr>
<tr>
<td>
<div class="caldisplaytable-cell" id="bPi">
<button class="calbutton" type="button" onclick="calculadora.writeMathFunction('PI')">π</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bSeven">
<button class="calbutton calbluebutton" type="button" onclick="calculadora.writeToDisplay('7')">7</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bEight">
<button class="calbutton calbluebutton" type="button" onclick="calculadora.writeToDisplay('8')">8</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bNine">
<button class="calbutton calbluebutton" type="button" onclick="calculadora.writeToDisplay('9')">9</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bMultiply">
<button class="calbutton calgreenbutton" type="button" onclick="calculadora.writeOperatorToDisplay('*')">x</button>
</div>
</td>
</tr>
<tr>
<td>
<div class="caldisplaytable-cell" id="bFactorial">
<button class="calbutton" type="button" onclick="calculadora.calculateFactorial()">n!</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bFour">
<button class="calbutton calbluebutton" type="button" onclick="calculadora.writeToDisplay('4')">4</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bFive">
<button class="calbutton calbluebutton" type="button" onclick="calculadora.writeToDisplay('5')">5</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bSix">
<button class="calbutton calbluebutton" type="button" onclick="calculadora.writeToDisplay('6')">6</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bSubtract">
<button class="calbutton calgreenbutton" type="button" onclick="calculadora.writeOperatorToDisplay('-')">-</button>
</div>
</td>
</tr>
<tr>
<td>
<div class="caldisplaytable-cell" id="bToggleSign">
<button class="calbutton calgreenbutton" type="button" onclick="calculadora.toggleSign()">(-)</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bOne">
<button class="calbutton calbluebutton" type="button" onclick="calculadora.writeToDisplay('1')">1</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bTwo">
<button class="calbutton calbluebutton" type="button" onclick="calculadora.writeToDisplay('2')">2</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bThree">
<button class="calbutton calbluebutton" type="button" onclick="calculadora.writeToDisplay('3')">3</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bAdd">
<button class="calbutton calgreenbutton" type="button" onclick="calculadora.writeOperatorToDisplay('+')">+</button>
</div>
</td>
</tr>
<tr>
<td>
<div class="caldisplaytable-cell" id="bOpenParentheses">
<button class="calbutton calgreenbutton" type="button" onclick="calculadora.writeOperatorToDisplay('(')">(</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bCloseParentheses">
<button class="calbutton calgreenbutton" type="button" onclick="calculadora.writeOperatorToDisplay(')')">)</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bZero">
<button class="calbutton calbluebutton" type="button" onclick="calculadora.writeToDisplay('0')">0</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bDot">
<button class="calbutton calgreenbutton" type="button" onclick="calculadora.writeToDisplay('.')">.</button>
</div>
</td>
<td>
<div class="caldisplaytable-cell" id="bEquals">
<button class="calbutton calgreenbutton" type="button" onclick="calculadora.solveOperation()">=</button>
</div>
</td>
</tr>
</table>
</div>



</div>
</div>
</div>
<!--------------CALCJS------------------->



<!----------------MOENYTTAKE----------------------->
<div class="modal fade" id="caltakegreen" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header bg-primary text-white">
<h5 class="modal-title" id="currencyModalLabel">
<i class="fas fa-calculator me-2"></i>Conversor de Divisas
</h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>


<div class="modal-body">
<div class="container-fluid">
<!-- Tasas de cambio actuales -->
<div class="row mb-4">


<?php foreach($tasas_de_cambio as $n):?>
<?php if($n['base'] !== 1):?>
<div class="col-md-6 mb-3 mb-md-0">
<div class="card-WW border-primary">
<div class="card-body text-center">
<h6 class="card-subtitle mb-2 text-muted"><?= $n['codigo']?>(<?= $n['simbolo']?>) a Bolívar</h6>
<h4 class="card-text text-primary fw-bold">
<?= $n['simbolo']?>1 = <span id="usdRate">Bs <?= $n['tasa_cambio']?></span>
</h4>
</div>
</div>
</div>
<?php endif;?>
<?php endforeach;?>


      
</div>
          
          
          
          
<!-- Contenedor del conversor -->
<div class="converter-container">
<!-- Fila superior: De -->
<div class="row align-items-end mb-4">
<div class="col-md-10">
<label for="fromCurrency" class="form-label fw-bold">De:</label>
<div class="row g-2">
<div class="col-md-6">
					  
					  
<select id="inINPUTMONEY" class="form-select">
<?php foreach($tasas_de_cambio as $n):?>
<option value="<?php echo $n['codigo'];?>" data-cambio="<?= $n['tasa_cambio'] ?>" <?= ($n['simbolo'] === '$')? 'selected':''?> ><?=$n['simbolo']?></option>
<?php endforeach;?>
</select>
                    
                    
</div>
<div class="col-md-6">
<div class="input-group">
<span class="input-group-text" id="siMBW32"></span>
<input type="number" id="fromAmount" class="form-control" placeholder="0.00" min="0" step="0.01" value="100">
</div>
</div>
</div>
</div>
</div>




<!-- Botón de intercambio -->
<div class="row mb-4">
<div class="col-12 text-center">
<button type="button" id="swapBtn" onclick="swapCurrencies()" class="btn btn-outline-primary rounded-circle p-3">
<i class="fas fa-exchange-alt fa-lg"></i>
</button>
</div>
</div>






<!-- Fila inferior: A -->
<div class="row align-items-end mb-4">
<div class="col-md-10">
<label for="outINPUTMONEY" class="form-label fw-bold">A:</label>

<!--------------->
<div class="row g-2">				
<!--------------->
<div class="col-md-6">
<select id="outINPUTMONEY" class="form-select">
<?php foreach($tasas_de_cambio as $n):?>
<option value="<?= $n['codigo']?>" data-cambio="<?= $n['tasa_cambio'] ?>"><?=$n['simbolo']?></option>
<?php endforeach;?>
</select>
</div>
<!--------------->




<div class="col-md-6">
<div class="input-group">
<span class="input-group-text" id="siMBW31"></span>
<input type="number" id="cantidadOutP" class="form-control" placeholder="0.00" readonly>
</div>
</div>
<!--------------->

</div>
<!--------------->




</div>




</div>
            
            <!-- Resultado de la conversión -->
            <div class="row">
              <div class="col-12">
                <div class="alert alert-success">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-0 fw-bold">Resultado de la conversión:</h6>
                    </div>
                    <div>
                      <h4 class="mb-0 text-success fw-bold" id="conversionResult">0.00</h4>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-4">
            <p class="text-muted small">
              <i class="fas fa-info-circle me-1"></i>
              Las tasas de cambio son valores de referencia. Vienen primordialmente de que estos esten bien actualizados.</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<!--------------------MOENYTTAKE-------------------->


<!------NOTIF----------->
<?php $freid = 0; foreach($notificaciones as $n): ?>
<?php 
$freid++;
$autor = null;

if($n['sender'] > 0){
	$autor = $con->one_query("SELECT username FROM usuarios_acceso WHERE id = ?",$n['sender']);
}
else{
	$autor = "Sistema";
}
?>

<!----------------->
<div class="modal fade" id="notif<?php echo $freid;?>" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<!-- Encabezado dinámico según prioridad -->
<div class="modal-header" id="notificacionHeader">
<h5 class="modal-title" id="modalVerNotificacionLabel">
<i class="fas fa-bell me-2"></i>Notificación
</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
<div class="container-fluid">
<!-- Fila superior: Información de cabecera -->
<div class="row mb-4">
<div class="col-12">
<div class="d-flex align-items-center justify-content-between">
<!-- Indicador de prioridad -->
<div class="d-flex align-items-center">
<div class="priority-badge me-3" id="priorityBadge">
<i class="fas fa-exclamation-circle"></i>
</div>
<div>
<span class="badge bg-secondary" id="priorityLabel">Prioridad Media</span>
<span class="badge bg-light text-dark ms-2" id="notificationId">#00<?=$n['prioridad']?></span>
</div>
</div>

<!-- Fecha -->
<div class="text-end">
<small class="text-muted" id="fechaCompleta"><?= htmlspecialchars($n['fecha_creacion']) ?></small>
<div>
<span class="badge bg-info" id="estadoNotificacion">No leída</span>
</div>
</div>
</div>
</div>
</div>

<!-- Título -->
<div class="row mb-3">
<div class="col-12">
<h4 class="fw-bold text-dark" id="notificacionTitulo"><?= htmlspecialchars($n['titulo']) ?></h4>
</div>
</div>

<!-- Información del remitente -->
<div class="row mb-4">
<div class="col-12">
<div class="card border-0 bg-light">
<div class="card-body py-3">
<div class="row">
<div class="col-md-6">
<div class="d-flex align-items-center">
<div class="avatar-remitente me-3">
<i class="fas fa-user-circle fa-2x text-primary"></i>
</div>
<div>
<h6 class="mb-1 fw-bold" id="remitenteNombre">Enviado por <b><?=$autor?></b></h6>
</div>
</div>
</div>
<div class="col-md-6 text-md-end mt-2 mt-md-0">
<div>
<i class="fas fa-paper-plane me-1 text-muted"></i>
<span class="text-muted">Enviado en:</span>
<span class="fw-bold ms-1" id="fechaEnvio"><?= htmlspecialchars($n['fecha_creacion']) ?></span>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
          
<!-- Contenido del mensaje -->
<div class="row">
<div class="col-12">
<div class="card border">
<div class="card-header bg-white border-bottom">
<h6 class="mb-0 fw-bold">
<i class="fas fa-align-left me-2"></i>Mensaje
</h6>
</div>
<div class="card-body">
<div class="notification-content" id="notificacionContenido">
<p><?= htmlspecialchars($n['mensaje']) ?></p>
</div>

</div>
</div>
</div>
</div>
          
<!-- Información adicional -->
<div class="row mt-4">
<div class="col-12">
<div class="d-flex flex-wrap justify-content-between align-items-center">
<div>
<span class="text-muted small">
<i class="fas fa-info-circle me-1"></i>
ID de notificación: <span id="notificacionUID" class="fw-bold"><?= $n['id_A']?></span>
</span>
</div>

</div>
</div>
</div>
</div>
</div>
      
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
<i class="fas fa-times me-1"></i>Cerrar
</button>

<!----------------->
<form method="post" action="remove.php">
<input type="hidden" name="IAL" value="<?= $n['id_A']?>">
<input type="hidden" name="Wmove" value="dropone">
<button type="submit" class="btn btn-success">
<i class="fas fa-check me-1"></i>Marcar como leída
</button>
</form>
<!----------------->

</div>

<!----------------->
</div>
</div>
</div>
<!----------------->
<?php endforeach;?>
<!------NOTIF----------->



<?php include "../footer.php";?>


</body>
</html>

<!--------
<script>
let cambioMBD = {
    <php foreach ($tasas_de_cambio as $n): ?>
        "<php echo addslashes($n['codigo']); ?>": "<php echo addslashes($n['tasa_cambio']); ?>",
        <php if ($n['base'] === 1): ?>
            "<php echo addslashes($n['codigo']); ?>": 1, // 1 VES = 1 VES
        <php endif; ?>
    <php endforeach; ?>
};


window.onload = function() {
///// MONEY EXCHANGE TOOLS
document.getElementById('siMBW31').textContent = document.getElementById('outINPUTMONEY').value;
document.getElementById('siMBW32').textContent = document.getElementById('inINPUTMONEY').value;
document.getElementById('outINPUTMONEY')?.addEventListener('change', function(event) {
document.getElementById('siMBW31').textContent = this.value;
});
document.getElementById('inINPUTMONEY')?.addEventListener('change', function(event) {
document.getElementById('siMBW32').textContent = this.value;
});

}


document.getElementById('inINPUTMONEY')?.addEventListener('change', function(event) {
let resulINM1 = cambioMBD[this.value];
let other = document.getElementById('outINPUTMONEY').value;

if(cambioMBD[resulINM1] && cambioMBD[other]){
	
	
}

for (let clave in cambioMBD) {
if(clave == this.value){
	alert("hola");
}
}
});
</script>

------->


<script>
// Datos de tasas de cambio desde PHP
let cambioMBD = {
    <?php foreach ($tasas_de_cambio as $n): ?>
        "<?php echo addslashes($n['codigo']); ?>": <?php echo ($n['base'] === 1) ? '1' : $n['tasa_cambio']; ?>,
    <?php endforeach; ?>
};

// Símbolos de las monedas
let simbolosMonedas = {
    <?php foreach ($tasas_de_cambio as $n): ?>
        "<?php echo addslashes($n['codigo']); ?>": "<?php echo addslashes($n['simbolo']); ?>",
    <?php endforeach; ?>
};

// Moneda base (VES)
let monedaBase = "VES";

$(document).ready(function() {
    // Inicializar símbolos
    actualizarSimbolos();
    
    // Configurar eventos
    $('#inINPUTMONEY, #outINPUTMONEY').on('change', function() {
        actualizarSimbolos();
        realizarConversion();
    });
    
    $('#fromAmount').on('input', function() {
        realizarConversion();
    });
    
    // Convertir cuando el modal se muestra
    $('#caltakegreen').on('shown.bs.modal', function() {
        realizarConversion();
    });
    
    // Realizar primera conversión
    realizarConversion();
});

// Función para actualizar símbolos en los inputs
function actualizarSimbolos() {
    let monedaIn = $('#inINPUTMONEY').val();
    let monedaOut = $('#outINPUTMONEY').val();
    
    $('#siMBW32').text(simbolosMonedas[monedaIn] || monedaIn);
    $('#siMBW31').text(simbolosMonedas[monedaOut] || monedaOut);
}

// Función para intercambiar divisas
function swapCurrencies() {
    let monedaIn = $('#inINPUTMONEY').val();
    let monedaOut = $('#outINPUTMONEY').val();
    let montoIn = $('#fromAmount').val();
    let montoOut = $('#cantidadOutP').val();
    
    // Intercambiar monedas
    $('#inINPUTMONEY').val(monedaOut);
    $('#outINPUTMONEY').val(monedaIn);
    
    // Intercambiar montos si están disponibles
    if (montoOut && montoOut !== '0.00') {
        $('#fromAmount').val(parseFloat(montoOut).toFixed(2));
    }
    
    actualizarSimbolos();
    realizarConversion();
}

// Función principal de conversión
function realizarConversion() {
    try {
        let monedaIn = $('#inINPUTMONEY').val();
        let monedaOut = $('#outINPUTMONEY').val();
        let montoIn = parseFloat($('#fromAmount').val()) || 0;
        
        // Validar que existan las tasas de cambio
        if (!cambioMBD[monedaIn] || !cambioMBD[monedaOut]) {
            $('#cantidadOutP').val('0.00');
            $('#conversionResult').text('Error: Tasa no disponible');
            return;
        }
        
        // Si son la misma moneda
        if (monedaIn === monedaOut) {
            $('#cantidadOutP').val(montoIn.toFixed(2));
            $('#conversionResult').text(
                `${simbolosMonedas[monedaIn] || monedaIn}${montoIn.toFixed(2)} = ${simbolosMonedas[monedaOut] || monedaOut}${montoIn.toFixed(2)}`
            );
            return;
        }
        
        // Si alguna es la moneda base (VES)
        if (monedaIn === monedaBase) {
            // Convertir desde moneda base a otra divisa
            let tasaOut = cambioMBD[monedaOut];
            let resultado = montoIn / tasaOut;
            
            $('#cantidadOutP').val(resultado.toFixed(2));
            $('#conversionResult').text(
                `${simbolosMonedas[monedaIn] || monedaIn}${montoIn.toFixed(2)} = ${simbolosMonedas[monedaOut] || monedaOut}${resultado.toFixed(2)}`
            );
            
        } else if (monedaOut === monedaBase) {
            // Convertir desde otra divisa a moneda base
            let tasaIn = cambioMBD[monedaIn];
            let resultado = montoIn * tasaIn;
            
            $('#cantidadOutP').val(resultado.toFixed(2));
            $('#conversionResult').text(
                `${simbolosMonedas[monedaIn] || monedaIn}${montoIn.toFixed(2)} = ${simbolosMonedas[monedaOut] || monedaOut}${resultado.toFixed(2)}`
            );
            
        } else {
            // Convertir entre dos divisas que no son la base
            // Usamos la moneda base como intermediaria
            let tasaIn = cambioMBD[monedaIn]; // Divisa In → VES
            let tasaOut = cambioMBD[monedaOut]; // Divisa Out → VES
            
            // Convertir: Divisa In → VES → Divisa Out
            let montoEnVES = montoIn * tasaIn;
            let resultado = montoEnVES / tasaOut;
            
            $('#cantidadOutP').val(resultado.toFixed(2));
            $('#conversionResult').text(
                `${simbolosMonedas[monedaIn] || monedaIn}${montoIn.toFixed(2)} = ${simbolosMonedas[monedaOut] || monedaOut}${resultado.toFixed(2)}`
            );
        }
        
    } catch (error) {
        console.error('Error en la conversión:', error);
        $('#cantidadOutP').val('0.00');
        $('#conversionResult').text('Error en conversión');
    }
}

// Función para convertir automáticamente cada vez que cambia algo
function configurarEventosConversion() {
    // Cada vez que se escribe en el input
    $('#fromAmount').on('keyup', function() {
        realizarConversion();
    });
    
    // Cada vez que se cambia una moneda
    $('#inINPUTMONEY, #outINPUTMONEY').on('change', function() {
        realizarConversion();
    });
    
    // Botón de intercambio
    $('#swapBtn').on('click', swapCurrencies);
}

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    configurarEventosConversion();
    actualizarSimbolos();
    realizarConversion();
});
</script>

