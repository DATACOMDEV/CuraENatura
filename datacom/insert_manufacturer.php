<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Europe/Rome');
setlocale(LC_ALL, 'it_IT.UTF8');
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

$cwd = dirname(__FILE__);
$magentoRoot = dirname($cwd);

$dbData = require($magentoRoot.'/app/etc/env.php');
$dbData = $dbData['db']['connection']['default'];

if (!function_exists('get_connection')) {
	function get_connection($dbData) {
		static $conn = null;
		
		if (is_null($conn)) {
			$conn = new \mysqli($dbData['host'], $dbData['username'], $dbData['password'], $dbData['dbname']);
		}
		
		if ($conn->connect_error) {
			throw new \Exception('Connection failed' . $conn->connect_error);
		}
		
		return $conn;
	}
}

function check_manufacturer_exists($conn, $newManufacturer) {
    $query = 'SELECT v.* FROM mg_eav_attribute_option_value v
    INNER JOIN mg_eav_attribute_option o ON o.option_id=v.option_id
    WHERE o.attribute_id=83 AND value=?;';
    $stmnt = $conn->prepare($query);
    $stmnt->bind_param('s', $newManufacturer);
    if (!$stmnt->execute()) die('KO - Errore in fase di verifica esistenza marchio');
    while ($row = $stmnt->fetch()) {
        die('OK');
    }
}

function insert_manufacturer($conn, $newManufacturer) {
    $conn->begin_transaction();
    try {
        $query = 'SELECT sort_order FROM mg_eav_attribute_option WHERE attribute_id=83 ORDER BY sort_order DESC LIMIT 1';
        $stmnt = $conn->prepare($query);
        if (!$stmnt->execute()) die('KO - Errore in fase di verifica recupero sort order');
        $stmnt->bind_result($sortOrder);
        while ($row = $stmnt->fetch()) {
            $sortOrder = $sortOrder + 1;
        }

        $query = 'INSERT INTO mg_eav_attribute_option (attribute_id, sort_order) VALUES (83, ?)';
        $stmnt = $conn->prepare($query);
        $stmnt->bind_param('i', $sortOrder);
        if (!$stmnt->execute()) die('KO - Errore in fase di inserimento record su mg_eav_attribute_option');

        $query = 'SELECT option_id FROM mg_eav_attribute_option WHERE attribute_id=83 ORDER BY option_id DESC LIMIT 1';
        $stmnt = $conn->prepare($query);
        if (!$stmnt->execute()) die('KO - Errore in fase di recupero option_id');
        $stmnt->bind_result($optionId);
        while ($row = $stmnt->fetch()) {
            $optionId = $optionId;
        }

        $query = 'INSERT INTO mg_eav_attribute_option_value (option_id, store_id, value) VALUES (?, 0, ?)';
        $stmnt = $conn->prepare($query);
        $stmnt->bind_param('is', $optionId, $newManufacturer);
        if (!$stmnt->execute()) {
            echo $stmnt->error;
            die('KO - Errore in fase di inserimento record su mg_eav_attribute_option_value');
        }

        $conn->commit();
    } catch (Exception $ex) {
        $conn->rollback();
        die(sprintf('KO - %s', $ex->getMessage()));
    }
}

if (!array_key_exists('manufacturer', $_REQUEST)) die('KO - Marchio non valorizzato oppure vuoto');

$newManufacturer = $_REQUEST['manufacturer'];
if (empty($newManufacturer)) die('KO - Marchio non valorizzato oppure vuoto');

$conn = get_connection($dbData);

check_manufacturer_exists($conn, $newManufacturer);

insert_manufacturer($conn, $newManufacturer);

echo 'OK';