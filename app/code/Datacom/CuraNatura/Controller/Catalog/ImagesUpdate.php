<?php

namespace Datacom\CuraNatura\Controller\Catalog;

class ImagesUpdate extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper;
    protected $_productRepository;
    protected $fileSystem;

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

        $tempImageFolder = sprintf('%s/pub/media/datacom/images', BP);

        foreach ($reqData as $sku => $data) {
            try {
				$targetProduct = $this->_productRepository->get($sku, false, \Magento\Store\Model\Store::DEFAULT_STORE_ID);
	
				if (empty($targetProduct) || !$targetProduct->getId()) throw new \Magento\Framework\Exception\NoSuchEntityException();
			} catch (\Magento\Framework\Exception\NoSuchEntityException $th) {
				continue;
			}

            foreach ($targetProduct->getMediaGalleryImages() as $img) {
                $imgFile = sprintf(
                    '%scatalog/product%s', 
                    $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath(),
                    $img->getFile()
                );

                if (!file_exists($imgFile)) continue;

                unlink($imgFile);
            }

            $targetProduct->setMediaGalleryEntries([]);

            //$this->_productRepository->save($targetProduct);

            foreach ($data as $img) {
                $imageTypeData = [];

                if ($img['image']) {
                    $imageTypeData[] = 'image';
                }

                if ($img['small_image']) {
                    $imageTypeData[] = 'small_image';
                }

                if ($img['thumbnail']) {
                    $imageTypeData[] = 'thumbnail';
                }

                $decodedImage = base64_decode($img['base64_encoded']);
                $targetFile = sprintf('%s/%s', $tempImageFolder, $img['filename']);
                file_put_contents($targetFile, $decodedImage);
                $relativeFile = sprintf('datacom/images/%s', $img['filename']);
                $targetProduct->addImageToMediaGallery($relativeFile, $imageTypeData, true, false);
                if (!file_exists($targetFile)) continue;
                unlink($targetFile);
            }

            //$this->_productRepository->save($targetProduct);
            $targetProduct->save();
        }

        /*$response = $this->_jsonHelper->jsonEncode($response);

        $this->getResponse()->setNoCacheHeaders();
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($response);*/
    }
}
