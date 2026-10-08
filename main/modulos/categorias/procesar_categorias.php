<?php
// modulos/categorias/procesar_categoria.php
session_start();
require_once '../../../config/connect.php';
$base = new MYSQL;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método no permitido";
    header("Location: ../../menu.php?pagina=categorias");
    exit(); 
}

$accion = $_POST['accion'] ?? '';

try {
	///salirSESSION();
    switch($accion) {
        case 'crear':
            $nombre = trim($_POST['nombre'] ?? '');

            if(empty($nombre)) {
                throw new Exception("El nombre es obligatorio");
            }

            // Verificar si ya existe
            $sql_check = "SELECT id FROM categorias WHERE nombre = ? AND activo = 1";
            $result_check = $base->one_query($sql_check,$nombre);
            
            if ($result_check !== false) {
                throw new Exception("¡Esa categoría ya existe!");
            }
            
            $sql = "INSERT INTO categorias (nombre) VALUES (?)";
            $result = $base->one_query($sql,$nombre);
            
            if ($result !== false or $result !== null) {
                $_SESSION['success'] = "Categoría <strong>$nombre</strong> creada!";
            } 
            
            else {
                throw new Exception("Error al crear categoría: " . $base->show_error_log());
                error_log("ERROR_EN_CRE_CAT: ".$base->show_error_log());
            }
            ///$stmt->close();
            break;
            
        case 'editar':
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');

            if($id === 0 || empty($nombre)) {
                throw new Exception("Datos incompletos para editar");
            }

            $sql = "UPDATE categorias SET nombre = ? WHERE id = ?";
            $result=$base->some_query($sql,[$nombre,$id]);
            
            if ($result !== false) {
                $_SESSION['success'] = "Categoría actualizada!";
            } else {
                throw new Exception("Error al actualizar: " . $base->show_error_log());
            }
            
            break;
            
        case 'eliminar':
            $id = intval($_POST['id'] ?? 0);
            
            if($id === 0) {
                throw new Exception("ID no válido para eliminar");
            }
            $result = $base->one_query("UPDATE categorias SET activo = FALSE WHERE id = ?",$id);
            
            if ($result !== false) {
                $_SESSION['success'] = "Categoría eliminada!";
            } else {
                throw new Exception("Error al eliminar: " . $base->show_error_log());
            }
            ///$stmt->close();
            break;
            
        default:
            throw new Exception("Acción no válida");
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../../menu.php?pagina=categorias");
exit();
?>
