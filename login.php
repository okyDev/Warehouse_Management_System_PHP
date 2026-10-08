<?php
session_start();
require_once(__DIR__ . '/config/alert.php');
require_once(__DIR__ . '/config/connect.php');
$base = new MYSQL;
$_SESSION['os_version'] = '0.0.3';
error_log("DESDE AQUI EN LOGI");
$path_logo = "assets/multimedia/logos/empresa.jpg";
error_log("Driver usado: ".$base->getDriver());

$info = $base->query_simple('SELECT * FROM empresa');
$_SESSION['EmpresaN1'] = $info['nombre'] ?? null;
$_SESSION['EmpresaN2'] = $info['iva'] ?? null;
$_SESSION['EmpresaN3'] = $info['email'] ?? null;
$_SESSION['EmpresaN4'] = $info['telefono'] ?? null;
$_SESSION['EmpresaN5'] = $info['rif'] ?? null;
$info = null;
$_SESSION['reinicio'] = false;



if (isset($_POST['BINI'])) {
try {
	$user = isset($_POST['n1']) ? trim($_POST['n1']) : '';
	$pw = isset($_POST['n2']) ? $_POST['n2'] : '';
	$hash = hash('sha1', $pw);

	$sql = "SELECT U.password, U.rol_id, U.activo, U.existe, U.is_above, U.cedula_empleado AS cedula, r.nombre_rol AS rol FROM usuarios_acceso U JOIN roles r ON U.rol_id = r.rol_id WHERE U.username = ? AND U.block = 0 LIMIT 1";
	$result = $base->one_query($sql, $user);



	if ($result > 0) {
		$usuario = $result;
		error_log("PASSWORD IN: [".$hash."] PASSWORD BASE: [".$usuario['password']."]");

		// Verificar contraseña
		if ($hash == $usuario['password']) {
			
			if ($usuario['existe'] == 0) { 
				$_SESSION['error'] = 'Su cuenta ha sido deshabilitada. Contacte al Administrador.';
				exit();
			}

			// Crear sesión
			$_SESSION['logeado'] = true;
			$_SESSION['user_cedula'] = $usuario['cedula'];
			$_SESSION['username'] = $user;
			$_SESSION['is_above'] = (isset($usuario['is_above']) and $usuario['is_above'] > 0)? true:false;
			//$idrol = $base->one_query("SELECT rol_id FROM roles WHERE nombre_rol = ?",$usuario['rol']);
			$_SESSION['user_rol'] = $usuario['rol_id'];
			$_SESSION['user_ROL'] = $usuario['rol'];



			// Activar usuario
			$sql = "UPDATE usuarios_acceso SET activo = true WHERE username = ?";
			$updateResult = $base->one_query($sql, $user);
			
			/// LOG
			setcookie('respaldoCI', $usuario['cedula'], 0, '/');

			$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$usuario['cedula'],"login exitoso","Login","Inicio de session",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);
			// Redirección
			header("Location: main/menu.php");
			exit();

		} else {
			// CORRECCIÓN: Primero limpiar sesión, luego mostrar alerta
			///$_SESSION = [];
			unset($_SESSION['logeado']);
			unset($_SESSION['user_cedula']);
			unset($_SESSION['username']);
			unset($_SESSION['user_rol']);
			
			$_SESSION['error'] =  'Contrase\u00F1a incorrecta';
			setcookie('respaldoCI', '', time() - 3600, '/');
		}

	} else {
		// CORRECCIÓN: Primero limpiar sesión, luego mostrar alerta
		$_SESSION = [];
		$_SESSION['error'] = 'Usuario incorrecto';
		setcookie('respaldoCI', '', time() - 3600, '/');
	}

} catch(Exception $e) {
	error_log("ERROR EN LOGIN: ".$e->getMessage());
	$_SESSION['error'] = 'Error en el sistema: ' . $e->getMessage();
}
}



if(isset($_SESSION['logeado']) AND isset($_SESSION['user_cedula']) AND $_SESSION['logeado'] === true){
$ci = $_SESSION['user_cedula'];
$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$ci,"Reincio Exitoso","Login","Session Reinciada",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);
// Redirección
header("Location: main/menu.php");
exit();
}



