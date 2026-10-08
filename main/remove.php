<?php 
session_start();
$sitio = $_COOKIE['pagina'] ?? $_GET['pagina'] ?? 'menu.php';
require_once(__DIR__ . '/../config/connect.php');
$base = new MYSQL;

if(!$_POST){
$_SESSION['errorGLOBAL'] = "Error en notificaciones: Metodo no autorizado, Error del sistema!";
error_log("Error en notificaciones: Metodo no autorizado, Error del sistema!");
header("Location: ".$sitio);
exit();
}

$accion = $_POST['Wmove'];
$ida = $_POST['IAL'] ?? null;



if(!isset($accion)){
$_SESSION['errorGLOBAL'] = "Error en notificaciones: Metodo no autorizado, Error del sistema(2)!";
error_log("Error en notificaciones: Metodo no autorizado, Error del sistema(2)!");
header("Location: ".$sitio);
exit();
}


if($accion === 'dropALL'){
try{
	if(!isset($_SESSION['idOTU'])){
		throw new Exception("Datos Invalidos!");
	}
	$sql = "select * from alertas a JOIN alertas_leidas ai WHERE ai.usuario_id != ?";
	$stmt = $base->one_query($sql,$_SESSION['idOTU']['id']);
	
if(!$stmt){
		throw new Exception("Usuario no identificado!");
	}

else{
	foreach($stmt as $n){
$old = $base->some_query("SELECT * FROM alertas_leidas WHERE id_al = ? AND usuario_id = ?", [$n['id_A'],$_SESSION['idOTU']['id']]);
if(!$old){
$stmt2 = $base->some_query("INSERT INTO alertas_leidas (id_al,usuario_id) VALUES (?, ?)",[$n['id_A'],$_SESSION['idOTU']['id']]);
if(!$stmt2){
throw new Exception("Error guardando las notificaciones!");
}
}
	}
}
}

catch(Exception $e){
error_log("Error eliminando Notificaciones: ".$e);
$_SESSION['errorGLOBAL'] = "Error eliminando Notificaciones: ".$e;
}
}


elseif($accion === 'dropone'){
	try{
		if(!isset($_SESSION['idOTU']) or !isset($ida)){
			throw new Exception("Datos Invalidos!");
		}
$old = $base->some_query("SELECT * FROM alertas_leidas WHERE (id_al = ? AND usuario_id = ?)", [$ida,$_SESSION['idOTU']['id']]);

if($old){
throw new Exception("Error interno!");
}

else{
$stmt = $base->some_query("INSERT INTO alertas_leidas (id_al, usuario_id) VALUES (?, ?)",[$ida,$_SESSION['idOTU']['id']]);
if($stmt === false){
throw new Exception("Error del sistema!");
}

}
	}

catch(Exception $e){
error_log("Error eliminando la notificacion: ".$e);
$_SESSION['errorGLOBAL'] = "Error eliminando la notificacion: ".$e;
	}
}

header("Location: ".$sitio);
exit();
?>