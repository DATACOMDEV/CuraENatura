<?php
require_once("config.php");

class myFunction {

	public static $conn = NULL;

	// connessione DB
	public static function dbConnect() {
		$blnRet = true;
		
		try {
			self::$conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
			self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			$blnRet = false;
		}

		return $blnRet;
	}

	// disconnessione DB
	public static function dbDisconnect() {
		self::$conn = NULL;
	}

	// gestore query -> return -> default -> statement della query
	// Parameters
	// $query -> string -> query da eseguire
	// $args -> array -> argomenti da passare alla query
	// $lastId -> boolean default false -> true ritorna l'ID dell'elemento inserito
	// $nrValues -> int default 0 -> numero dei parametri VALUES nella INSERT
	// $closeConn -> boolean default true -> indica se la connessione al DB va chiusa dopo l'esecuzione della query
	public static function dbExecuteQuery($query, $args, $lastId = false, $nrValues = 0, $closeConn = true) {
		if (!self::dbConnect()) throw new Exception("Errore connessione al DataBase");
		
		$query = self::tablePrefix($query);

		if ($nrValues > 0) $query = str_replace("#values#", self::getValuesQuery($nrValues), $query);

		$stmt = self::$conn->prepare($query);
		if($stmt === false) throw new Exception("Errore query: " . $query);

		if ($stmt->execute($args) === false) throw new Exception(self::$conn->errorInfo()[2]);

		$ret = $stmt;
		if ($lastId) $ret = self::$conn->lastInsertId();

		if ($closeConn) self::dbDisconnect();

		return $ret;
	}

	// gestore select -> return -> fetch object
	// Parameters
	// $query -> string -> query da eseguire
	// $args -> array -> argomenti da passare alla query
	// $single -> boolean default true (fetch) -> indica se eseguire il fetch o il fetchAll
	// $class -> string default NULL -> nome della classe per il fetchAll
	public static function dbSelect($query, $args, $single = true, $class = NULL) {
		$query = self::tablePrefix($query);
		$stmt = self::dbExecuteQuery($query, $args, false, 0, false);
		if ($single) {
			$res = $stmt->fetch(PDO::FETCH_OBJ);
		} else {
			$res = $stmt->fetchAll(PDO::FETCH_CLASS, $class);
		}

		if ($res === false) {
			if (self::$conn->errorInfo()[0] != "00000") throw new Exception(self::$conn->errorInfo()[2]);
		}

		$stmt = NULL;

		self::dbDisconnect();

		return $res;
	}

    // gestore ricerca record -> return -> l'id dell'elemento ricercato, altrimenti 0
 	// Parameters
	// $query -> string -> query da eseguire
	// $args -> array -> argomenti da passare alla query
	public static function getId($query, $args) {
		$ret = 0;
		$query = self::tablePrefix($query);
        $res = myFunction::dbSelect($query, $args);
        if (is_object($res)) $id = $res->ID;
        if (isset($id)) $ret = $id;
        return $ret;
	}

	// ritorna una stringa di ?, in base al numero specificato
 	// Parameters
	// $nbr -> int -> numero per il repeat
	private static function getValuesQuery($nbr) {
		$values = str_repeat("?,", $nbr);
		$values = rtrim($values, ",");
		return $values;
	}

	private static function tablePrefix($query) {
		return str_replace("#prefix#", TABLE_PREFIX, $query);
	}
}

class getMessage {
	//classe per select fatchAll
}