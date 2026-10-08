<?php
// procesar_proveedor.php
session_start();
require_once '../../../config/connect.php'; 

// Inicializar conexión PDO
$base = new MYSQL;

// === 1. VERIFICACIÓN INICIAL ===
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método no permitido";
    header("Location: ../../menu.php?pagina=monedas");
    exit(); 
}

try {

if(isset($_POST['accion'])) {

if($_POST['accion'] === 'editar'){
            $id = $_POST['idEDIT'] ?? '';
            $codigo = $_POST['codigo'] ?? '';
            $simbolo = $_POST['simbolo'] ?? '';
            $tasa_cambio = $_POST['tasa_cambio'] ?? 1; 

            // Validar que tenemos ID
            if(empty($id)) {
                throw new Exception("ID de moneda no proporcionado");
            }

            // Construir consulta dinámica
            $sql = "UPDATE monedas SET ";
            $params = [];
            $updates = [];

            // Campos a actualizar
            if (!empty($codigo)) {
                $updates[] = "codigo = ?";
                $params[] = $codigo;
            }

            if (!empty($simbolo)) {
                $updates[] = "simbolo = ?";
                $params[] = $simbolo;
            }

            if (isset($tasa_cambio) && $tasa_cambio != '') {
                $updates[] = "tasa_cambio = ?";
                $params[] = floatval($tasa_cambio);
            }

            // Si no hay campos para actualizar
            if (empty($updates)) {
                throw new Exception("No hay campos para actualizar");
            }
$updates = 'SET fecha_updateD = ?,SET fecha_updateM = ?, SET fecha_updateY = ? ';
            $sql .= $updates ." WHERE id = ?";
            $params[] = date('d');
             $params[] = date('m');
              $params[] = date('Y');
            $params[] = $id;

            // Ejecutar con PDO
            $result = $base->some_query($sql, $params);
            
            if ($result === false) {
                throw new Exception("Error al actualizar: " . $base->show_error_log());
            }
            
            $_SESSION['success'] = "Moneda actualizada correctamente";
            
        } 
        
else if($_POST['accion'] === 'crear'){
            $codigo = $_POST['codigo'] ?? '';
            $simbolo = $_POST['simbolo'] ?? '';
            $tasa_cambio = $_POST['tasa_cambio'] ?? 1;

            // Validaciones básicas
            if(empty($codigo)) {
                throw new Exception("El código de moneda es obligatorio");
            }

            // Verificar si ya existe usando PDO
            $sql_check = "SELECT codigo FROM monedas WHERE codigo = ?";
            $result_check = $base->one_query($sql_check, $codigo);
            
            if ($result_check !== false) {
                throw new Exception("La moneda '$codigo' ya existe!");
            }

            // Insertar nueva moneda usando PDO
            $sql = "INSERT INTO monedas (codigo, simbolo, tasa_cambio, fechad,fecham,fechay,fecha_updateD,fecha_updateM,fecha_updateY) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$codigo, $simbolo, floatval($tasa_cambio),date('d'),date('m'),date('Y'),date('d'),date('m'),date('Y')];
            
            $result = $base->some_query($sql, $params);
            
            if ($result === false) {
                throw new Exception("Error al crear moneda: " . $base->show_error_log());
            }
            
            $_SESSION['success'] = "Moneda <strong>$codigo</strong> creada correctamente";
            
        } else {
            throw new Exception("Acción no válida: " . $_POST['accion']);
        }
    } 
    

else {
        throw new Exception("No se recibieron datos de acción");
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

// 4. REDIRECCIÓN FINAL
header("Location: ../../menu.php?pagina=monedas");
exit();
?>
