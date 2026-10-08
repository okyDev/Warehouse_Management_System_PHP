<?php
// modulos/productos/procesar_producto.php
session_start();
require_once '../../../config/connect.php';
require_once '../../../config/auth.php';

$base = new MYSQL;

$ci = null;
if(isset($_SESSION['cedula'])){
	$ci = $_SESSION['cedula'];
	
}
elseif (isset($_COOKIE['respaldoCI'])){
	$ci = $_COOKIE['respaldoCI'];
}
///$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$ci,"Nuevo Producto con Exito","Producto","El usuario creo un nuevo Producto",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método no permitido";
    header("Location: ../../menu.php?pagina=productos");
    exit(); 
}
//
$accion = $_POST['accion'] ?? '';

if(preg_match('/[A-Z]/', $precio_venta) or preg_match('/[a-z]/', $precio_venta) or strpos($precio_venta, ',,') !== false){
	throw new Exception("SKU, Nombre y Precio son obligatorios");
	}

try {
    switch($accion) {
        case 'crear':
            $sku = trim($_POST['sku'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $precio_venta = floatval($_POST['precio_venta'] ?? 0);
            $stock_minimo = intval($_POST['stock_minimo'] ?? 5);
            $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : NULL;
            $moneda_venta_id = intval($_POST['moneda_venta_id'] ?? 0);
            $marca = trim($_POST['marca'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $d = date('d');$m = date("m"); $y = date("Y"); $h = date("h A");
            //$proveedores = $_POST['proveedores'] ?? [];

            // Validaciones
            if(empty($sku) || empty($nombre) || $precio_venta <= 0) {
                throw new Exception("SKU, Nombre y Precio son obligatorios");
            }
            
            

            if($moneda_venta_id === 0) {
                throw new Exception("Selecciona una moneda válida");
            }

if(verify("crear_productos") === false){
	throw new Exception("El usuario no cuenta con permisos");
}
            // Verificar si SKU ya existe
            $sql_check = "SELECT sku FROM productos WHERE sku = ?";
            $result_check = $base->one_query($sql_check,$sku);
            
            if ($result_check > 0) {
                throw new Exception("El Producto [$sku]--$nombre ya existe");
            }

            // Insertar producto
            $sql = "INSERT INTO productos (sku, nombre, precio_venta, categoria_id, moneda_venta_id, descripcion, marca, stock_minimo, fecha_inD, fecha_inM, fecha_inY, fecha_inH) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $result = $base->some_query($sql,[$sku, $nombre, $precio_venta, $categoria_id, $moneda_venta_id, $descripcion, $marca, $stock_minimo, $d,$m,$y,$h]);
            
            if ($result !== false) {               
                $_SESSION['success'] = "Producto <strong>$sku</strong> creado exitosamente";
                $log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$ci,"Crear","Producto","El usuario creo un nuevo Producto",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);
            } else {
				
                $_SESSION['error'] = "Error al crear producto: " . $base->show_error_log();
$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$ci,"Intento","Producto","El usuario intento crear un nuevo Producto",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);
            }
            break;






        case 'editar':
            $sku = trim($_POST['sku'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $precio_venta = floatval($_POST['precio_venta'] ?? 0);
            $stock_minimo = intval($_POST['stock_minimo'] ?? 5);
            $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : NULL;
            $moneda_venta_id = intval($_POST['moneda_venta_id'] ?? 0);
            $descripcion = trim($_POST['descripcion'] ?? '');
            //$proveedores = $_POST['proveedores'] ?? [];
            $marca = trim($_POST['marca'] ?? '');
$cambios = [];
            if(empty($sku) || empty($nombre) || $precio_venta <= 0) {
                throw new Exception("Nombre y Precio son obligatorios");
            }
if(verify("editar_productos") === false){
	throw new Exception("El usuario no cuenta con permisos");
}


            // Actualizar producto
            $sql = "UPDATE productos SET 
                    nombre = ?, 
                    precio_venta = ?, 
                    categoria_id = ?, 
                    moneda_venta_id = ?, 
                    descripcion = ?,
                    marca = ?,
                    stock_minimo = ?
                    WHERE sku = ?";
            
            ///$stmt = $base->prepare($sql);
            //$stmt->bind_param("sdiissis", );
            $result = $base->some_query($sql,[$nombre, $precio_venta, $categoria_id, $moneda_venta_id, $descripcion, $marca,$stock_minimo, $sku]);
            if ($result !== false) {
                $_SESSION['success'] = "Producto <strong>$sku</strong> actualizado";
$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$ci,"Edicion","Producto","El usuario edito un Producto",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);                

$antes = $base->one_query("SELECT * FROM productos WHERE sku = ?",$sku);

$campos = [
    'nombre' => $nombre,
    'precio_venta' => $precio_venta,
    'stock_minimo' => $stock_minimo,
    'categoria_id' => $categoria_id,
    'moneda_venta_id' => $moneda_venta_id,
    'descripcion' => $descripcion,
    'marca' => $marca,
];

foreach ($campos as $campo => $nuevo_valor) {
    if (isset($nuevo_valor)) {

$valor_anterior = $antes[$campo]; // Esto va a consultar el valor anterior
if($valor_anterior !== $nuevo_valor){
        $regis = $base->some_query(
            "INSERT INTO cambios_productos 
            (producto_sku, cedula_empleado, campo_modificado, valor_anterior, valor_nuevo, fecha_upD, fecha_upM, fecha_upY, fecha_upH, fecha_upMI) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $sku,
                $ci,
                $campo,
                $valor_anterior,
                $nuevo_valor,
                date("d"),
                date("m"),
                date("Y"),
                date("h"),
                date("i"),
            ]
        );
    }
   }
}

}
else {
                //throw new Exception("Error al actualizar: " . $stmt->error);
                $_SESSION['error'] = "Error al actualizar: " . $base->show_error_log();
$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$ci,"Intento","Producto","El usuario intento editar un Producto",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);                
            }
            //$stmt->close();/
            break;
            
        case 'eliminar':
            $sku = trim($_POST['sku'] ?? '');
            
            if(empty($sku)) {
                throw new Exception("SKU no válido para eliminar");
            }
            
            if(verify("drop_productos") === false){
	throw new Exception("El usuario no cuenta con permisos");
}

            $result = $base->one_query("UPDATE productos SET activo = FALSE WHERE sku = ?",$sku);
            
            if ($result) {
                $_SESSION['success'] = "Producto eliminado";
$log = $base->some_query("INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$ci,"Eliminar","Producto","El usuario elimino un Producto",date("d"),date("m"),date("Y"),date("h"),date("i"),date("A")]);                
            } else {
                throw new Exception("Error al eliminar: " . $base->show_error_log());
            }
            $stmt->close();
            break;
            
        default:
            throw new Exception("Acción no válida");
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../../menu.php?pagina=productos");
exit();
?>
