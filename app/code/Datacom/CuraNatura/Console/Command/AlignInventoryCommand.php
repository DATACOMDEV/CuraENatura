<?php

namespace Datacom\CuraNatura\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AlignInventoryCommand extends \Symfony\Component\Console\Command\Command
{
    const ATTRIBUTE_CODE_UNICO_INVENTARIO = 'dtm_inventario_unico';
    const ATTRIBUTE_CODE_UNICO_PREZZO = 'dtm_inventario_unico_prezzo';
    const ATTRIBUTE_CODE_VECCHIO_PREZZO_SCONTATO = 'dtm_inventario_prz_precedente';

    protected $_conn;
    protected $_stockRegistryInterface;
    protected $_productAction;
    protected $_productRepositoryInterface;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistryInterface,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
	) {
        $this->_state = $state;
        $this->_conn = $resourceConnection;
        $this->_stockRegistryInterface = $stockRegistryInterface;
        $this->_productAction = $productAction;
        $this->_productRepositoryInterface = $productRepositoryInterface;

		parent::__construct();
	}

	/**
	 * Configures the current command.
	 */
	protected function configure()
	{
		$this
			->setName('datacom:aligninventory')
			->setDescription('Sincronizza le giacenze inventariali dei prodotti con sorgenti esterni');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
	{
        $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        /*$rows = file(dirname(__FILE__).'/Cartel1.csv');
        foreach ($rows as $r) {
            $d = explode(';', $r);
            $product = $this->_productRepositoryInterface->getById(intval($d[0]));
            if ($product->getData(self::ATTRIBUTE_CODE_UNICO_INVENTARIO) == 0) continue;
            $stockItem = $this->_stockRegistryInterface->getStockItem($product->getId());
            $newQty = intval($d[1]);
            if ($newQty == $stockItem->getQty()) continue;
            if ($newQty == 0) {
                $stockItem->setIsInStock(0);
            }
            echo "FATTO: ".$product->getSku()."\r\n";
            $stockItem->setQty($newQty);
            $stockItem->save();
        }

        return;*/

        $now = new \DateTime();
        $output->writeln(sprintf('Avvio procedura allineamento inventario - %s', $now->format('Y-m-d H:i:s')));

        $lockFile = dirname(__FILE__).'/AlignInventoryCommand.lock';
        $maxRows = 100;

        if (file_exists($lockFile)) {
            $output->writeln('Un altro allineamento è già in esecuzione. Attendere.');
            return;
        }

        touch($lockFile);

        $err = null;

        try {
            $hasFinished = $this->_execute($input, $output, $maxRows);
        } catch (\Exception $ex) {
            $err = $ex;
        }

        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        if (!is_null($err)) throw $err;

        if ($hasFinished) {
            $output->writeln('Operazione completata');
            $output->writeln('----------------------');
            return;
        } 
        $output->writeln('----------------------');
    }

    protected function _execute(InputInterface $input, OutputInterface $output, $maxRows) {
        $handleStock = true;
        $handlePrice = true;

        $toEnableIds = [];
        $toDisableIds = [];

        $output->writeln(sprintf('Gestione stock: %s', $handleStock ? 'si' : 'no'));
        $output->writeln(sprintf('Gestione prezzi: %s', $handlePrice ? 'si' : 'no'));
        
        $frontendStoreId = 1;
        
        $lastEntityId = 0;
        $lastIdFile = dirname(__FILE__).'/AlignInventoryCommand.info';
        if (file_exists($lastIdFile)) {
            $lastEntityId = file_get_contents($lastIdFile);
            $lastEntityId = trim($lastEntityId);
            $lastEntityId = intval($lastEntityId);
        }

        $output->write(sprintf('Gestione file salvati precedentemente...'));
        try {
            $this->checkTodo('toenable.json', $frontendStoreId);
            $this->checkTodo('todisable.json', $frontendStoreId);
            $output->writeln(sprintf('...fatto'));
        } catch (\Exception $ex) {
            $output->writeln(sprintf('...errore'));
            throw $ex;
        }
        
        $conn = $this->_conn->getConnection();

        $rows = $conn->fetchAll(sprintf('SELECT e.entity_id 
        FROM mg_catalog_product_entity e
        INNER JOIN mg_catalog_product_entity_int ei ON ei.attribute_id=174 AND ei.store_id=0 AND ei.entity_id=e.entity_id
        WHERE ei.value=1 AND e.entity_id>%d
        ORDER BY e.entity_id ASC
        LIMIT %d', $lastEntityId, $maxRows));

        /*$rows = [
            0 => [
                'entity_id' => 14632
            ]
        ];*/

        $stockRules = $this->getStockRulesSettings();
        $unicoData = $this->getInventoryDataUnico($stockRules['UnicoDiscount']);

        $empty = true;
        foreach ($rows as $r) {
            $empty = false;
            try {
                $product = $this->_productRepositoryInterface->getById($r['entity_id'], false, $frontendStoreId);
                $wasDisabled = $product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
                $isProductStillPresent = array_key_exists($product->getSku(), $unicoData);

                if ($isProductStillPresent) {
                    $forceDisable = $unicoData[$product->getSku()]['price'] == 0;

                    if (!$wasDisabled && $forceDisable) {
                        $output->writeln(sprintf('Articolo %s gestito tramite inventario UNICO ma con prezzo a zero. Disattivato', $product->getSku()));
                        $toDisableIds[] = $product->getId();
                        $lastEntityId = $r['entity_id'];
                        continue;
                    }
                }

                $hasAlreadySavedProduct = false;
                $details = $this->manageInventoryData($product, $unicoData, $handleStock, $handlePrice, $hasAlreadySavedProduct);
                if (empty($details)) {
                    $lastEntityId = $r['entity_id'];
                    continue;
                }
                $output->writeln(sprintf('Articolo %s gestito tramite inventario: %s', $product->getSku(), implode(', ', $details)));

                if ($hasAlreadySavedProduct) {
                    $lastEntityId = $r['entity_id'];
                    continue;
                }

                if (!$isProductStillPresent) {
                    if ($wasDisabled) {
                        $lastEntityId = $r['entity_id'];
                        continue;
                    }
                    $output->writeln(sprintf('Articolo %s gestito tramite inventario UNICO ma non più presente. Disattivato', $product->getSku()));
                    //$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    $toDisableIds[] = $product->getId();
                    //$this->_productRepositoryInterface->save($product);
                    $lastEntityId = $r['entity_id'];
                    continue;
                } else if ($wasDisabled) {
                    $output->writeln(sprintf('Articolo %s gestito tramite inventario UNICO ma disattivato. Riattivato', $product->getSku()));
                    //$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                    $toEnableIds[] = $product->getId();
                    //$this->_productRepositoryInterface->save($product);
                    $lastEntityId = $r['entity_id'];
                    continue;
                }
            } catch (\Exception $ex) {
                $lastEntityId = $r['entity_id'];
                $output->writeln($ex->getMessage());
                $output->writeln($ex->getTraceAsString());
                continue;
            }
        }

        if (file_exists($lastIdFile)) {
            unlink($lastIdFile);
        }

        if ($empty) return true;
        
        file_put_contents($lastIdFile, $lastEntityId.'');

        if (!empty($toEnableIds)) {
            try {
                $output->writeln(sprintf('Riattivazione %d articoli...', count($toEnableIds)));
                $this->_productAction->updateAttributes($toEnableIds, ['status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED], $frontendStoreId);
                $output->writeln(sprintf('...fatto'));
            } catch (\Exception $ex) {
                $this->logUpdatedIds($toEnableIds, 'toenable.json');
                if (!empty($toDisableIds)) {
                    $this->logUpdatedIds($toDisableIds, 'todisable.json');
                }
                throw $ex;
            }
        }

        if (!empty($toDisableIds)) {
            try {
                $output->writeln(sprintf('Disattivazione %d articoli', count($toDisableIds)));
                $this->_productAction->updateAttributes($toDisableIds, ['status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED], $frontendStoreId);
                $output->writeln(sprintf('...fatto'));
            } catch (\Exception $ex) {
                $this->logUpdatedIds($toDisableIds, 'todisable.json');
                throw $ex;
            }
        }

        return false;
    }

    private function checkTodo($filename, $frontendStoreId) {
        $i = 0;
        do {
            $i++;
            $file = sprintf('%s/%s.%d', dirname(__FILE__), $filename, $i);

            if (!file_exists($file)) break;

            $ids = file_get_contents($file);
            if (empty($ids)) continue;
            $ids = json_decode($ids, true);
            if (empty($ids)) continue;

            if ($filename == 'toenable.json') {
                $this->_productAction->updateAttributes($ids, ['status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED], $frontendStoreId);
                unlink($file);
                continue;
            }

            $this->_productAction->updateAttributes($ids, ['status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED], $frontendStoreId);
            unlink($file);
        } while (true);
    }

    private function logUpdatedIds($ids, $filename) {
        $i = 0;
        do {
            $i++;
            $file = sprintf('%s/%s.%d', dirname(__FILE__), $filename, $i);
        } while (file_exists($file));
        
        file_put_contents($file, json_encode($ids));
    }

    protected function manageInventoryData($product, $unicoData, $handleStock, $handlePrice, &$hasAlreadySavedProduct) {
        $attrData = [];
        $returnDetails = [];
        $returnDetails[] = 'UNICO';

        if (!array_key_exists($product->getSku(), $unicoData)) {
            $hasAlreadySavedProduct = false;
            return $returnDetails;
        }

        if ($this->updateProductQty($product->getId(), $unicoData[$product->getSku()]['qty'])) {
            $hasAlreadySavedProduct = true;
            return $returnDetails;
        }

        if ($this->updateProductPrice($product, $unicoData[$product->getSku()]['price'], $unicoData[$product->getSku()]['special_price'])) {
            $hasAlreadySavedProduct = true;
            return $returnDetails;
        }

        /*$newQty = $this->manageDataUnico($product, $unicoData, $attrData);
        if ($newQty > -1) {
            if ($handleStock) {
                $this->updateProductQty($product->getId(), $newQty);
            }
            if ($handlePrice) {
                $this->updateProductPrice($product, true);
            }
            $returnDetails[] = 'UNICO';
        } else {
            if ($handlePrice) {
                $this->updateProductPrice($product, false);
            }
        }*/

        if (empty($returnDetails)) {
            $returnDetails[] = 'nessuno';
        }
        
        if (empty($attrData)) return $returnDetails;

        $this->_productAction->updateAttributes([$product->getId()], $attrData, 0);

        return $returnDetails;
    }

    protected function updateProductPrice($product, $unicoPrice, $unicoSpecialPrice) { 
        $retval = false;
        if ($product->getPrice() != $unicoPrice) {
            $product->setPrice($unicoPrice);
            $retval = true;
        }
        if ($product->getSpecialPrice() != $unicoSpecialPrice) {
            $product->setSpecialPrice($unicoSpecialPrice);
            $retval = true;
        }
        if ($retval) {
            $this->_productRepositoryInterface->save($product);
        }        
        return $retval;
    }

    /*protected function updateProductPrice($product, $useUnicoPrice) {
        $unicoPrice = $product->getData(self::ATTRIBUTE_CODE_UNICO_PREZZO);    
        if (!$useUnicoPrice) {
            $oldPrice = $product->getData(self::ATTRIBUTE_CODE_VECCHIO_PREZZO_SCONTATO);
            if ($product->getSpecialPrice() == $oldPrice &&
            is_null($product->getData(self::ATTRIBUTE_CODE_VECCHIO_PREZZO_SCONTATO))) return;
            $product->setData(self::ATTRIBUTE_CODE_VECCHIO_PREZZO_SCONTATO, null);
            $product->setSpecialPrice($oldPrice);
            $this->_productRepositoryInterface->save($product);
            echo "ci passo \r\n";
            return;
        }
        
        $oldSpecialPrice = $product->getSpecialPrice();
        if (is_null($oldSpecialPrice) || empty($oldSpecialPrice)) {
            $oldSpecialPrice = 99999;
        }
        
        //$this->_productAction->updateAttributes([$product->getId()], [self::ATTRIBUTE_CODE_VECCHIO_PREZZO_SCONTATO => $product->getSpecialPrice()], 0);
        
        if ($product->getData(self::ATTRIBUTE_CODE_VECCHIO_PREZZO_SCONTATO) == $product->getSpecialPrice() &&
        ($oldSpecialPrice <= $unicoPrice || $product->getSpecialPrice() == $unicoPrice)) return;

        $product->setData(self::ATTRIBUTE_CODE_VECCHIO_PREZZO_SCONTATO, $product->getSpecialPrice());
        
        if ($oldSpecialPrice > $unicoPrice) {
            $product->setSpecialPrice($unicoPrice);
            $product->setSpecialFromDate(strtotime('yesterday'));
            $product->setSpecialToDate(strtotime('+3 years'));
        }
        
        $this->_productRepositoryInterface->save($product);
    }*/

    protected function updateProductQty($productId, $newQty) {
        $stockItem = $this->_stockRegistryInterface->getStockItem($productId);

        if ($stockItem->getQty() == $newQty) return false;
        $wasInStock = $stockItem->getIsInStock();
        $stockItem->setQty($newQty);
        if (!$wasInStock && $newQty > 0) {
            $stockItem->setIsInStock(1);
        }
        $stockItem->save();
        return true;
    }

    /*protected function manageDataUnico($product, $unicoData, &$attrData) {
        if (!array_key_exists($product->getSku(), $unicoData)) {
            if ($product->getData(self::ATTRIBUTE_CODE_UNICO_INVENTARIO) != 0) {
                $attrData[self::ATTRIBUTE_CODE_UNICO_INVENTARIO] = 0;
            }

            if ($product->getData(self::ATTRIBUTE_CODE_UNICO_PREZZO) != 0) {
                $attrData[self::ATTRIBUTE_CODE_UNICO_PREZZO] = 0;
            }

            return -1;
        }
        
        if ($product->getData(self::ATTRIBUTE_CODE_UNICO_INVENTARIO) != 1) {
            $attrData[self::ATTRIBUTE_CODE_UNICO_INVENTARIO] = 1;
        }

        if ($product->getData(self::ATTRIBUTE_CODE_UNICO_PREZZO) != $unicoData[$product->getSku()]['price']) {
            $attrData[self::ATTRIBUTE_CODE_UNICO_PREZZO] = $unicoData[$product->getSku()]['price'];
        }
        
        return $unicoData[$product->getSku()]['qty'];
    }*/

    protected function getInventoryDataUnico($unicoDiscountPercentage) {
        $targetFile = BP.'/inventario/listino_unico.csv';

        $filwRows = file($targetFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        $maxUnicoQty = 20;

        $productsData = [];
        foreach ($filwRows as $fullRow) {
            $row = explode(';', $fullRow);

            $curQty = intval($row[1]);
            if ($curQty > $maxUnicoQty) {
                $curQty = $maxUnicoQty;
            }

            $priceCol = floatval($row[3]);
            $taxRate = floatval($row[4]);
            //$priceCol =  $priceCol + ($priceCol * $taxRate);
            $priceCol = round($priceCol, 2);
            $specialPriceCol = round($priceCol * ((100 - $unicoDiscountPercentage) / 100), 2);
            $productsData[''.$row[0]] = [
                'qty' => $curQty,
                'price' => $priceCol,
                'special_price' => $specialPriceCol
            ];
        }

        return $productsData;
    }

    private function getStockRulesSettings() {
        return json_decode(file_get_contents(BP.'/inventario/StockRules.json'), true);
    }
}