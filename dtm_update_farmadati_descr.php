<?php

$phpExecutable = '/usr/local/bin/ea-php74';
$magentoExecutable = dirname(__FILE__).'/bin/magento';
$lockFile = sprintf('%s/dtm_update_farmadati_descr.lock', dirname(__FILE__));

//if (file_exists($lockFile)) return;

touch($lockFile);

$dbData = require('app/etc/env.php');
$dbData = $dbData['db']['connection']['default'];

if (!function_exists('get_connection')) {
	function get_connection($dbData) {
		static $conn = null;
		
		if (is_null($conn)) {
			$conn = new \mysqli($dbData['host'], $dbData['username'], $dbData['password'], $dbData['dbname']);
		}
		
		if ($conn->connect_error) {
			throw new \Exception('Connection failed: ' . $conn->connect_error);
		}
		
		return $conn;
	}
}

$err = null;

try {
    $conn = get_connection($dbData);

	if ($conn->connect_error) {
		throw new \Exception('Connection failed: ' . $conn->connect_error);
	}

	$file = dirname(_FILE__).'/descrizione prodotti farmadati.csv';
	$skusAndDescr = file($file);

	foreach ($skusAndDescr as $rowData) {
		$data = explode(';', $rowData);
		$data[0] = trim($data[0]);
		
		$data[1] = substr($rowData, strlen($data[0])+1);

		$query = 'SELECT e.entity_id, t.value
		FROM mg_catalog_product_entity e 
		LEFT OUTER JOIN mg_catalog_product_entity_text t ON t.entity_id=e.entity_id AND t.store_id=1 AND t.attribute_id=75
		WHERE e.sku=?';

		$stmnt = $conn->prepare($query);
		if (!$stmnt) throw new \Exception('Statement error: '.$conn->error);

		$stmnt->bind_param('s', $data[0]);
		$stmnt->execute();

		$result = $stmnt->get_result();
		if (!$result) throw new \Exception('Query error: '.$conn->error);

		while ($result && $row = $result->fetch_assoc()) {
			if (empty($row['value'])) {
				$query = 'INSERT INTO mg_catalog_product_entity_text (attribute_id, store_id, entity_id, value) VALUES (75, 1, ?, ?)';
				/*echo $query."\r\n";
				echo $row['entity_id']."\r\n";
				echo $data[1]."\r\n";*/
				$stmnt = $conn->prepare($query);
				$stmnt->bind_param('ds', $row['entity_id'], $data[1]);
				$stmnt->execute();
				continue;
			}

			$query = 'UPDATE mg_catalog_product_entity_text SET value=? WHERE attribute_id=75 AND store_id=1 AND entity_id=?';
			/*echo $query."\r\n";
			echo $row['entity_id']."\r\n";
			echo $data[1]."\r\n";*/
			$stmnt = $conn->prepare($query);
			$stmnt->bind_param('sd', $data[1], $row['entity_id']);
			$stmnt->execute();
		}
	}
} catch (Exception $ex) {
    $err = $ex;
}

if (file_exists($lockFile)) {
    unlink($lockFile);
}

if (!is_null($err)) throw $err;