<?php
include_once 'config.php';

$db_servername = "db";
$db_username = "root";
$db_password = "root";
$dbname = "smallhubcrm";
$conn = get_DB();



function get_DB(){
	
	global $db_servername, $db_username, $db_password, $dbname, $conn;
	$dsn = 'mysql:host='.$db_servername.';dbname='.$dbname;


	try {
		$conn = new PDO($dsn, $db_username, $db_password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $conn;
		
	} catch (PDOException $e) {
		echo "Error en la conexión: " . $e->getMessage();
	}
		
}

function trace($sql){
	echo $sql . "<br/>";
	
}

//************************************************************************************************
// consulta para recuperar datos de BD
//************************************************************************************************
function query($sql){
	global $conn;
	if (__DEBUGSQL__) trace($sql); 
	
	$consulta = $conn->prepare($sql);
	$consulta->execute();
	return  $consulta->fetchAll(PDO::FETCH_ASSOC);

}

function query1($sql){
	global $conn;

	if (__DEBUGSQL__) trace($sql); 
	
	$consulta = $conn->prepare($sql);
	$consulta->execute();
	return  $consulta->fetch(PDO::FETCH_ASSOC);

}

function count_records($sql){
	global $conn;
	
	$sql = "select count(0) as total from ( $sql ) as V";
	//if (__DEBUGSQL__) trace($sql); 
	$data = query1($sql);
	
	return $data["total"];

}


function quote($txt, $type="text"){
	global $conn;
	if ($type == "text" || $type == "date")
		return $conn->quote($txt);
	else
		return $txt;
}

function mask($valor,$tipo){
	if ($tipo == "password") return md5($valor);
	else return $valor;

}

function appendcondition($sql,$condition){
	//trace($sql);
	//trace($condition);
	if (strpos( strtolower($sql), "group by " ) >0) {
		return $sql .= " having " . $condition;
		
	}else if (strpos( strtolower($sql), "having " ) >0) {
		return $sql .= " and " . $condition; 
	}else if (strpos( strtolower($sql), "where " ) >0) {
		return $sql .= " and " . $condition;
	} else {
		return $sql .= " where (" . $condition . ")";
	}	
}


function appendOR($exp,$condition){
	if ($exp != "")
		return $exp . " OR " . $condition ;
	else 
		return $condition;
	
	
}


/*
	función gen�rica, toma una tabla y un array e inserta los datos del array en la tabla 
	emparejando los nombres de las columnas con el array
*/
function dbinsert($tabla, $datos){
	global $conn;
	
	$campos = array_keys($datos);
	$fields = ""; $params = "";
	
	foreach($campos as $campo){
		
		$fields .= $campo.",";
		$params .= ":".$campo.",";
		
	}
	$fields = substr($fields, 0, -1);
	$params = substr($params, 0, -1);
	
	$sql = "INSERT INTO ".$tabla." (" . $fields .") VALUES (".$params.")";
	
	$stmt = $conn->prepare($sql);
	
	if (__DEBUGSQL__) trace($sql);
	
	// Verifica si la consulta preparada es v�lida
	if ($stmt) {
		// Vincular par�metros y ejecutar la consulta preparada
		foreach($datos  as $campo => $val){
			$stmt->bindValue( ":".$campo, $val  );							
		}
		
		$stmt->execute();
		
		return $conn->lastInsertId();
		
	}
	return false;
}

function dbupdate($tabla, $datos, $key){
	global $conn;
	
	// Preparar la instrucci�n SQL de inserci�n con una consulta preparada
	$sql = "update ".$tabla." set ";
	$campos = array_keys($datos);
	foreach($campos  as $campo){
		
		$sql .= $campo." = :".$campo . ",";
		
	}
	$sql = substr($sql, 0, -1);
	$sql .= " where " . $key ." = :" .$key; 
	
	//echo $sql;
	if (__DEBUGSQL__) trace($sql);
	$stmt = $conn->prepare($sql);
	
	// Verifica si la consulta preparada es v�lida
	if ($stmt) {
		// Vincular par�metros y ejecutar la consulta preparada
		foreach($datos  as $campo => $val){
			$stmt->bindValue( ":".$campo, $val  );							
		}
		
		$stmt->execute();
		
		return true;
		
	}
	return false;

	
}


function dbdelete($tabla, $datos){
	global $conn;
	
	// Preparar la instrucci�n SQL de inserci�n con una consulta preparada
	$sql = "delete from ".$tabla;
	$campos = array_keys($datos);
		
	foreach($campos  as $campo){
		$sql = appendcondition($sql, $campo." = :".$campo );		
	}

	
	//echo $sql;
	if (__DEBUGSQL__) trace($sql);
	$stmt = $conn->prepare($sql);
	
	// Verifica si la consulta preparada es v�lida
	if ($stmt) {
		// Vincular par�metros y ejecutar la consulta preparada
		foreach($datos  as $campo => $val){
			$stmt->bindValue( ":".$campo, $val  );							
		}
		
		$stmt->execute();
		
		return true;
		
	}
	return false;

}