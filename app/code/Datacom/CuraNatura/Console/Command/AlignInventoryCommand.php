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

        if (file_exists($lockFile)) {
            $output->writeln('Un altro allineamento è già in esecuzione. Attendere.');
            return;
        }

        touch($lockFile);

        $err = null;

        try {
            $this->_execute($input, $output);
        } catch (\Exception $ex) {
            $err = $ex;
        }

        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        if (!is_null($err)) throw $err;

        $output->writeln('Operazione completata');
        $output->writeln('----------------------');
    }

    protected function _execute(InputInterface $input, OutputInterface $output) {
        $handleStock = true;
        $handlePrice = true;

        $output->writeln(sprintf('Gestione stock: %s', $handleStock ? 'si' : 'no'));
        $output->writeln(sprintf('Gestione prezzi: %s', $handlePrice ? 'si' : 'no'));
        
        $conn = $this->_conn->getConnection();

        $rows = $conn->fetchAll('SELECT e.entity_id 
        FROM mg_catalog_product_entity e
        INNER JOIN mg_catalog_product_entity_int ei ON ei.attribute_id=174 AND ei.store_id=0 AND ei.entity_id=e.entity_id
        WHERE ei.value=1
        ORDER BY e.entity_id ASC');

        /*$rows = [
            0 => [
                'entity_id' => 14632
            ]
        ];*/

        $stockRules = $this->getStockRulesSettings();
        $unicoData = $this->getInventoryDataUnico($stockRules['UnicoDiscount']);
        
        foreach ($rows as $r) {
            try {
                $product = $this->_productRepositoryInterface->getById($r['entity_id']);
                $wasDisabled = $product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;

                $hasAlreadySavedProduct = false;
                $details = $this->manageInventoryData($product, $unicoData, $handleStock, $handlePrice, $hasAlreadySavedProduct);
                if (empty($details)) continue;
                $output->writeln(sprintf('Articolo %s gestito tramite inventario: %s', $product->getSku(), implode(', ', $details)));

                if ($hasAlreadySavedProduct) continue;

                if (!array_key_exists($product->getSku(), $unicoData)) {
                    if ($wasDisabled) continue;
                    $output->writeln(sprintf('Articolo %s gestito tramite inventario UNICO ma non più presente. Disattivato', $product->getSku()));
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    $this->_productRepositoryInterface->save($product);
                    continue;
                } else if ($wasDisabled) {
                    $output->writeln(sprintf('Articolo %s gestito tramite inventario UNICO ma disattivato. Riattivato', $product->getSku()));
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                    $this->_productRepositoryInterface->save($product);
                    continue;
                }
            } catch (\Exception $ex) {
                $output->writeln($ex->getMessage());
                $output->writeln($ex->getTraceAsString());
                continue;
            }
        }
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
        if ($product->getPrice() == $unicoPrice) return false;

        $product->setPrice($unicoPrice);
        $product->setSpecialPrice($unicoSpecialPrice);
        $this->_productRepositoryInterface->save($product);
        return true;
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