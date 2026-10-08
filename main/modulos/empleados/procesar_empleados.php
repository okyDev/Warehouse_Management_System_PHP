<?php
// procesar_empleado.php
session_start();
require_once '../../../config/connect.php'; // DESCOMENTA ESTO
$base = new MYSQL;

// 1. Verificar permisos y método de solicitud
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Acceso denegado.";
    error_log("ERRROR CON POST");
}


$accion = $_POST['accion'] ?? '';

if(empty($accion)){
	error_log("ERROR CON ACCION A");
	  $_SESSION['error'] = "ERROR interno porfavor contacte e informe por medio del soporte";
	}

//$base->begin_transaction();

try {
	
switch($accion){
case 'crear':

$id_tipo = $_POST['id_tipo'] ?? '';        // FK a tipos_identificacion
$cedula = $_POST['cedula'] ?? '';          // PK
$nombre1 = trim($_POST['nombre1'] ?? '');
$nombre2 = trim($_POST['nombre2'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$telefono = $_POST['telefono'] ?? '';
$direccion = trim($_POST['direccion'] ?? '');
$fecha_creacionD = date('d');
$fecha_creacionM = date('m');
$fecha_creacionY = date('Y');



if (empty($cedula) || empty($apellido) || empty($nombre1)) {
    $_SESSION['error'] = "Faltan los campos cedula, apellido y primer nombre.";
    header("Location: ../../menu.php?pagina=empleados");
    exit();
}

$sql_check = "SELECT cedula FROM empleados_datos WHERE cedula = ?";
$result_check = $base->one_query($sql_check,$cedula);

//// EXISTE CEDUWA
if ($result_check !== false) {
throw new Exception("La cedula $cedula ya esta vinculada a un usuario!");
}
///// ENTONXES EMPEXAR A AGREGAR EMPLEADO wTw
$sql_empleado = "INSERT INTO empleados_datos (cedula, id_tipo, nombre1, nombre2, apellido, telefono, direccion, fecha_creacionD, fecha_creacionM,fecha_creacionY) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_empleado = $base->some_query($sql_empleado,[$cedula, $id_tipo, $nombre1, $nombre2, $apellido, $telefono, $direccion,$fecha_creacionD, $fecha_creacionM, $fecha_creacionY]);


if (!$stmt_empleado) {
throw new Exception("Error al insertar datos del empleado: " . $base->show_error_log());
}

$_SESSION['success'] = "Empleado ha sido creado correctamente!!!";
break;


case 'editar':

    $cedula = trim($_POST['cedula'] ?? ''); // Viene del hidden
    $id_tipo = intval($_POST['id_tipo'] ?? 0); 
    $nombre1 = trim($_POST['nombre1'] ?? '');
    $nombre2 = trim($_POST['nombre2'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($cedula) || empty($apellido) || empty($nombre1)) {
        throw new Exception("Cédula, Apellido y Primer Nombre son obligatorios.");
    }

    $sql = "UPDATE empleados_datos SET 
                id_tipo = ?, 
                nombre1 = ?, 
                nombre2 = ?, 
                apellido = ?, 
                telefono = ?, 
                direccion = ?
            WHERE cedula = ?";
 
$stmt = $base->some_query($sql,[$id_tipo, $nombre1, $nombre2, $apellido, $telefono, $direccion, $cedula]);

    if ($stmt !== false) {
        $_SESSION['success'] = "Empleado con cédula <strong>$cedula</strong> actualizado.";
    } else {
        throw new Exception("Error al actualizar empleado: " . $base->show_error_log());
    }
    //$stmt->close();
    
    break;
    
// ... (código existente, por ejemplo, el catch) ...

	}
   

} 

catch (Exception $e) {
$_SESSION['error'] = "Error Ejecutando operacion: ".$e->getMessage();
error_log("ERROR CREANDO USARIO: ".$e);
}


header("Location: ../../menu.php?pagina=empleados");
exit();
?>
