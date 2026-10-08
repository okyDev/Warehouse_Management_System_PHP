<?php
session_start();
require_once '../../../config/connect.php';
$base = new MYSQL;

$motivo = $_POST['action'] ?? null;    
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método no permitido";
    header("Location: ../../menu.php?pagina=config");
    exit(); 
}

try {
    //// SEGURIDAD
    $allowmemram = filter_input(INPUT_POST, 'permitirwaittime', FILTER_VALIDATE_BOOLEAN);
    $fecha_adios = $_POST['tiempotowait'] ?? null;
    $allowtries = filter_input(INPUT_POST, 'blockear', FILTER_VALIDATE_BOOLEAN);
    $intentosallow = $_POST['blockearpor'] ?? null;

    ///// EMPRESA
    $file = procesarImagen($_FILES['logo'] ?? null);
    $name = $_POST['Ename'] ?? null;
    $ci = $_POST['Erif'] ?? null;
    $phone = $_POST['Ephone'] ?? null;
    $email = $_POST['Eemail'] ?? null;
    $dir = $_POST['Edirecc'] ?? null;

    /////// COPIAS DE SEGURIDAD
    $hora = $_POST['hourTO'] ?? null;
    $frecuencia = $_POST['frecuencia'] ?? null;
    $day = $_POST['day'] ?? null;
    $true_au_backup = filter_input(INPUT_POST, 'trueaubackup', FILTER_VALIDATE_BOOLEAN);
    
    
    //// BS
    $who = $_POST['usID'] ?? null;
    $clave = $_POST['userKey'];
    $pass = hash('sha1',$clave);
    

    if(!isset($motivo) or $motivo == null){
        throw new Exception("Error registrando los datos, intente otra vez");
    }

    // Comenzar transacción
    if (method_exists($base, 'beginTransaction')) {
        $base->beginTransaction();
    }

    //// MOTIVO: EMPRESA CHANGE
    if($motivo === 'empresa_info'){
        if($name === 'Ninguno' or $ci === 'Ninguno' or $phone === 'Ninguno' or $email === 'Ninguno' or $dir === 'Ninguno'){
            throw new Exception("Variables invalidas");
        }

        if(!isset($name) or !isset($ci) or !isset($email) or !isset($phone) or !isset($dir)){
            throw new Exception("Variables invalidas");
        }

        //// IF NAME AND CI ARE EMPTY GET OUT
        if(empty($name) or empty($ci)){
            throw new Exception("Para registrar una empresa debe tener almenos el nombre y el rif");
        }

        ///// ¿EXISTE YA ALGO?
        $old = $base->query_simple("SELECT * FROM empresa LIMIT 1");

        //// UPDATE
        if($old !== false){
            $updates = [];
            $params = [];

            if($old['nombre'] !== $name){
                $updates[] = 'nombre = ?';
                $params[] = $name;
            }

            if($old['rif'] !== $ci){
                $updates[] = 'rif = ?';
                $params[] = $ci;
            }

            if($old['email'] !== $email){
                $updates[] = 'email = ?';
                $params[] = $email;
            }

            if($old['direccion'] !== $dir){
                $updates[] = 'direccion = ?';
                $params[] = $dir;
            }

            if($old['telefono'] !== $phone){
                $updates[] = 'telefono = ?';
                $params[] = $phone;
            }

            if(empty($updates)){
                throw new Exception("Los datos enviados deben ser diferentes!");
            }

            // Agregar fecha de actualización
            $updates[] = 'fecha_upD = ?';
            $params[] = date('d');
            $updates[] = 'fecha_upM = ?';
            $params[] = date('m');
            $updates[] = 'fecha_upY = ?';
            $params[] = date('Y');
            $updates[] = 'fecha_upH = ?';
            $params[] = date('H');

            // Agregar WHERE
            $params[] = $old['id'];

            $sql = "UPDATE empresa SET " . implode(', ', $updates) . " WHERE id = ?";
            
            error_log("SQL UPDATE: " . $sql);
            error_log("Params: " . print_r($params, true));

            $stmt = $base->some_query($sql, $params);
            
            if($stmt !== false){
                $_SESSION['success'] = "Información de " . count($updates) . " campos fue actualizada con éxito!";
            } else {
                throw new Exception("Error actualizando: " . $base->show_error_log());
            }
        }
        ///// FIRST INSERT
        else {
            if($name === null or $phone === null or $ci === null or $email === null){
                throw new Exception("Por favor ingrese todos los datos requeridos.");
            }

            $stmt = $base->some_query(
                "INSERT INTO empresa (nombre, rif, direccion, telefono, email, iva, fecha_upD, fecha_upM, fecha_upY, fecha_upH) 
                 VALUES (?, ?, ?, ?, ?, 0.16, ?, ?, ?, ?)",
                [$name, $ci, $dir, $phone, $email, date('d'), date('m'), date('Y'), date('H')]
            );
            
            if($stmt !== false){
                $_SESSION['success'] = "Información ingresada con éxito!!";
            } else {
                throw new Exception("Error ingresando: " . $base->show_error_log());
            }
        }
    }
  
    ////// SEGURIDAD
    elseif($motivo === 'segurity'){
        if($intentosallow === null or $fecha_adios === null or $allowmemram === null or $allowtries === null){
            throw new Exception("Por favor no deje los campos vacios.");
        }

        //// ¿EXISTE ALGO?
        $old = $base->query_simple("SELECT * FROM configuraciones_sistema LIMIT 1");

        if($old !== false){
            $updates = [];
            $params = [];

            if($old['eliminar_mermaF'] != $allowmemram){
                $updates[] = 'eliminar_mermaF = ?';
                $params[] = $allowmemram ? 1 : 0;
            }

            if($old['fecha_eliminar_d'] != $fecha_adios){
                $updates[] = 'fecha_eliminar_d = ?';
                $params[] = $fecha_adios;
            }

            if($old['blockear_despues'] != $allowtries){
                $updates[] = 'blockear_despues = ?';
                $params[] = $allowtries ? 1 : 0;
            }

            if($old['numero_intentos'] != $intentosallow){
                $updates[] = 'numero_intentos = ?';
                $params[] = $intentosallow;
            }

            if(empty($updates)){
                throw new Exception("No hay cambios para actualizar");
            }

            // Agregar fecha de actualización
            $updates[] = 'fecha_actualizacionD = ?';
            $params[] = date('d');
            $updates[] = 'fecha_actualizacionM = ?';
            $params[] = date('m');
            $updates[] = 'fecha_actualizacionY = ?';
            $params[] = date('Y');
            $updates[] = 'fecha_actualizacionH = ?';
            $params[] = date('H');
            $updates[] = 'fecha_actualizacionMI = ?';
            $params[] = date('i');

            // Agregar WHERE
            $params[] = $old['id_c'];

            $sql = "UPDATE configuraciones_sistema SET " . implode(', ', $updates) . " WHERE id_c = ?";
            
            error_log("SQL UPDATE Seguridad: " . $sql);

            $stmt = $base->some_query($sql, $params);

            if($stmt !== false){
                $_SESSION['success'] = "Información de seguridad actualizada con éxito!";
            } else {
                throw new Exception("Error actualizando información de seguridad: " . $base->show_error_log());
            }
        } else {
            $stmt = $base->some_query(
                "INSERT INTO configuraciones_sistema 
                (eliminar_mermaF, fecha_eliminar_d, blockear_despues, numero_intentos, 
                fecha_actualizacionD, fecha_actualizacionM, fecha_actualizacionY, 
                fecha_actualizacionH, fecha_actualizacionMI)  
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $allowmemram ? 1 : 0, 
                    $fecha_adios, 
                    $allowtries ? 1 : 0, 
                    $intentosallow, 
                    date('d'), date('m'), date('Y'), date('H'), date('i')
                ]
            );

            if($stmt !== false){
                $_SESSION['success'] = "Información para la seguridad ha sido ingresada con éxito!!";
            } else {
                throw new Exception("Error ingresando información para la seguridad: " . $base->show_error_log());
            }
        }
    }



