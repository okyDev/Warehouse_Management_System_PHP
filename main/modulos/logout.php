<?php
session_start();
// Destruir todas las variables de sesión
require_once '../../config/connect.php';
$base = new MYSQL;


$razon = $_GET['razon'] ?? null;
//$sql = 'update usuarios_acceso set activo = false where username = ?';

// Preparar la consulta de actualización

$sql = "UPDATE usuarios_acceso SET activo = false WHERE username = ?";

// Preparar y vincular
$resultado = $base->one_query($sql,$_SESSION['username']);

$ci = null;
if(isset($_SESSION['cedula'])){
	$ci = $_SESSION['cedula'];
	
}
elseif (isset($_COOKIE['respaldoCI'])){
	$ci = $_COOKIE['respaldoCI'];
}



if($razon === null){
// Ejecutar la consulta
if ($resultado!== null) {
$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$ci,"Cierre de Session","Logout","Cierre de Session",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al login
header("Location: ../../index.php");
$_SESSION['logeado'] = false;
setcookie('respaldoCI', '', time() - 3600, '/');
setcookie('pagina', '', time() - 3600, '/');
exit();

    } 
}
else{

$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$ci,"Intento de acceso a contenido no permito","Logout","Intento de acceso sin permiso ".$razon,date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al login
header("Location: ../../index.php");
$_SESSION['logeado'] = false;
setcookie('respaldoCI', '', time() - 3600, '/');
exit();

}
	
// Cerrar la declaración y la conexión
?>


