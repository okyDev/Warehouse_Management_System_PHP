<?php
// modulos/reportes/reportes.php
require_once('../config/connect.php');

//API
$base = new MYSQL;
$step_one = verify("gestionar_reportes");
$ProveCAT = $base->query_all('SELECT * FROM proveedor_categoria');
$empleado = $base->query_all("SELECT * FROM empleados_datos");
$coins = $base->query_all("SELECT * FROM monedas");

// Parámetros para filtros
$fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
$tipo_reporte = $_POST['tipo_reporte'] ?? null;
$filtro = $_POST['opcionesFILTRAR'] ?? "";
$reporte_data = [];
///$variables = [];

// Inicializar variables
$estadisticas = [
'total_productos' => 0,
'total_proveedores' => 0,
'total_ventas' => 0,
'total_ingresos' => 0
];

///$variables = [];

if($_POST && $tipo_reporte){
try{
//// VARS
$ap = $_POST['lastname'] ?? "nada";
$CI = $_POST['numerodedocumento'] ?? null;
$Empleado = $_POST['empleadoID'] ?? null;
$empleadolastname = $_POST[''] ?? null;

$coin = $_POST['onecoinchoosed'] ?? null;


$monebegin = $_POST['dinero_inicial'] ?? null;
$monebegincoin = $_POST['dinero_intervalo'] ?? null;
$moneend = $_POST['dinero_final'] ?? null;


$monedaU = $_POST['coinnunico'] ?? null;
$dineroU = $_POST['dinerounico'] ?? null;
$fechaunica = $_POST['fecha_unica'] ?? null;


// Convertir fechas a formato de tu BD
$inicioD = date('d', strtotime($fecha_inicio));
$inicioM = date('m', strtotime($fecha_inicio));
$inicioY = date('Y', strtotime($fecha_inicio));

$diaU = date('d', strtotime($fechaunica));
$mesU = date('m', strtotime($fechaunica));
$yearU = date('Y', strtotime($fechaunica));

$finD = date('d', strtotime($fecha_fin));
$finM = date('m', strtotime($fecha_fin));
$finY = date('Y', strtotime($fecha_fin));



// Reporte de Ventas
if ($tipo_reporte === "Ventas") {
///FILTRO FECHA 1
if($filtro === 'opcionf1'){
//fecha_unica
$sql_ventas = "SELECT V.*, E.nombre1, E.apellido, M.codigo as moneda 
				  FROM ventas V 
				  JOIN empleados_datos E ON V.cedula_empleado = E.cedula 
				  JOIN monedas M ON V.moneda_id = M.id 
				  WHERE V.activo = 1 
				  AND V.fecha_ventaY = ? AND V.fecha_ventaM = ? AND V.fecha_ventaD = ? DESC";

	$params = [
		$diaU, $mesU, $yearU
	];
	
	$reporte_data = $base->some_query($sql_ventas, $params);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
	
///$variables[] = $filtro,$diaU, $mesU, $yearU;

}


///FILTRO FECHA 2
elseif($filtro === 'opcionf2'){
$sql_ventas = "SELECT V.*, E.nombre1, E.apellido, M.codigo as moneda 
				  FROM ventas V 
				  JOIN empleados_datos E ON V.cedula_empleado = E.cedula 
				  JOIN monedas M ON V.moneda_id = M.id 
				  WHERE V.activo = 1 
				  AND ((V.fecha_ventaY > ?) OR (V.fecha_ventaY = ? AND V.fecha_ventaM > ?) 
				  OR (V.fecha_ventaY = ? AND V.fecha_ventaM = ? AND V.fecha_ventaD >= ?))
				  AND ((V.fecha_ventaY < ?) OR (V.fecha_ventaY = ? AND V.fecha_ventaM < ?) 
				  OR (V.fecha_ventaY = ? AND V.fecha_ventaM = ? AND V.fecha_ventaD <= ?))
				  ORDER BY V.fecha_ventaY DESC, V.fecha_ventaM DESC, V.fecha_ventaD DESC";

	$params = [
		$inicioY, $inicioY, $inicioM, $inicioY, $inicioM, $inicioD,
		$finY, $finY, $finM, $finY, $finM, $finD
	];
	
	$reporte_data = $base->some_query($sql_ventas, $params);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
} 

///FILTRO dinero 1
elseif($filtro === 'opcionf3'){
$sql_ventas = "SELECT * FROM ventas WHERE activo = 1 AND total = ? AND moneda_id = ?";

$reporte_data = $base->some_query($sql_ventas,[$dineroU, $monedaU]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
	
	
}

///FILTRO dinero 2
elseif($filtro === 'opcionf4'){
$sql_ventas = "SELECT * FROM ventas WHERE activo = TRUE AND (total >= ?) AND (total <= ?) AND (moneda_id = ?) ORDER BY total, moneda_id DESC";
$reporte_data = $base->some_query($sql_ventas, [$monebegin, $moneend, $monebegincoin]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
	
	
}


///Filtrar por tipo de moneda registrado
elseif($filtro === 'opcionf5'){
$sql_ventas = "SELECT * FROM ventas WHERE activo = TRUE AND moneda_id = ?";
$reporte_data = $base->some_query($sql_ventas, [$coin]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
}

///Filtrar por Empleado
elseif($filtro === 'opcionf6'){
$sql_ventas = "SELECT * FROM ventas WHERE activo = TRUE AND cedula_empleado = ?";
$reporte_data = $base->some_query($sql_ventas, [$Empleado]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
}


///Filtrar por Numero de Documento
elseif($filtro === 'opcionf7'){
$sql_ventas = "SELECT * FROM ventas WHERE activo = TRUE AND cedula_empleado = ?";
$reporte_data = $base->some_query($sql_ventas, [$CI]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}

}

/////Filtrar por Apellido
elseif($filtro === 'opcionf8'){
$sql_ventas = "SELECT V*, E.apellido as, E.nombre1 FROM ventas V JOIN empleados_datos E ON E.cedula = V.cedula_empleado WHERE activo = TRUE AND E.apellido = ?";
$reporte_data = $base->some_query($sql_ventas, [$empleadolastname]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}

}


////FILTRAR POR REGISTRO
elseif($filtro === 'opcionf9'){

}
///TODO
else{
$reporte_data = $base->query_all("SELECT * FROM ventas WHERE activo = 1");
}         

}




// Reporte de Productos
elseif ($tipo_reporte === 'Productos') {
if($filtro === 'opcionf1'){
	$sql_productos = "SELECT * FROM productos 
					 WHERE activo = 1  
					 AND ((fecha_inY > ?) OR (fecha_inY = ? AND fecha_inM > ?) 
					 OR (fecha_inY = ? AND fecha_inM = ? AND fecha_inD >= ?)) 
					 AND ((fecha_inY < ?) OR (fecha_inY = ? AND fecha_inM < ?) 
					 OR (fecha_inY = ? AND fecha_inM = ? AND fecha_inD <= ?)) 
					 ORDER BY fecha_inY DESC, fecha_inM DESC, fecha_inD DESC";

	$params = [
		$inicioY, $inicioY, $inicioM, $inicioY, $inicioM, $inicioD,
		$finY, $finY, $finM, $finY, $finM, $finD
	];
	
	$reporte_data = $base->some_query($sql_productos, $params);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
}


///FILTRO FECHA 2
elseif($filtro === 'opcionf2'){
$sql_ventas = "SELECT V.*, E.nombre1, E.apellido, M.codigo as moneda FROM productos V 
JOIN monedas M ON V.moneda_venta_id = M.id WHERE V.activo = 1 AND ((V.fecha_inY > ?) OR (V.fecha_inY = ? AND V.fecha_inM > ?) OR (V.fecha_inY = ? AND V.fecha_inM = ? AND V.fecha_inD >= ?)) AND ((V.fecha_inY < ?) OR (V.fecha_inY = ? AND V.fecha_inM < ?) OR (V.fecha_inY = ? AND V.fecha_inM = ? AND V.fecha_inD <= ?)) ORDER BY V.fecha_inY DESC, V.fecha_inM DESC, V.fecha_inD DESC";

	
$reporte_data = $base->some_query($sql_ventas, [$inicioY, $inicioY, $inicioM, $inicioY, $inicioM, $inicioD, $finY, $finY, $finM, $finY, $finM, $finD]);
if ($reporte_data === false) {
		$reporte_data = [];
	}
}

///FILTRO dinero 1
elseif($filtro === 'opcionf3'){
$sql_ventas = "SELECT p.*, m.codigo as moneda FROM productos p JOIN monedas m ON p.moneda_venta_id = m.id WHERE p.activo = 1 AND p.precio_venta = ? AND p.moneda_venta_id = ?";

$reporte_data = $base->some_query($sql_ventas,[$dineroU, $monedaU]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
	
	
}

///FILTRO dinero 2
elseif($filtro === 'opcionf4'){
$sql_ventas = "SELECT * FROM productos WHERE activo = TRUE AND (precio_venta >= ?) AND (precio_venta <= ?) AND (moneda_venta_id = ?) ORDER BY precio_venta, moneda_venta_id DESC";
$reporte_data = $base->some_query($sql_ventas, [$monebegin, $moneend, $monebegincoin]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
	
	
}


///Filtrar por tipo de moneda registrado
elseif($filtro === 'opcionf5'){
$sql_ventas = "SELECT * FROM productos WHERE activo = TRUE AND moneda_venta_id = ?";
$reporte_data = $base->some_query($sql_ventas, [$coin]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
}

///Filtrar por Empleado
elseif($filtro === 'opcionf6'){
$reporte_data = [];
}


///Filtrar por Numero de Documento
elseif($filtro === 'opcionf7'){
$reporte_data = [];

}

/////Filtrar por Apellido
elseif($filtro === 'opcionf8'){
$reporte_data = [];
}

elseif($filtro === 'opcionf9'){
$reporte_data = $base->query_all("SELECT * FROM cambios_productos");

}



else{
$reporte_data = $base->query_all("SELECT * FROM productos WHERE activo = 1");
}            
}


// Reporte de LOTES
elseif ($tipo_reporte === 'Lotes') {
///FILTRO FECHA 1
if($filtro === 'opcionf1'){
//fecha_unica
$sql_ventas = "SELECT V.*, E.nombre1, E.apellido, M.codigo as moneda 
				  FROM lotes V 
				  JOIN empleados_datos E ON V.proveedor_id = E.cedula 
				  JOIN monedas M ON V.moneda_compra_id = M.id 
				  WHERE V.activo = 1 
				  AND V.fecha_entrada_d = ? AND V.fecha_entrada_m = ? AND V.fecha_entrada_y = ?";

$params = [
	$diaU, $mesU, $yearU
];

	
	$reporte_data = $base->some_query($sql_ventas, $params);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
	
	
}


///FILTRO FECHA 2
elseif($filtro === 'opcionf2'){
$sql_ventas = "SELECT V.*, E.nombre1, E.apellido, M.codigo AS moneda 
				  FROM ventas V 
				  JOIN empleados_datos E ON V.cedula_empleado = E.cedula 
				  JOIN monedas M ON V.moneda_id = M.id 
WHERE V.activo = 1 AND ((V.fecha_ventaY > ?) OR (V.fecha_ventaY = ? AND V.fecha_ventaM > ?) OR 
					   (V.fecha_ventaY = ? AND V.fecha_ventaM = ? AND V.fecha_ventaD >= ?))
				  AND ((V.fecha_ventaY < ?) OR 
					   (V.fecha_ventaY = ? AND V.fecha_ventaM < ?) OR 
					   (V.fecha_ventaY = ? AND V.fecha_ventaM = ? AND V.fecha_ventaD <= ?))
				  ORDER BY V.fecha_ventaY DESC, V.fecha_ventaM DESC, V.fecha_ventaD DESC";

$params = [
	$inicioY, $inicioY, $inicioM, $inicioY, $inicioM, $inicioD,
	$finY, $finY, $finM, $finY, $finM, $finD
];

	
	$reporte_data = $base->some_query($sql_ventas, $params);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
} 

///FILTRO dinero 1
elseif($filtro === 'opcionf3'){
$sql_ventas = "SELECT * FROM lotes WHERE activo = 1 AND costo_unitario = ? AND moneda_compra_id = ?";

$reporte_data = $base->some_query($sql_ventas,[$dineroU, $monedaU]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
	
	
}

///FILTRO dinero 2
elseif($filtro === 'opcionf4'){
$sql_ventas = "SELECT * FROM lotes WHERE activo = TRUE AND (costo_unitario >= ?) AND (costo_unitario <= ?) AND (moneda_compra_id = ?) ORDER BY costo_unitario, moneda_compra_id DESC";
$reporte_data = $base->some_query($sql_ventas, [$monebegin, $moneend, $monebegincoin]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
	
	
}


///Filtrar por tipo de moneda registrado
elseif($filtro === 'opcionf5'){
$sql_ventas = "SELECT * FROM lotes WHERE activo = TRUE AND moneda_compra_id = ?";
$reporte_data = $base->some_query($sql_ventas, [$coin]);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
}

///Filtrar por Empleado
elseif($filtro === 'opcionf6'){
$reporte_data = [];
}


///Filtrar por Numero de Documento
elseif($filtro === 'opcionf7'){
$reporte_data = [];
}

/////Filtrar por Apellido
elseif($filtro === 'opcionf8'){
$reporte_data = [];
}

elseif($filtro === 'opcionf9'){
	$reporte_data = $base->query_all("SELECT * FROM cambios_productos");
}

///TODO
else {
	$sql_lotes = "SELECT * FROM lotes WHERE activo = 1";
	$reporte_data = $base->query_all($sql_lotes);
	if ($reporte_data === false) {
		$reporte_data = [];
	}
}  
}



// Reporte de STOCKS
elseif ($tipo_reporte === 'Stock') {
if($filtro === 'opcionf1' || $filtro === 'opcionf2' || $filtro === 'opcionf3' || $filtro === 'opcionf4' || $filtro === 'opcionf5' || $filtro === 'opcionf6' || $filtro === 'opcionf7' || $filtro === 'opcionf8'){
	$reporte_data = [];
}
else{
$sql_stock = "SELECT sku, nombre, precio_venta, stock_actual, stock_minimo,
			 CASE 
				WHEN stock_actual = 0 THEN 'AGOTADO'
				WHEN stock_actual <= stock_minimo THEN 'BAJO'
				ELSE 'NORMAL'
			 END as estado_stock
			 FROM productos 
			 WHERE activo = 1
			 ORDER BY stock_actual ASC, nombre ASC";

$reporte_data = $base->query_all($sql_stock);
if ($reporte_data === false) {
	$reporte_data = [];
}
}

}



// Reporte de USUARIOS
elseif ($tipo_reporte === 'Usuarios') {
	
if($filtro === 'opcionf1' || $filtro === 'opcionf2' || $filtro === 'opcionf3' || $filtro === 'opcionf4' || $filtro === 'opcionf5'){
$reporte_data = [];
}



///Filtrar por Empleado
elseif($filtro === 'opcionf6'){
$sql_usuarios = "SELECT U.*,E.nombre1, E.apellido FROM usuarios_acceso U JOIN empleados_datos E ON U.cedula_empleado = E.cedula  WHERE U.activo = 1 AND U.cedula_empleado = ?";
$reporte_data = $base->some_query($sql_usuarios,[$Empleado]);
if ($reporte_data === false) {
	$reporte_data = [];
}
}


///Filtrar por Numero de Documento
elseif($filtro === 'opcionf7'){
$sql_usuarios = "SELECT U.*,E.nombre1, E.apellido FROM usuarios_acceso U JOIN empleados_datos E ON U.cedula_empleado = E.cedula  WHERE U.activo = 1 AND U.cedula_empleado = ?";
$reporte_data = $base->some_query($sql_usuarios,[$Empleado]);
if ($reporte_data === false) {
	$reporte_data = [];
}

}

/////Filtrar por Apellido
elseif($filtro === 'opcionf8'){
$sql_usuarios = "SELECT U.*,E.nombre1, E.apellido FROM usuarios_acceso U JOIN empleados_datos E ON U.cedula_empleado = E.cedula  WHERE U.activo = 1 AND E.apellido = ?";
$reporte_data = $base->some_query($sql_usuarios,[$ap]);
if ($reporte_data === false) {
	$reporte_data = [];
}
}

else{
$sql_usuarios = "SELECT * FROM usuarios_acceso WHERE activo = 1";
$reporte_data = $base->query_all($sql_usuarios);
if ($reporte_data === false) {
	$reporte_data = [];
}
}
}


// Reporte de CATEGORIAS
elseif ($tipo_reporte === 'Categorias') {
if($filtro === 'opcionf1' || $filtro === 'opcionf2' || $filtro === 'opcionf3' || $filtro === 'opcionf4' || $filtro === 'opcionf5' || $filtro === 'opcionf6' || $filtro === 'opcionf7' || $filtro === 'opcionf8'){
	$reporte_data = [];
}
else{
$sql_categorias = "SELECT * FROM categorias WHERE activo = 1";
$reporte_data = $base->query_all($sql_categorias);
if ($reporte_data === false) {
	$reporte_data = [];
}
}
}


// Reporte de Empleados
elseif ($tipo_reporte === 'Empleados') {

if($filtro === 'opcionf1' || $filtro === 'opcionf2' || $filtro === 'opcionf3' || $filtro === 'opcionf4' || $filtro === 'opcionf5'){
$reporte_data = [];
}



///Filtrar por Empleado
elseif($filtro === 'opcionf6'){
$sql_usuarios = "SELECT * FROM empleados_datos WHERE cedula = ?";
$reporte_data = $base->some_query($sql_usuarios,[$Empleado]);
if ($reporte_data === false) {
	$reporte_data = [];
}
}


///Filtrar por Numero de Documento
elseif($filtro === 'opcionf7'){
$sql_usuarios = "SELECT * FROM empleados_datos WHERE cedula = ?";
$reporte_data = $base->some_query($sql_usuarios,[$Empleado]);
if ($reporte_data === false) {
	$reporte_data = [];
}

}

/////Filtrar por Apellido
elseif($filtro === 'opcionf8'){
$sql_usuarios = "SELECT * FROM empleados_datos WHERE apellido = ?";
$reporte_data = $base->some_query($sql_usuarios,[$ap]);
if ($reporte_data === false) {
	$reporte_data = [];
}
}

elseif($filtro === 'opcionf10'){
$idem = $_POST['idE'];
$reporte_data = $base->some_query("SELECT * FROM logs WHERE id_usuario = ?",[$idem]);
}

else{
$sql_empleados = "SELECT * FROM empleados_datos";
$reporte_data = $base->query_all($sql_empleados);
if ($reporte_data === false) {
	$reporte_data = [];
}
}
}



// Reporte de Proveedores
elseif ($tipo_reporte === 'Proveedores') {

if($filtro === 'opcionf1'){
$reporte_data = [];
}


///FILTRO FECHA 2
elseif($filtro === 'opcionf2'){
$reporte_data = [];
} 

///FILTRO dinero 1
elseif($filtro === 'opcionf3'){
$reporte_data = [];
}

///FILTRO dinero 2
elseif($filtro === 'opcionf4'){
$reporte_data = [];
}


///Filtrar por tipo de moneda registrado
elseif($filtro === 'opcionf5'){
$reporte_data = [];
}

///Filtrar por Empleado
elseif($filtro === 'opcionf6'){
$reporte_data = [];
}


///Filtrar por Numero de Documento
elseif($filtro === 'opcionf7'){
$sql_proveedores = "SELECT * FROM proveedores WHERE activo = 1 AND (id = ? OR rif_empresa_proveedora = ?)";
$reporte_data = $base->some_query($sql_proveedores,[$CI]);
if ($reporte_data === false) {
	$reporte_data = [];
}
}

/////Filtrar por Apellido
elseif($filtro === 'opcionf8'){
$reporte_data = [];
}

///TODO
else {
$sql_proveedores = "SELECT * FROM proveedores WHERE activo = 1";
$reporte_data = $base->query_all($sql_proveedores);
if ($reporte_data === false) {
	$reporte_data = [];
}
}
}
//// ALL ENDS HERE


// Estadísticas generales
$sql_estadisticas = "SELECT 
			(SELECT COUNT(*) FROM productos WHERE activo = 1) as total_productos,
			(SELECT COUNT(*) FROM proveedores WHERE activo = 1) as total_proveedores,
			(SELECT COUNT(*) FROM ventas WHERE activo = 1) as total_ventas,
			(SELECT COALESCE(SUM(total), 0) FROM ventas WHERE activo = 1) as total_ingresos";


$result_estadisticas = $base->query_simple($sql_estadisticas);
if ($result_estadisticas !== false) {
$estadisticas = $result_estadisticas;
}

} 

catch(Exception $e) {
error_log("Error en reportes.php: " . $e->getMessage());
echo "<div class='alert alert-danger'>Error al generar el reporte: " . $e->getMessage() . "</div>";
}
}

// Función para contar ventas de proveedores
function contarVentas($wvar, $productoId) {
$total = 0;
if (is_array($wvar)) {
foreach ($wvar as $venta) {
if (isset($venta['proveedor_id']) && $venta['proveedor_id'] == $productoId) {
	$total++;
}
}
}
return $total;
}



if(!verify("gestionar_reportes")){
salirSESSION();
}

?>

<style>
/* (Tu CSS permanece igual) */
.btn:disabled {
background-color: #6c757d;
border-color: #6c757d;
opacity: 0.65;
cursor: not-allowed;
}

.card-header.bg-primary {
background-color: #2c7be5 !important;
}

.card.text-white.bg-info {
background-color: #00d97e !important;
}

.card.text-white.bg-success {
background-color: #2c7be5 !important;
}

.card.text-white.bg-warning {
background-color: #f6c343 !important;
}

.card.text-white.bg-primary {
background-color: #6e84a3 !important;
}

.card-header.bg-dark {
background-color: #1e2a38 !important;
}

.btn-primary {
background-color: #2c7be5;
border-color: #2c7be5;
}

.btn-success {
background-color: #00d97e;
border-color: #00d97e;
}

.badge.bg-light {
background-color: #f9fafd !important;
color: #1e2a38 !important;
}

.table-hover tbody tr:hover {
background-color: rgba(44, 123, 229, 0.05);
}

.form-check-input:checked {
background-color: #2c7be5;
border-color: #2c7be5;
}

.form-group {
margin-bottom: 1rem;
}

#opcionesHUB {
margin-top: 1rem;
padding: 1rem;
border-radius: 0.375rem;
background-color: #f9fafd;
border: 1px solid #e3ebf6;
}
</style>

<div class="container-fluid">
<h3 class="mb-4"><i class="bi bi-graph-up"></i> Sistema de Reportes</h3>

<!-- Filtros1 -->
<div class="card mb-4 shadow-sm">
<div class="card-header bg-primary text-white">
<h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros del Reporte</h5>
</div>

<div class="card-body">
<form method="POST" class="row g-3">
<div class="col-md-3">
<label class="form-label">Tipo de Reporte</label>
<select name="tipo_reporte" class="form-select" required id="tipo_del_reporte">
<option value="">Seleccione un reporte</option>
<option value="Ventas" <?= $tipo_reporte === 'Ventas' ? 'selected' : '' ?>>Ventas</option>
<option value="Productos" <?= $tipo_reporte === 'Productos' ? 'selected' : '' ?>>Productos</option>
<option value="Lotes" <?= $tipo_reporte === 'Lotes' ? 'selected' : '' ?>>Lotes</option>
<option value="Stock" <?= $tipo_reporte === 'Stock' ? 'selected' : '' ?>>Stock</option>
<option value="Usuarios" <?= $tipo_reporte === 'Usuarios' ? 'selected' : '' ?>>Usuarios</option>
<option value="Categorias" <?= $tipo_reporte === 'Categorias' ? 'selected' : '' ?>>Categorias</option>
<option value="Empleados" <?= $tipo_reporte === 'Empleados' ? 'selected' : '' ?>>Empleados</option>
<option value="Proveedores" <?= $tipo_reporte === 'Proveedores' ? 'selected' : '' ?>>Proveedores</option>
</select>
	</div>

	<div class="col-md-6">
		<div class="form-check form-switch mt-4">
			<input class="form-check-input" type="checkbox" name="desfecha" id="desfecha">
			<label class="form-check-label" for="desfecha">Filtrar por fechas</label>
		</div>
<!-- Filtros1 ends here -->                    

<!-- Filtros2 -->
<div id="opcionesHUB" class="mt-3">
<!-- Tu HTML para opciones de fecha permanece igual -->
<select id="opcionesFILTRAR" name="opcionesFILTRAR" onchange="mostrarDiv()">
<option value="">Ninguno</option>
<!------>
<option value="opcionf1" id="divopcion1" >Filtrar por fecha</option>
<option value="opcionf2" id="divopcion2">Filtrar por intervalo de fechas</option>
<!------>

<!------>
<option value="opcionf3" id="divopcion3" >Filtrar por sifra monetaria</option>
<option value="opcionf4" id="divopcion4" >Filtrar por intervalo monetario</option>
<option value="opcionf5" id="divopcion5" >Filtrar por tipo de moneda registrado</option>


<!------>
<option value="opcionf6" id="divopcion6" >Filtrar por Empleado</option>
<option value="opcionf7" id="divopcion7" >Filtrar por Numero de Documento</option>
<option value="opcionf8" id="divopcion8" >Filtrar por Apellido</option>
<option value="opcionf9" id="divopcion9" >Mostrar Registro</option>
<option value="opcionf10" id="divopcion10">Mostrar Registro por Usuario</option>
<!------>
</select>



<!---FECHA1--->
<div class="row mb-4" id="opcionf1" style="display:none;">
<div class="col-md-12">
<label class="form-label fw-bold text-primary">Buscar por fecha única</label>
<input type="date" name="fecha_unica" id="n1FECHA" class="form-control"
value="<?= $fecha_inicio ?>">
</div>
</div>
<!--FEHA2---->
<div class="row mb-4" id="opcionf2" style="display:none;">
<div class="col-12">
<label class="form-label fw-bold text-primary">Buscar por intervalo de fechas</label>
</div>
<div class="col-md-6">
<label class="form-label small">Fecha Inicio</label>
<input type="date" name="fecha_inicio" id="n1FECHA" class="form-control"
value="<?= $fecha_inicio ?>">
</div>
<div class="col-md-6">
<label class="form-label small">Fecha Fin</label>
<input type="date" name="fecha_fin" id="n2FECHA" class="form-control"
value="<?= $fecha_fin ?>">
</div>
</div>



<!---MONEY1--->
<div  class="row mb-4" id="opcionf3" style="display:none;">
<div class="col-12">
<label class="form-label fw-bold text-primary">Filtrar por sifra monetaria</label>
</div>
<div class="col-md-6 d-flex align-items-center">
<label class="form-label me-2 mb-0">Monto</label>
<select width="5px" name="coinnunico">
<option value="">Eliga uno</option>
<?php foreach($coins as $c):?>
<option value="<?= $c['id']?>"><?= $c['simbolo']?></option>
<?php endforeach;?>
</select>
<input type="text" name="dinerounico" class="form-control spinner"
placeholder="32.456,45">
</div>
</div>


<!---MONEY2--->
<div class="row mb-4" id="opcionf4" style="display:none;">
<div class="col-12">
<label class="form-label fw-bold text-primary">Intervalo monetario</label>
</div>
<div class="col-md-6 d-flex align-items-center">
<label class="form-label me-2 mb-0">Inicial</label>
<select width="5px" name="dinero_intervalo">
<option value="">Eliga uno</option>
<?php foreach($coins as $c):?>
<option value="<?= $c['id']?>"><?= $c['simbolo']?></option>
<?php endforeach;?>
</select>
<input type="text" name="dinero_inicial" class="form-control spinner"
placeholder="32.456,45">
</div>

<div class="col-md-6 d-flex align-items-center">
<label class="form-label me-2 mb-0">Final</label>
<select width="5px">
<option value="">Eliga uno</option>
<?php foreach($coins as $c):?>
<option value="<?= $c['id']?>"><?= $c['simbolo']?></option>
<?php endforeach;?>
</select>
<input type="text" name="dinero_final" class="form-control spinner"
placeholder="32.456,45">
</div>
</div>



<!--COINS---->
<div  class="row mb-4" id="opcionf5" style="display:none;">
<div class="col-12">
<label class="form-label fw-bold text-primary">Filtrar por tipo de moneda</label>
</div>
<div class="col-md-6 d-flex align-items-center">
<label class="form-label me-2 mb-0">Moneda</label>
<select class="form-select" name="onecoinchoosed">
<option value="">Eliga uno</option>
<?php foreach($coins as $c):?>
<option value="<?= $c['id']?>"><?= $c['simbolo']?></option>
<?php endforeach;?>
</select>
</div>
</div>

<!------>
<div  class="row mb-4" id="opcionf6" style="display:none;">
<div class="col-12">
<label class="form-label fw-bold text-primary">Filtrar por Empleado</label>
</div>
<label class="form-label me-2 mb-0">Ingrese un Empleado</label>
<div class="col-md-6 d-flex align-items-center">
<select name="empleadoID" class="form-select">
<option value="">Eliga uno</option>
<?php foreach($empleado as $N):?>
<option value="<?= $N['cedula'] ?>"><?= $N['nombre1'] ?> <?= $N['apellido']?></option>
<?php endforeach;?>
</select>
</div>
</div>

<!------>
<div  class="row mb-4" id="opcionf7" style="display:none;">
<div class="col-12">
<label class="form-label fw-bold text-primary">Filtrar por Numero de documento</label>
</div>
<label class="form-label me-2 mb-0">Ingrese un Empleado</label>
<div class="col-md-6 d-flex align-items-center">
<input type="number" name="numerodedocumento" class="form-input">
</div>
</div>

<!------>
<div  class="row mb-4" id="opcionf8" style="display:none;">
<div class="col-12">
<label class="form-label fw-bold text-primary">Filtrar por Empleado</label>
</div>
<label class="form-label me-2 mb-0">Ingrese un Empleado</label>
<div class="col-md-6 d-flex align-items-center">
<input type="text" class="form-input" name="lastname">
</div>
</div>



<div  class="row mb-4" id="opcionf10" style="display:none;">
<div class="col-12">
<label class="form-label fw-bold text-primary">Filtrar por Empleado</label>
</div>
<label class="form-label me-2 mb-0">Ingrese un Empleado</label>
<div class="col-md-6 d-flex align-items-center">
<select name="idE" id="idE">
<?php foreach($empleado as $n):?> 
<option value="<?= $n['cedula']?>"> <?= $n['nombre1']?> <?= $n['apellido']?></option>
<?php endforeach;?>
</select>
</div>
</div>
<!-- Filtros2 ENS HERE -->                    
</div>

		
</div>

<div class="col-md-3 d-flex align-items-end">
<div class="d-grid gap-2 w-100">
<button type="submit" class="btn btn-primary">
<i class="bi bi-search"></i> Generar Reporte
</button>

<?php if($tipo_reporte === null): ?>
<span></span>
<?php else: ?>
<button type="button" class="btn btn-success" name="generarPDF" onclick="exportarPDF()">
<i class="bi bi-file-pdf"></i> Exportar PDF
</button>
<?php endif; ?>
		</div>
	</div>
</form>
</div>
</div>

<!-- Estadísticas Rápidas -->
<div class="row mb-4">
<div class="col-md-3">
<div class="card text-white bg-info shadow-sm">
	<div class="card-body text-center">
		<h4 class="card-title"><?= $estadisticas['total_productos'] ?></h4>
		<p class="card-text">Productos</p>
	</div>
</div>
</div>
<div class="col-md-3">
<div class="card text-white bg-success shadow-sm">
	<div class="card-body text-center">
		<h4 class="card-title"><?= $estadisticas['total_ventas'] ?></h4>
		<p class="card-text">Ventas Totales</p>
	</div>
</div>
</div>
<div class="col-md-3">
<div class="card text-white bg-warning shadow-sm">
	<div class="card-body text-center">
		<h4 class="card-title"><?= number_format($estadisticas['total_ingresos'], 2) ?></h4>
		<p class="card-text">Ingresos Totales</p>
	</div>
</div>
</div>
<div class="col-md-3">
<div class="card text-white bg-primary shadow-sm">
	<div class="card-body text-center">
		<h4 class="card-title"><?= $estadisticas['total_proveedores'] ?></h4>
		<p class="card-text">Proveedores</p>
	</div>
</div>
</div>
</div>

<!-- Reporte -->
<?php if ($tipo_reporte !== null): ?>
<div class="card shadow-sm">
<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
<h5 class="mb-0">
	<i class="bi bi-table"></i> 
	Reporte de <?= $tipo_reporte ?>
</h5>
<span class="badge bg-light text-dark">
	<?= count($reporte_data) ?> registros encontrados
</span>
</div>

<div class="card-body">
<?php if (!empty($reporte_data)): ?>
<div class="table-responsive">
	<table class="table table-striped table-hover" id="tablaReporte">
		<thead>
			<?php if ($tipo_reporte === 'Ventas'): ?>
			<tr>
				<th>Código Venta</th>
				<th>Fecha</th>
				<th>Empleado</th>
				<th>Cliente</th>
				<th>Total</th>
				<th>Descuento</th>
				<th>Método Pago</th>
			</tr>
			<?php elseif ($tipo_reporte === 'Lotes'): ?>
			<tr>
				<th>ID</th>
				<th>Cantidad</th>
				<th>Precio Compra</th>
				<th>Producto SKU</th>
				<th>Fecha Ingreso</th>
			</tr>
			<?php elseif ($tipo_reporte === 'Stock'): ?>
			<tr>
				<th>Producto</th>
				<th>SKU</th>
				<th>Precio</th>
				<th>Stock Actual</th>
				<th>Stock Mínimo</th>
				<th>Estado</th>
			</tr>
			<?php elseif ($tipo_reporte === 'Usuarios'): ?>
			<tr>
				<th>Usuario</th>
				<th>Email</th>
				<th>Rol</th>
				<th>Estado</th>
			</tr>
			<?php elseif ($tipo_reporte === 'Categorias'): ?>
			<tr>
				<th>ID</th>
				<th>Nombre</th>
				<th>Descripción</th>
			</tr>
			<?php elseif ($tipo_reporte === 'Empleados'): ?>
			<?php if($filtro === 'opcionf10'):?>
			<th>CEDULA</th>
			<th>ACCION</th>
			<th>MODULO</th>
			<th>DETALLES</th>
			<th>Fecha</th>
			<?php else:?>
			<tr>
				<th>Cédula</th>
				<th>Nombre</th>
				<th>Apellido</th>
				<th>Teléfono</th>
			</tr>
			<?php endif;?>
			
			<?php elseif ($tipo_reporte === 'Proveedores'): ?>
			<tr>
				<th>RIF</th>
				<th>Nombre</th>
				<th>Teléfono</th>
				<th>Email</th>
				<th>Empresa</th>
				<th>Numero de suministros</th>
			</tr>	
			
			<?php elseif ($tipo_reporte === 'Productos'): ?>
			<?php if($filtro === 'opcionf9'):?>
			<th>Producto-Codigo</th>
				<th>Documento-Empleado</th>
				<th>Campo</th>
				<th>Valor_anterior</th>
				<th>Valor_nuevo</th>
				<th>Fecha</th>
			<?php else:?>
			<tr>
				<th>SKU</th>
				<th>Producto</th>
				<th>Marca</th>
				<th>Precio</th>
				<th>Stock</th>
			</tr>
			<?php endif;?>
			
			<?php endif; ?>
		</thead>
		<tbody>
<?php foreach ($reporte_data as $fila): ?>


<!-------->
<?php if ($tipo_reporte === 'Ventas'): ?>
<tr>
<td><?= $fila['codigo_venta'] ?></td>
<td><?= $fila['fecha_ventaD'] ?>/<?= $fila['fecha_ventaM'] ?>/<?= $fila['fecha_ventaY'] ?></td>
<td><?= $fila['nombre1'] ?> <?= $fila['apellido'] ?></td>
<td><?= $fila['cliente_documento'] ?: 'No especificado' ?></td>
<td><strong><?= number_format($fila['total'], 2) ?> <?= $fila['moneda'] ?></strong></td>
<td><?= number_format($fila['descuento'], 2) ?></td>
<td><?= $fila['metodo_pago'] ?></td>
</tr>
<!-------->			


<!-------->
<?php elseif ($tipo_reporte === 'Productos'): ?>
<tr>
<?php if($filtro === 'opcionf9'):?>
<td><?= $fila['producto_sku'] ?? 'nada'?></td>
<td><?= $fila['cedula_empleado'] ?? 'nada'?></td>
<td><?= $fila['campo_modificado'] ?? 'nada'?></td>
<td><?= $fila['valor_anterior'] ?? 'nada'?></td>
<td><?= $fila['valor_nuevo'] ?? 'nada'?></td>
<td><?= $fila['fecha_upD'] ?? 'nada'?>/<?= $fila['fecha_upM']?>/<?= $fila['fecha_upD']?> [<?= $fila['fecha_upH']?>:<?= $fila['fecha_upMI']?>]</td>
</tr>
<?php else:?>
<tr>
<td><?= $fila['sku'] ?? 'nada' ?></td>
<td><?= htmlspecialchars($fila['nombre'])  ?? 'nada'?></td>
<td><span class="badge bg-primary"><?= $fila['marca']  ?? 'nada'?></span></td>
<td><?= number_format($fila['precio_venta'], 2) ?></td>
<td><strong><?= $fila['stock_actual']  ?? 'nada'?></strong></td>
</tr>
<?php endif;?>
<!-------->


<!-------->
<?php elseif ($tipo_reporte === 'Lotes'): ?>
<tr>
<td><?= $fila['lote_sku'] ?></td>
<td><?= $fila['stock_actual'] ?></td>
<td><?= number_format($fila['costo_unitario'], 2) ?></td>
<td><?= $fila['producto_sku'] ?></td>
<td><?= $fila['fecha_entrada_d'] ?>/<?= $fila['fecha_entrada_m'] ?>/<?= $fila['fecha_entrada_y'] ?></td>
</tr>
<!-------->




<!-------->
<?php elseif ($tipo_reporte === 'Stock'): ?>
<tr>
<td><?= htmlspecialchars($fila['nombre']) ?></td>
<td><?= $fila['sku'] ?></td>
<td><?= number_format($fila['precio_venta'], 2) ?></td>
<td><?= $fila['stock_actual'] ?></td>
<td><?= $fila['stock_minimo'] ?></td>
<td>
<span class="badge <?= 
$fila['estado_stock'] === 'AGOTADO' ? 'bg-danger' : 
($fila['estado_stock'] === 'BAJO' ? 'bg-warning' : 'bg-success') 
?>">
<?= $fila['estado_stock'] ?>
</span>
</td>
</tr>
<!-------->


<!-------->

<?php elseif ($tipo_reporte === 'Usuarios'): ?>
<tr>
<td><?= $fila['username'] ?></td>
<td><?= $fila['email'] ?></td>
<td><?= $fila['rol'] ?></td>
<td><span class="badge <?= $fila['activo'] ? 'bg-success' : 'bg-danger' ?>"><?= $fila['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
</tr>
<!-------->


<?php elseif ($tipo_reporte === 'Categorias'): ?>
<tr>
<td><?= $fila['id'] ?></td>
<td><?= htmlspecialchars($fila['nombre']) ?></td>
<td><?= htmlspecialchars($fila['descripcion']) ?></td>
</tr>
<!-------->



<!-------->
<?php elseif ($tipo_reporte === 'Empleados'): ?>
<?php if($filtro === 'opcionf10'):?>
<?php if($fila['accion'] === 'Intento de acceso a contenido no permito'):?>
<tr>
<td style="color: red;"><?= $fila['id_usuario']?></td>
<td style="color: red;"><?= $fila['accion']?></td>
<td style="color: red;"><?= $fila['modulo']?></td>
<td style="color: red;"><?= $fila['detalles']?></td>
<td style="color: red;"><?= $fila['fechad']?>/<?= $fila['fecham']?>/<?= $fila['fechay']?> [<?= $fila['fechah']?>:<?= $fila['fechami']?> <?= $fila['fechaAMPM']?>]</td>
</tr>
<?php else:?>
<tr>
<td><?= $fila['id_usuario']?></td>
<td><?= $fila['accion']?></td>
<td><?= $fila['modulo']?></td>
<td><?= $fila['detalles']?></td>
<td><?= $fila['fechad']?>/<?= $fila['fecham']?>/<?= $fila['fechay']?> [<?= $fila['fechah']?>:<?= $fila['fechami']?> <?= $fila['fechaAMPM']?>]</td>
</tr>
<?php endif;?>
<!---NORMAL----->
<?php else:?>
<tr>
<td><?= $fila['cedula'] ?></td>
<td><?= $fila['nombre1'] ?></td>
<td><?= $fila['apellido'] ?></td>
<td><?= $fila['telefono'] ?></td>
</tr>
<?php endif;?>

<!-------->


<!-------->
<?php elseif ($tipo_reporte === 'Proveedores'): ?>
<tr>
<td><?= $fila['id'] ?></td>
<td><?= htmlspecialchars($fila['nombre_contacto']) ?></td>
<td><?= $fila['telefono'] ?></td>
<td><?= $fila['email']?></td>
<td><?= $fila['empresa_proveedora'] ?></td>
</tr>
<!-------->
					
				
<?php endif; ?>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="text-center py-4">
<i class="bi bi-inbox display-4 text-muted"></i>
<h5 class="text-muted">No hay datos para el reporte seleccionado</h5>
<p class="text-muted">Intente con otros filtros o fechas</p>
</div>
<?php endif; ?>
</div>
</div>
<?php else: ?>
<div class="text-center py-5">
<i class="bi bi-graph-up display-1 text-muted"></i>
<h3 class="text-muted mt-3">Seleccione una opción y genere un reporte</h3>
</div>
<?php endif; ?>
</div>

<script>
$("#opcionesHUB").hide();
let valorPADRE = "";

$(document).ready(function() {
$('#tipo_reporte').change(function() {
valorPADRE = $(this).val();
});
});
//FILTro 1




$(document).ready(function() {
$('#desfecha').change(function() {
if ($(this).is(':checked')) {
$("#opcionesHUB").show();
}
else {
$("#opcionesHUB").hide();
}
});
});

function exportarPDF() {
const tipo_reporte = $("#tipo_del_reporte").val();

///$tipo_reporte = $_POST['tipo_reporte'] ?? null;
const filtro = $("#opcionesFILTRAR").val();
///$filtro = $_POST['opcionesFILTRAR'] ?? "";

var dataw = <?php echo json_encode($reporte_data);?>;
//console.log(dataw);

    if (!tipo_reporte) {
        alert('Por favor, seleccione un tipo de reporte');
        return;
    }

var serializedData = encodeURIComponent(JSON.stringify(dataw));

// Abrir el PDF pasando los parámetros en la URL
window.open(`./modulos/reportes/generar_pdf.php?tipo_reporte=${tipo_reporte}&data=${serializedData}`, '_blank');

///window.open(`./modulos/reportes/generar_pdf.php?tipo_reporte=${tipo_reporte}&data=${dataw}`, '_blank');
}


//// FILTRO 2 
$(document).ready(function() {
var activo = <?= json_encode($step_one)?>;
if(activo == false){
	window.location.href = './modulos/logout.php?razon=${"gestionar_reportes"}';
}

$('#opcionesFILTRAR').change(function() {
	const valorSeleccionado = $(this).val();
	
	
	// Ocultar todos los divs
	$('.opcionf').hide();
	
	// Mostrar el div correspondiente basado en la selección
	if (valorSeleccionado == 'opcionf1') {
	if(valorPADRE != "Categorias"){
		$('#opcionf1').show();
		$('#opcionf3').hide();
		$('#opcionf2').hide();
		$('#opcionf6').hide();
		$('#opcionf4').hide();
		$('#opcionf5').hide();
		$('#opcionf10').hide();
	}
	else{
		$('#opcionf1').hide();
		$('#opcionf3').hide();
		$('#opcionf2').hide();
		$('#opcionf6').hide();
		$('#opcionf4').hide();
		$('#opcionf5').hide();
		$('#opcionf10').hide();
	}
	} 
	else if (valorSeleccionado == 'opcionf2') {
		$('#opcionf2').show();
		$('#opcionf1').hide();
		$('#opcionf3').hide();
		$('#opcionf6').hide();
		$('#opcionf4').hide();
		$('#opcionf5').hide();
		$('#opcionf10').hide();
	}
	
	else if (valorSeleccionado == 'opcionf3') {
		$('#opcionf3').show();
		$('#opcionf1').hide();
		$('#opcionf2').hide();
		$('#opcionf6').hide();
		$('#opcionf4').hide();
		$('#opcionf5').hide();
		$('#opcionf10').hide();
	}
	
	else if (valorSeleccionado == 'opcionf4') {
		$('#opcionf4').show();
		$('#opcionf1').hide();
		$('#opcionf2').hide();
		$('#opcionf3').hide();
		$('#opcionf5').hide();
		$('#opcionf6').hide();
		$('#opcionf10').hide();
	}
	
	else if (valorSeleccionado == 'opcionf5') {
		$('#opcionf5').show();
		$('#opcionf1').hide();
		$('#opcionf2').hide();
		$('#opcionf3').hide();
		$('#opcionf4').hide();
		$('#opcionf6').hide();
		$('#opcionf10').hide();
	}
	
	else if (valorSeleccionado == 'opcionf6') {
		$('#opcionf6').show();
		$('#opcionf1').hide();
		$('#opcionf2').hide();
		$('#opcionf3').hide();
		$('#opcionf4').hide();
		$('#opcionf5').hide();
		$('#opcionf10').hide();
	}
	
	else if (valorSeleccionado == 'opcionf10') {
		$('#opcionf10').show();
		$('#opcionf1').hide();
		$('#opcionf2').hide();
		$('#opcionf3').hide();
		$('#opcionf4').hide();
		$('#opcionf5').hide();
		$('#opcionf6').hide();
	}
	
	else{
		$('#opcionf1').hide();
		$('#opcionf2').hide();
		$('#opcionf3').hide();
		$('#opcionf4').hide();
		$('#opcionf5').hide();
		$('#opcionf6').hide();
		$('#opcionf10').hide();
	}
});
});        
</script>