//// BACKUPTBS
//// BACKUPTBS
elseif($motivo === 'bsUP'){
    
    // Obtener y validar el ID del usuario
    $who = $_POST['who'] ?? null;
    
    if($who === null || empty(trim($who))){
        // Registrar intento sospechoso
        $dateNOW = date('Y_m_d [h:i:s a]');
        $mensaje = "Intento de crear copia de base de datos sin identificación";
        
        // Insertar alerta (usando sentencia preparada)
        $base->some_query(
            "INSERT INTO alertas (tipo, titulo, mensaje, fecha_creacion, prioridad) 
             VALUES (?, ?, ?, ?, ?)",
            ['ALERTA', 'INTENTO DE PERJUDICAR EL SISTEMA', $mensaje, $dateNOW, 3]
        );
        
        // Redirigir y terminar
        header("Location: ../logout.php?razon=Intento no autorizado");
        exit(); // ← IMPORTANTE: Salir inmediatamente
    }
    
    // Obtener información del usuario
    $old = $base->one_query(
        "SELECT is_above, block, username, password FROM usuarios_acceso WHERE cedula_empleado = ?",$who);
    
    // Verificar si el usuario existe
    if(!$old || empty($old)){
        $dateNOW = date('Y_m_d [h:i:s a]');
        $mensaje = "El empleado [V- $who] intentó realizar una copia de seguridad del sistema sin autorización [" . date('d/m/Y h:i a') . "]";
        
        $base->some_query(
            "INSERT INTO alertas (tipo, titulo, mensaje, fecha_creacion, prioridad) 
             VALUES (?, ?, ?, ?, ?)",
            ['ALERTA', 'INTENTO DE PERJUDICAR EL SISTEMA', $mensaje, $dateNOW, 3]
        );
        
        header("Location: ../logout.php?razon=Usuario no encontrado");
        exit();
    }
    
    // Verificar si está bloqueado
    if($old['block'] == 1){ // 1 = true en MySQL/SQLite
        throw new Exception("Su cuenta está bloqueada. Contacte al administrador.");
    }
    
    if($old['password'] !== $pass){
		throw new Exception("Clave invalida!");
	}
    
    // Verificar permisos (is_above = 1 para admin)
    if($old['is_above'] != 1 or $old['is_above'] !== true){ // Comparar con 1, no con true
        // Registrar intento no autorizado
        $dateNOW = date('Y_m_d [h:i:s a]');
        $mensaje = "El empleado " . ($old['username'] ?? 'desconocido') . 
                  " [V-$who] intentó realizar una copia de seguridad sin permisos administrativos [" . 
                  date('d/m/Y h:i a') . "]";
        
        // Bloquear usuario después de intento no autorizado
        $base->some_query(
            "UPDATE usuarios_acceso SET block = 1 WHERE cedula_empleado = ?",
            [$who]
        );
        
        // Registrar alerta
        $base->some_query(
            "INSERT INTO alertas (tipo, titulo, mensaje, fecha_creacion, prioridad) 
             VALUES (?, ?, ?, ?, ?)",
            ['ALERTA', 'INTENTO NO AUTORIZADO', $mensaje, $dateNOW, 3]
        );
        
        // Crear log de seguridad
        $base->some_query(
            "INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $who, 
                'Intento de backup no autorizado', 
                'Backup', 
                $mensaje,
                date('d'), date('m'), date('Y'), date('h'), date('i'), date('a')
            ]
        );
        
        header("Location: ../logout.php?razon=Permisos insuficientes");
        exit();
    }
    

    try {
        // Crear directorio de respaldos si no existe
        $backupDir = '../../../Respaldos/';
        if(!is_dir($backupDir)){
            mkdir($backupDir, 0755, true);
        }
        
        // Generar nombre único para el backup
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $backupDir . 'copia_BS_' . $timestamp . '_' . substr(md5($who . time()), 0, 8) . '.sql';
        
        // Ejecutar backup
        $backupResult = $base->bsUP($filename);
        
        if($backupResult){
            // Registrar backup exitoso
            $base->some_query(
                "INSERT INTO registro_backup (admin_id, nombre_zip, version, peso, fechad, fecham, fechay, fechah, fechami, tipohora) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $_SESSION['user_id'] ?? 0, // Asegúrate de tener user_id en sesión
                    basename($filename),
                    '1.0.0', // Versión de tu sistema
                    filesize($filename),
                    date('d'), date('m'), date('Y'), date('h'), date('i'), date('a')
                ]
            );
            
            // Registrar log
            $base->some_query(
                "INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $who,
                    'Backup exitoso',
                    'Backup',
                    'Copia de seguridad creada: ' . basename($filename),
                    date('d'), date('m'), date('Y'), date('h'), date('i'), date('a')
                ]
            );
            
            $_SESSION['success'] = "¡Copia de seguridad creada exitosamente!";
            $_SESSION['backup_file'] = basename($filename);
            
        } else {
            throw new Exception("Error al crear la copia de seguridad");
        }
        
    } catch(Exception $e) {
        // Registrar error
        $base->some_query(
            "INSERT INTO logs (id_usuario, accion, modulo, detalles, fechad, fecham, fechay, fechah, fechami, fechaAMPM) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $who,
                'Error en backup',
                'Backup',
                'Error: ' . $e->getMessage(),
                date('d'), date('m'), date('Y'), date('h'), date('i'), date('a')
            ]
        );
        
        $_SESSION['error'] = "Error creando copia de seguridad: " . $e->getMessage();
        error_log("ERROR BACKUP: " . $e->getMessage());
    }

