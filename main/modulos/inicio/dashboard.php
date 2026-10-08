<?php
require_once '../config/connect.php'; 

//// API
$base = new MYSQL;

///fechas
$d = date("d"); $m = date("m"); $y = date("Y"); $h = date("H"); $mi = date("i");

//// BASES
$lotes = $base->query_all("SELECT * FROM lotes WHERE activo =TRUE");
$productos = $base->query_all("SELECT * FROM productos WHERE activo =TRUE");
$ventas = $base->some_query("SELECT M.simbolo as moneda, V.fecha_ventaMi, I.codigo as CI, V.codigo_venta,V.cedula_empleado, E.apellido as apellido, V.total, V.fecha_ventaD, V.fecha_ventaM,V.fecha_ventaY,V.fecha_ventaH FROM ventas V JOIN empleados_datos E ON E.cedula = V.cedula_empleado JOIN tipos_identificacion I ON I.id = E.id_tipo JOIN monedas as M ON M.id = V.moneda_id WHERE (V.fecha_ventaD = ?) and (V.fecha_ventaM = ?) and (V.fecha_ventaY = ?) and (V.fecha_ventaH = ?) and V.activo = true LIMIT 5",[$d,$m,$y,$h]);
$ventasALL = $base->query_all("SELECT * FROM ventas WHERE activo=TRUE");
$monedas = $base->query_all("SELECT * FROM monedas WHERE activo=TRUE");

$valorLOTES = array_sum(array_column($lotes, 'costo_unitario'));


/// STCKCS BAJOS
$sumaStocksBajos = 0;
foreach($productos as $p){
if($p['stock_actual'] <= $p['stock_minimo']){
$sumaStocksBajos++;
}
	}


///// MONEDA y su cambio
$intercambio = [];
foreach($monedas as $n){
if($n['base'] === false){
$intercambio[$n['id']] = $n['tasa_cambio'];
	}
}

$ganacias_semanal = [];
///// OBTENER ganacias de cada semana
if(isset($ventasALL)){
$dia = $d;
$total = 0;
$semana = 1;
$n = 0;

do{
//// LOOP
foreach($ventasALL as $v){
if($v['fecha_ventaD'] == $dia AND $v['fecha_ventaM'] == $m AND $v['fecha_ventaY'] == $y ){

if($v['moneda_id'] === 1){
$total += (int)$v['total'];
}
else{
$id_m = $v['moneda_id'];
$valor_cambio = array_filter($intercambio, function($var){
	return $var[$id_m];
});
$total += (int)$valor_cambio * $v['total'];
}
	///error_log("El total ahroa es : ".$v['total']);
}
}


/// SE CUMPLE UNA SEMANA?
if($n === 7){
	$w = 'semana '.$semana;
	$ganacias_semanal[$w] = $total;
	
	/// cero 
	$total =0;
	$semana++;
	$n = 0;
}
//// SUMAR Y SEGUIR
$n++;
$dia--;
}while($dia != 0);
	
}//// ENDHERE

$total_ganancia_semanal = count($ganacias_semanal);



///// FUNCIONES
function sumaStocksIguales($productos) {
return array_sum(
array_column(
array_filter($productos, function($producto) {
return $producto['stock_actual'] <= $producto['stock_minimo'];
}),
'stock_actual' // Cambié 'stock' a 'stock_actual' para que coincida con los campos
)
);
}