if(!isset($_SESSION['backupaudate'])){
//// ALL SETTINGS
$backup_set = $base->query_all('SELECT copias_automaticas, eliminar_mermaF, fecha_eliminar_d, FROM configuraciones_sistema');
$fecha_b = $base->query_all('SELECT * FROM copias_automaticas WHERE estado = 0');

//// IS ANY SAVED AND ENABLED?
if($backup_set !== false AND $backup_set['copias_automaticas'] === true AND $fecha_b !== false){

//// FRECUENCIAS
$fecha_c_d = $fecha_b['fecha_creacionD'];
$fecha_c_m = $fecha_b['fecha_creacionM'];
$fecha_c_y = $fecha_b['fecha_creacionY'];
$fecha_c_h = $fecha_b['fecha_creacionH'];
$fecha_c_mi = $fecha_b['fecha_creacionI'];
$fecha_c_om = $fecha_b['fecha_creacionPM'];
$wek = $fecha_b['week_day'];

///// MENSUAL
if($fecha_b['frecuencia'] === 'Mensual'){


$faltaY =  $fecha_c_y - date('Y') ;
$faltam = 0;
//// CALCULAR MES
if($fecha_c_m + 1 === 13){$faltam = 1;}else{$faltam = $fecha_c_m + 1;}
if($faltaY <= 0 AND $faltam === date('m') AND date('N') === $wek){
$_SESSION['backupaudate'] = true;
}

else{
	$_SESSION['backupaudate'] = false;
}
}///// MENSUAL ENDS HERE


///SEMANAL
elseif($fecha_b['frecuencia'] === 'Semanal'){
	$fechaBE = new DateTime($fecha_c_y.'-'.$fecha_c_m.''.$fecha_c_d);
	$fechaActual = new DateTime();
	
	$intervalo = $fechaBE->diff($fechaActual);
	
	if($intervalo->days >= 7){
		if(date('N') === $wek){
			$_SESSION['backupaudate'] = true;
		}
		else{
			$_SESSION['backupaudate'] = false;
		}
	}
	else{
		$_SESSION['backupaudate'] = false;
	}
}


elseif($fecha_b['frecuencia'] === 'Diaria'){
	if($fecha_c_d + 1 == date('d') AND date('N') === $wek){
		$_SESSION['backupaudate'] = true;
	}
	
	else{
		$_SESSION['backupaudate'] = false;
	}
}

}

else{
	$_SESSION['backupaudate'] = false;
}
}//// ALL ENDS HERE

error_log("EL VALOR BACK ES: ".($_SESSION['backupaudate']? 'True':'false'));
?>





<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Sistema de gestion del almacen</title>
<script src="/assets/js/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="/assets/js/boostrapjs/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="./assets/css/bootstrap.min.css">

<style>
	* {
		margin: 0;
		padding: 0;
		box-sizing: border-box;
		font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
	}

