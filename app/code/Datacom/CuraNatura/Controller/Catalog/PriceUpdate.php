<?php

namespace Datacom\CuraNatura\Controller\Catalog;

class PriceUpdate extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper;
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

            if ($targetProduct->getData(\Datacom\CuraNatura\Console\Command\AlignInventoryCommand::ATTRIBUTE_CODE_UNICO_INVENTARIO) == 1) {
                if (!empty($data['special_price']) && floatval($data['special_price']) < floatval($targetProduct->getData(\Datacom\CuraNatura\Console\Command\AlignInventoryCommand::ATTRIBUTE_CODE_UNICO_PREZZO))) continue;
            }

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
}
