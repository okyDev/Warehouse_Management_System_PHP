<?php
require_once '../config/connect.php';
require_once '../config/auth.php';
$base = new MYSQL;

$step_one = verify("crear_venta");
error_log("EL RESULTADO: ".$step_one);
error_log("				VENTAS				");
// MONEDAS (corrección)
$dolarBS_result = $base->query_all("SELECT tasa_cambio FROM monedas WHERE id = 2");
$dolarBS = isset($dolarBS_result[0]['tasa_cambio']) ? (float)$dolarBS_result[0]['tasa_cambio'] : 1.0;
$IVA_RATE = 0.12;

// Inicializar carrito
if (!isset($_SESSION['CARRITO']) || !is_array($_SESSION['CARRITO'])) {
    $_SESSION['CARRITO'] = array();
}



// Variables de Total
$totales = [
'subtotal_bs' => 0.00,
'iva_bs' => 0.00,
'total_bs' => 0.00,
'total_usd' => 0.00
];

//// Funciones de Lógica

function calcularTotalesCarrito(float $dolarBS, float $IVA_RATE): array {
$subtotal_bs = 0.00;

foreach ($_SESSION['CARRITO'] as $sku => $p) {
// 1. Calcular el precio unitario en Bs
$precio_unitario_bs = (float)$p['precio_venta'];
if ($p['moneda_venta_id'] == 2 && $dolarBS > 0) {
$precio_unitario_bs *= $dolarBS;
}

// 2. Calcular el subtotal de la línea
$subtotal_linea_bs = $precio_unitario_bs * (int)$p['cantidad'];

// Almacenar el subtotal de la línea de nuevo en la sesión (necesario para el bucle de la tabla)
$_SESSION['CARRITO'][$sku]['subtotal_linea_bs'] = $subtotal_linea_bs;

$subtotal_bs += $subtotal_linea_bs;
}

$iva_bs = $subtotal_bs * $IVA_RATE;
$total_bs = $subtotal_bs + $iva_bs;
$total_usd = ($dolarBS > 0) ? $total_bs / $dolarBS : 0.00;

return [
'subtotal_bs' => $subtotal_bs,
'iva_bs' => $iva_bs,
'total_bs' => $total_bs,
'total_usd' => $total_usd
];
}





function generarCodigoVenta($base) {
// Función de generación de código... (Tu código original)
$ano = date('Y');
$letra = 'V-'.$ano;
$numero = 1;

do {
$sku = $letra . str_pad($numero, 4, '0', STR_PAD_LEFT);
$query = "SELECT COUNT(*) AS count FROM ventas WHERE codigo_venta = ?";
$resultado = $base->one_query($query,$sku);
$numero++;
} while ($resultado['count'] > 0);

return $sku;
}

//// PROCESAMIENTO DE ACCIONES

// SUMAR CANTIDAD TRON
if ($_POST) {
$nueva_cantidad = (int)$_POST['ACTUALIZAR_CANTIDAD'];
if($nueva_cantidad > 0){
$skuI = $_POST['skuaActualizar'];

$pussy = $nueva_cantidad;
///$ass = $pussy + $nueva_cantidad;
error_log("HOLA");
error_log("La cantidad reportada es : ".$ass." del proucto : ".$skuI);

if (isset($_SESSION['CARRITO'][$skuI])) {
if ($nueva_cantidad <= 0) {
unset($_SESSION['CARRITO'][$skuI]); // Eliminar si es 0 o menos
} else {
$_SESSION['CARRITO'][$skuI]['cantidad'] = $pussy;
}
}
}
else{
	error_log("JAVA");
}
}




