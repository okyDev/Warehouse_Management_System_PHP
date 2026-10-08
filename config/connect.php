<?php
//// API MADE IN 2025-11-29
/// V.00.02
/// UPDATE 2026-01-01
class MYSQL {
	///// V A R I A B L E S
	///// MYSQL
    private $servername = "localhost";
    private $username = "root";                    // Tu usuario MySQL
    private $password = ""; // Tu password MySQL
    private $dbname = "";       // Nombre de tu base de datos
    private $port = 3306;
    private $lastError = null;
    private $base = null;
    private $driver = 'mysql'; // o 'sqlite'
    
    //// SQLITE
    private $filelite = __DIR__.'/respaldo.db';

///// F U N C I O N E S

//// CONTRUCTOR
    public function __construct($autoConnect = true) {
        if ($autoConnect) {
            $this->connectar();
        }
    }

//// CONNECTOR NUCLEAR V.00.02
public function connectar() {
        try {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', 
                          $this->servername, $this->port, $this->dbname);
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // lanzar excepciones
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch assoc por defecto
                PDO::ATTR_EMULATE_PREPARES   => false,                 // usar prepares nativos
            ];
            
            $this->base = new PDO($dsn, $this->username, $this->password, $options);
            $this->driver = "mysql";
            return true;
        }
        catch(PDOException $e) {
			 try {
                $this->base = new PDO("sqlite:" . $this->filelite);
                $this->base->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->base->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->driver = 'sqlite';
                return true;
            }
            catch(PDOException $e2) {
                error_log("Error conectando a SQLite: " . $e2->getMessage());
                return false;
            }
        }
    }


public function getDriver() {
        return $this->driver;
    }
    
    
//// ESTA CONECTADO?
 public function conexionActiva(): bool {
        if ($this->base === null) {
            return false;
        }
        
        // Verificar si la conexión sigue viva
        try {
            $this->base->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }



//// MYSQL O SQLITE3? FUNCION DESCARFDAD
public function adaptQuery($sql) {
        if ($this->driver === 'sqlite') {
            // Adaptar consultas para SQLite
            $sql = str_replace('`', '"', $sql); // Cambiar backticks
            
            // Adaptar LIMIT (MySQL: LIMIT 0,10 -> SQLite: LIMIT 10 OFFSET 0)
            $sql = preg_replace('/LIMIT\s+(\d+)\s*,\s*(\d+)/i', 'LIMIT $2 OFFSET $1', $sql);
            
            // Adaptar funciones de fecha
            $sql = str_ireplace('NOW()', 'datetime(\'now\')', $sql);
            $sql = str_ireplace('CURDATE()', 'date(\'now\')', $sql);
            
            // Otras adaptaciones según sea necesario
        }
        return $sql;
    }

//////////////////// Q U E R Y 		W A Y S //////////////////////////

//// ONLY QUERY ONE SIMPLE

public function query_simple(string $script) {
        try {
            // Aseguramos que haya conexión
            if (!$this->conexionActiva()) {
                $this->connectar();
            }
			
			$script2 = $this->adaptQuery($script);
            $result = $this->base->query($script2);
            if ($result === false) {
                return false;
            }

            return $result->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en query_simple: " . $e->getMessage());
            return false;
        }
    }



//// QUERY WITHOUT ASK
public function query_all(string $script) {
        try {
            // Aseguramos que haya conexión
            if (!$this->conexionActiva()) {
                $this->connectar();
            }

            $result = $this->base->query($script);
            if ($result === false) {
                return false;
            }

            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en query_all: " . $e->getMessage());
            return false;
        }
    }


//// QUERY SEVERAL BY ONE
public function one_query_all(string $script, $var) {
        try {
            // Aseguramos conexión
            if (!$this->conexionActiva()) {
                $this->connectar();
            }

            $stmt = $this->base->prepare($script);
            if ($stmt === false) {
                throw new Exception('Error al preparar la consulta');
            }

            // ---------- EXECUTE ----------
            $stmt->execute([$var]);
            // ---------- RESULTADO ----------

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en one_query_all: " . $e->getMessage());
            return false;
        }
        catch(Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en one_query_all: " . $e->getMessage());
            return false;
        }
    }


//// QUERY ONE BY ONE
public function one_query(string $script, $var) {
        try {
            // Aseguramos conexión
            if (!$this->conexionActiva()) {
                $this->connectar();
            }

            $stmt = $this->base->prepare($script);
            if ($stmt === false) {
                throw new Exception('Error al preparar la consulta');
                error_log("NO PASO EL SQL ONE");
            }

            // ---------- EXECUTE ----------
            $stmt->execute([$var]);
            // ---------- RESULTADO ----------
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en one_query: " . $e->getMessage());
            return false;
        }
        catch(Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en one_query: " . $e->getMessage());
            return false;
        }
    }


//// QUERRY SEVERAL STUFF
public function some_query(string $script, array $datos = []) {
        try {
            // Aseguramos conexión
            if (!$this->conexionActiva()) {
                $this->connectar();
            }

            $stmt = $this->base->prepare($script);
            if ($stmt === false) {
                throw new Exception('Error al preparar la consulta');
            }

            // ---------- EXECUTE ----------
            $stmt->execute($datos);
            // ---------- RESULTADO ----------
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en some_query: " . $e->getMessage());
            return false;
        }
        catch(Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en some_query: " . $e->getMessage());
            return false;
        }
    }


//////////////////// Q U E R Y 		W A Y S 	E N D S 	H E R E   //////////////////////////



///// ACCIONES DE PRECAUCION
//// EMPEZAR MOVIMIENTO
public function beginTransaction(): bool {
        try {
            if (!$this->conexionActiva()) {
                $this->connectar();
            }
            return $this->base->beginTransaction();
        }
        catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al iniciar transacción: " . $e->getMessage());
            return false;
        }
    }

