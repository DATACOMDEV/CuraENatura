<?php

namespace Datacom\CuraNatura\Controller\Catalog;

class PriceUpdate extends \Magento\Framework\App\Action\Action
{
    protected $_productRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository
	)
	{
        parent::__construct($context);

        $this->_jsonHelper = $jsonHelper;
        $this->_productRepository = $productRepository;

        //$this->_productCollectionFactory = $productCollectionFactory;

        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	}

	public function execute()
	{
        $reqContent = $this->getRequest()->getContent();

        if (empty($reqContent)) throw new \Exception('Manca il corpo della richiesta');

        $reqData = $this->_jsonHelper->jsonDecode($reqContent);

        if (empty($reqData)) throw new \Exception('Impossibile decodificare il corpo della richiesta');

        if (!array_key_exists('products', $reqData)) throw new \Exception('Formato json errato');

        $reqData = $reqData['products'];

        foreach ($reqData as $sku => $data) {
            try {
				$targetProduct = $this->_productRepository->get($sku, false, \Magento\Store\Model\Store::DEFAULT_STORE_ID);
	
				if (empty($targetProduct) || !$targetProduct->getId()) throw new \Magento\Framework\Exception\NoSuchEntityException();
			} catch (\Magento\Framework\Exception\NoSuchEntityException $th) {
				continue;
			}

            $mustSave = false;

            $newPrice = $data['price'];

            if ($targetProduct->getPrice() != $newPrice) {
                $targetProduct->setPrice($newPrice);
                $mustSave = true;
            }

            $newSpecialPrice = $data['special_price'];
            if ($newSpecialPrice >= $newPrice) {
                $newSpecialPrice = null;
            }

            if ($targetProduct->getSpecialPrice() != $newSpecialPrice) {
                if (empty($newSpecialPrice)) {
                    $targetProduct->setSpecialPrice(null);
                    $targetProduct->setSpecialFromDate(null);
                    $targetProduct->setSpecialToDate(null);
                } else {
                    $targetProduct->setSpecialPrice($newSpecialPrice);
                    $targetProduct->setSpecialFromDate(strtotime('yesterday'));
                    $targetProduct->setSpecialToDate(strtotime('+3 years'));
                }
                $mustSave = true;
            }

            if (!$mustSave) continue;

            $this->_productRepository->save($targetProduct);
        }

        /*$response = $this->_jsonHelper->jsonEncode($response);

        $this->getResponse()->setNoCacheHeaders();
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($response);*/
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
            $p = $this->_productRepository->get($s, false, 0);
            
            //$retval[$p->getId()] = [
                $retval[$p->getSku()] = [
                'sku' => $p->getSku(),
                'name' => $p->getName(),
                'price' => $this->_getFormattedPrice($p->getPrice()),
                'special_price' => $this->_getFormattedPrice($p->getSpecialPrice()),
                'status' => $p->getStatus() == 1 ? true : false
            ];
        }

        return $retval;
    }

    private function _getFormattedPrice($price) {
        if (empty($price)) return 0;

        return $price;
    }
}
