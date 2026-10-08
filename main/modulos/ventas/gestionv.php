<?php 
///session_start();
require_once '../config/connect.php';
$base = new MYSQL;

//// TABLAS
$ventas = $base->query_all("SELECT *,monedas.simbolo as moneda FROM ventas JOIN monedas ON monedas.id = ventas.moneda_id WHERE ventas.activo = 1");
$detalles_ventas = $base->query_all("SELECT * FROM detalle_ventas WHERE activo =1");
$lotes = $base->query_all("SELECT * FROM lotes WHERE activo =1");


// Extrae los documentos de clientes
$documentos = array_column($ventas, 'cliente_documento');
$documentosUnicos = array_unique($documentos);
$numeroDocumentosUnicos = count($documentosUnicos);

$ventasPendientes = array_filter($ventas, function($venta) {
    return $venta['estado'] !== 'COMPLETADA';
});

// Cuenta el número de elementos filtrados
$cantidadPendientes = count($ventasPendientes);

//// CONCEGUIR GANAS TOTALES
///if(!empty($detalles_ventas)){}

function calcularGananciasTotales($base, $filtro_fecha = null) {
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
    
$resumen = $base->some_query($sql,[$year,$month]);
return $resumen;
}



$filtro_fecha = ['year' => date('Y'), 'month' => date('m')];

// Calcular ganancias
$ganancias = calcularGananciasTotales($base, $filtro_fecha);
?>


<!-- 1. ENCABEZADO -->
<div class="cain_gestion-v">
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">
                <i class="bi bi-graph-up-arrow text-primary"></i> Dashboard de Ventas
            </h1>
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark me-2">
                    <i class="bi bi-calendar"></i> 
                    <span id="fechaActual"></span>
                </span>
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download"></i> Exportar
                </button>
            </div>
        </div>
        <p class="text-muted mb-0 small">Resumen completo y gestión de ventas</p>
    </div>
</div>

<!-- 2. ESTADÍSTICAS PRINCIPALES -->
<div class="row mb-3">
<div class="col-lg-6 col-md-12 mb-3">
<div class="card ventasgestiondashboard-card ventasgestiontotal-ganancias h-100">
        <div class="card-body compact-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title text-white-50 ganancias-totales-text mb-1">Ganancias Totales</h5>
                    <h2 class="stats-number text-white mb-1"><?= $ganancias['total_ganancia']*-1 ?></h2> 
                    <p class="card-text text-white-50 mb-0 small">
                        <span class="badge bg-secondary">
                            <i class="bi bi-arrow-up"></i> 0.00% 
                        </span>
                    </p>
                </div>
                </div>
        </div>
    </div>
</div>

<div class="col-lg-6 col-md-12">
    <div class="row g-2">
        <div class="col-md-4 col-6">
            <div class="card ventasgestiondashboard-card h-100 compact-card">
                <div class="card-body p-2">
                    <small class="text-muted d-block">Total Ventas</small>
                    <h4 class="stats-number text-primary mb-0"><?= count($ventas) ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6">
            <div class="card ventasgestiondashboard-card h-100 compact-card">
                <div class="card-body p-2">
                    <small class="text-muted d-block">Mejor Venta</small>
                    <h4 class="stats-number text-success mb-0">$<?= number_format($maxValor = max(array_column($ventas, 'total')), 2) ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6">
            <div class="card ventasgestiondashboard-card h-100 compact-card">
                <div class="card-body p-2">
                    <small class="text-muted d-block">Total Clientes</small>
                    <h4 class="stats-number text-info mb-0"><?= $numeroDocumentosUnicos?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6">
            <div class="card ventasgestiondashboard-card h-100 compact-card">
                <div class="card-body p-2">
                    <small class="text-muted d-block">Pendientes</small>
                    <h4 class="stats-number text-warning mb-0"><?= $cantidadPendientes ?></h4>
                </div>
            </div>
        </div>

        
            </div>
        </div>
    </div>
</div>





