<?php
session_start();
require_once '../../../config/connect.php'; 
$base = new MYSQL;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método no permitido.";
    header("Location: ../../menu.php?pagina=lotes");
    exit();
}

$accion = $_POST['accion'] ?? '';
$redirect_url = "../../menu.php?pagina=lotes";
$empleado_id = $_SESSION['user_cedula'] ?? 'SISTEMA'; // ✅ Corregido: user_cedula

// Fechas actuales
$d = date("d"); $m = date("m"); $y = date("Y"); $h = date("H"); $mi = date("i");

try {
    // ----------------------------------------------------------------------------------
    // 1. CREAR LOTE
    // ----------------------------------------------------------------------------------
    if ($accion === 'crear_lote') {
        
        $lote_sku = trim($_POST['numero_lote'] ?? '');
        $producto_sku = trim($_POST['productoSKU'] ?? '');
        $proveedor_id = trim($_POST['proveedorID'] ?? '');
        $cantidad_inicial = intval($_POST['cantidad_inicial'] ?? 0);
        $costo_unitario = floatval(str_replace(',', '.', $_POST['costo_unitario'] ?? 0));
        $moneda_compra_id = intval($_POST['moneda_compra_id'] ?? 0);
        
        // Fechas de Vencimiento
        $v_d = intval($_POST['fecha_vencimiento_d'] ?? 0);
        $v_m = intval($_POST['fecha_vencimiento_m'] ?? 0);
        $v_y = intval($_POST['fecha_vencimiento_y'] ?? 0);
        
        // Validación
        if (empty($lote_sku) || empty($producto_sku) || empty($proveedor_id) || $cantidad_inicial <= 0 || $costo_unitario <= 0 || $moneda_compra_id <= 0) {
            throw new Exception("Datos de lote incompletos: SKU, Producto, Proveedor, Cantidad, Costo y Moneda son obligatorios.");
        }

        // Manejo de fechas de vencimiento
        $vencimiento_d = ($v_d > 0 && $v_m > 0 && $v_y > 0) ? $v_d : null;
        $vencimiento_m = ($v_d > 0 && $v_m > 0 && $v_y > 0) ? $v_m : null;
        $vencimiento_y = ($v_d > 0 && $v_m > 0 && $v_y > 0) ? $v_y : null;

        $sql = "INSERT INTO lotes (lote_sku, producto_sku, proveedor_id, cantidad_inicial, costo_unitario, moneda_compra_id, fecha_entrada_d, fecha_entrada_m, fecha_entrada_y, fecha_entrada_H, fecha_entrada_mi, fecha_vencimiento_d, fecha_vencimiento_m, fecha_vencimiento_y, stock_actual) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $lote_sku, $producto_sku, $proveedor_id, $cantidad_inicial, $costo_unitario, $moneda_compra_id, 
            $d, $m, $y, $h, $mi, $vencimiento_d, $vencimiento_m, $vencimiento_y, $cantidad_inicial
        ];

        $result = $base->some_query($sql, $params);
        
        if ($result === false) { // ✅ Verificación correcta
            throw new Exception("Error al registrar el lote: " . $base->show_error_log());
        }

        // Actualizar stock del producto
        $sql_update_producto = "UPDATE productos SET stock_actual = stock_actual + ? WHERE sku = ?";
        $result_producto = $base->some_query($sql_update_producto, [$cantidad_inicial, $producto_sku]);

        if ($result_producto === false) {
            throw new Exception("Fallo al actualizar el stock del producto.");
        }

        $_SESSION['success'] = "Lote **$lote_sku** creado con $cantidad_inicial unidades. Stock ingresado correctamente.";
    }
    
    // ----------------------------------------------------------------------------------
    // 2. AJUSTE DE STOCK
    // ----------------------------------------------------------------------------------
    elseif ($accion === 'ajuste_stock') {
        
        $lote_sku = trim($_POST['lote_id'] ?? '');
        $nueva_cantidad = intval($_POST['nueva_cantidad'] ?? -1);
        $motivo = trim($_POST['motivo'] ?? '');
        
        if (empty($lote_sku) || $nueva_cantidad < 0 || empty($motivo)) {
            throw new Exception("Datos de ajuste incompletos o cantidad no válida.");
        }
        
        // Obtener Stock Anterior
        $sql_old = "SELECT stock_actual FROM lotes WHERE lote_sku = ?";
        $lote_data = $base->one_query($sql_old, $lote_sku);

        if (!$lote_data) {
            throw new Exception("Lote no encontrado.");
        }
        
        $stock_anterior = $lote_data['stock_actual'];
        $diferencia = $nueva_cantidad - $stock_anterior;
        $tipo_ajuste = ($diferencia > 0) ? 'ENTRADA (Ajuste)' : 'SALIDA (Ajuste)';
        
        // Actualizar Lote
        $sql = "UPDATE lotes SET stock_actual = ? WHERE lote_sku = ?";
        $result = $base->some_query($sql, [$nueva_cantidad, $lote_sku]);

        if ($result === false) {
            throw new Exception("Error al actualizar el stock del lote $lote_sku: " . $base->show_error_log());
        }
        
        // Registrar Auditoría
        $sql_auditoria = "INSERT INTO cambios_lotes (lote_id, empleado_id, fecha_cambioD, fecha_cambioM, fecha_cambioY, fecha_cambioH, fecha_cambioMI, tipo_cambio, campo_afectado, valor_anterior, valor_nuevo, razon) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $detalle_cambio = "AJUSTE MANUAL DE STOCK (Diferencia: " . ($diferencia > 0 ? '+' : '') . $diferencia . ")";

        $result_auditoria = $base->some_query($sql_auditoria, [
            $lote_sku, $empleado_id, $d, $m, $y, $h, $mi, $tipo_ajuste, 
            'stock_actual', $stock_anterior, $nueva_cantidad, $motivo
        ]);
            
        if ($result_auditoria === false) {
            throw new Exception("Error al registrar la auditoría de stock.");
        }

        $_SESSION['success'] = "Stock del lote **$lote_sku** ajustado de $stock_anterior a $nueva_cantidad. Diferencia: **" . ($diferencia > 0 ? '+' : '') . $diferencia . "**.";
    }
    
    // ----------------------------------------------------------------------------------
    // 3. EDICIÓN AVANZADA
    // ----------------------------------------------------------------------------------
    elseif ($accion === 'editar_lote_avanzado') {
        
        if ($_SESSION['user_rol'] !== 'Administrador' && $_SESSION['user_rol'] !== "programador") {
            throw new Exception("Permiso denegado: Solo Administradores o Programadores pueden usar la Edición Avanzada.");
        }
        
        $SKU = trim($_POST['lote_sku'] ?? '');
        $ProveedorID = trim($_POST['proveedor_id'] ?? '');
        $MonedaID = intval($_POST['moneda_compra_id'] ?? 0);
        $CostoUnitario = floatval(str_replace(',', '.', $_POST['costo_unitario'] ?? 0));
        $Razon = trim($_POST['motivo'] ?? '');
        $empleado_id = $_POST['empleado_id'] ?? $empleado_id;
        
        $Fecha1 = intval($_POST['fecha_vencimiento_d'] ?? 0);
        $Fecha2 = intval($_POST['fecha_vencimiento_m'] ?? 0);
        $Fecha3 = intval($_POST['fecha_vencimiento_y'] ?? 0);

        if (empty($SKU) || empty($Razon)) {
            throw new Exception("Lote SKU y Razón de Edición son obligatorios.");
        }
        
        // Obtener Valores Anteriores
        $sql_old = "SELECT proveedor_id, moneda_compra_id, costo_unitario, fecha_vencimiento_d, fecha_vencimiento_m, fecha_vencimiento_y FROM lotes WHERE lote_sku = ?";
        $lote_anterior = $base->one_query($sql_old, $SKU);

        if (!$lote_anterior) {
            throw new Exception("Lote no encontrado para la edición avanzada.");
        }
        
        // Construir UPDATE dinámico
        $updates = [];
        $params = [];
        $campos_afectados = "";
        $cambios_registrados = 0;
        
        // Proveedor ID
        if ($ProveedorID != $lote_anterior['proveedor_id']) {
            $updates[] = "proveedor_id = ?";
            $params[] = $ProveedorID;
            $campos_afectados .= "Proveedor, ";
            $cambios_registrados++;
            
            $sql_aud = "INSERT INTO cambios_lotes (lote_id, empleado_id, fecha_cambioD, fecha_cambioM, fecha_cambioY, fecha_cambioH, fecha_cambioMI, tipo_cambio, campo_afectado, valor_anterior, valor_nuevo, razon) VALUES (?, ?, ?, ?, ?, ?, ?, 'Edición Avanzada', 'proveedor_id', ?, ?, ?)";
            $base->some_query($sql_aud, [$SKU, $empleado_id, $d, $m, $y, $h, $mi, $lote_anterior['proveedor_id'], $ProveedorID, $Razon]);
        }

        // Moneda Compra ID
        if ($MonedaID != $lote_anterior['moneda_compra_id']) {
            $updates[] = "moneda_compra_id = ?";
            $params[] = $MonedaID;
            $campos_afectados .= "Moneda de Compra, ";
            $cambios_registrados++;
            
            $sql_aud = "INSERT INTO cambios_lotes (lote_id, empleado_id, fecha_cambioD, fecha_cambioM, fecha_cambioY, fecha_cambioH, fecha_cambioMI, tipo_cambio, campo_afectado, valor_anterior, valor_nuevo, razon) VALUES (?, ?, ?, ?, ?, ?, ?, 'Edición Avanzada', 'moneda_compra_id', ?, ?, ?)";
            $base->some_query($sql_aud, [$SKU, $empleado_id, $d, $m, $y, $h, $mi, $lote_anterior['moneda_compra_id'], $MonedaID, $Razon]);
        }

        // Costo Unitario
        if ($CostoUnitario != $lote_anterior['costo_unitario']) {
            $updates[] = "costo_unitario = ?";
            $params[] = $CostoUnitario;
            $campos_afectados .= "Costo Unitario, ";
            $cambios_registrados++;
            
            $sql_aud = "INSERT INTO cambios_lotes (lote_id, empleado_id, fecha_cambioD, fecha_cambioM, fecha_cambioY, fecha_cambioH, fecha_cambioMI, tipo_cambio, campo_afectado, valor_anterior, valor_nuevo, razon) VALUES (?, ?, ?, ?, ?, ?, ?, 'Edición Avanzada', 'costo_unitario', ?, ?, ?)";
            $base->some_query($sql_aud, [$SKU, $empleado_id, $d, $m, $y, $h, $mi, $lote_anterior['costo_unitario'], $CostoUnitario, $Razon]);
        }
        
        // Fechas de Vencimiento
        $nueva_v = [$Fecha1, $Fecha2, $Fecha3];
        $vieja_v = [
            $lote_anterior['fecha_vencimiento_d'] ?? 0, 
            $lote_anterior['fecha_vencimiento_m'] ?? 0, 
            $lote_anterior['fecha_vencimiento_y'] ?? 0
        ];

        if ($nueva_v != $vieja_v) {
            $updates[] = "fecha_vencimiento_d = ?, fecha_vencimiento_m = ?, fecha_vencimiento_y = ?";
            
            $p1 = ($Fecha1 > 0) ? $Fecha1 : null;
            $p2 = ($Fecha2 > 0) ? $Fecha2 : null;
            $p3 = ($Fecha3 > 0) ? $Fecha3 : null;
            
            $params[] = $p1; 
            $params[] = $p2; 
            $params[] = $p3;
            $campos_afectados .= "Fecha de Vencimiento, ";
            $cambios_registrados++;
            
            $valor_anterior_str = implode('/', array_filter($vieja_v, function($v) { return $v > 0; }));
            $valor_nuevo_str = implode('/', array_filter($nueva_v, function($v) { return $v > 0; }));

            $sql_aud = "INSERT INTO cambios_lotes (lote_id, empleado_id, fecha_cambioD, fecha_cambioM, fecha_cambioY, fecha_cambioH, fecha_cambioMI, tipo_cambio, campo_afectado, valor_anterior, valor_nuevo, razon) VALUES (?, ?, ?, ?, ?, ?, ?, 'Edición Avanzada', 'fecha_vencimiento', ?, ?, ?)";
            $base->some_query($sql_aud, [$SKU, $empleado_id, $d, $m, $y, $h, $mi, $valor_anterior_str, $valor_nuevo_str, $Razon]);
        }

        // Ejecutar UPDATE si hay cambios
        if ($cambios_registrados > 0) {
            $sql = "UPDATE lotes SET " . implode(", ", $updates) . " WHERE lote_sku = ?";
            $params[] = $SKU;
            
            $result = $base->some_query($sql, $params);
            
            if ($result === false) {
                throw new Exception("Error al actualizar el lote $SKU: " . $base->show_error_log());
            }
            
            $campos_afectados = rtrim($campos_afectados, ", ");
            $_SESSION['success'] = "Lote **$SKU** actualizado. Campos modificados: **$campos_afectados**.";
        } else {
            $_SESSION['warning'] = "Lote **$SKU** no actualizado: No se detectaron cambios en los campos enviados.";
        }
    }
    
    // ----------------------------------------------------------------------------------
    // 4. ELIMINAR/DESACTIVAR
    // ----------------------------------------------------------------------------------
    elseif($accion === 'desactivar') {
        $lote_sku_del = trim($_POST['lote_sku_eliminar'] ?? '');
        if (empty($lote_sku_del)) {
            throw new Exception("ID de lote para eliminar no válido.");
        }
        
        $sql = "UPDATE lotes SET activo = 0 WHERE lote_sku = ?";
        $result = $base->some_query($sql, [$lote_sku_del]);
        
        if ($result === false) {
            throw new Exception("Error al desactivar el lote $lote_sku_del: " . $base->show_error_log());
        }
        
        $_SESSION['success'] = "Lote **$lote_sku_del** desactivado correctamente.";
    }

    // ----------------------------------------------------------------------------------
    // 5. ACCIÓN NO VÁLIDA
    // ----------------------------------------------------------------------------------
    else {
        throw new Exception("Acción '$accion' no válida o no implementada.");
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error de Lote: " . $e->getMessage();
}

// Redirección final
header("Location: " . $redirect_url);
exit();
?>