///// EXITO EJECUTAR
public function commit(): bool {
        try {
            if ($this->base->inTransaction()) {
                return $this->base->commit();
            }
            return false;
        }
        catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al hacer commit: " . $e->getMessage());
            return false;
        }
    }

//// ERROR NO EJECUTAR
public function rollBack(): bool {
        try {
            if ($this->base->inTransaction()) {
                return $this->base->rollBack();
            }
            return false;
        }
        catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al hacer rollback: " . $e->getMessage());
            return false;
        }
    }


///// ?
public function inTransaction(): bool {
        try {
            return $this->base->inTransaction();
        }
        catch(PDOException $e) {
            return false;
        }
    }


//// OBTENER ERRORES !!!
public function show_error_log() {
        return $this->lastError;
    }


//// OBTENER ID DE INSERT
public function lastInsertId() {
        try {
            return $this->base->lastInsertId();
        }
        catch(PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en lastInsertId: " . $e->getMessage());
            return false;
        }
    }


    //// OBTENER CONEXIÓN PDO DIRECTA (para operaciones avanzadas)
public function getConnection() {
        if (!$this->conexionActiva()) {
            $this->connectar();
        }
        return $this->base;
    }



    
public function version(){
if (!$this->conexionActiva()) {
$this->connectar();
}
try{
$result = $this->base->query("SELECT VERSION() AS version");
if ($result === false) {
	return false;
}

$version = $result->fetch(PDO::FETCH_ASSOC);
return $version['version'];
	  }
catch(Exception $e){
	error_log("EROR VERSION: $e");
	return 'SQLITE3';
}
}



public function bsUP($filepath){
try{
$command = "mysqldump --opt -h ".$this->servername." -u ".$this->username." -p".$this->password." ".$this->dbname." > $filepath";

// Ejecutar el comando
system($command, $returnVar);

// Verificar si tuvo éxito
if ($returnVar === 0) {
    error_log("Copia de seguridad realizada exitosamente: $filepath");
} else {
    error_log("Error al realizar la copia de seguridad.");
}
}

catch(Exception $e){
error_log("ERRRO_GENERAR: ".$e);
}
}



} /// ENDS HERE
?>
