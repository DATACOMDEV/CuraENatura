<?php
require_once("lib/function.php");

$obj = new gateway();

if ($_GET["cmd"] == "testConnDb") gateway::testConnDb();
if ($_GET["cmd"] == "exportOrder") $obj->exportOrder((isset($_GET["status"]) ? $_GET["status"] : "complete"), (isset($_GET["orderId"]) ? $_GET["orderId"] : ""));

class gateway {

    // test connessione DB
    public static function testConnDb() {
        $ret = "Connessione DataBase OK";
        if (!myFunction::dbConnect()) $ret = "Errore connessione al DataBase";
        myFunction::dbDisconnect();
        echo $ret;
    }

    // esporta gli ordini
    public function exportOrder($status, $orderId) {
        try {
            define("DATA_FOLDER", dirname(__FILE__));

            $strCSV = "";
            $blnUpdateLastId = true;

            $fileConfig = DATA_FOLDER . "/config.cfg";
            $fileCSV = DATA_FOLDER . "/exportOrder.csv";

            if (DEBUG) {
                echo "fileConfig: $fileConfig<br />";
                echo "fileCSV: $fileCSV<br />";
                echo "status: $status<br />";
            }

            $lastId = file_get_contents($fileConfig);
            $lastId += 1;
            if ($orderId != "") {
                $query = "SELECT entity_id AS ID FROM #prefix#sales_order WHERE increment_id=?";
                $args = array($orderId);
                $lastId = myFunction::getId($query, $args);
                $blnUpdateLastId = false;
            }
            $orderId = $lastId;

            if (DEBUG) echo "orderId: $orderId<br />";

            $query = "SELECT o.entity_id, o.status, o.increment_id, a.firstname customer_firstname, a.lastname customer_lastname, ADDTIME(o.created_at, '0 2:00:00.00') AS created_at, o.shipping_invoiced, o.shipping_tax_amount, o.msp_cod_amount, o.msp_cod_tax_amount, i.tax_percent, i.row_invoiced, i.tax_invoiced, o.applied_rule_ids, o.base_discount_amount, i.base_discount_amount order_item_discount, i.discount_tax_compensation_amount FROM #prefix#sales_order o INNER JOIN #prefix#sales_order_item i ON o.entity_id=i.order_id INNER JOIN #prefix#sales_order_address a ON a.parent_id=i.order_id AND a.address_type='billing' WHERE o.status=? AND o.entity_id>=? ORDER BY o.created_at DESC";
            $args = array($status, $lastId);
            if (strtoupper($status) == "ALL") {
                $query = "SELECT o.entity_id, o.status, o.increment_id, a.firstname customer_firstname, a.lastname customer_lastname, ADDTIME(o.created_at, '0 2:00:00.00') AS created_at, o.shipping_invoiced, o.shipping_tax_amount, o.msp_cod_amount, o.msp_cod_tax_amount, i.tax_percent, i.row_invoiced, i.tax_invoiced, o.applied_rule_ids, o.base_discount_amount, i.base_discount_amount order_item_discount, i.discount_tax_compensation_amount FROM #prefix#sales_order o INNER JOIN #prefix#sales_order_item i ON o.entity_id=i.order_id INNER JOIN #prefix#sales_order_address a ON a.parent_id=i.order_id AND a.address_type='billing' WHERE o.entity_id>=? ORDER BY o.created_at DESC";
                $args = array($lastId);
                $blnUpdateLastId = false;
            }

            if (DEBUG) echo "<br />query: $query<br /><br />";

            $orders = myFunction::dbSelect($query, $args, false, "getMessage");

            $oldId = 0;
            $netto22 = 0;
            $iva22 = 0;
            $netto10 = 0;
            $iva10 = 0;
            $netto4 = 0;
            $iva4 = 0;
            $sped = 0;
            $cod = 0;            
            $msg = "";
            $blnLastId = false;
            $blnFirstRow = true;

            $strCSV .= "STATO;N. ORDINE;DATA;CLIENTE;NETTO IVA 22%;ALIQUOTA IVA 22%;NETTO IVA 10%;ALIQUOTA IVA 10%;NETTO IVA 4%;ALIQUOTA IVA 4%;SPEDIZIONE;SCONTO;CONTRASSEGNO;NOTE\r\n";
            foreach ($orders as $order) {
                $id = $order->entity_id;

                if (!$blnLastId) {
                    $lastId = $id;
                    $blnLastId = true;
                }
                if (DEBUG) {
                    echo "id: $id<br />";
                    echo "oldId: $oldId<br />";
                }

                if ($id != $oldId) {
                    if (!$blnFirstRow) $strCSV .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\r\n", $netto22, $iva22, $netto10, $iva10, $netto4, $iva4, $sped, $sconto, $cod, $msg);

                    $blnFirstRow = true;
                    $netto22 = 0;
                    $iva22 = 0;
                    $netto10 = 0;
                    $iva10 = 0;
                    $netto4 = 0;
                    $iva4 = 0;
                    $sped = 0;
                    $sconto = 0;
                    $cod = 0;
                    $msg = "";

                    $oldId = $id;
                    // $netto22 += $order->shipping_invoiced;
                    // $iva22 += $order->shipping_tax_amount;
                    $sped = $order->shipping_invoiced + $order->shipping_tax_amount;
                    // if ($order->msp_cod_amount !== NULL) $netto22 += $order->msp_cod_amount;
                    // if ($order->msp_cod_tax_amount !== NULL) $iva22 += $order->msp_cod_tax_amount;
                    if ($order->msp_cod_amount !== NULL) $cod = $order->msp_cod_amount + $order->msp_cod_tax_amount;
                    $scontoIds = array();
                    if (!empty($order->applied_rule_ids)) {
                        $scontoIds = explode(',', $order->applied_rule_ids);
                    }
                    $sconto = abs($order->base_discount_amount);
                    if (in_array(1, $scontoIds)) {
                        if ($sped > 0) {
                            $sped -= 6.9;
                        }
                        $sconto -= 6.9;
                    }
                    if ($blnFirstRow) $strCSV .= sprintf("%s;%s;%s;%s %s;", $order->status, $order->increment_id, $order->created_at, $order->customer_firstname, $order->customer_lastname);
                }
                
                if ($id == $oldId) {
                    $blnFirstRow = false;
                    $orderItemDiscount = $order->order_item_discount; // importo sconto apèplicato sulla riga
                    if (empty($orderItemDiscount)) {
                        $orderItemDiscount = 0;
                    }
                    $orderItemTaxCompensation = $order->discount_tax_compensation_amount; // arrotondamento
                    if (empty($orderItemTaxCompensation)) {
                        $orderItemTaxCompensation = 0;
                    }
                    switch ($order->tax_percent) {
                        case 22:
                            $netto22 += $order->row_invoiced + $orderItemTaxCompensation - $orderItemDiscount;
                            $iva22 += $order->tax_invoiced;
                            break;
                        case 10:
                            $netto10 += $order->row_invoiced + $orderItemTaxCompensation - $orderItemDiscount;
                            $iva10 += $order->tax_invoiced;
                            break;
                        case 4:
                            $netto4 += $order->row_invoiced + $orderItemTaxCompensation - $orderItemDiscount;
                            $iva4 += $order->tax_invoiced;
                            break;
                        default:
                            $msg .= sprintf("L'ordine n. %s ha un'aliquota IVA diversa da quelle gestite (%g). Verificare l'aliquota IVA associata all'articolo acquistato. - ", $order->increment_id, $order->tax_percent);
                    }
                }
            }

            if (!$blnFirstRow) $strCSV .= sprintf("%s;%s;%s;%s;%s;%s;%s\r\n", $netto22, $iva22, $netto10, $iva10, $netto4, $iva4, $msg);

            file_put_contents($fileCSV, $strCSV);

            if (!DEBUG) {
                if ($blnUpdateLastId) file_put_contents($fileConfig, $lastId);

                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($fileCSV).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($fileCSV));
                readfile($fileCSV);
            }
            if (DEBUG) echo "FINITO";
        } catch (Exception $ex) {
            if (DEBUG) echo "ERROR - " . $ex->getMessage();
            if (!DEBUG) echo "ATTENZIONE!<br /><br />Si è verificato un errore, riprovare a ricaricare la pagina. Se il problema persiste contattare l'assistenza tecnica.";
        }
    }
}