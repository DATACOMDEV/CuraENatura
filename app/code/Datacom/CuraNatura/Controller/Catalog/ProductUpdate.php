<?php

namespace Datacom\CuraNatura\Controller\Catalog;

class ProductUpdate extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper;
    protected $_productRepository;
    protected $_taxCalculation;
    protected $_productAction;
    protected $_productFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory
	)
	{
        parent::__construct($context);

        $this->_jsonHelper = $jsonHelper;
        $this->_productRepository = $productRepository;
        $this->_taxCalculation = $taxCalculation;
        $this->_productAction = $productAction;
        $this->_productFactory = $productFactory;

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

        $taxRequest = $this->_taxCalculation->getRateRequest(null, null, null, $this->_storeManager->getStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID));

        foreach ($reqData as $sku => $data) {
            try {
				$targetProduct = $this->_productRepository->get($sku, false, \Magento\Store\Model\Store::DEFAULT_STORE_ID);
                $isNewProduct = false;
				if (empty($targetProduct) || !$targetProduct->getId()) throw new \Magento\Framework\Exception\NoSuchEntityException();
			} catch (\Magento\Framework\Exception\NoSuchEntityException $th) {
				$targetProduct = $this->_productFactory->create();
                $isNewProduct = true;

                //TODO: valorizzare tutti gli attributi obbligatori che però non si valorizzano da data entry
			}

            $attributesToUpdate = [];

            if (array_key_exists('tax_rate', $data)) {
                $newTaxRate = doubleval($data['tax_rate']);

                if ($this->_taxCalculation->getRate($taxRequest->setProductClassId($p->getTaxClassId())) != $newTaxRate) {
                    switch ($newTaxRate) {
                        case 4:
                            $attributesToUpdate['tax_rate'] = 13;
                            break;
                        case 10:
                            $attributesToUpdate['tax_rate'] = 12;
                            break;
                        default:    //22%
                        $attributesToUpdate['tax_rate'] = 11;
                            break;
                    }
                }
            }

            if (array_key_exists('name', $data) && $p->getName() != $data['name']) {
                $attributesToUpdate['name'] = $data['name'];
            }

            if (array_key_exists('manufacturer', $data) && $p->getAttributeText('manufacturer') != $data['manufacturer']) {
                $attr = $p->getResource()->getAttribute('manufacturer');
                if ($attr->usesSource()) {
                    $optionId = $attr->getSource()->getOptionId($data['manufacturer']);
                }
                $attributesToUpdate['manufacturer'] = $optionId;
            }

            if (array_key_exists('composition', $data) && $p->getData('composition') != $data['composition']) {
                $attributesToUpdate['composition'] = $data['composition'];
            }

            if (array_key_exists('contraindications', $data) && $p->getData('contraindications') != $data['contraindications']) {
                $attributesToUpdate['contraindications'] = $data['contraindications'];
            }

            if (array_key_exists('use_method', $data) && $p->getData('use_method') != $data['use_method']) {
                $attributesToUpdate['use_method'] = $data['use_method'];
            }

            if (array_key_exists('description', $data) && $p->getData('description') != $data['description']) {
                $attributesToUpdate['description'] = $data['description'];
            }

            if (array_key_exists('short_description', $data) && $p->getData('short_description') != $data['short_description']) {
                $attributesToUpdate['short_description'] = $data['short_description'];
            }

            if (array_key_exists('meta_title', $data) && $p->getData('meta_title') != $data['meta_title']) {
                $attributesToUpdate['meta_title'] = $data['meta_title'];
            }

            if (array_key_exists('meta_description', $data) && $p->getData('meta_description') != $data['meta_description']) {
                $attributesToUpdate['meta_description'] = $data['meta_description'];
            }

            if (array_key_exists('meta_keyword', $data) && $p->getData('meta_keyword') != $data['meta_keyword']) {
                $attributesToUpdate['meta_keyword'] = $data['meta_keyword'];
            }

            if (!empty($attributesToUpdate)) {
                $this->_productAction->updateAttributes([$p->getId()], $attributesToUpdate, \Magento\Store\Model\Store::DEFAULT_STORE_ID);
            }

            if (!array_key_exists('url_key', $data)) continue;

            if ($data['url_key'] == $p->getUrlKey()) continue;

            $p->setUrlKey($data['url_key']);

            try {
                $this->_productRepository->save($targetProduct);
            } catch (\Exception $ex) {
                throw new \Exception('Impossibile salvare il prodotto con la nuova url key');
            }

            /*if (!$mustSave) continue;

            $this->_productRepository->save($targetProduct);*/
        }

        /*$response = $this->_jsonHelper->jsonEncode($response);

        $this->getResponse()->setNoCacheHeaders();
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($response);*/
    }
}