function calcularGananciasTotales($base, $filtro_fecha = null) {
/**
* Calcula las ganancias totales restando costos de lotes a las ventas
* Fórmula: Ganancia = TotalVenta - (CostoLote * CantidadVendida)
*/

// Construir WHERE para filtros de fecha
$where_fecha = "";
$params = [];
$types = "";

if ($filtro_fecha) {
$where_fecha = "WHERE v.fecha_ventaY = ? AND v.fecha_ventaM = ?";
$params = [$filtro_fecha['year'], $filtro_fecha['month']];
$types = "ii";
}

// Consulta para obtener ventas con detalles y costos de lotes
$sql = "
SELECT 
v.id_venta,
v.codigo_venta,
v.fecha_ventaD,
v.fecha_ventaM, 
v.fecha_ventaY,
v.total as total_venta,
v.descuento,
v.moneda_id,
dv.producto_sku,
dv.lote_id,
dv.cantidad as cantidad_vendida,
dv.precio_unitario,
dv.subtotal as subtotal_detalle,
l.costo_unitario,
l.moneda_compra_id,
p.nombre as producto_nombre,
m_venta.codigo as moneda_venta,
m_compra.codigo as moneda_compra
FROM ventas v
JOIN detalle_ventas dv ON v.id_venta = dv.venta_id
JOIN lotes l ON dv.lote_id = l.lote_sku
JOIN productos p ON dv.producto_sku = p.sku
JOIN monedas m_venta ON v.moneda_id = m_venta.id
JOIN monedas m_compra ON l.moneda_compra_id = m_compra.id
$where_fecha
AND v.activo = 1 
AND dv.activo = 1 
AND l.activo = 1
ORDER BY v.fecha_ventaY DESC, v.fecha_ventaM DESC, v.fecha_ventaD DESC
";

////
$datos = $base->some_query($sql,$params);


// Calcular ganancias
$ganancias = [
'total_ventas' => 0,
'total_costos' => 0,
'total_ganancia' => 0,
'total_iva' => 0,
'ventas_por_moneda' => [],
'detalles_ventas' => []
];

foreach ($datos as $fila) {
// Convertir costos si las monedas son diferentes (aquí necesitarías tasas de cambio)
$costo_total_item = $fila['costo_unitario'] * $fila['cantidad_vendida'];

// Calcular IVA (16% sobre el subtotal después de descuento)
$subtotal_neto = $fila['subtotal_detalle'] - ($fila['descuento'] / count($datos));
$iva_item = $subtotal_neto * 0.16;

// Ganancia del item
$ganancia_item = $fila['subtotal_detalle'] - $costo_total_item - $iva_item;

// Acumular totals
$ganancias['total_ventas'] += $fila['subtotal_detalle'];
$ganancias['total_costos'] += $costo_total_item;
$ganancias['total_iva'] += $iva_item;
$ganancias['total_ganancia'] += $ganancia_item;

// Agrupar por moneda
$moneda = $fila['moneda_venta'];
if (!isset($ganancias['ventas_por_moneda'][$moneda])) {
$ganancias['ventas_por_moneda'][$moneda] = [
'ventas' => 0,
'costos' => 0,
'ganancia' => 0
];
}

$ganancias['ventas_por_moneda'][$moneda]['ventas'] += $fila['subtotal_detalle'];
$ganancias['ventas_por_moneda'][$moneda]['costos'] += $costo_total_item;
$ganancias['ventas_por_moneda'][$moneda]['ganancia'] += $ganancia_item;

// Guardar detalles
$ganancias['detalles_ventas'][] = [
'venta_id' => $fila['id_venta'],
'codigo_venta' => $fila['codigo_venta'],
'fecha' => $fila['fecha_ventaD'] . '/' . $fila['fecha_ventaM'] . '/' . $fila['fecha_ventaY'],
'producto' => $fila['producto_nombre'],
'cantidad' => $fila['cantidad_vendida'],
'precio_venta' => $fila['precio_unitario'],
'costo_unitario' => $fila['costo_unitario'],
'subtotal_venta' => $fila['subtotal_detalle'],
'costo_total' => $costo_total_item,
'iva' => $iva_item,
'ganancia' => $ganancia_item,
'moneda_venta' => $fila['moneda_venta'],
'moneda_compra' => $fila['moneda_compra']
];
}

return $ganancias;
}

// Función para obtener resumen mensual
function obtenerResumenMensual($base, $year = null, $month = null) {
$year = $year ?? date('Y');
$month = $month ?? date('m');

$sql = "
SELECT 
v.fecha_ventaM as mes,
v.fecha_ventaY as año,
COUNT(DISTINCT v.id_venta) as total_ventas,
SUM(v.total) as ingresos_totales,
SUM(dv.cantidad) as total_productos_vendidos,
m.codigo as moneda
FROM ventas v
JOIN detalle_ventas dv ON v.id_venta = dv.venta_id
JOIN monedas m ON v.moneda_id = m.id
WHERE v.fecha_ventaY = ? AND v.fecha_ventaM = ?
AND v.activo = 1 AND dv.activo = 1
GROUP BY v.fecha_ventaY, v.fecha_ventaM, m.codigo
ORDER BY v.fecha_ventaY DESC, v.fecha_ventaM DESC
";

/////
$resumen = $base->some_query($sql,[$year,$month]);


return $resumen;
}



