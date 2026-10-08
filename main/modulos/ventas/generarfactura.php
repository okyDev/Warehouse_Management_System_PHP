<?php
session_start();
ob_start();
// generar_factura.php
// ESTO DEBE SER LO PRIMERO - SIN ESPACIOS ANTES
// Desactivar output buffering y limpiar buffer
if (ob_get_level()) ob_end_clean();

require_once '../../../config/connect.php';
require_once '../../../tcpdf/TCPDF-main/tcpdf.php';


$base = new MYSQL;

define('LOGO_PATH', "../../../assets/multimedia/logos/empresa.jpg");


// --- 1. OBTENER Y VALIDAR ID DE VENTA ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: ID de venta no proporcionado o inválido.");
}
set_time_limit(0); // 0 significa que el script puede ejecutarse indefinidamente
$venta_id = (int)$_GET['id'];

// --- 2. CONSULTAR DATOS DE VENTA (CORREGIDO) ---
$sql_venta = "
    SELECT v.*, e.nombre1 AS nombre_empleado, m.simbolo AS simbolo_moneda,
           t.codigo as tipo_cliente_codigo
    FROM ventas v 
    JOIN empleados_datos e ON v.cedula_empleado = e.cedula  
    JOIN monedas m ON v.moneda_id = m.id
    JOIN tipos_identificacion t ON v.tipo_id_cli = t.id
    WHERE v.id_venta = ? AND v.activo = 1
";

$stmt = $base->one_query($sql_venta,$venta_id);
if (!$stmt) {
    die("Error preparando consulta: " . $base->error);
}

$venta = $stmt;

if (!$venta) {
    die("Error: Venta no encontrada o inactiva.");
}

// --- 3. CONSULTAR DETALLES (CORREGIDO) ---
$sql_detalles = "
    SELECT 
        dv.*, 
        p.nombre AS nombre_producto,
        p.descripcion
    FROM detalle_ventas dv
    JOIN productos p ON dv.producto_sku = p.sku
    WHERE dv.venta_id = ? AND dv.activo = 1
";

$stmt_detalles = $base->one_query_all($sql_detalles,$venta_id);
if (!$stmt_detalles) {
    die("Error preparando detalles: " . $base->error);
}
$detalles = $stmt_detalles;

if (empty($detalles)) {
    die("Error: No se encontraron detalles para esta venta.");
}

