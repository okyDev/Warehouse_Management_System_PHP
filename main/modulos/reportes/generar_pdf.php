<?php
session_start();
ob_start(); 

require_once('../../../tcpdf/TCPDF-main/tcpdf.php'); 

// Ruta del logo (Asegúrate de que sea .jpg si no tienes GD/Imagick, o .png si está activo)
define('LOGO_PATH', "../../../assets/multimedia/logos/empresa.jpg");

// Parámetros del reporte
$tipo_reporte = $_GET['tipo_reporte'] ?? '';
$reporte_data = json_decode($_GET['data'], true);
$titulo_reporte = "REPORTE DE " . strtoupper($tipo_reporte);

if (empty($tipo_reporte) && empty($reporte_data)) {
    die('Tipo de reporte no especificado');
}

// =======================================================
// --- CLASE PERSONALIZADA PARA EL HEADER ---
// =======================================================
class MYPDF extends TCPDF {
    public function Header() {
        // 1. Logo
        if (file_exists(LOGO_PATH)) {
            // Image(ruta, x, y, ancho, alto, tipo, link, align, resize, dpi, palign, ismask, imgmask, border, fitbox)
            $this->Image(LOGO_PATH, 15, 10, 25, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // 2. Información de la Empresa (Posicionada a la derecha del logo)
        $this->SetFont('helvetica', 'B', 12);
        $this->SetXY(45, 10);
        $this->Cell(0, 5, $_SESSION['EmpresaN1'], 0, 1, 'L');
        
        $this->SetFont('helvetica', '', 9);
        $this->SetX(45);
        $this->Cell(0, 5, $_SESSION['EmpresaN5'], 0, 1, 'L');
        $this->SetX(45);
        $this->Cell(0, 5, 'Tel: '.$_SESSION['EmpresaN4'].' | Email: '.$_SESSION['EmpresaN3'], 0, 1, 'L');

        // 3. Título del Reporte (Centrado o a la derecha)
        $this->SetFont('helvetica', 'B', 14);
        $this->SetY(15);
        $this->Cell(0, 10, "REPORTE DE " . strtoupper($_GET['tipo_reporte']), 0, 1, 'R');

        // Línea decorativa
        $this->Line(15, 32, 282, 32); 
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

try {
    ob_clean();
    
    // Instanciar la clase personalizada (L = Landscape)
    $pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    
    $pdf->SetCreator('Sistema de Reportes');
    $pdf->SetAuthor($_SESSION['EmpresaN1']);
    $pdf->SetTitle($titulo_reporte);
    
    // Aumentamos el margen superior (35) para que quepa el Header
    $pdf->SetMargins(15, 35, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    $pdf->AddPage();
    
    // Información secundaria del reporte
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 5, 'Fecha de generación: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
    
    $filtro = $_GET['filtro'] ?? '';
    $filtro_desc = "Sin filtro";
    if (!empty($filtro)) {
        $filtro_desc = "Filtro Aplicado: " . $filtro;
    }

    $pdf->Cell(0, 5, $filtro_desc, 0, 1, 'L');
    $pdf->Cell(0, 5, 'Total de registros: ' . count($reporte_data), 0, 1, 'L');
    $pdf->Ln(5);
    
    // =======================================================
    // --- TU LÓGICA DE TABLAS (SIN CAMBIOS) ---
    // =======================================================
    $html = '<table border="1" cellpadding="4" style="font-size: 9px;">';
    $header_style = 'style="background-color: #f2f2f2; font-weight: bold;"';

    if ($tipo_reporte === 'Ventas') {
        $html .= "<tr $header_style>
                    <th width='15%'>Código Venta</th>
                    <th width='12%'>Fecha</th>
                    <th width='18%'>Empleado</th>
                    <th width='15%'>Cliente (Doc)</th>
                    <th width='12%'>Total</th>
                    <th width='12%'>Descuento</th>
                    <th width='16%'>Método Pago</th>
                  </tr>";
        
        foreach ($reporte_data as $fila) {
            $fecha = $fila['fecha_ventaD'] . '/' . $fila['fecha_ventaM'] . '/' . $fila['fecha_ventaY'];
            $empleado = htmlspecialchars($fila['nombre1'] . ' ' . $fila['apellido']);
            $cliente = $fila['cliente_documento'] ?: 'No especificado';
            $total = number_format($fila['total'], 2, ',', '.') . ' ' . $fila['moneda'];
            $descuento = number_format($fila['descuento'], 2, ',', '.');
            
            $html .= '<tr>
                        <td>' . htmlspecialchars($fila['codigo_venta']) . '</td>
                        <td>' . $fecha . '</td>
                        <td>' . $empleado . '</td>
                        <td>' . $cliente . '</td>
                        <td>' . $total . '</td>
                        <td>' . $descuento . '</td>
                        <td>' . htmlspecialchars($fila['metodo_pago']) . '</td>
                      </tr>';
        }
        
    } elseif ($tipo_reporte === 'Productos') {
        $html .= "<tr $header_style>
                    <th width='15%'>SKU</th>
                    <th width='30%'>Producto</th>
                    <th width='15%'>Marca</th>
                    <th width='15%'>Categoría ID</th>
                    <th width='15%'>Precio Venta</th>
                    <th width='10%'>Stock</th>
                  </tr>";
        
        foreach ($reporte_data as $fila) {
            $precio = number_format($fila['precio_venta'], 2, ',', '.') . ' ' . $fila['moneda'];
            $html .= '<tr>
                        <td>' . htmlspecialchars($fila['sku']) . '</td>
                        <td>' . htmlspecialchars($fila['nombre']) . '</td>
                        <td>' . htmlspecialchars($fila['marca']) . '</td>
                        <td>' . htmlspecialchars($fila['categoria_id']) . '</td>
                        <td>' . $precio . '</td>
                        <td>' . htmlspecialchars($fila['stock_actual']) . '</td>
                      </tr>';
        }
        
    } elseif ($tipo_reporte === 'Lotes') {
        $html .= "<tr $header_style>
                    <th width='15%'>Lote SKU</th>
                    <th width='20%'>Producto SKU</th>
                    <th width='15%'>Stock</th>
                    <th width='15%'>Costo Unitario</th>
                    <th width='15%'>Proveedor ID</th>
                    <th width='20%'>Fecha Ingreso</th>
                  </tr>";
        
        foreach ($reporte_data as $fila) {
            $fecha = $fila['fecha_entrada_d'] . '/' . $fila['fecha_entrada_m'] . '/' . $fila['fecha_entrada_y'];
            $costo = number_format($fila['costo_unitario'], 2, ',', '.') . ' ' . ($fila['moneda_compra_codigo'] ?? 'N/A');
            $html .= '<tr>
                        <td>' . htmlspecialchars($fila['lote_sku']) . '</td>
                        <td>' . htmlspecialchars($fila['producto_sku']) . '</td>
                        <td>' . htmlspecialchars($fila['stock_actual']) . '</td>
                        <td>' . $costo . '</td>
                        <td>' . htmlspecialchars($fila['proveedor_id']) . '</td>
                        <td>' . $fecha . '</td>
                      </tr>';
        }

    } elseif ($tipo_reporte === 'Stock') {
        $html .= "<tr $header_style>
                    <th width='30%'>Producto</th>
                    <th width='15%'>SKU</th>
                    <th width='15%'>Precio Venta</th>
                    <th width='15%'>Stock Actual</th>
                    <th width='15%'>Stock Mínimo</th>
                    <th width='10%'>Estado</th>
                  </tr>";
        
        foreach ($reporte_data as $fila) {
            $color_estado = '';
            if ($fila['estado_stock'] === 'AGOTADO') $color_estado = 'color: red; font-weight: bold;';
            if ($fila['estado_stock'] === 'BAJO') $color_estado = 'color: orange;';
            
            $html .= '<tr>
                        <td>' . htmlspecialchars($fila['nombre']) . '</td>
                        <td>' . htmlspecialchars($fila['sku']) . '</td>
                        <td>' . number_format($fila['precio_venta'], 2, ',', '.') . '</td>
                        <td>' . htmlspecialchars($fila['stock_actual']) . '</td>
                        <td>' . htmlspecialchars($fila['stock_minimo']) . '</td>
                        <td style="' . $color_estado . '">' . htmlspecialchars($fila['estado_stock']) . '</td>
                      </tr>';
        }
    }

    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Finalización
    ob_end_clean();
    $pdf->Output('reporte_' . strtolower($tipo_reporte) . '_' . date('Y-m-d') . '.pdf', 'D');

    // Nota: El retorno mediante header() no funcionará después de Output('D')
    // Se recomienda llamar a este script con target="_blank" desde el HTML original.
    exit;
    
} catch (Exception $e) {
    ob_clean();
    die('Error al generar PDF: ' . $e->getMessage());
}



/*    $reporte_data = [];
    $titulo_reporte = '';
    $params = [];
    $sql = '';

    // --- Conversión de Fechas ---
    // Convertir fechas a día, mes, año para las consultas LIKE
    $inicioD = date('d', strtotime($fecha_inicio));
    $inicioM = date('m', strtotime($fecha_inicio));
    $inicioY = date('Y', strtotime($fecha_inicio));

    $finD = date('d', strtotime($fecha_fin));
    $finM = date('m', strtotime($fecha_fin));
    $finY = date('Y', strtotime($fecha_fin));

    if (!empty($fechaunica)) {
        $diaU = date('d', strtotime($fechaunica));
        $mesU = date('m', strtotime($fechaunica));
        $yearU = date('Y', strtotime($fechaunica));
    }


    // =======================================================
    // --- LÓGICA DE FILTRADO (Implementando tu Base POST) ---
    // =======================================================

    if ($tipo_reporte === "Ventas") {
        $titulo_reporte = 'REPORTE DE VENTAS';
        $select_ventas = "SELECT V.*, E.nombre1, E.apellido, M.codigo as moneda 
                          FROM ventas V 
                          JOIN empleados_datos E ON V.cedula_empleado = E.cedula 
                          JOIN monedas M ON V.moneda_id = M.id 
                          WHERE V.activo = 1 ";

        if ($filtro === 'opcionf1' && !empty($fechaunica)) {
            // Filtro Fecha Única
            $sql = $select_ventas . "AND V.fecha_ventaY = ? AND V.fecha_ventaM = ? AND V.fecha_ventaD = ? ORDER BY V.fecha_ventaY DESC, V.fecha_ventaM DESC, V.fecha_ventaD DESC";
            $params = [$yearU, $mesU, $diaU];
        } elseif ($filtro === 'opcionf2') {
            // Filtro Rango de Fechas
            $sql = $select_ventas . "
                AND ((V.fecha_ventaY > ?) OR (V.fecha_ventaY = ? AND V.fecha_ventaM > ?) OR (V.fecha_ventaY = ? AND V.fecha_ventaM = ? AND V.fecha_ventaD >= ?))
                AND ((V.fecha_ventaY < ?) OR (V.fecha_ventaY = ? AND V.fecha_ventaM < ?) OR (V.fecha_ventaY = ? AND V.fecha_ventaM = ? AND V.fecha_ventaD <= ?))
                ORDER BY V.fecha_ventaY DESC, V.fecha_ventaM DESC, V.fecha_ventaD DESC";
            $params = [$inicioY, $inicioY, $inicioM, $inicioY, $inicioM, $inicioD, $finY, $finY, $finM, $finY, $finM, $finD];
        } elseif ($filtro === 'opcionf3' && !empty($dineroU) && !empty($monedaU)) {
            // Filtro Dinero Único
            $sql = $select_ventas . "AND V.total = ? AND V.moneda_id = ?";
            $params = [$dineroU, $monedaU];
        } elseif ($filtro === 'opcionf4' && !empty($monebegin) && !empty($moneend) && !empty($monebegincoin)) {
            // Filtro Rango de Dinero
            $sql = $select_ventas . "AND (V.total >= ?) AND (V.total <= ?) AND (V.moneda_id = ?) ORDER BY V.total DESC";
            $params = [$monebegin, $moneend, $monebegincoin];
        } elseif ($filtro === 'opcionf5' && !empty($coin)) {
            // Filtro por Moneda
            $sql = $select_ventas . "AND V.moneda_id = ?";
            $params = [$coin];
        } elseif ($filtro === 'opcionf6' && !empty($Empleado)) {
            // Filtro por Empleado (ID)
            $sql = $select_ventas . "AND V.cedula_empleado = ?";
            $params = [$Empleado];
        } elseif ($filtro === 'opcionf7' && !empty($CI)) {
            // Filtro por Documento (CI)
            $sql = $select_ventas . "AND V.cliente_documento = ?"; // Asumiendo que el documento del cliente se guarda en cliente_documento
            $params = [$CI];
        } elseif ($filtro === 'opcionf8' && !empty($ap)) {
            // Filtro por Apellido del Empleado
            $sql = "SELECT V.*, E.nombre1, E.apellido, M.codigo as moneda 
                    FROM ventas V 
                    JOIN empleados_datos E ON V.cedula_empleado = E.cedula 
                    JOIN monedas M ON V.moneda_id = M.id 
                    WHERE V.activo = 1 AND E.apellido LIKE ?";
            $params = ["%" . $ap . "%"]; // Uso LIKE para buscar el apellido
        } else {
            // Sin filtro específico (TODO)
            $sql = $select_ventas . "ORDER BY V.fecha_ventaY DESC, V.fecha_ventaM DESC, V.fecha_ventaD DESC";
        }
    } 
    
    // --- Lógica para Productos ---
    elseif ($tipo_reporte === 'Productos') {
        $titulo_reporte = 'REPORTE DE PRODUCTOS';
        $select_productos = "SELECT p.*, m.codigo as moneda 
                             FROM productos p 
                             JOIN monedas m ON p.moneda_venta_id = m.id 
                             WHERE p.activo = 1 ";

        if ($filtro === 'opcionf1' && !empty($fechaunica)) {
             // Filtro Fecha Única (asumiendo fecha_inY/M/D son los campos)
            $sql = $select_productos . "AND p.fecha_inY = ? AND p.fecha_inM = ? AND p.fecha_inD = ? ORDER BY p.fecha_inY DESC, p.fecha_inM DESC, p.fecha_inD DESC";
            $params = [$yearU, $mesU, $diaU];
        } elseif ($filtro === 'opcionf2') {
            // Filtro Rango de Fechas (fecha_inY/M/D)
            $sql = $select_productos . "
                AND ((p.fecha_inY > ?) OR (p.fecha_inY = ? AND p.fecha_inM > ?) OR (p.fecha_inY = ? AND p.fecha_inM = ? AND p.fecha_inD >= ?))
                AND ((p.fecha_inY < ?) OR (p.fecha_inY = ? AND p.fecha_inM < ?) OR (p.fecha_inY = ? AND p.fecha_inM = ? AND p.fecha_inD <= ?))
                ORDER BY p.fecha_inY DESC, p.fecha_inM DESC, p.fecha_inD DESC";
            $params = [$inicioY, $inicioY, $inicioM, $inicioY, $inicioM, $inicioD, $finY, $finY, $finM, $finY, $finM, $finD];
        } elseif ($filtro === 'opcionf3' && !empty($dineroU) && !empty($monedaU)) {
            // Filtro Precio Único
            $sql = $select_productos . "AND p.precio_venta = ? AND p.moneda_venta_id = ?";
            $params = [$dineroU, $monedaU];
        } elseif ($filtro === 'opcionf4' && !empty($monebegin) && !empty($moneend) && !empty($monebegincoin)) {
            // Filtro Rango de Precio
            $sql = $select_productos . "AND (p.precio_venta >= ?) AND (p.precio_venta <= ?) AND (p.moneda_venta_id = ?) ORDER BY p.precio_venta DESC";
            $params = [$monebegin, $moneend, $monebegincoin];
        } elseif ($filtro === 'opcionf5' && !empty($coin)) {
            // Filtro por Moneda de Venta
            $sql = $select_productos . "AND p.moneda_venta_id = ?";
            $params = [$coin];
        } else {
            // Sin filtro específico (TODO)
            $sql = $select_productos . "ORDER BY p.nombre ASC";
        }
    } 
    
    // --- Lógica para Lotes ---
    elseif ($tipo_reporte === 'Lotes') {
        $titulo_reporte = 'REPORTE DE LOTES';
        $select_lotes = "SELECT L.*, M.codigo AS moneda_compra_codigo
                         FROM lotes L 
                         JOIN monedas M ON L.moneda_compra_id = M.id 
                         WHERE L.activo = 1 ";

        if ($filtro === 'opcionf1' && !empty($fechaunica)) {
            // Filtro Fecha Única (fecha_entrada_d/m/y)
            $sql = $select_lotes . "AND L.fecha_entrada_y = ? AND L.fecha_entrada_m = ? AND L.fecha_entrada_d = ? ORDER BY L.fecha_entrada_y DESC, L.fecha_entrada_m DESC, L.fecha_entrada_d DESC";
            $params = [$yearU, $mesU, $diaU];
        } elseif ($filtro === 'opcionf2') {
            // Filtro Rango de Fechas (fecha_entrada_d/m/y)
            $sql = $select_lotes . "
                AND ((L.fecha_entrada_y > ?) OR (L.fecha_entrada_y = ? AND L.fecha_entrada_m > ?) OR (L.fecha_entrada_y = ? AND L.fecha_entrada_m = ? AND L.fecha_entrada_d >= ?))
                AND ((L.fecha_entrada_y < ?) OR (L.fecha_entrada_y = ? AND L.fecha_entrada_m < ?) OR (L.fecha_entrada_y = ? AND L.fecha_entrada_m = ? AND L.fecha_entrada_d <= ?))
                ORDER BY L.fecha_entrada_y DESC, L.fecha_entrada_m DESC, L.fecha_entrada_d DESC";
            $params = [$inicioY, $inicioY, $inicioM, $inicioY, $inicioM, $inicioD, $finY, $finY, $finM, $finY, $finM, $finD];
        } elseif ($filtro === 'opcionf3' && !empty($dineroU) && !empty($monedaU)) {
            // Filtro Costo Unitario Único
            $sql = $select_lotes . "AND L.costo_unitario = ? AND L.moneda_compra_id = ?";
            $params = [$dineroU, $monedaU];
        } elseif ($filtro === 'opcionf4' && !empty($monebegin) && !empty($moneend) && !empty($monebegincoin)) {
            // Filtro Rango de Costo Unitario
            $sql = $select_lotes . "AND (L.costo_unitario >= ?) AND (L.costo_unitario <= ?) AND (L.moneda_compra_id = ?) ORDER BY L.costo_unitario DESC";
            $params = [$monebegin, $moneend, $monebegincoin];
        } elseif ($filtro === 'opcionf5' && !empty($coin)) {
            // Filtro por Moneda de Compra
            $sql = $select_lotes . "AND L.moneda_compra_id = ?";
            $params = [$coin];
        } elseif ($filtro === 'opcionf6' && !empty($Empleado)) {
            // Filtro por ID de Proveedor (asumiendo proveedor_id = cedula de empleado)
            $sql = $select_lotes . "AND L.proveedor_id = ?";
            $params = [$Empleado];
        } elseif ($filtro === 'opcionf7' && !empty($CI)) {
            // Filtro por Documento/RIF del Proveedor (asumiendo que se busca por documento en la tabla proveedores)
            // Se requeriría JOIN a proveedores, simplificamos asumiendo que el ID del proveedor está en lotes
            $sql = $select_lotes . "AND L.proveedor_id IN (SELECT id FROM proveedores WHERE rif_empresa_proveedora = ?)";
            $params = [$CI];
        } else {
            // Sin filtro específico (TODO)
            $sql = $select_lotes . "ORDER BY L.fecha_entrada_y DESC, L.fecha_entrada_m DESC, L.fecha_entrada_d DESC";
        }
    } 
    
    // --- Lógica para Stock ---
    elseif ($tipo_reporte === 'Stock') {
        $titulo_reporte = 'REPORTE DE STOCK';
        // El reporte de Stock en tu base solo tiene una consulta general.
        // Los filtros (opcionf1-f8) no tienen lógica implementada en tu código base para este reporte.
        if (in_array($filtro, ['opcionf1', 'opcionf2', 'opcionf3', 'opcionf4', 'opcionf5', 'opcionf6', 'opcionf7', 'opcionf8'])) {
            $reporte_data = []; // No hay datos para filtros no implementados
        } else {
            $sql = "SELECT sku, nombre, precio_venta, stock_actual, stock_minimo,
                    CASE 
                        WHEN stock_actual = 0 THEN 'AGOTADO'
                        WHEN stock_actual <= stock_minimo THEN 'BAJO'
                        ELSE 'NORMAL'
                    END as estado_stock
                    FROM productos 
                    WHERE activo = 1
                    ORDER BY stock_actual ASC, nombre ASC";
        }
    }

    // --- Lógica para Usuarios ---
    elseif ($tipo_reporte === 'Usuarios') {
        $titulo_reporte = 'REPORTE DE USUARIOS';
        $select_usuarios = "SELECT U.*, E.nombre1, E.apellido 
                            FROM usuarios_acceso U 
                            JOIN empleados_datos E ON U.cedula_empleado = E.cedula 
                            WHERE U.activo = 1 ";

        if ($filtro === 'opcionf6' && !empty($Empleado)) {
            // Filtro por ID de Empleado
            $sql = $select_usuarios . "AND U.cedula_empleado = ?";
            $params = [$Empleado];
        } elseif ($filtro === 'opcionf7' && !empty($CI)) {
            // Filtro por Documento (CI)
            $sql = $select_usuarios . "AND U.cedula_empleado = ?";
            $params = [$CI];
        } elseif ($filtro === 'opcionf8' && !empty($ap)) {
            // Filtro por Apellido del Empleado
            $sql = $select_usuarios . "AND E.apellido = ?";
            $params = [$ap];
        } else {
            // Sin filtro específico (TODO)
            $sql = $select_usuarios . "ORDER BY E.apellido ASC";
        }
    }

    // --- Lógica para Categorías ---
    elseif ($tipo_reporte === 'Categorias') {
        $titulo_reporte = 'REPORTE DE CATEGORÍAS';
        // Solo tiene una consulta general en tu base
        if (in_array($filtro, ['opcionf1', 'opcionf2', 'opcionf3', 'opcionf4', 'opcionf5', 'opcionf6', 'opcionf7', 'opcionf8'])) {
            $reporte_data = [];
        } else {
            $sql = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre ASC";
        }
    }

    // --- Lógica para Empleados ---
    elseif ($tipo_reporte === 'Empleados') {
        $titulo_reporte = 'REPORTE DE EMPLEADOS';
        $select_empleados = "SELECT * FROM empleados_datos WHERE activo = 1 ";
        
        if ($filtro === 'opcionf6' && !empty($Empleado)) {
            // Filtro por ID de Empleado
            $sql = $select_empleados . "AND cedula = ?";
            $params = [$Empleado];
        } elseif ($filtro === 'opcionf7' && !empty($CI)) {
            // Filtro por Documento (CI)
            $sql = $select_empleados . "AND cedula = ?";
            $params = [$CI];
        } elseif ($filtro === 'opcionf8' && !empty($ap)) {
            // Filtro por Apellido
            $sql = $select_empleados . "AND apellido = ?";
            $params = [$ap];
        } else {
            // Sin filtro específico (TODO)
            $sql = $select_empleados . "ORDER BY apellido ASC";
        }
    }

    // --- Lógica para Proveedores ---
    elseif ($tipo_reporte === 'Proveedores') {
        $titulo_reporte = 'REPORTE DE PROVEEDORES';
        $select_proveedores = "SELECT * FROM proveedores WHERE activo = 1 ";
        
        if ($filtro === 'opcionf7' && !empty($CI)) {
            // Filtro por Documento/RIF
            $sql = $select_proveedores . "AND (id = ? OR rif_empresa_proveedora = ?)";
            $params = [$CI, $CI];
        } else {
            // Sin filtro específico (TODO)
            $sql = $select_proveedores . "ORDER BY nombre_empresa_proveedora ASC";
        }
    }


    // --- Ejecución de Consulta ---
    if (!empty($sql)) {
        if (!empty($params)) {
            // Si hay parámetros, usar some_query (asumo que maneja prepared statements)
            $reporte_data = $base->some_query($sql, $params);
        } else {
            // Si no hay parámetros, usar query_all
            $reporte_data = $base->query_all($sql);
        }
        
        if ($reporte_data === false) {
            // Manejo de error de la base de datos
            $reporte_data = [];
            error_log("Error de BD en $tipo_reporte con filtro $filtro: " . ($base->show_error_log() ?? 'Error desconocido'));
        }
    }
    
    // Verificar si hay datos
    if(empty($reporte_data)) {
        ob_clean();
        die('No hay datos para generar el reporte PDF con el filtro seleccionado.');
    }
    
*/


?>
