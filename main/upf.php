<?php
session_start();
require_once(__DIR__ . '/../config/connect.php');
$base = new MYSQL;


///
$motivo = $_POST['motivos'] ?? null;
$page =  $_COOKIE['pagina'] ?? 'menu.php';
$cedula_empleado = $_POST['ekw'] ?? null;
$comfirm = sha1(trim($_POST['passwor_configrm'] ?? ''));
$fileedit = $_FILES["instapicture"] ?? null;
$username = trim($_POST['edit_name'] ?? '');

if ($_SERVER["REQUEST_METHOD"] !== "POST" or !isset($motivo)) {
    $_SESSION['errorGLOBAL'] = "Error interno!";
    error_log("Error interno!");
    header("Location: ".$page);
    exit(); 
}


try{
if($motivo === 'actua_us'){

/// UPDATE NAME
if(empty($cedula_empleado) or $cedula_empleado === null) {throw new Exception("Error Verificando la identidad del usuario!");}
$old = $base->one_query("SELECT * FROM usuarios_acceso WHERE cedula_empleado = ? AND block = false",$cedula_empleado);

if(!$old){throw new Exception("Error Usuario no reconocido por el sistema");}


if($old['password'] !== $comfirm){throw new Exception("Clave invalida");}




/// UPDATE NAME
if($username !== $old['username']){
$sql ="UPDATE usuarios_acceso SET username = ? WHERE id = ?";
$stmt = $base->some_query($sql,[$username,$old['id']]);
if ($stmt !== false) {
$_SESSION['succesGLOBAL'] = "Clave actualizada con exito!";
}

else {
throw new Exception("Error al actualizar usuario: " . $base->show_error_log());
}       
		}


///// UPDATE PFP
if($fileedit !== null){
$filepf = procesarImagen($fileedit) ?? null;
if($filepf === null){throw new Exception("Error registrado su imagen de perfil!");}
$id = $base->one_query("SELECT id FROM usuarios_acceso WHERE cedula_empleado = ?",$cedula_empleado);
$old = $base->one_query("SELECT * FROM usuario_profile WHERE usuario_id = ?",$cedula_empleado);

if($id === false){
	throw new Exception("Error al actualizar la foto de perfil del usuario: " . $base->show_error_log());
}

/// EXISTE : SI
if($old !== false){
$stm2 = $base->some_query("UPDATE usuario_profile SET image_name = ? WHERE usuario_id = ?",[$filepf,$id['id']]);


/// EJECUTO
if($stm2 !== false){
if(isset($_SESSION['success'])){
$_SESSION['succesGLOBAL'] .= " y Foto de perfil actualizado con exito!";
}
else{
$_SESSION['succesGLOBAL'] = "Foto de perfil actualizado con exito!";
}

}
/// NO EJECU
else{
throw new Exception("Error actualizando foto de perfil, ".$base->show_error_log());
}

}


/// NO
else{
$stm2 = $base->some_query("INSERT INTO usuario_profile (usuario_id,image_name) VALUES (?,?)",[$id['id'],$filepf]);
if($stm2 !== false){
if(isset($_SESSION['succesGLOBAL'])){
$_SESSION['succesGLOBAL'] .= " y Foto de perfil actualizado con exito!";
}
else{
$_SESSION['succesGLOBAL'] = "Foto de perfil actualizado con exito!";
}

}
else{
throw new Exception("Error actualizando foto de perfil,".$base->show_error_log());
}
}
}


//// NO SE EJECUTO NIGNUN CAMBIO
else{throw new Exception("No se ejecuto ningun cambio");}



}

}

catch(Exception $e){
$_SESSION['errorGLOBAL'] = $e;
error_log("ERROR process_feregrin.php: ".$e);
}





function procesarImagen($archivo) {
    if ($archivo && $archivo["error"] === UPLOAD_ERR_OK) {
        $targetDir = "../assets/multimedia/pfp/";
        $imageFileType = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($imageFileType, $allowedTypes)) {
            $newFileName = uniqid('profile_', true) . '.' . $imageFileType;
            $targetFile = $targetDir . $newFileName;
            
            if (move_uploaded_file($archivo["tmp_name"], $targetFile)) {
                return $newFileName;
            }
        }
    }
    return null;
}


header("Location: ".$page);
exit();	
?>