$filtro_fecha = ['year' => date('Y'), 'month' => date('m')];
$ganancias = calcularGananciasTotales($base, $filtro_fecha);

?>


<script>
$(document).ready(function() {
  // Variables
  const dataVentasSemanales = [];
  const labels = [];

  <?php foreach ($ganacias_semanal as $n => $v): ?>
    <?php if ($v > 0): ?>
      dataVentasSemanales.push(<?= json_encode($v) ?>);
    <?php endif; ?>
    labels.push(<?= json_encode($n) ?>);
  <?php endforeach; ?>

  const ctx = document.getElementById('movimientosChart').getContext('2d');

  new Chart(ctx, {
    type: 'bar', // Tipo de gráfico
    data: {
      labels: labels,
      datasets: [{
        label: 'Ventas Brutas (USD)',
        data: dataVentasSemanales,
        backgroundColor: 'rgba(25, 135, 84, 0.8)',
        borderColor: 'rgba(25, 135, 84, 1)',
        borderWidth: 2.5,
        borderRadius: 5 // Efecto de "cuadro"
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false, // Ocultar leyenda
        },
        title: {
          display: true,
          text: 'Ventas Brutas Totales por Semana del Mes',
          font: {
            size: 14
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) {
                label += ': ';
              }
              if (context.parsed.y !== null) {
                label += new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'VES' }).format(context.parsed.y);
              }
              return label;
            }
          }
        },
        annotation: {
          annotations: {
            line1: {
              type: 'line',
              yMin: 10, // valor en el que la línea estará posicionada
              yMax: 10, // se debe igualar a yMin para que sea una línea horizontal
              borderColor: 'rgba(255, 0, 0, 1)', // color de la línea
              borderWidth: 2,
              label: {
                enabled: true,
                content: 'Objetivo',
                position: 'end'
              }
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Monto de Venta (Bs)'
          }
        }
      }
    }
  });
   });
</script>


<!---HTML--->
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center mb-4">
<div>
<h2 class="mb-1"><i class="bi bi-speedometer2" style="object-fit: cover;"></i> Panel de Control (SGA)</h2>
<p class="text-muted mb-0">Bienvenido, <?php echo $_SESSION['username'] ?? 'Usuario'; ?></p>
</div>
<div class="text-end">
<small class="text-muted">Último acceso: <?php echo date('d/m/Y h:i'); ?></small>
</div>
</div>


<!----ACCIONES RAPIDAS---->
<div class="row mb-4">
    <div class="col-xl-6 col-lg-5 w-100">
        <div class="card shadow mb-4 h-auto  py-2">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="p-2">  <!-- Espaciado alrededor de los botones -->
                        <a href="menu.php?pagina=productos" class="btn btn-primary">
                            <i class="bi bi-box-seam"></i> Gestionar Productos
                        </a>
                    </div>
                    
                    <div class="p-2">
                        <a href="menu.php?pagina=lotes" class="btn btn-success">
                            <i class="bi bi-arrow-down-square"></i> Nueva Entrada/Ingreso
                        </a>
                    </div>
                    
                    <div class="p-2">
                        <a href="menu.php?pagina=ventas" class="btn btn-info">
                            <i class="bi bi-cart-plus"></i> Registrar Venta
                        </a>
                    </div>

                    <div class="p-2">
                        <a href="menu.php?pagina=proveedores" class="btn btn-warning">
                            <i class="bi bi-person-badge"></i> Gestionar Proveedores
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!--CARD ENDS HERE--->
    </div>
</div>
<!-----ACCIONES RAPIDAS ENDS HERE---------->










<!----ESTADISITCAS---->
<div class="row mb-4">
<div class="col-xl-3 col-md-6 mb-4">
<div class="card border-left-primary shadow h-100 py-2">
<div class="card-body">
<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Productos Activos</div>
<div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($productos)?></div>
</div>
</div>
</div>

<div class="col-xl-3 col-md-6 mb-4">
<div class="card border-left-success shadow h-100 py-2">
<div class="card-body">
<div class="text-xs font-weight-bold text-success text-uppercase mb-1">Valor Inventario (Venta)</div>
<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo 'Bs ' . number_format($valorLOTES, 2); ?></div>
</div>
</div>
</div>