// 2. BUSQUEDAS (Tu código original, solo para búsqueda)
$coincide = null;
if($_POST && isset($_POST['SEACHERT'])){
    $NOMBREP = $_POST['searchNombre'] ?? '';
    $SKUS = $_POST['searchCodigo'] ?? '';
    
    error_log("Búsqueda - SKU: '$SKUS', Nombre: '$NOMBREP'"); // Para debug

    // Búsqueda por SKU
    if(!empty($SKUS) && empty($NOMBREP)){
        $sql = "SELECT P.*, M.simbolo as moneda FROM productos P 
                JOIN monedas M ON M.id = P.moneda_venta_id 
                WHERE P.activo = TRUE AND P.sku = ?";
        $result = $base->one_query($sql, $SKUS); // ✅ Corregido: $SKUS
        
        if($result !== false && $result !== null){
            $coincide = [$result]; // Convertir a array para el foreach
        } else {
            $coincide = []; // No se encontró
        }
    }
    // Búsqueda por nombre
    elseif(empty($SKUS) && !empty($NOMBREP)){
        $sql = "SELECT P.*, M.simbolo as moneda FROM productos P 
                JOIN monedas M ON M.id = P.moneda_venta_id 
                WHERE P.activo = TRUE AND 
                (P.nombre LIKE ? OR P.marca LIKE ? OR P.descripcion LIKE ?)";
        
        $search_term = "%{$NOMBREP}%";
        $coincide = $base->some_query($sql, [$search_term, $search_term, $search_term]);
        
        if($coincide === false) {
            $coincide = []; // En caso de error
        }
    }
}




// 3. AGREGAR PRODUCTO (Lógica Corregida: Maneja duplicados por SKU)
if($_POST AND isset($_POST['AGREGARTOTHECAR'])){
$SKU = $_POST['SKU'];
$sql = "SELECT P.*, M.simbolo as moneda FROM productos P JOIN monedas M ON M.id = P.moneda_venta_id WHERE P.activo = TRUE AND P.sku = ?";
$producto_base = $base->one_query($sql,$SKU);
///$query->close();

if ($producto_base) {
$sku_key = $producto_base['sku'];

if (isset($_SESSION['CARRITO'][$sku_key])) {
$_SESSION['CARRITO'][$sku_key]['cantidad']++;
} else {
$producto_base['cantidad'] = 1;
$_SESSION['CARRITO'][$sku_key] = $producto_base;
}
}
}



// ELIMINAR
if($_POST and isset($_POST['DROP'])){
$SKUPDROP = $_POST['skuaActualizar'];
unset($_SESSION['CARRITO'][$SKUPDROP]);
error_log("AQUI ELIMINAR : ".$SKUPDROP);
}

//// LLAMADA FINAL Y VARIABLES GLOBALES
$totales = calcularTotalesCarrito($dolarBS, $IVA_RATE);
$AUTOID = generarCodigoVenta($base);
?>


<?php if($step_one === true):?>
<div class="container mt-4">
<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
<h1 class="h3 mb-0">
<i class="bi bi-cart-plus-fill text-primary"></i> Realizar Venta
</h1>
<div class="d-flex align-items-center">
<span class="badge bg-secondary me-2">Venta #<span><?= $AUTOID ?></span></span>
<span class="badge bg-secondary me-2">Empleado CV: V<span><?= $_SESSION['user_cedula'] ?></span></span>
<span class="text-muted">INFO</span>
</div>
</div>

<!-- Mensajes de alerta -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
<i class="bi bi-check-circle-fill me-2"></i>
<?php echo $_SESSION['success']; ?>
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
<i class="bi bi-exclamation-triangle-fill me-2"></i>
<?php echo $_SESSION['error']; ?>
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>



<div class="row">
<!-- Sidebar de productos -->
<div class="col-lg-4 mb-4">
<div class="sidebarSELL">
<h5 class="mb-3"></h5>

<a id="UNHIDE" class="btn btn-primary" data-toggle="collapse" data-target="#miDiv" aria-expanded="false" aria-controls="#miDiv">Buscar Por</a>
<form method="POST">

<!-- Busqueda por código -->
<div class="collapse mt-3" id="miDiv">
<div class="card card-body">

<div class="search-section">
<label for="searchCodigo" class="form-label fw-bold">Buscar por código</label>
<div class="input-group">
<span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
<input type="text" name="searchCodigo" placeholder="Ingrese código del producto..." class="form-control">
</div>
</div>

