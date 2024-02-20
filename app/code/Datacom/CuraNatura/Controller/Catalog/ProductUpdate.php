<?php

namespace Datacom\CuraNatura\Controller\Catalog;

class ProductUpdate extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper;
    protected $_productRepository;
    protected $_taxCalculation;
    protected $_productAction;
    protected $_productFactory;
    protected $_storeManager;
    protected $_optionLabelFactory;
    protected $_optionFactory;
    protected $_attributeOptionManagement;
    protected $_attributeRepository;
    protected $_tableFactory;

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

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $this->_optionLabelFactory = $objectManager->create('Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory');
        $this->_optionFactory = $objectManager->create('Magento\Eav\Api\Data\AttributeOptionInterfaceFactory');
        $this->_attributeOptionManagement = $objectManager->create('Magento\Eav\Api\AttributeOptionManagementInterface');
        $this->_attributeRepository = $objectManager->create('Magento\Catalog\Api\ProductAttributeRepositoryInterface');
        $this->_tableFactory = $objectManager->create('Magento\Eav\Model\Entity\Attribute\Source\TableFactory');
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
				if (empty($targetProduct) || !$targetProduct->getId()) throw new \Magento\Framework\Exception\NoSuchEntityException();
			} catch (\Magento\Framework\Exception\NoSuchEntityException $th) {
				$targetProduct = $this->_productFactory->create();

                $targetProduct->setName($data['name']);
                $targetProduct->setSku($sku);
                $targetProduct->setUrlKey($data['url_key']);
                $targetProduct->setAttributeSetId(4);
                $targetProduct->setPrice(1);

                $this->_productRepository->save($targetProduct);
                $targetProduct = $this->_productRepository->get($sku, false, \Magento\Store\Model\Store::DEFAULT_STORE_ID);
			}

            $attributesToUpdate = [];

            if (array_key_exists('tax_rate', $data)) {
                $newTaxRate = doubleval($data['tax_rate']);

                if ($this->_taxCalculation->getRate($taxRequest->setProductClassId($targetProduct->getTaxClassId())) != $newTaxRate) {
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

            if (array_key_exists('name', $data) && $targetProduct->getName() != $data['name']) {
                $attributesToUpdate['name'] = $data['name'];
            }

            if (array_key_exists('manufacturer', $data) && trim(strtolower($targetProduct->getAttributeText('manufacturer'))) != trim(strtolower($data['manufacturer']))) {
                $optionId = $this->getOptionId('manufacturer', $data['manufacturer']);
                if (!$optionId) {
                    $this->createAttributeValue('manufacturer', $data['manufacturer']);
                    $optionId = $this->getOptionId('manufacturer', $data['manufacturer'], true);
                }
                $attributesToUpdate['manufacturer'] = $optionId;
            }

            if (array_key_exists('composition', $data) && $targetProduct->getData('composizione') != $data['composition']) {
                $attributesToUpdate['composizione'] = $data['composition'];
            }

            if (array_key_exists('contraindications', $data) && $targetProduct->getData('controindicazioni') != $data['contraindications']) {
                $attributesToUpdate['controindicazioni'] = $data['contraindications'];
            }

            if (array_key_exists('use_method', $data) && $targetProduct->getData('modo_uso') != $data['use_method']) {
                $attributesToUpdate['modo_uso'] = $data['use_method'];
            }

            if (array_key_exists('description', $data) && $targetProduct->getData('description') != $data['description']) {
                $attributesToUpdate['description'] = $data['description'];
            }

            if (array_key_exists('short_description', $data) && $targetProduct->getData('short_description') != $data['short_description']) {
                $attributesToUpdate['short_description'] = $data['short_description'];
            }

            if (array_key_exists('meta_title', $data) && $targetProduct->getData('meta_title') != $data['meta_title']) {
                $attributesToUpdate['meta_title'] = $data['meta_title'];
            }

            if (array_key_exists('meta_description', $data) && $targetProduct->getData('meta_description') != $data['meta_description']) {
                $attributesToUpdate['meta_description'] = $data['meta_description'];
            }

            if (array_key_exists('meta_keyword', $data) && $targetProduct->getData('meta_keyword') != $data['meta_keyword']) {
                $attributesToUpdate['meta_keyword'] = $data['meta_keyword'];
            }

            if (!empty($attributesToUpdate)) {
                foreach ($attributesToUpdate as $code => $val) {
                    $targetProduct->setData($code, $val);
                }
                $this->_productRepository->save($targetProduct);
                //$this->_productAction->updateAttributes([$targetProduct->getId()], $attributesToUpdate, \Magento\Store\Model\Store::DEFAULT_STORE_ID);
            }

            $mustSave = false;

            if (array_key_exists('url_key', $data) && $data['url_key'] == $targetProduct->getUrlKey()) {
                $targetProduct->setUrlKey($data['url_key']);
                $mustSave = true;
            }

            if (array_key_exists('cat_ids', $data)) {
                $newCatIds = $data['cat_ids'];
                $oldCatIds = $targetProduct->getCategoryIds();

                $sortedNewCatIds = $newCatIds;
                $sortedOldCatIds = $oldCatIds;

                sort($sortedNewCatIds, SORT_NUMERIC);
                sort($sortedOldCatIds, SORT_NUMERIC);

                $sortedNewCatIds = implode(',', $sortedNewCatIds);
                $sortedOldCatIds = implode(',', $sortedOldCatIds);

                if ($sortedNewCatIds != $sortedOldCatIds) {
                    $targetProduct->setCategoryIds($newCatIds);
                    $mustSave = true;
                }
            }

            if (!$mustSave) continue;

            try {
                $this->_productRepository->save($targetProduct);
            } catch (\Exception $ex) {
                throw new \Exception(sprintf('Impossibile salvare il prodotto: %s', $ex->getMessage()));
            }

            /*if (!$mustSave) continue;

            $this->_productRepository->save($targetProduct);*/
        }

        /*$response = $this->_jsonHelper->jsonEncode($response);

        $this->getResponse()->setNoCacheHeaders();
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($response);*/
    }

    private function createAttributeValue($attributeCode, $label) {
        $optionLabel = $this->_optionLabelFactory->create();
        $optionLabel->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $optionLabel->setLabel($label);

        $option = $this->_optionFactory->create();
        $option->setLabel($optionLabel->getLabel());
        $option->setStoreLabels([$optionLabel]);
        $option->setSortOrder(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $option->setIsDefault(false);

        $this->_attributeOptionManagement->add(
            \Magento\Catalog\Model\Product::ENTITY,
            $this->_attributeRepository->get($attributeCode)->getAttributeId(),
            $option
        );
    }

    private function getOptionId($attributeCode, $label, $force = false)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $this->_attributeRepository->get($attributeCode);

        // Build option array if necessary
        if ($force === true || !isset($this->attributeValues[ $attribute->getAttributeId() ])) {
            $this->attributeValues[ $attribute->getAttributeId() ] = [];

            // We have to generate a new sourceModel instance each time through to prevent it from
            // referencing its _options cache. No other way to get it to pick up newly-added values.

            /** @var \Magento\Eav\Model\Entity\Attribute\Source\Table $sourceModel */
            $sourceModel = $this->_tableFactory->create();
            $sourceModel->setAttribute($attribute);

            foreach ($sourceModel->getAllOptions() as $option) {
                $this->attributeValues[ $attribute->getAttributeId() ][ $option['label'] ] = $option['value'];
            }
        }

        // Return option ID if exists
        if (isset($this->attributeValues[ $attribute->getAttributeId() ][ $label ])) {
            return $this->attributeValues[ $attribute->getAttributeId() ][ $label ];
        }

        // Return false if does not exist
        return false;
    }
}