<div class="col-xl-3 col-md-6 mb-4">
<div class="card border-left-info shadow h-100 py-2">
<div class="card-body">
<div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Ventas (Hoy)</div>
<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo '$' . number_format($ganancias['total_ganancia'], 2); ?></div>
</div>
</div>
</div>

<div class="col-xl-3 col-md-6 mb-4">
<div class="card border-left-danger shadow h-100 py-2">
<div class="card-body">
<div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Productos en Stock Crítico</div>
<div class="h5 mb-0 font-weight-bold text-gray-800"><?= $sumaStocksBajos ?></div>
</div>
</div>
</div>
</div>



<!---GRAFICO- col-xl-8 col-lg-7 ---->
<div class="row">
<div>
<div class="card shadow mb-4">
<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
<h6 class="m-0 font-weight-bold text-primary">Movimientos del Inventario (Últimas Semanas)</h6>
</div>
<div class="card-body">
<div class="chart-area">
<canvas id="movimientosChart" class="custom_grafica"></canvas> 
</div>
</div>
</div>
</div>
</div>
<!---GRAFICO ENDS HERE--->




<!---TOP 5 anomalias mas locas del mundo numero 5--->
<div class="row">


<!---TOP 5 desnutridos----->
<div class="col-lg-6 mb-4">
<div class="card shadow">
<div class="card-header py-3">
<h6 class="m-0 font-weight-bold text-danger">⚠️ Top 5 Productos con Stock Crítico</h6>
</div>
<div class="card-body">
<?php if($sumaStocksBajos > 4): ?>
<div class="table-responsive">

<!---TABLA--->
<table class="table table-sm table-hover">

<!------>
<thead>
<tr>
<th>Producto</th>
<th>Stock Actual</th>
<th>Mínimo</th>
<th>Faltante</th>
</tr>
</thead>
<!------>

<tbody>

<!------>
<?php foreach($productos as $n): ?>
<!------>
<tr>
<?php if($n['stock_actual'] === 0):?>
<td><?= $n['sku'] ?> - <?= $n['nombre'] ?> - <?= $n['marca'] ?></td>
<td class="text-danger fw-bold"><?= $n['stock_actual'] ?></td>
<td><?= $n['stock_minimo']?></td>
<td class="text-danger fw-bold"><?php 
$faltante = ($n['stock_actual'] - ($n['stock_minimo'] * 2)) / -1;
echo $faltante;
?> </td>
<?php endif;?>
</tr>

<!------>
<?php endforeach;?>
<!------>

</tbody>
</table>
<!------>


</div>
<a href="menu.php?pagina=productos" class="btn btn-outline-danger btn-sm">Ver todo el inventario</a>
<?php else: ?>
<p class="text-success">✅ Todo el stock está en niveles óptimos.</p>
<?php endif; ?>
</div>
</div>
</div>


<!---TOP 5 wins----->
<div class="col-lg-6 mb-4">
<div class="card shadow">
<div class="card-header py-3">
<h6 class="m-0 font-weight-bold text-primary">Últimas 5 Ventas</h6>
</div>
<div class="card-body">
<?php if ($ventas < 0): ?>
<div class="table-responsive">
<table class="table table-sm table-hover">
<thead>
<tr>
<th># Factura</th>
<th>Empleado</th>
<th>Total</th>
<th>Fecha</th>
</tr>
</thead>
<tbody>
<?php foreach($ventas as $n): ?>
<tr>
<td><?= $n['codigo_venta'] ?></td>
<td> <?= $n['CI']?> - <?= $n['cedula_empleado']?> - <?= $n['apellido'] ?></td>
<td class="fw-bold"><?= $n['moneda']?>. <?= number_format($n['total'], 2) ?></td>
<td><?= $n['fecha_ventaD'] ?>/<?= $n['fecha_ventaM'] ?>/<?= $n['fecha_ventaY'] ?> [<?= $n['fecha_ventaH'] ?>:<?= $n['fecha_ventaMi'] ?>]</td>
</tr>
<?php endforeach; ?>


</tbody>
</table>
</div>
<a href="menu.php?pagina=ventas" class="btn btn-outline-primary btn-sm">Ver todas las ventas</a>
<?php else: ?>
<p class="text-muted">No hay ventas registradas recientemente.</p>
<?php endif; ?>
</div>
</div>
</div>
</div>
</div>