<!-- Busqueda por nombre -->
<div class="search-section">
<label for="searchNombre" class="form-label fw-bold">Buscar por nombre, categoria,..</label>
<div class="input-group">
<span class="input-group-text"><i class="bi bi-card-text"></i></span>
<input type="text" name="searchNombre" placeholder="Ingrese nombre del producto..." class="form-control">
</div>
<br>
<button type="submit" name="SEACHERT" class="buttonBuscarVENTAS">
    <i class="bi bi-search"></i> BUSCAR
</button>

</div>

</div>
</div>

</form>


<!-- Lista de productos -->
<h6 class="mt-4 mb-3">Productos Disponibles</h6>
<?php if($coincide !== null):?>
<div class="list-opcion">
<?php foreach($coincide as $n1):?>
<div class="product-card">
<div class="d-flex justify-content-between align-items-start">
<div>
<form method="POST" class="productoCard">
<label>[<?=$n1['sku']?>]</label>
<label><b>NOmbre:</b> <?= $n1['nombre']?></label>
<label><b>Marca:</b> <?= $n1['marca']?></label>
<label><b>Precio:</b> <?=$n1['moneda']?> <?= $n1['precio_venta']?></label>
<label><b>Descripcion:</b> <?= $n1['descripcion']?></label>
<input type="hidden" name="SKU" value="<?= $n1['sku'] ?>">
<?php if($n1['stock_actual'] === 0 or $n1['stock_actual'] < $n1['stock_minimo']):?>
<label>No hay [<?=$n1['sku']?> <?= $n1['nombre']?>] en el almacen.</label>
<?php else:?>
<br>
<button type="submit" name="AGREGARTOTHECAR" class="buttonVentaadd">Agregar</button>
<?php endif;?>
</form>
</div>
<!---->
</div>
</div>
<!---->
<?php endforeach;?>
</div>

<?php else:?>
<label class="form-label fw-bold">Ingrese un parametro de busqueda para buscar</label>
<?php endif;?>
</div>

</div>
<!---->



<!-- Panel de venta -->
<div class="col-lg-8">
<div class="money-box">
<h5 class="mb-3"><i class="bi bi-receipt me-2"></i>Detalle de Venta</h5>

<!-- Tabla de productos en venta -->
<div class="table-responsive">
<table class="table table-hover" id="tablaV">
<thead class="table-light">
<tr><th>Producto</th><th>Precio Unit.</th><th width="120">Cantidad</th>
<th>Total (Bs)</th><th width="80">Acción</th>
</tr>
</thead>
<tbody>
<?php if(empty($_SESSION['CARRITO'])):?>
<tr>
<td colspan="5">No hay productos agregados</td>
</tr>

<?php else:?>


<?php foreach($_SESSION['CARRITO'] as $p):?>

<tr data-sku="<?= $p['sku']?>">
<td>[<?= $p['sku']?>] <?= $p['nombre']?></td>
<td><?= $p['moneda']?> <?= number_format($p['precio_venta'], 2) ?></td>

<td>
<form method="POST" id="form-cant-<?= $p['sku']?>" class="form-cantidad">
<input type="hidden" name="skuaActualizar" value="<?= $p['sku'] ?>">
<input type='number' name="ACTUALIZAR_CANTIDAD" class="form-control"  value="<?= $p['cantidad']?>" style="width: 70px;" onchange="this.form.submit()" >
</form>
</td>

<td class="line-total-bs">
Bs <?= number_format($p['subtotal_linea_bs'], 2) ?>
</td>

<td>
<form method="POST" name="AgregarNUEVO">
<input type="hidden" name="ACTUALIZAR_CANTIDAD">
<input type="hidden" name="skuaActualizar" value="<?= $p['sku'] ?>">
<button type="submit" class="btn btn-danger btn-sm" title="Eliminar Producto" name="DROP">
<i class="bi bi-x-lg">X</i>
</button>
</form>
</td>

