<?php

namespace Datacom\CuraNatura\Controller\Catalog;

class ProductImages extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper;
    protected $_productRepository;
    protected $_fileSystem;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Filesystem $fileSystem
	)
	{
        parent::__construct($context);

        $this->_jsonHelper = $jsonHelper;
        $this->_productRepository = $productRepository;
        $this->_fileSystem = $fileSystem;

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
        $targetSku = $this->getRequest()->getParam('sku', -1);

        $response = [
            'status' => 'ok',
            'message' => null,
            'data' => null
        ];

        if ($targetSku == -1) {
            $response['status'] = 'ko';
            $response['message'] = 'Manca il parametro sku';
            return $response;
        }

        if (empty($targetSku) == -1) {
            $response['status'] = 'ko';
            $response['message'] = 'Sku non valido';
            return $response;
        }

        $response['data'] = $this->_getCatalogData($targetSku);

        if (empty($response['data'])) {
            $response['data'] = null;
        }

        return $response;
    }

    private function _getCatalogData($targetSku) {
        $p = $this->_productRepository->get($targetSku, false, \Magento\Store\Model\Store::DEFAULT_STORE_ID);
            
        $productImages = [];
        foreach ($p->getMediaGalleryImages() as $img) {
            $imgFile = sprintf(
                '%scatalog/product%s', 
                $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath(),
                $img->getFile()
            );

            $imgContent = file_get_contents($imgFile);

            $base64str = sprintf(
                'data:image/%s;base64,%s',
                pathinfo($imgFile, PATHINFO_EXTENSION),
                base64_encode($imgContent)
            );

            $productImages[$img->getId()] = [
                'thumbnail' =>  $img->getFile() == $p->getData('thumbnail') ? 1 : 0,
                'image' => $img->getFile() == $p->getData('image') ? 1 : 0,
                'small_image' =>  $img->getFile() == $p->getData('small_image') ? 1 : 0,
                'base64_encoded' => $base64str
                //'base64_encoded' => sprintf($this->_fileSystem->getDirectoryRead('%scatalog/product%s', \Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath(), $img->getFile())
            ];
        }

        return $productImages;
    }

    private function _getFormattedPrice($price) {
        if (empty($price)) return 0;

        return $price;
    }
}