.loadingPanel {
            display: none; /* Oculto inicialmente */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            text-align: center;
            padding-top: 20%;
        }
        
	body {
		background: linear-gradient(135deg, #f5e3c8 0%, #d7ccc8 100%);
		min-height: 100vh;
		display: flex;
		justify-content: center;
		align-items: center;
		padding: 20px;
	}

	.container {
		display: flex;
		max-width: 1000px;
		width: 100%;
		background: white;
		border-radius: 15px;
		box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
		overflow: hidden;
	}

	.left-panel {
		flex: 1;
		background-color:#291588;
		background-size: cover;
		color: white;
		padding: 40px;
		display: flex;
		flex-direction: column;
		justify-content: center;
	}

	.right-panel {
		flex: 1;
		padding: 50px 40px;
		display: flex;
		flex-direction: column;
		justify-content: center;
	}

	.logo {
		display: flex;
		align-items: center;
		margin-bottom: 30px;
	}

	.logo-icon {
		font-size: 32px;
		margin-right: 10px;
	}

	.logo-text {
		font-size: 24px;
		font-weight: 700;
	}

	.welcome-text {
		font-size: 28px;
		margin-bottom: 10px;
		color: #5d4037;
	}

	.subtitle {
		color: #8d6e63;
		margin-bottom: 30px;
	}

	.form-group {
		margin-bottom: 20px;
	}

	label {
		display: block;
		margin-bottom: 8px;
		color: #5d4037;
		font-weight: 500;
	}

	input {
		width: 100%;
		padding: 12px 15px;
		border: 1px solid #d7ccc8;
		border-radius: 8px;
		font-size: 16px;
		transition: all 0.3s;
	}

	input:focus {
		border-color: #8d6e63;
		box-shadow: 0 0 0 2px rgba(141, 110, 99, 0.2);
		outline: none;
	}

	.btn-login {
		background: #8d6e63;
		color: white;
		border: none;
		padding: 14px;
		border-radius: 8px;
		font-size: 16px;
		font-weight: 600;
		cursor: pointer;
		transition: background 0.3s;
		width: 100%;
		margin-top: 10px;
	}

	.btn-login:hover {
		background: #6d4c41;
	}

	.forgot-password {
		text-align: right;
		margin-top: 10px;
	}

	.forgot-password a {
		color: #8d6e63;
		text-decoration: none;
		font-size: 14px;
	}

	.forgot-password a:hover {
		text-decoration: underline;
	}

	.system-features {
		margin-top: 40px;
	}

	.feature {
		display: flex;
		align-items: center;
		margin-bottom: 15px;
	}

	.feature-icon {
		background: rgba(255, 255, 255, 0.2);
		width: 60px;
		height: 40px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		margin-right: 15px;
		font-size: 18px;
	}

	.feature-text {
		font-size: 16px;
	}

	.copyright {
		margin-top: 30px;
		text-align: center;
		font-size: 14px;
		color: rgba(255, 255, 255, 0.7);
	}

	@media (max-width: 768px) {
		.container {
			flex-direction: column;
		}
		
		.left-panel {
			padding: 30px 20px;
		}
		
		.right-panel {
			padding: 30px 20px;
		}
	}
	
 .logo1{
		 width:200px;
		 border-radius:40px;
		 }
		 
		.logo2{
		 width:80px;
		 border-radius:50px;
		 margin-right:30px;
		 }
		 
		 .iconVER{
			 width:30px;
			 }
			 
			 
			 .passPLACE{
				 display:flex;
				 }
</style>

</head>
<body>
<div class="container">

<div class="left-panel">
<div class="logo">
<div class="logo-icon"><img src="<?= $path_logo ?? 'assets/logos/empresa/svg'?>" class="logo1"></div>
<div class="logo-text"><?= $_SESSION['EmpresaN1'] ?? 'Sin definir' ?></div>
</div>
<h1>Sistema de Administracion</h1>
<p>Controla tu inventario, gestiona proveedores y optimiza la producción de tu tienda</p>

<div class="system-features">
<div class="feature">
<div class="feature-icon"><img src="assets/logos/universidad.jpg" class="logo2"></div>
<div class="feature-text">Sistema propocionado de la mano de la <b>Universidad Politecnica Terriotorial de Puerto Cabello</b></div>
</div>
</div>

<div class="copyright">
&copy; <?=date('Y')?> <?= $_SESSION['EmpresaN1'] ?? 'Sin definir'?>. Todos los derechos reservados.
</div>
</div>
	
<div class="right-panel">
<!-- Mensajes -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" id="alert1">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="quitaralerta(1)"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" id="alert2">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="quitaralerta(2)"></button>
</div>
<?php endif; ?>


<h2 class="welcome-text">Bienvenido</h2>

<p class="subtitle">Inicia sesión en tu cuenta</p>



<form method="POST">
<div class="form-group">
<label for="username">Usuario</label>
<input type="text" name="n1" placeholder="Ingresa tu usuario" required>
</div>

<div class="form-group">
<label for="password">Contraseña</label>
<div class="passPLACE">
<input type="checkbox" id="seepass" value="" style="display:none;">

<label for="seepass">
<img src="assets/multimedia/icons/n1.png" class="iconVER" id="iconPASS">
</label>

<input type="password" name="n2" placeholder="Ingresa tu contraseña" required>
</div>
</div>

<button type="submit" class="btn-login" name="BINI">Iniciar Sesión</button>

<!-----	
<div class="forgot-password">
<!---<a href="recoveryp.php" id="miEnlace" onclick="mostrarLoading(event)">¿Olvidaste tu contraseña?</label>---
</div>
----->
</form>

</div>
</div>

<div id="loadingPanel" class='loadingPanel'><h4>Cargando...</h4></div>
</body>
</html>

<script>	
function togglePasswordVisibility() {
const passwordInput = document.getElementsByName('n2')[0]; // Cambié getElementById por getElementsByName
const image = document.getElementById('iconPASS');

const imageVisible = 'assets/multimedia/icons/n1.png'; // Imagen cuando se muestra
const imageHidden = 'assets/multimedia/icons/n2.png'; // Imagen cuando se oculta

// Cambiar el tipo de input según el estado del checkbox
if (document.getElementById('seepass').checked) {
	passwordInput.type = 'text'; // Mostrar la contraseña
	image.src = imageVisible;
} else {
	passwordInput.type = 'password'; // Ocultar la contraseña
	image.src = imageHidden;
}
}

// Llama a la función de toggle al cargar la página y cada vez que cambie
document.addEventListener('DOMContentLoaded', function() {
togglePasswordVisibility(); // Llama al inicio
document.getElementById('seepass').addEventListener('change', togglePasswordVisibility);
});


function quitaralerta(n){
	if(nombre == 1){
		$('#alert1').hide();
	}
	else{
		$('#alert2').hide();
	}
}



function mostrarLoading(event) {
        // Evitar que el enlace se siga
        event.preventDefault();
        
        // Mostrar el panel de carga
        document.getElementById('loadingPanel').style.display = 'block';

        // Retrasar la acción del enlace
        setTimeout(function() {
            // Redirigir a la URL del enlace después de 5 segundos
            window.location.href = event.target.href;
        }, 3000); // 5000 milisegundos = 5 segundos
    }
</script>