<!---VALORES--->
<input type="hidden" id="BSXDOLAR" value="<?= $dolarBS?>">
<!------>
</tr>
<?php endforeach;?>
<?php endif;?>
<!------>

</tbody>
</table>
</div>


<!-- ENVIAR CARTET PARA QUE CAIGA EL BONOOOOOOOOOOOOo--->
<form method="POST" action="modulos/ventas/procesar_venta.php">
<input type="hidden" name="VENTASKU" value="<?= $AUTOID?>" readonly>
<div class="total-box mt-4">
<div class="row">
<!---ROW1-->

<div class="col-md-6">
<div class="mb-2">
<label for="clienteNombre" class="form-label fw-bold">Cedula del Cliente</label>
<input type="text" name="cliente_documento" class="form-control" value="V-">
</div>



<div class="mb-2">
<label for="tipoPago" class="form-label fw-bold">Metodos de Pago</label>
<select class="form-select" name="tipoPago" id="tdpV">
<option value="simple" onclick="NOMIXTO()">Pago Simple</option>
<option value="mixto" onclick="MIXTO()">Pago Mixto</option>
</select>
</div>
</div>
</div>


<!--ROW 2--->
<div class="row">
<div class="col-md-6">
<div class="d-flex justify-content-between mb-2">
<span>Subtotal (sin IVA):</span>
<span class="fw-bold">Bs <?= number_format($totales['subtotal_bs'],2)?></span>
<span>$ <?= number_format($totales['subtotal_bs'] / $dolarBS, 2) ?></span>
</div>

<div class="d-flex justify-content-between mb-1">
<span>IVA (12%):</span>
<span id="ivaCompra">Bs <?= number_format($totales['iva_bs'], 2) ?></span>
</div>
<div class="d-flex justify-content-between mb-1 fw-bold fs-5">
<span>Total Final:</span>
<span id="totalCompra">Bs <?= number_format($totales['total_bs'], 2) ?></span>
</div>
<div class="d-flex justify-content-between mb-1 small text-muted">
<span>Bs <span> <?= number_format($totales['total_bs'], 2) ?></span></span>
<span>$ <span id="totalDolarCompra"><?= number_format($totales['total_usd'], 2) ?></span></span>

<input type="hidden" id="totalBsCompra" value="<?= number_format($totales['total_bs'], 2) ?>">
</div>
</div>
</div>
</div>




<!-- PAGOS MIXTO -->
<div id="pagoMixtoSection" class="pago-mixto-section" style="display: none;">
<h6 class="mb-3">
<i class="bi bi-credit-card-2-front"></i> Detalle de Pagos Mixtos
</h6>

<!-- #1 -->
<div class="metodo-pago-item">
<label class="form-label fw-bold">Pagado por</label>
<select name="pago1">
<option value="Efectivo">Efectivo</option>
<option value="Biopago">Biopago</option>
<option value="Tarjeta">Tarjeta</option>
</select>
<div class="input-group">
<select id="moneda1" name="moneda1">
<option value="bs">Bs</option>
<option value="$">$</option>
</select>
<input type="number" name="pay1" id="payplace1" class="form-control" placeholder="0.00" min="0" step="0.01" value="0">
</div>
</div>

<!-- #2 -->
<div class="metodo-pago-item">
<label class="form-label fw-bold">Pagado por</label>
<select name="pago2">
<option value="Efectivo">Efectivo</option>
<option value="Biopago">Biopago</option>
<option value="Tarjeta">Tarjeta</option>
</select>

<div class="input-group">
<select id="moneda2" name="moneda2">
<option value="bs">Bs</option>
<option value="$">$</option>
</select>
<input type="number" name="pay2" id="payplace2" class="form-control" placeholder="0.00" min="0" step="0.01" value="0">
</div>
</div>

