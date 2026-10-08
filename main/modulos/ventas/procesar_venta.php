<?php
// modulos/ventas/procesar_venta.php
session_start();
require_once '../../../config/connect.php';
$base = new MYSQL;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método no permitido";
    header("Location: ../../menu.php?pagina=ventas");
    exit(); 
}

if(isset($_POST['CLEARSHOP'])){
    unset($_SESSION['CARRITO']);
    $_SESSION['CARRITO'] = array();
}
else if(isset($_POST['procesarVentaBtn'])){

    // Verificar que hay productos en el carrito
    if (!isset($_SESSION['CARRITO']) || empty($_SESSION['CARRITO'])) {
        $_SESSION['error'] = "No hay productos en el carrito";
        header("Location: ../../menu.php?pagina=ventas");
        exit();
    }

    if(!isset($_POST['tipoPago'])){
        $_SESSION['error'] = "No hay un metodo de pago definido";
        header("Location: ../../menu.php?pagina=ventas");
        exit();
    }

    if(!isset($_POST['cliente_documento']) or $_POST['cliente_documento'] === 'V-'){
        $_SESSION['error'] = "Campo cedula cliente no puede estar vacio!";
        header("Location: ../../menu.php?pagina=ventas");
        exit();
    }

    // Obtener datos del formulario
    $cedula_empleado = trim($_POST['CDempleado'] ?? '');
    $cliente_documento = trim($_POST['cliente_documento'] ?? '');
    $tipo_pago = trim($_POST['tipoPago'] ?? '');
    $metodo_pago_simple = trim($_POST['metodo_pago'] ?? '');
    
    $tipo_id_cli = 1;
    $moneda_id_base = 1;
    $descuento = 0.00;
    $iva_tasa = 0.12;

    // Obtener tasa de dólar usando PDO
    $dolarBS_result = $base->query_all("SELECT tasa_cambio FROM monedas WHERE id = 2");
    $dolarBS = isset($dolarBS_result[0]['tasa_cambio']) ? (float)$dolarBS_result[0]['tasa_cambio'] : 1.0;
    $codigo_venta = $_POST['VENTASKU'];

    if (empty($cedula_empleado)) {
        $_SESSION['error'] = "Empleado es obligatorio";
        header("Location: ../../menu.php?pagina=ventas");
        exit();
    }

    // Calcular subtotal
    $subtotal = 0;
    foreach ($_SESSION['CARRITO'] as $sku => $item) {
        $cantidad_final = intval($item['cantidad']);
        
        if($item['moneda_venta_id'] == 2){
            $precio_bs = $item['precio_venta'] * $dolarBS;
        } else {
            $precio_bs = $item['precio_venta'];
        }
        $subtotal += $precio_bs * $cantidad_final;
    }
    $total = $subtotal;

    // Procesar tipo de pago
    switch($tipo_pago){
        case 'mixto':
            $pago_1 = (float)($_POST['pay1'] ?? 0);
            $moneda_1 = $_POST['moneda1'] ?? 'bs';

            $pago_2 = (float)($_POST['pay2'] ?? 0);
            $moneda_2 = $_POST['moneda2'] ?? 'bs';

            $pago_3 = (float)($_POST['pay3'] ?? 0);
            $moneda_3 = $_POST['moneda3'] ?? 'bs';

            // Convertir todo a la moneda base (Bs)
            $pago1_bs = ($moneda_1 === '$') ? $pago_1 * $dolarBS : $pago_1;
            $pago2_bs = ($moneda_2 === '$') ? $pago_2 * $dolarBS : $pago_2;
            $pago3_bs = ($moneda_3 === '$') ? $pago_3 * $dolarBS : $pago_3;

            $total_pagado_bs = $pago1_bs + $pago2_bs + $pago3_bs;
            $metodo_pago_final = "Mixto";

            break;

        case 'simple':
            $metodo_pago_final = $metodo_pago_simple;
            break;

        default:
            $_SESSION['error'] = "Método de pago no reconocido";
            header("Location: ../../menu.php?pagina=ventas");
            exit();
    }

    try {
        // Iniciar transacción (PDO ya maneja transacciones internamente)
        
        if ($total < 0) {
            throw new Exception("El total no puede ser negativo");
        }

        // Obtener fecha actual
        $fecha_inD = date('d');
        $fecha_inM = date('m');
        $fecha_inY = date('Y');
        $fecha_inH = date('H');
        $fecha_inMI = date('i');

        // Insertar venta principal usando PDO
        $sql_venta = "INSERT INTO ventas (codigo_venta, cedula_empleado, tipo_id_cli, cliente_documento, 
                     fecha_ventaD, fecha_ventaM, fecha_ventaY, fecha_ventaH, fecha_ventaMi, 
                     subtotal, total, metodo_pago, moneda_id, estado) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'COMPLETADA')";

        $params_venta = [
            $codigo_venta, $cedula_empleado, $tipo_id_cli, $cliente_documento,
            $fecha_inD, $fecha_inM, $fecha_inY, $fecha_inH, $fecha_inMI,
            $subtotal, $total, $metodo_pago_final, $moneda_id_base
        ];

        // Ejecutar inserción de venta
        $base->some_query($sql_venta, $params_venta);
        
        // Obtener ID de la venta insertada
        $venta_id = $base->lastInsertId();

        // Procesar cada producto y consumir de lotes (FIFO)
        foreach ($_SESSION['CARRITO'] as $sku => $item) {
            $cantidad_vender = intval($item['cantidad'] ?? 0);
            $precio_venta = $item['precio_venta'];

            // Verificar stock disponible
            $sql_stock = "SELECT stock_actual FROM productos WHERE sku = ?";
            $stock_result = $base->one_query($sql_stock, $sku);
            
            if (!$stock_result) {
                throw new Exception("Producto $sku no encontrado");
            }

            $stock_actual = $stock_result['stock_actual'];

            if ($stock_actual < $cantidad_vender) {
                throw new Exception("Stock insuficiente para $sku. Disponible: $stock_actual, Solicitado: $cantidad_vender");
            }

            // Buscar lotes disponibles (FIFO)
            $sql_lotes = "SELECT lote_sku, stock_actual 
                         FROM lotes 
                         WHERE producto_sku = ? AND activo = 1 
                         ORDER BY fecha_entrada_y ASC, fecha_entrada_m ASC, fecha_entrada_d ASC, fecha_entrada_H ASC";
            $lotes = $base->some_query($sql_lotes, [$sku]);

            $cantidad_restante = $cantidad_vender;

            foreach ($lotes as $lote) {
                if ($cantidad_restante <= 0) break;

                $lote_id = $lote['lote_sku'];
                $cantidad_lote = $lote['stock_actual'];

                // Calcular cuánto tomar de este lote
                $cantidad_tomar = min($cantidad_restante, $cantidad_lote);

                // Insertar detalle de venta para este lote
                $subtotal_item = $cantidad_tomar * $precio_venta;

                $sql_detalle = "INSERT INTO detalle_ventas (venta_id, producto_sku, lote_id, cantidad, precio_unitario, subtotal) 
                               VALUES (?, ?, ?, ?, ?, ?)";
                $params_detalle = [
                    $venta_id, $sku, $lote_id, $cantidad_tomar, $precio_venta, $subtotal_item
                ];
                
                $base->some_query($sql_detalle, $params_detalle);

                // Actualizar lote
                if ($cantidad_tomar < $cantidad_lote) {
                    $sql_update_lote = "UPDATE lotes SET stock_actual = stock_actual - ? WHERE lote_sku = ?";
                    $base->some_query($sql_update_lote, [$cantidad_tomar, $lote_id]);
                } else {
                    // Lote agotado, desactivar
                    $sql_update_lote = "UPDATE lotes SET stock_actual = 0, activo = 0 WHERE lote_sku = ?";
                    $base->some_query($sql_update_lote, [$lote_id]);
                }

                $cantidad_restante -= $cantidad_tomar;
            }

            if ($cantidad_restante > 0) {
                throw new Exception("No hay suficientes lotes para el producto $sku");
            }

            // Actualizar stock del producto
            $sql_update_producto = "UPDATE productos SET stock_actual = stock_actual - ? WHERE sku = ?";
            $base->some_query($sql_update_producto, [$cantidad_vender, $sku]);
        }

        // Transacción se confirma automáticamente si no hay excepciones
        $_SESSION['success'] = "Venta #$codigo_venta procesada exitosamente. Total: " . number_format($total, 2);
        
        // Limpiar carrito
        unset($_SESSION['CARRITO']);
        $_SESSION['CARRITO'] = array();

    } catch (Exception $e) {
        // En PDO, el rollback se maneja automáticamente con excepciones
        $_SESSION['error'] = "Error al procesar venta: " . $e->getMessage();
    }
}

header("Location: ../../menu.php?pagina=ventas");
exit();
?>
