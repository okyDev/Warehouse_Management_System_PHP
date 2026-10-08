<?php
// modulos/proveedores/procesar_proveedor.php
session_start();
require_once '../../../config/connect.php';

// Inicializar conexión PDO
$base = new MYSQL;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Asegúrese de mantenerse en la página";
    header("Location: ../../menu.php?pagina=proveedores");
    exit();
}

$accion = $_POST['accion'] ?? '';
$categorias = $_POST['categorias'] ?? [];

try {
    // Validar categorías solo si la acción es crear o editar
    if (($accion === 'crear' || $accion === 'editar') && (empty($categorias) || !is_array($categorias))) {
        throw new Exception("Debe seleccionar al menos una categoría para el proveedor.");
    }
    
    switch($accion) {
        // ==========================================
        // CREAR PROVEEDOR
        // ==========================================
        case 'crear':
            $id = trim($_POST['id'] ?? '');
            $id_tipo = intval($_POST['id_tipo'] ?? 0);
            $nombre_contacto = trim($_POST['nombre_contacto'] ?? '');
            $empresa_proveedora = trim($_POST['empresa_proveedora'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $rifE = trim($_POST['rifE'] ?? '');

            // Validaciones
            if(empty($id) || empty($nombre_contacto) || empty($email)) {
                throw new Exception("ID, Contacto y Email son obligatorios");
            }

            if($id_tipo === 0) {
                throw new Exception("Tipo de ID no válido");
            }

            // Verificar si ya existe usando PDO
            $sql_check = "SELECT id FROM proveedores WHERE id = ?";
            $result_check = $base->one_query($sql_check, $id);
            
            if ($result_check !== false) {
                throw new Exception("El proveedor con ID $id ya existe");
            }

            // Insertar proveedor usando PDO
            $sql = "INSERT INTO proveedores (id, id_tipo, nombre_contacto, empresa_proveedora, email, telefono, descripcion, rif_empresa_proveedora) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [$id, $id_tipo, $nombre_contacto, $empresa_proveedora, $email, $telefono, $descripcion, $rifE];
            $result = $base->some_query($sql, $params);
            
            if ($result === false) {
                throw new Exception("Error al crear proveedor: " . $base->show_error_log());
            }
            
            // Insertar Relaciones N:M en proveedor_categoria usando PDO
            $sql_relacion = "INSERT INTO proveedor_categoria (proveedor_id, categoria_id) VALUES (?, ?)";
            
            foreach ($categorias as $cat_id) {
                $cat_id_int = intval($cat_id); 
                if ($cat_id_int > 0) {
                    $result_relacion = $base->some_query($sql_relacion, [$id, $cat_id_int]);
                    if ($result_relacion === false) {
                        throw new Exception("Error al asignar categoría ID: $cat_id_int");
                    }
                }
            }
            
            $_SESSION['success'] = "Proveedor <strong>$id</strong> creado exitosamente con " . count($categorias) . " categorías asignadas.";
            break;

        // ==========================================
        // EDITAR PROVEEDOR
        // ==========================================
        case 'editar':
            $id = trim($_POST['id'] ?? '');
            $id_tipo = intval($_POST['id_tipo'] ?? 0);
            $nombre_contacto = trim($_POST['nombre_contacto'] ?? '');
            $empresa_proveedora = trim($_POST['empresa_proveedora'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $rifE = trim($_POST['rifE'] ?? '');

            if(empty($id) || empty($email) || empty($nombre_contacto)) {
                throw new Exception("ID, Contacto y Email son obligatorios");
            }

            // Actualizar datos del proveedor usando PDO
            $sql = "UPDATE proveedores SET 
                    id_tipo = ?, 
                    nombre_contacto = ?, 
                    empresa_proveedora = ?, 
                    email = ?, 
                    telefono = ?, 
                    descripcion = ?,
                    rif_empresa_proveedora = ?
                    WHERE id = ?";
            
            $params = [$id_tipo, $nombre_contacto, $empresa_proveedora, $email, $telefono, $descripcion, $rifE, $id];
            $result = $base->some_query($sql, $params);
            
            if ($result === false) {
                throw new Exception("Error al actualizar proveedor: " . $base->show_error_log());
            }

            // Eliminar todas las relaciones existentes para este proveedor
            $sql_delete_rel = "DELETE FROM proveedor_categoria WHERE proveedor_id = ?";
            $result_delete = $base->some_query($sql_delete_rel, [$id]);
            
            if ($result_delete === false) {
                throw new Exception("Error al eliminar categorías anteriores");
            }

            // Re-insertar las categorías seleccionadas
            $sql_relacion = "INSERT INTO proveedor_categoria (proveedor_id, categoria_id) VALUES (?, ?)";
            
            foreach ($categorias as $cat_id) {
                $cat_id_int = intval($cat_id);
                if ($cat_id_int > 0) {
                    $result_relacion = $base->some_query($sql_relacion, [$id, $cat_id_int]);
                    if ($result_relacion === false) {
                        throw new Exception("Error al asignar categoría ID: $cat_id_int");
                    }
                }
            }

            $_SESSION['success'] = "Proveedor <strong>$id</strong> actualizado, incluyendo " . count($categorias) . " categorías.";
            break;
            
        // ==========================================
        // ELIMINAR PROVEEDOR (DESACTIVAR)
        // ==========================================
        case 'eliminar':
            $id = trim($_POST['id'] ?? '');
            
            if(empty($id)) {
                throw new Exception("ID no válido para eliminar");
            }

            // Actualizar estado usando PDO
            $sql = "UPDATE proveedores SET activo = 0 WHERE id = ?";
            $result = $base->some_query($sql, [$id]);
            
            if ($result === false) {
                throw new Exception("Error al eliminar proveedor: " . $base->show_error_log());
            }
            
            $_SESSION['success'] = "Proveedor <strong>$id</strong> desactivado correctamente.";
            break;
            
        // ==========================================
        // ACCIÓN NO VÁLIDA
        // ==========================================
        default:
            throw new Exception("Acción '$accion' no válida");
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../../menu.php?pagina=proveedores");
exit();
?>