<!-- #3 -->
<div class="metodo-pago-item">
<label class="form-label fw-bold">Pagado por</label>
<select name="pago3">
<option value="Efectivo">Efectivo</option>
<option value="Biopago">Biopago</option>
<option value="Tarjeta">Tarjeta</option>
<option value="Ninguno">Ninguno</option>
</select>
<div class="input-group">
<select id="moneda3" name="moneda3">
<option value="bs">Bs</option>
<option value="$">$</option>
</select>
<input type="number" name="pay3" id="payplace3" class="form-control" placeholder="0.00" min="0" step="0.01" value="0" onchange="actualizarPagoMixto()">
</div>
</div>



<!-- Resumen de pagos mixtos -->
<div class="mt-3 p-2 bg-white rounded">
<div class="d-flex justify-content-between mb-1">
<span>Total Pagado:</span>
<span></span>
</div>
<div class="d-flex justify-content-between mb-1">
<span>Saldo Pendiente:</span>
<span id="howBs" class="saldo-pendiente">Bs0.00</span>
<span id="how$" class="saldo-pendiente">$0.00</span>
</div>
<div id="alertaPagoMixto" class="alert alert-warning mt-2 py-2" style="display: none;">
<small><i class="bi bi-exclamation-triangle"></i> El total pagado no coincide con el total de la venta</small>
</div>
</div>
</div>
<!-- PAGOS MIXTO ENDS HERE -->


<!--Pago NORMAL -->
<div id="pagoSimpleSection">
<div class="mb-2">
<label for="metodoPagoSimple" class="form-label fw-bold">Método de Pago</label>
<select class="form-select" id="metodoPagoSimple" name="metodo_pago">
<option value="efectivo">Efectivo</option>
<option value="tarjeta">Tarjeta</option>
<option value="transferencia">Transferencia</option>
</select>
</div>
</div>
<!--Pago NORMAL ENDS HERE -->

<!-- Botones de acción -->
<div class="d-flex justify-content-between">
<!-- Formulario para procesar venta -->
<input type='hidden' name="CDempleado" value="<?= $_SESSION['user_cedula']?>">

<button type="submit" class="btn btn-outline-secondary" name="procesarVentaBtn"><i class="bi bi-check-lg"></i> Procesar Venta</button>

<button type="submit" class="btn btn-outline-secondary" name="CLEARSHOP">
<i class="bi bi-trash"></i> Limpiar Carrito </button>
             
<button type="button" class="btn btn-outline-primary me-2">
<i class="bi bi-printer"></i> Imprimir</button>
</div>
</form>


</div>


</div>


<!---->
</div>
</div>

<?php else:?>
<div class="alert alert-danger" role="alert">
  <h4 class="alert-heading">Error de Permisos</h4>
  <p>El usuario no cuenta con los permisos necesarios para acceder a esta función.</p>
</div>
<!---->
<?php endif;?>



<script>
let totalBS = parseFloat(document.getElementById('totalBsCompra')?.value) || 0;
let totalDolar = parseFloat(document.getElementById('totalDolarCompra')?.value) || 0;
const dolarpricedude = parseFloat(document.getElementById('BSXDOLAR')?.value) || 1; 
var activo = <?= json_encode($step_one)?>;


document.getElementById('UNHIDE')?.addEventListener('click', function() {
$("#miDiv").toggle();
});

function calcularPagoLinea(index) {
const moneda = document.getElementById(`moneda${index}`)?.value;
const montoIngresado = parseFloat(document.getElementById(`payplace${index}`)?.value) || 0;
let monto_en_bs = 0;
let monto_en_dolar = 0;

if (moneda === '$') {
monto_en_dolar = montoIngresado;
monto_en_bs = montoIngresado * dolarpricedude;
} 
else if (moneda === 'bs') {
monto_en_bs = montoIngresado;
monto_en_dolar = dolarpricedude > 0 ? montoIngresado / dolarpricedude : 0;
}

        return { bs: monto_en_bs, dolar: monto_en_dolar };
    }