<!-- 3. BARRA DE BÚSQUEDA Y FILTROS -->
<div class="row mb-3">
    <div class="col-12">
        <div class="ventasgestionsearch-section">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label for="searchInput" class="form-label fw-bold small">Buscar Venta</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="ID, cliente...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="filtroEstado" class="form-label fw-bold small">Estado</label>
                    <select class="form-select form-select-sm" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="completada">Completada</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtroFecha" class="form-label fw-bold small">Fecha</label>
                    <select class="form-select form-select-sm" id="filtroFecha">
                        <option value="">Todas</option>
                        <option value="hoy">Hoy</option>
                        <option value="semana">Esta semana</option>
                        <option value="mes">Este mes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtroEmpleado" class="form-label fw-bold small">Vendedor</label>
                    <select class="form-select form-select-sm" id="filtroEmpleado">
                        <option value="">Todos</option>
                        <!-- PHP: foreach empleados as empleado -->
                        <option value="<!-- PHP: empleado_id -->"><!-- PHP: empleado_nombre --></option>
                        <!-- PHP: endforeach -->
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm w-100" id="btnAplicarFiltros">
                        <i class="bi bi-funnel"></i> Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. LISTAS DE VENTAS Y EMPLEADOS -->
<div class="list_verow">




<!-- Tabla de Ventas Recientes -->
<div class="tablaventsell">

<div class="card ventasgestiondashboard-card">
<div class="card-header bg-white py-2">
<h5 class="card-title mb-0 small fw-bold">
<i class="bi bi-list-ul text-primary"></i> Ventas Recientes
</h5>
</div>
<div class="card-body p-0">
<div class="table-responsive">











<table class="table table-sm">
<thead class="table-light">
<tr><th class="small">ID</th> <th class="small">Fecha</th>
<th class="small">Cliente</th>
<th class="small">Vendedor</th>
<th class="small text-end">Monto</th>
<th class="small">Estado</th>
<th class="small text-center">Acciones</th>
</tr>
</thead>
<tbody>
<?php if(empty($ventas)):?>
<label>NADA</label>
<?php else:?>
<?php foreach($ventas as $self):?>
<tr class="venta-item">
<td><?= $self['codigo_venta']?></td>
<td>
<?php
$fecha = $self['fecha_ventaD']."/".$self['fecha_ventaM']."/".$self['fecha_ventaY']." ".$self['fecha_ventaH'].":".$self['fecha_ventaMi'];
echo $fecha;
?>
</td>
<td><?= $self['cliente_documento']?></td>
<td>V-<?=  $self['cedula_empleado']?></td>
<td><?=$self['moneda']?> <?=$self['total']?></td>
<td><?=$self['estado']?></td>

<td class="text-center">
<div class="btn-group btn-group-sm">
<form method="POST">
<label for="butdetalles" class="gestiBO"><i class="bi bi-eye"></i></label>
<button id="butdetalle">
</button>
</form>


<!-- En tu página de ventas -->
<a href="modulos/ventas/generarfactura.php?id=<?= $self['id_venta'] ?>" 
class="btn btn-success btn-sm">
<i class="bi bi-file-pdf"></i> Descargar Factura
</a>


<form method="POST">
<button class="btn btn-outline-danger btn-sm" title="Cancelar">
<i class="bi bi-x-circle"></i>
</button>
</form>
</div>
</td>
</tr>
<?php endforeach;?>

<?php endif;?>
<!-- PHP: endforeach -->
</tbody>
</table>









</div>
</div>








            <div class="card-footer bg-white py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Mostrando <!-- PHP: ventas_mostradas --> de <!-- PHP: total_ventas --> ventas</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Anterior</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">Siguiente</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

</div>






    <!-- Lista de Empleados -->
<div class="desempeno">
        <div class="card ventasgestiondashboard-card h-auto">
            <div class="card-header bg-white py-2">
                <h5 class="card-title mb-0 small fw-bold">
                    <i class="bi bi-people-fill text-primary"></i> Desempeño Vendedores
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="ventasgestionempleados-list">
                    <!-- PHP: foreach empleados as empleado -->
                    <div class="ventasgestionempleado-item p-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-1 me-2">
                                    <i class="bi bi-person-circle text-primary small"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 small fw-bold"><!-- PHP: empleado_nombre --></h6>
                                    <small class="text-muted"><!-- PHP: empleado_ventas --> ventas</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-success small">$<!-- PHP: empleado_total --></span>
                                <div class="progress mt-1" style="width: 60px; height: 3px;">
                                    <div class="ventasgestionprogress-bar bg-success" style="width: <!-- PHP: empleado_progreso -->%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- PHP: endforeach -->
                </div>
            </div>
            <div class="card-footer bg-white py-2 text-center">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Período: <!-- PHP: periodo_actual -->
                </small>
            </div>
        </div>
    </div>
</div>
