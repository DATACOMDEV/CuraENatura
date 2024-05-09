<?php

namespace Datacom\CuraNatura\Controller\Catalog;

class ProductInventory extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper;
    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
	)
	{
        parent::__construct($context);

        $this->_jsonHelper = $jsonHelper;
        $this->_productCollectionFactory = $productCollectionFactory;

        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	}

	public function execute()
	{
        $response = $this->innerExec();

        $response = $this->_jsonHelper->jsonEncode($response);

        $this->getResponse()->setNoCacheHeaders();
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($response);
    }

    private function innerExec() {
        $targetSku = $this->getRequest()->getParam('sku', -1);

        $getMagazzinoFile = $this->getRequest()->getParam('magazzino', -1);

        if ($getMagazzinoFile == -1) {
            $response = $this->_getCatalogInventoryData($targetSku);
        } else {
            $response = $this->_getMagazzinoData($targetSku, $getMagazzinoFile);
        }

        return $response;
    }

    private function _getMagazzinoData($targetSku, $getMagazzinoFile) {
        $retval = [
            'data' => [
                'skus' => [],
                'prices' => []
            ]
        ];

        if ($getMagazzinoFile == 'UNICO') {
            $targetFile = BP.'/inventario/listino_unico.csv';

            $filwRows = file($targetFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
            foreach ($filwRows as $fullRow) {
                $row = explode(';', $fullRow);
                $priceCol = floatval($row[3]);
                $retval['data']['skus'][] = $row[0];
                $retval['data']['prices'][$row[0]] = $priceCol;
            }
        }

        return $retval;
    }

    private function _getCatalogInventoryData($targetSku) {
        $retval = [
            'data' => []
        ];

        $productCollection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect(['sku', \Datacom\CuraNatura\Console\Command\AlignInventoryCommand::ATTRIBUTE_CODE_UNICO_INVENTARIO, 'price']);

        foreach ($productCollection as $p) {
            $retval['data'][$p->getSku()] = [
                'unico' => $p->getData(\Datacom\CuraNatura\Console\Command\AlignInventoryCommand::ATTRIBUTE_CODE_UNICO_INVENTARIO) == 0 ? false : true,
                'price' => $p->getPrice()
            ];
        }

        return $retval;
    }
}
