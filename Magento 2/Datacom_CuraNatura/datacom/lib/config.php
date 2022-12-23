<?php
define("DEBUG", false);

if (DEBUG) {
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	error_reporting(E_ALL);
}

// dati DB
define("DB_HOST", "localhost");
define("DB_NAME", "curaenat_mage151");
define("DB_USER", "curaenat_mage151");
define("DB_PASS", "9CSp(1Pm@4");
define("TABLE_PREFIX", "mg_");
define("DB_CHAR", "utf8");