// --- 4. GENERAR PDF ---
class PDF extends TCPDF {
 public function Header() {
        // 1. EL LOGO: Lo ponemos en posición fija (x:10, y:10)
        // El parámetro 'false' en el penúltimo lugar evita que el cursor se mueva automáticamente
        if (file_exists(LOGO_PATH)) {
            $this->Image(LOGO_PATH, 10, 10, 30, '', 'JPG', '', 'T', false, 500, '', false, false, 0, false, false, false);
        }

        // 2. EL TEXTO: Usamos SetXY para escribir AL LADO del logo (a partir de X=45)
        $this->SetFont('helvetica', 'B', 15);
        $this->SetXY(45, 12); 
        $this->Cell(0, 10, 'FACTURA DE VENTA', 0, 1, 'L');

        $this->SetFont('helvetica', 'B', 10);
        $this->SetX(45);
        $this->Cell(0, 5, $_SESSION['EmpresaN1'], 0, 1, 'L');

        $this->SetFont('helvetica', '', 8);
        $this->SetX(45);
        $this->Cell(0, 4, $_SESSION['EmpresaN5'], 0, 1, 'L');
        $this->SetX(45);
        $this->Cell(0, 4, 'Tel: '.$_SESSION['EmpresaN4'].' | Email: '.$_SESSION['EmpresaN3'], 0, 1, 'L');

        // Línea divisoria debajo del header
        $this->Line(10, 40, 200, 40);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Crear PDF
$pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema Almacén');
$pdf->SetTitle('Factura ' . $venta['codigo_venta']);
$pdf->SetSubject('Factura de Venta');

// Configuración
$pdf->SetMargins(10, 40, 10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('helvetica', '', 10);

// Agregar página
$pdf->AddPage();

// --- INFORMACIÓN DE LA VENTA ---
$cliente_documento = !empty($venta['cliente_documento']) ? 
    $venta['tipo_cliente_codigo'] . '-' . $venta['cliente_documento'] : 
    'CONSUMIDOR FINAL';

$html_info = '
<table border="0" cellpadding="2" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="width: 50%;">
            <span style="font-weight: bold;">FACTURA N°:</span> ' . $venta['codigo_venta'] . '<br>
            <span style="font-weight: bold;">FECHA:</span> ' . 
            sprintf('%02d', $venta['fecha_ventaD']) . '/' . 
            sprintf('%02d', $venta['fecha_ventaM']) . '/' . 
            $venta['fecha_ventaY'] . ' ' . $venta['fecha_ventaH'] . '<br>
            <span style="font-weight: bold;">VENDEDOR:</span> ' . $venta['nombre_empleado'] . '
        </td>
        <td style="width: 50%;">
            <span style="font-weight: bold;">CLIENTE:</span> ' . $cliente_documento . '<br>
            <span style="font-weight: bold;">MÉTODO PAGO:</span> ' . strtoupper($venta['metodo_pago']) . '<br>
            <span style="font-weight: bold;">ESTADO:</span> ' . $venta['estado'] . '
        </td>
    </tr>
</table>
<br><br>
';

$pdf->writeHTML($html_info, true, false, true, false, '');

// --- TABLA DE DETALLES ---
$simbolo = $venta['simbolo_moneda'];
$subtotal_factura = 0;

$html_detalles = '
<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-color: #cccccc;">
    <tr style="background-color: #f0f0f0;">
        <th style="width: 15%; font-weight: bold; text-align: center;">SKU</th>
        <th style="width: 45%; font-weight: bold;">Descripción</th>
        <th style="width: 15%; font-weight: bold; text-align: right;">Precio Unit.</th>
        <th style="width: 10%; font-weight: bold; text-align: center;">Cant.</th>
        <th style="width: 15%; font-weight: bold; text-align: right;">Subtotal</th>
    </tr>
';

foreach ($detalles as $detalle) {
    $precio_unitario = number_format($detalle['precio_unitario'], 2);
    $subtotal_item = number_format($detalle['subtotal'], 2);
    $subtotal_factura += $detalle['subtotal'];
    
    $html_detalles .= '
    <tr>
        <td style="width: 15%; text-align: center;">' . $detalle['producto_sku'] . '</td>
        <td style="width: 45%;">' . htmlspecialchars($detalle['nombre_producto']) . '</td>
        <td style="width: 15%; text-align: right;">' . $simbolo . ' ' . $precio_unitario . '</td>
        <td style="width: 10%; text-align: center;">' . $detalle['cantidad'] . '</td>
        <td style="width: 15%; text-align: right;">' . $simbolo . ' ' . $subtotal_item . '</td>
    </tr>';
}

// Cálculos
$descuento = $venta['descuento'] ?? 0;
$iva_tasa = 0.16;
$base_imponible = $subtotal_factura - $descuento;
$iva_monto = $base_imponible * $iva_tasa;
$total_calculado = $base_imponible + $iva_monto;

$html_detalles .= '
    <tr style="background-color: #f8f8f8;">
        <td colspan="4" style="text-align: right; font-weight: bold;">SUBTOTAL</td>
        <td style="text-align: right; font-weight: bold;">' . $simbolo . ' ' . number_format($subtotal_factura, 2) . '</td>
    </tr>
    <tr style="background-color: #f8f8f8;">
        <td colspan="4" style="text-align: right; font-weight: bold;">DESCUENTO</td>
        <td style="text-align: right; font-weight: bold;">' . $simbolo . ' ' . number_format($descuento, 2) . '</td>
    </tr>
    <tr style="background-color: #f8f8f8;">
        <td colspan="4" style="text-align: right; font-weight: bold;">BASE IMPONIBLE</td>
        <td style="text-align: right; font-weight: bold;">' . $simbolo . ' ' . number_format($base_imponible, 2) . '</td>
    </tr>
    <tr style="background-color: #f8f8f8;">
        <td colspan="4" style="text-align: right; font-weight: bold;">IVA (' . ($iva_tasa * 100) . '%)</td>
        <td style="text-align: right; font-weight: bold;">' . $simbolo . ' ' . number_format($iva_monto, 2) . '</td>
    </tr>
    <tr style="background-color: #cccccc;">
        <td colspan="4" style="text-align: right; font-weight: bold; font-size: 11pt;">TOTAL</td>
        <td style="text-align: right; font-weight: bold; font-size: 11pt;">' . $simbolo . ' ' . number_format($venta['total'], 2) . '</td>
    </tr>
</table>
<br><br>
';

$pdf->writeHTML($html_detalles, true, false, true, false, '');

// --- FIRMAS ---
$html_firmas = '
<table border="0" cellpadding="5" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="width: 50%; text-align: center;">
            <br><br>
            _________________________<br>
            <strong>VENDEDOR</strong><br>
            ' . $venta['nombre_empleado'] . '
        </td>
        <td style="width: 50%; text-align: center;">
            <br><br>
            _________________________<br>
            <strong>CLIENTE</strong><br>
            ' . $cliente_documento . '
        </td>
    </tr>
</table>
';

$pdf->writeHTML($html_firmas, true, false, true, false, '');

// --- 5. SALIDA DEL PDF ---
// LIMPIAR CUALQUIER BUFFER ANTES DE OUTPUT
while (ob_get_level()) {
    ob_end_clean();
}

// ENVIAR HEADERS PRIMERO
/*
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Factura_' . $venta['codigo_venta'] . '.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
*/

// SALIDA DIRECTA
//$pdf->Output('Factura_' . $venta['codigo_venta'] . '.pdf', 'D');

// IMPORTANTE: TERMINAR EJECUCIÓN AQUÍ


ob_clean();
$pdf->Output('factura.pdf', 'D');
ob_end_flush();


header("Location: ../../menu.php?pagina=gestionv");
exit();
?>