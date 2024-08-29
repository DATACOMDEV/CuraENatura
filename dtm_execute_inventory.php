<?php

$phpExecutable = '/usr/local/bin/ea-php74';
$magentoExecutable = dirname(__FILE__).'/bin/magento';
$lockFile = sprintf('%s/dtm_execute_inventory.lock', dirname(__FILE__));

if (file_exists($lockFile)) return;

touch($lockFile);

$err = null;

try {
    for ($i = 1; $i <= 3; $i++) {
        echo sprintf("Iterazione %d\r\n", $i);
        
        $output = null;
        $retval = null;
    
        echo exec(implode(' ', [$phpExecutable, $magentoExecutable, 'datacom:aligninventory']), $output, $retval);
        echo "Operation status: ".$retval."\r\n";
        echo print_r($output, true)."\r\n";

        echo "-------\r\n";
    }
} catch (Exception $ex) {
    $err = $ex;
}

if (file_exists($lockFile)) {
    unlink($lockFile);
}

if (!is_null($err)) throw $err;