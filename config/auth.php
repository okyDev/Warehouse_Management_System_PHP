<?php
require_once('connect.php');

function verify($accion) {
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar si hay sesión activa
    if (!isset($_SESSION['user_rol'], $_SESSION['user_cedula'])) {
        return false;
    }
    
    $n = false;
    $idrol = $_SESSION['user_rol'];
    $userCD = (string)$_SESSION['user_cedula'];
    
    // Crear instancia de MYSQL
    $wase234 = new MYSQL();
    
    // Verificar conexión
    if (!$wase234->conexionActiva()) {
        error_log("Error verificando conexion (auth): No se pudo conectar a la base de datos en verify()");
        return false;
    }
    
    
    if($_SESSION['is_above'] === true){
		return true;
	}
    try {
        // Obtener ID del permiso
        $permiso = $wase234->one_query("SELECT id FROM permisos WHERE nombre = ?",$accion);
        
        if (!$permiso || !isset($permiso['id'])) {
            return false; // Permiso no existe
        }
        
        // Obtener ID del usuario
        $userid = $wase234->one_query("SELECT id FROM usuarios_acceso WHERE cedula_empleado = ?", $userCD);
        
        if (!$userid || !isset($userid['id'])) {
            return false; // Usuario no existe
        }
        
        $ni = (string)$userid["id"];
        $permiso_id = $permiso['id'];
        
        // Verificar permisos específicos del usuario
        $permisosU = $wase234->one_query_all("SELECT permiso_id FROM usuario_permisos WHERE usuario_id = ?", $ni);
        
        if (!empty($permisosU)) {
            foreach($permisosU as $perm_usuario) {
                if ($permiso_id == $perm_usuario['permiso_id']) {
                    $n = true;
                    break;
                }
            }
        }
        
        // Si no tiene permisos específicos, verificar permisos del rol
        if (!$n) {
            $rol_permisos = $wase234->one_query_all("SELECT permiso_id FROM rol_permisos WHERE rol_id = ?", $idrol);
            
            if (!empty($rol_permisos)) {
                foreach($rol_permisos as $perm_rol) {
                    if ($perm_rol['permiso_id'] == $permiso_id) {
                        $n = true;
                        break;
                    }
                }
            }
        }
        
        return $n;
        
    } catch(Exception $e) {
        error_log("Error en verify(): " . $e->getMessage());
        return false;
    }
}

// Función adicional para verificar múltiples permisos a la vez
function verifyMultiple($acciones) {
    if (!is_array($acciones)) {
        return verify($acciones);
    }
    
    foreach ($acciones as $accion) {
        if (!verify($accion)) {
            return false;
        }
    }
    
    return true;
}

// Función para obtener todos los permisos del usuario (para cache)
function getUserPermissions() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_rol'], $_SESSION['user_cedula'])) {
        return [];
    }
    
    $idrol = $_SESSION['user_rol'];
    $userCD = $_SESSION['user_cedula'];
    $permisos = [];
    
    $db = new MYSQL();
    
    try {
        // Obtener ID del usuario
        $userid = $db->one_query("SELECT id FROM usuarios_acceso WHERE cedula_empleado = ?", $userCD);
        
        if (!$userid) {
            return $permisos;
        }
        
        $ni = (string)$userid["id"];
        
        // Permisos específicos del usuario
        $permisosU = $db->some_query(
            "SELECT p.nombre FROM usuario_permisos up 
             JOIN permisos p ON up.permiso_id = p.id 
             WHERE up.usuario_id = ?",
            [$ni]
        );
        
        // Permisos del rol
        $permisosRol = $db->some_query(
            "SELECT p.nombre FROM rol_permisos rp 
             JOIN permisos p ON rp.permiso_id = p.id 
             WHERE rp.rol_id = ?",
            [$idrol]
        );
        
        // Combinar todos los permisos
        foreach($permisosU as $perm) {
            $permisos[] = $perm['nombre'];
        }
        
        foreach($permisosRol as $perm) {
            $permisos[] = $perm['nombre'];
        }
        
        // Eliminar duplicados y retornar
        return array_unique($permisos);
        
    } catch(Exception $e) {
        error_log("Error en getUserPermissions: " . $e->getMessage());
        return [];
    }
}
?>
