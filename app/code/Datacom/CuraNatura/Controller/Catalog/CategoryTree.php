<?php

namespace Datacom\CuraNatura\Controller\Catalog;

class CategoryTree extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper;
    protected $_categoryRepository;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
	)
	{
        parent::__construct($context);

        $this->_jsonHelper = $jsonHelper;
        $this->_categoryRepository = $categoryRepository;
        $this->_storeManager = $storeManager;

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
        $targetCatId = $this->getRequest()->getParam('category_id', -1);
        
        if ($targetCatId == -1) {
            $targetCatId = $this->_storeManager->getStore(1)->getRootCategoryId();
        }

        $response = $this->_getCategoryTree($this->_categoryRepository->get($targetCatId, $this->_storeManager->getStore()->getId()));

        return $response;
    }

    private function _getCategoryTree($category) {
        $retval = [
            'name' => $category->getName(),
            'id' => $category->getId(),
            'subcats' => []
        ];
        foreach ($category->getChildrenCategories() as $subcat) {
            $retval['subcats'][] = $this->_getCategoryTree($subcat);
        }
        return $retval;
    }

    private function _getFormattedPrice($price) {
        if (empty($price)) return 0;

        return $price;
    }
}
