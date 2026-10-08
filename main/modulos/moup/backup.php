<?php
header('Content-Type: application/json');
date_default_timezone_set('America/Caracas');
require_once (__dir__.'./../../../config/connect.php');

$base = new MYSQL;
$base->beginTransaction();
try{
$day = date('d');
$moth = date('m');
$year = date('Y');
$hour = date('h');
$minute = date('i');
$time = date('a');

$rootP = './../../../';
$finalpath = './../../../Respaldos/';
$zipname = 'Copia_Sistema_'.$day.'-'.$moth.'-'.$year.'_'.$hour.'-'.$minute.'.zip';
$zipfilename = $finalpath.$zipname;
$zip = new ZipArchive();
$version = $_SESSION['os_version'] ?? '0.0.5';

set_time_limit(0); // 0 significa que el script puede ejecutarse indefinidamente

//// check 1
if (file_exists($zipfilename)) {
    echo json_encode(['success' => false, 'message' => 'El archivo de copia de seguridad ya existe.']);
    exit; // Sale del script
}


//// check 2
if (!file_exists($finalpath)) {
    mkdir($finalpath, 0755, true); // Crear el directorio si no existe
}


/// CREATE
$zip->open($zipfilename,ZipArchive::CREATE | ZipArchive::OVERWRITE);


$files = new RecursiveIteratorIterator(	
new RecursiveDirectoryIterator($rootP),RecursiveIteratorIterator::LEAVES_ONLY
);

foreach($files as $name => $file){
	if(!$file->isDir()){
		$filePath = $file->getRealPath();
		$relativepath = substr($filePath,strlen($rootP));
		
		///// ONLY CODE FOLDER !!!!
		$relativepath = substr($relativepath, strpos($relativepath, '0.oscode')); // Asegúrate de que la carpeta es correcta

	if($relativepath !== $zipfilename){
		$zip->addFile($filePath,$relativepath);
}
	}
}


/// SE CREO
if($zip->close()){
	$peso = obtenerPesoArchivo($zipfilename);
	$ci = $_COOKIE['respaldoCI'] ?? null;
	$id = $base->one_query('SELECT id FROM usuarios_acceso WHERE cedula_empleado = ?',$ci);
	
if($id === false or $ci === null){
throw new Exception('Error registrando accion, reinicie su session y vuelva a intentar.');
}

error_log("EL ID ES: ".$id['id']);
$result = $base->some_query("INSERT INTO registro_backup (admin_id, nombre_zip, version, peso, fechad, fecham, fechay, fechah, fechami, tipohora) VALUES (?, ? ,? , ?, ? , ?, ?, ?, ?, ?)",[$id['id'],$zipname,$version,$peso,$day,$moth,$year,$hour,$minute,$time]);

if($result !== false){
	echo json_encode(['success' => true, 'message' => 'Copia creada.']);
	error_log("Copia de Seguridad Creada con Exito!");
}
else{
	$archiv = eliminarArchivo($zipfilename);
	throw new Exception('No se puedo registrar la accion ('.$base->show_error_log() . ')');
	error_log("NOP SE CREO ZP".$base->show_error_log());
}
	
}


//// NO SE CREO
else{
	throw new Exception('Error Creando el copia de seguridad');
	error_log("NOP SE CREO ZP");
}


$base->commit();
}
catch(Exception $e){
	error_log("Error Creado copia de Seguridad: ".$e->getMessage());
	 echo json_encode(['success' => false, 'message' => $e->getMessage()]);
	 $base->rollBack();
}


function obtenerPesoArchivo($rutaArchivo) {
    // Verifica si el archivo existe
    if (file_exists($rutaArchivo)) {
        // Obtiene el tamaño en bytes
        $tamanoBytes = filesize($rutaArchivo);
        
        // Convierte a megabytes (1 MB = 1024 * 1024 bytes)
        $tamanoMB = $tamanoBytes / (1024 * 1024);
        
        return round($tamanoMB, 2); // Redondea a 2 decimales
    } else {
        return 'El archivo no existe.';
    }
}



function eliminarArchivo($rutaArchivo) {
    if (file_exists($rutaArchivo)) {
        unlink($rutaArchivo); // Elimina el archivo
        return true;
    }
    return false;
}

?>
