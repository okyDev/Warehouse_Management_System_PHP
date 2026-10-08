<?php
// modulos/empleados/procesar_usuario.php
session_start();
require_once '../../../config/connect.php';
$base = new MYSQL;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método no permitido";
    header("Location: ../../menu.php?pagina=empleados");
    exit(); 
}

$accion = $_POST['accion'] ?? '';

try {
    switch($accion) {
        case 'crear':
            $cedula_empleado = trim($_POST['empleadoChoose'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $rol_id = intval($_POST['rol_id'] ?? 0);
            $file = procesarImagen($_FILES["imageInput"] ?? null);
          

            // Validaciones
            if(empty($cedula_empleado) || empty($username) || empty($password) || $rol_id === 0) {
                throw new Exception("Todos los campos son obligatorios");
            }

            if ($password !== $confirm_password) {
                throw new Exception("Las contraseñas no coinciden");
            }

            if (strlen($password) < 4) {
                throw new Exception("La contraseña debe tener al menos 4 caracteres");
            }

            // Verificar si el empleado existe
            $sql_check_emp = "SELECT cedula FROM empleados_datos WHERE cedula = ?";
            $result_check_emp = $base->one_query($sql_check_emp, $cedula_empleado);
            
            if ($result_check_emp === false) {
                throw new Exception("El empleado no existe");
            }


            // Verificar si ya tiene usuario
            $sql_check_user = "SELECT id FROM usuarios_acceso WHERE cedula_empleado = ?";
            $result_check_user = $base->one_query($sql_check_user,$cedula_empleado);
            
            if ($result_check_user !== false) {
                throw new Exception("Este empleado ya tiene un usuario asignado");
            }


            // Verificar si username ya existe
            $sql_check_username = "SELECT id FROM usuarios_acceso WHERE username = ?";
            $result_check_username = $base->one_query($sql_check_username,$username);
            
            if ($result_check_username !== false) {
                throw new Exception("El username ya está en uso");
}

            // Hash de contraseña (SHA1 como en tu ejemplo)
            //$password_hash = password_hash($password, PASSWORD_DEFAULT);
            $password_hash = hash('sha1', $password);

            // Obtener fecha actual
            $fecha_inD = date('d');
            $fecha_inM = date('m');
            $fecha_inY = date('Y');
            $fecha_inH = date('h:i');


    
            // Insertar usuario LARGOOOO
$sql = "INSERT INTO usuarios_acceso (cedula_empleado, username, password, rol_id, fecha_inD, fecha_inM, fecha_inY, fecha_inH) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $base->some_query($sql,[$cedula_empleado, $username, $password_hash, $rol_id, $fecha_inD, $fecha_inM, $fecha_inY, $fecha_inH]);
            
if ($stmt !== false) {
                $_SESSION['success'] = "Usuario <strong>$username</strong> creado exitosamente";
            } else {
                throw new Exception("Error al crear usuario: " . $base->show_error_log());
            }
            ///$stmt->close();
            break;
            

case 'gestionar':
$cedula_empleado = trim($_POST['cedula_empleadoedit'] ?? '');
$nueva_password = trim($_POST['nueva_password'] ?? '');
$fileedit = procesarImagen($_FILES["imageInputEdit"] ?? null);
$username = trim($_POST['namedit'] ?? '');


if(empty($cedula_empleado)) {
throw new Exception("Cédula de empleado no válida");
}

$old = $base->one_query("SELECT * FROM usuarios_acceso WHERE cedula_empleado = ? AND block = false",$cedula_empleado);


if($old === false){
	throw new Exception("Error Usuario no reconocido por el sistema");
}

/// CREAR INSERT OR UPDATRE
$sql = "UPDATE usuarios_acceso SET";
$params = [];

$password_hash = sha1($nueva_password);
if($password_hash !== $old['password']){
	$params[] = $password_hash;
	$sql .=" password = ?";
}


if($username !== $old['username']){
	$params[] = $username;
	$sql .=" username = ?";
}
if(!empty($params)){
$sql .= " WHERE cedula_empleado = ?";
$params[] = $cedula_empleado;

if(empty($cedula_empleado)){
	throw new Exception("Cédula de empleado no válida");
}
$stmt = $base->some_query($sql,$params);

if ($stmt !== false) {
$_SESSION['success'] = "Clave actualizada con exito!";
}

else {
throw new Exception("Error al actualizar usuario: " . $base->show_error_log());
}       
}

elseif($fileedit !== null){
$id = $base->one_query("SELECT id FROM usuarios_acceso WHERE cedula_empleado = ?",$cedula_empleado);
$old = $base->one_query("SELECT * FROM usuario_profile WHERE usuario_id = ?",$cedula_empleado);

if($id === false){
	throw new Exception("Error al actualizar la foto de perfil del usuario: " . $base->show_error_log());
}

/// EXISTE : SI
if($old !== false){
$stm2 = $base->some_query("UPDATE usuario_profile SET image_name = ? WHERE usuario_id = ?",[$fileedit,$id['id']]);


/// EJECUTO
if($stm2 !== false){
if(isset($_SESSION['success'])){
$_SESSION['success'] .= " y Foto de perfil actualizado con exito!";
}
else{
$_SESSION['success'] = "Foto de perfil actualizado con exito!";
}

}
/// NO EJECU
else{
throw new Exception("Error actualizando foto de perfil, ".$base->show_error_log());
}

}


/// NO
else{
$stm2 = $base->some_query("INSERT INTO usuario_profile (usuario_id,image_name) VALUES (?,?)",[$id['id'],$fileedit]);
if($stm2 !== false){
if(isset($_SESSION['success'])){
$_SESSION['success'] .= " y Foto de perfil actualizado con exito!";
}
else{
$_SESSION['success'] = "Foto de perfil actualizado con exito!";
}

}
else{
throw new Exception("Error actualizando foto de perfil,".$base->show_error_log());
}
}
}
	
else{
	throw new Exception("No se ejecuto ningun cambio");
}
break;
////// 			ENDS HERE EDITAR
   
default:
throw new Exception("Acción no válida");
}
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}


function procesarImagen($archivo) {
    if ($archivo && $archivo["error"] === UPLOAD_ERR_OK) {
        $targetDir = "../../../assets/multimedia/pfp/";
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

header("Location: ../../menu.php?pagina=empleados");
exit();
?>
