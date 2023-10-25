<?php

namespace Datacom\CuraNatura\Controller\Catalog;

class ProductList extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper;
    protected $_resourceConnection;
    protected $_productRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ProductRepository $productRepository
	)
	{
        parent::__construct($context);

        $this->_jsonHelper = $jsonHelper;
        $this->_resourceConnection = $resourceConnection;
        $this->_productRepository = $productRepository;

        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	}

	public function execute()
	{
        $response = $this->innerExec();

        if ($response['status'] == 'ko') {
            //$this->getResponse()->setHttpResponseCode(201);
            $this->getResponse()->setStatusCode(400);
        }

        $response = $this->_jsonHelper->jsonEncode($response);

        $this->getResponse()->setNoCacheHeaders();
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($response);
    }

    private function innerExec() {
        $pageIndex = $this->getRequest()->getParam('index', -1);
        $pageSize = $this->getRequest()->getParam('size', -1);

        $response = [
            'status' => 'ok',
            'message' => null,
            'data' => null
        ];

        if ($pageIndex == -1) {
            $response['status'] = 'ko';
            $response['message'] = 'Manca il parametro index';
            return $response;
        }

        if ($pageIndex == 0) {
            $response['status'] = 'ko';
            $response['message'] = 'index deve essere maggiore di 0';
            return $response;
        }

        if ($pageSize == -1) {
            $response['status'] = 'ko';
            $response['message'] = 'Manca il parametro size';
            return $response;
        }

        if ($pageSize == 0) {
            $response['status'] = 'ko';
            $response['message'] = 'size deve essere maggiore di 0';
            return $response;
        }

        $response['data'] = $this->_getCatalogData($pageIndex, $pageSize);

        if (empty($response['data'])) {
            $response['data'] = null;
        }

        return $response;
    }

    private function _getCatalogData($pageIndex, $pageSize) {
        $retval = [];

        $p1 = (($pageIndex-1) * $pageSize);
        $p2 = $pageSize;

        $conn = $this->_resourceConnection->getConnection();

        $query = sprintf('SELECT sku FROM %s ORDER BY entity_id ASC LIMIT %d, %d', $this->_resourceConnection->getTableName('catalog_product_entity'), $p1, $p2);
        $skus = $conn->fetchCol($query);

        foreach ($skus as $s) {
            $p = $this->_productRepository->get($s, false, \Magento\Store\Model\Store::DEFAULT_STORE_ID);
            
            $specialPrice = $p->getSpecialPrice();
            if (empty($specialPrice)) {
                $specialPrice = $p->getPrice();
            }

            if ($specialPrice > $p->getPrice()) {
                $specialPrice = $p->getPrice();
            }

            //$retval[$p->getId()] = [
            $retval[$p->getSku()] = [
                'sku' => $p->getSku(),
                'name' => $p->getName(),
                'price' => $this->_getFormattedPrice($p->getPrice()),
                'special_price' => $this->_getFormattedPrice($specialPrice),
                'status' => $p->getStatus() == 1 ? true : false,
                'manufacturer' => $p->getAttributeText('manufacturer')
            ];
        }

        return $retval;
    }

    private function _getFormattedPrice($price) {
        if (empty($price)) return 0;

        return $price;
    }
}
