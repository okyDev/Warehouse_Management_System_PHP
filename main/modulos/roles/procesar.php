<?php
// modulos/roles/procesar_rol.php
session_start();
require_once '../../../config/connect.php';
require_once '../../../config/auth.php';
// Asumo que tu clase MYSQL maneja la conexión y métodos de consulta.
$base = new MYSQL; 

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
$_SESSION['error'] = "Método no permitido";
header("Location: ../../menu.php?pagina=roles");
exit();
}

$accion = $_POST['accion'] ?? '';

try {
//$base->autocommit(false); // Iniciar transacción

switch($accion) {
case 'crear':

if(!verify("crear_roles")){throw new Exception("Falta de Permisos necesarios");}
$nombre = trim($_POST['nombre'] ?? '');
// Los permisos vienen como un array de IDs o Nombres de Permisos
$permisos_nombres = $_POST['permisos'] ?? []; 

if (empty($nombre)) throw new Exception("El nombre del rol es obligatorio.");
// Permitimos crear un rol sin permisos asignados (aunque no es recomendado)

// 1. CREAR EL ROL
$sql_crear_rol = "INSERT INTO roles (nombre_rol) VALUES (?)";
// Asumo que one_query retorna true/false y toma el SQL y los parámetros.
$ejecucion = $base->one_query($sql_crear_rol, $nombre);

if ($ejecucion === false) throw new Exception("Error al crear rol: " . $base->show_error_log());

// OBTENER EL ID DEL ROL RECIÉN CREADO
// **IMPORTANTE: Asumo que tu objeto $base tiene un método para obtener el último ID insertado.**
$rol_id = $base->lastInsertId(); 

// 2. ASIGNAR PERMISOS AL ROL (rol_permisos)
if (!empty($permisos_nombres)) {

// Obtener los IDs de los permisos seleccionados a partir de sus nombres
$permisos_placeholder = implode(',', array_fill(0, count($permisos_nombres), '?'));
$sql_obtener_ids = "SELECT id FROM permisos WHERE nombre IN ($permisos_placeholder)";

$permisos_ids_res = $base->one_query($sql_obtener_ids, $permisos_nombres);

if (empty($permisos_ids_res)) {
	throw new Exception("Error: No se pudieron obtener los IDs de los permisos seleccionados.");
}

// Preparar la inserción de permisos
$sql_asignar_permiso = "INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)";

foreach ($permisos_ids_res as $permiso_row) {
	$permiso_id = $permiso_row['id'];
	$ejecucion_permiso = $base->some_query($sql_asignar_permiso, [$rol_id, $permiso_id]);
	
	if ($ejecucion_permiso === false) {
		throw new Exception("Error al asignar permiso ID $permiso_id al rol $rol_id.");
	}
}
}

$_SESSION['success'] = "Rol '$nombre' creado y permisos asignados exitosamente.";
break;



case 'editar':
$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$permisos = $_POST['permisos'] ?? [];

if(!verify("editar_roles")){throw new Exception("Falta de Permisos necesarios");}
if ($id <= 0 || empty($nombre)) throw new Exception("Datos de edición incompletos.");

// 1. ACTUALIZAR EL NOMBRE DEL ROL
$sql_actualizar_rol = "UPDATE roles SET nombre_rol = ? WHERE rol_id = ?";
$ejecucion_rol = $base->some_query($sql_actualizar_rol,[$nombre, $id]);

if ($ejecucion_rol === false) throw new Exception("Error al editar rol: " . $base->show_error_log());

// 2. ELIMINAR TODOS LOS PERMISOS VIEJOS
$sql_eliminar_viejos = "DELETE FROM rol_permisos WHERE rol_id = ?";
$ejecucion_borrado = $base->some_query($sql_eliminar_viejos,[$id]);

if ($ejecucion_borrado === false) throw new Exception("Error al eliminar permisos viejos.");

foreach ($permisos as $permiso_ids) {
$ejecucion_permiso = $base->some_query("INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)",[$id,$permiso_ids]);
	if ($ejecucion_permiso === false) {
		throw new Exception("Error al reasignar permiso ID $permiso_ids al rol $id.");
	}
} 


$_SESSION['success'] = "Rol ID $id actualizado a '$nombre' y permisos reasignados.";
break;

case 'eliminar':
$id = intval($_POST['id'] ?? 0);
if(!verify("drop_roles")){throw new Exception("Falta de Permisos necesarios");}
if ($id <= 0) throw new Exception("ID de rol no válido para eliminar.");

// Nota: Antes de desactivar el rol, deberías desvincularlo de los usuarios,
// pero por simplicidad, solo desactivamos el rol.

// 1. ELIMINAR LOS PERMISOS DEL ROL (Necesario si la tabla rol_permisos no tiene ON DELETE CASCADE)
$sql_eliminar_permisos = "DELETE FROM rol_permisos WHERE rol_id = ?";
$base->one_query($sql_eliminar_permisos, $id); // No verificamos, si no hay, no pasa nada

// 2. DESACTIVAR EL ROL
$sql_desactivar_rol = "UPDATE roles SET activo = false WHERE rol_id = ?";
$ejecucion_desactivar = $base->one_query($sql_desactivar_rol, $id);

if ($ejecucion_desactivar === false) throw new Exception("Error al eliminar rol: " . $base->show_error_log());

$_SESSION['success'] = "Rol ID $id eliminado.";
break;

default:
throw new Exception("Acción no válida.");
}

//$base->commit(); // Confirmar la transacción

} catch (Exception $e) {
///$base->rollback(); // Revertir si hay error
$_SESSION['error'] = $e->getMessage();
}

// Redirección final
header("Location: ../../menu.php?pagina=roles");
exit();
?>
