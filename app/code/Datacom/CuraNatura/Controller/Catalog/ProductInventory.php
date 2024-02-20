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

        $response = $this->_getCatalogInventoryData($targetSku);

        return $response;
    }

    private function _getCatalogInventoryData($targetSku) {
        $retval = [
            'data' => []
        ];

        $productCollection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect(['sku', \Datacom\CuraNatura\Console\Command\AlignInventoryCommand::ATTRIBUTE_CODE_UNICO_INVENTARIO]);

        foreach ($productCollection as $p) {
            $retval['data'][$p->getSku()] = [
                'unico' => $p->getData(\Datacom\CuraNatura\Console\Command\AlignInventoryCommand::ATTRIBUTE_CODE_UNICO_INVENTARIO) == 0 ? false : true
            ];
        }

        return $retval;
    }
}