function actualizarPagoMixto() {
        // Calcular pagos individuales
        const pago1 = calcularPagoLinea(1);
        const pago2 = calcularPagoLinea(2);
        const pago3 = calcularPagoLinea(3);

        // Sumar todos los pagos
        const totalPagadoBS = pago1.bs + pago2.bs + pago3.bs;
        const totalPagadoDolar = pago1.dolar + pago2.dolar + pago3.dolar;
        
        // Calcular saldos pendientes
        const saldoPendienteBS = totalBS - totalPagadoBS;
        const saldoPendienteDolar = totalDolar - totalPagadoDolar;
        
        // --- ACTUALIZAR EL DOM ---
        
        // Actualizar Total Pagado (Mostramos en Bs y $)
        const totalPagadoSpan = document.querySelector('#pagoMixtoSection .mt-3 .d-flex.justify-content-between:nth-child(1) span:nth-child(2)');
        if(totalPagadoSpan) {
             totalPagadoSpan.textContent = `Bs ${totalPagadoBS.toFixed(2)} / $${totalPagadoDolar.toFixed(2)}`;
        }

        // Actualizar Saldo Pendiente
        document.getElementById('howBs').textContent = `Bs ${saldoPendienteBS.toFixed(2)}`;
        document.getElementById('how$').textContent = `$${saldoPendienteDolar.toFixed(2)}`;
        
        // --- VALIDACIÓN Y BOTÓN ---
        
        const alerta = document.getElementById('alertaPagoMixto');
        
        // Validamos si la diferencia en Dólares (o Bs) es mayor a una tolerancia mínima (0.01)
        const pagoCompleto = Math.abs(saldoPendienteBS) < 0.01;

        if (pagoCompleto) {
            alerta.style.display = 'none';
           
        } else {
            alerta.style.display = 'block';
           
        }
        
        // Actualizar input oculto para PHP (Si planeas enviar estos datos)
        const pagosMixtos = {
            total_pagado_bs: totalPagadoBS.toFixed(2),
            total_pagado_usd: totalPagadoDolar.toFixed(2),
            pagos: [
                { monto: parseFloat(document.getElementById('payplace1')?.value) || 0, moneda: document.getElementById('moneda1')?.value },
                { monto: parseFloat(document.getElementById('payplace2')?.value) || 0, moneda: document.getElementById('moneda2')?.value },
                { monto: parseFloat(document.getElementById('payplace3')?.value) || 0, moneda: document.getElementById('moneda3')?.value }
            ]
        };
        // Reemplaza 'pagosMixtosInput' con el ID del input oculto que usas para enviar estos datos a PHP
        // document.getElementById('pagosMixtosInput').value = JSON.stringify(pagosMixtos); 
    }


    // --- MANEJO DE VISTAS (MIXTO / SIMPLE) ---
document.getElementById("tdpV").addEventListener('click', function(){
	if(this.value == 'simple'){
		document.getElementById('pagoMixtoSection').style.display = 'none';
        document.getElementById('pagoSimpleSection').style.display = 'block';
	}
	
	else if(this.value == 'mixto'){
		document.getElementById('pagoMixtoSection').style.display = 'block';
        document.getElementById('pagoSimpleSection').style.display = 'none';
	}
	
	else{
		document.getElementById('pagoMixtoSection').style.display = 'none';
        document.getElementById('pagoSimpleSection').style.display = 'none';
	}
	
	});

    // --- EVENT LISTENERS ---

    // Escuchar cambios en los inputs de pago mixto (Payplace)
    document.getElementById('payplace1')?.addEventListener('input', actualizarPagoMixto);
    document.getElementById('payplace2')?.addEventListener('input', actualizarPagoMixto);
    document.getElementById('payplace3')?.addEventListener('input', actualizarPagoMixto);

    // Escuchar cambios en la selección de moneda (Moneda)
    document.getElementById('moneda1')?.addEventListener('change', actualizarPagoMixto);
    document.getElementById('moneda2')?.addEventListener('change', actualizarPagoMixto);
    document.getElementById('moneda3')?.addEventListener('change', actualizarPagoMixto);


    // Inicialización al cargar la página
$(document).ready(function() {
if(activo == false){
	window.location.href = './modulos/logout.php?razon={ventas}';
}
});
</script>
