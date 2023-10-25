<?php
define("DEBUG", false);

if (DEBUG) {
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	error_reporting(E_ALL);
}

if (!defined('DTM_INITIALIZED')) {
	function dtm_initialize() {
		$cwd = dirname(__FILE__);

		$magentoRoot = dirname(dirname($cwd));
		
		$allDbData = require($magentoRoot.'/app/etc/env.php');
		$allDbData = $allDbData['db'];
		$dbData = $allDbData['connection']['default'];
		
		// dati DB
		define("DB_HOST", $dbData['host']);
		define("DB_NAME", $dbData['dbname']);
		define("DB_USER", $dbData['username']);
		define("DB_PASS", $dbData['password']);
		define("TABLE_PREFIX", $allDbData['table_prefix']);
		define("DB_CHAR", "utf8");
	
		define('DTM_INITIALIZED', 1);
	}

	dtm_initialize();
}