catch (Exception $e){
	error_log("SISTEMA_COPIA_ERROR: ".$e);
}
}
    
    
    
    
    
    //// COMMIT si existe el método
    if (method_exists($base, 'commit')) {
        $base->commit();
    }

    $_SESSION['reinicio'] = true;
    $_SESSION['configR'] = $base->query_simple("SELECT * FROM configuraciones_sistema");
    
    header("Location: ../../menu.php?pagina=config");
    exit();

} catch(Exception $e) {
    // ROLLBACK si existe el método
    if (method_exists($base, 'rollBack')) {
        $base->rollBack();
    }
    
    $_SESSION['error'] = $e->getMessage();
    error_log("ERROR_EN_CONFIG_SAVE: " . $e->getMessage());
    
    header("Location: ../../menu.php?pagina=config");
    exit();
}

function procesarImagen($archivo) {
    if ($archivo && $archivo["error"] === UPLOAD_ERR_OK) {
        $targetDir = "../../../assets/multimedia/logos/";
        $imageFileType = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowedTypes)) {
            $newFileName = 'empresa.' . 'jpg';
            $targetFile = $targetDir . $newFileName;

            // Mover archivo subido
            if (move_uploaded_file($archivo["tmp_name"], $targetFile)) {
                return $newFileName;
            }
        }
    }
    return null;
}
?>