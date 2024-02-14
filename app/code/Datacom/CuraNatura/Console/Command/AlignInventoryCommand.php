<?php

namespace Datacom\CuraNatura\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AlignInventoryCommand extends \Symfony\Component\Console\Command\Command
{
    const ATTRIBUTE_CODE_UNICO_INVENTARIO = 'dtm_inventario_unico';
    const ATTRIBUTE_CODE_UNICO_PREZZO = 'dtm_inventario_unico_prezzo';

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

        $rows = file(dirname(__FILE__).'/Cartel1.csv');
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

        return;

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
        $conn = $this->_conn->getConnection();

        $rows = $conn->fetchAll('SELECT entity_id FROM mg_catalog_product_entity ORDER BY entity_id ASC');

        $unicoData = $this->getInventoryDataUnico();
        
        foreach ($rows as $r) {
            try {
                $product = $this->_productRepositoryInterface->getById($r['entity_id']);
                $details = $this->manageInventoryData($product, $unicoData);
                if (empty($details)) continue;
                $output->writeln(sprintf('Articolo %s gestito tramite inventario: %s', $product->getSku(), implode(', ', $details)));
            } catch (\Exception $ex) {
                $output->writeln($ex->getMessage());
                continue;
            }
        }
    }

    protected function manageInventoryData($product, $unicoData) {
        $attrData = [];
        $returnDetails = [];

        $newQty = $this->manageDataUnico($product, $unicoData, $attrData);
        if ($newQty > -1) {
            $this->updateProductQty($product->getId(), $newQty);
            $returnDetails[] = 'UNICO';
        }

        if (empty($attrData)) return $returnDetails;

        $this->_productAction->updateAttributes([$product->getId()], $attrData, 0);

        return $returnDetails;
    }

    protected function updateProductQty($productId, $newQty) {
        $stockItem = $this->_stockRegistryInterface->getStockItem($productId);

        if ($stockItem->getQty() == $newQty) return;

        $wasInStock = $stockItem->getIsInStock();

        $stockItem->setQty($newQty);
        if (!$wasInStock && $newQty > 0) {
            $stockItem->setIsInStock(1);
        }
        $stockItem->save();
    }

    protected function manageDataUnico($product, $unicoData, &$attrData) {
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
    }

    protected function getInventoryDataUnico() {
        $targetFile = BP.'/inventario/listino_unico.csv';

        $filwRows = file($targetFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        $productsData = [];
        foreach ($filwRows as $fullRow) {
            $row = explode(';', $fullRow);
            $productsData[''.$row[0]] = [
                'qty' => intval($row[1]),
                'price' => floatval($row[2])
            ];
        }

        return $productsData;
    }
}