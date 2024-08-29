<?php

$phpExecutable = '/usr/local/bin/ea-php74';
$magentoExecutable = dirname(__FILE__).'/bin/magento';
$lockFile = sprintf('%s/dtm_execute_send_order_email.lock', dirname(__FILE__));

if (file_exists($lockFile)) return;

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

if (!function_exists('update_ids_email')) {
	function update_ids_email($conn, $idsToUpdate) {
		if (empty($conn)) return;
        
        $query = 'UPDATE mg_sales_order SET send_email=1 WHERE entity_id IN ('.implode(', ', $idsToUpdate).')';
        $conn->query($query);
	}
}

$err = null;

try {
    $conn = get_connection($dbData);

	if ($conn->connect_error) {
		throw new \Exception('Connection failed: ' . $conn->connect_error);
	}

    $query = 'SELECT entity_id FROM mg_sales_order WHERE ((state=\'processing\' AND status=\'processing\') OR (state=\'complete\' AND status=\'complete\')) 
    AND email_sent IS NULL AND send_email IS NULL
    AND created_at>=\''.date('Y-m-01').'\'';
	$result = $conn->query($query);
    if (!$result) throw new \Exception('Query error: '.$conn->error);

    $idsToUpdate = [];
    while ($result && $row = $result->fetch_assoc()) {
        $idsToUpdate[] = $row['entity_id'];
    }

    update_ids_email($conn, $idsToUpdate);
} catch (Exception $ex) {
    $err = $ex;
}

if (file_exists($lockFile)) {
    unlink($lockFile);
}

if (!is_null($err)) throw $err;