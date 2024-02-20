<?php

namespace Datacom\CuraNatura\Controller\Catalog;

class ProductStockRuleUpdate extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
	)
	{
        parent::__construct($context);

        $this->_jsonHelper = $jsonHelper;

        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	}

	public function execute()
	{
        $response = $this->innerExec();

        $response = $this->_jsonHelper->jsonEncode($response);

        $this->getResponse()->setNoCacheHeaders();
		$this->getResponse()->setHeader('Content-type', 'application/json');
		//$this->getResponse()->setBody($response);
    }

    private function innerExec() {
        $reqContent = $this->getRequest()->getContent();

        if (empty($reqContent)) throw new \Exception('Manca il corpo della richiesta');
        
        file_put_contents(BP.'/inventario/StockRules.json', $reqContent);

        $response = [
            'data' => 'ok'
        ];

        return $response;
    }
}
