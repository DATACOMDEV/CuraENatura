<?php

namespace Datacom\CuraNatura\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KelkooFeedCommand extends \Symfony\Component\Console\Command\Command
{
    protected $_productFactory;
    protected $_productRepositoryInterface;
    protected $_categoryRepository;
    protected $_storeManager;
    protected $_dir;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\DirectoryList $dir
	) {
        $this->_state = $state;
        $this->_productFactory = $productFactory;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_categoryRepository = $categoryRepository;
        $this->_storeManager = $storeManager;
        $this->_dir = $dir;

		parent::__construct();
	}

	/**
	 * Configures the current command.
	 */
	protected function configure()
	{
		$this
			->setName('datacom:kelkoofeed')
			->setDescription('Genera il file feed di Kelkoo');
    }

    protected function getSimpleProductsIds() {
        return $this->_productFactory->create()
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', 4)
            ->addAttributeToFilter('type_id', 'simple')
            ->addAttributeToFilter('trovaprezzi', true)
            ->getAllIds();
    }

    protected function getConfigurableProductsIds() {
        return $this->_productFactory->create()
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', 4)
            ->addAttributeToFilter('type_id', 'configurable')
            ->addAttributeToFilter('trovaprezzi', true)
            ->getAllIds();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
	{
        $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $outputFile = $this->_dir->getPath('media').'/kelkoo_export.html';
        $head = array('Product Name', 'Brand', 'Short Product Description', 'Product Description', 'Original Product Price', 'Product Price', 'Product Id', 'Product URL', 'Availability', 'Num. Availability', 'Categories', 'Image URL', 'Shipping Cost', 'Weight', 'SKU code', 'EAN code', 'Shipping Time');

        if (file_exists($outputFile)) {
            unlink($outputFile);
        }

        file_put_contents($outputFile, '<b>'.implode('</b> | <b>', $head).'</b><endrecord>', FILE_APPEND);
        
        /* NON HANNO PRODOTTI CONFIGURABILI
        $prodIds = $this->getConfigurableProductsIds();
        foreach ($prodIds as $prodId) {
            $product = $this->_productRepositoryInterface->getById($prodId);
        }*/

        $prodIds = $this->getSimpleProductsIds();
        foreach ($prodIds as $prodId) {
            $product = $this->_productRepositoryInterface->getById($prodId);
            $categoryIds = $product->getCategoryIds();

            $category = array();
            foreach ($categoryIds as $catId) {
                $curCat = $this->_categoryRepository->get($catId);
                $category[] = $curCat->getName();
            }
            
            $productQty = $product->getExtensionAttributes()->getStockItem()->getQty();
            $shortDescription = str_replace(array('’'), array('\''), strip_tags($product->getShortDescription()));
            $shortDescription = substr($shortDescription, 0, 249);
            $description = str_replace(array('’'), array('\''), strip_tags($product->getDescription()));
            $description = substr($description, 0, 249);
            $rowData = array($product->getName(), $product->getAttributeText('manufacturer'), $shortDescription, $description, number_format($product->getPrice(), 2), number_format($product->getFinalPrice(), 2), $product->getId(), $product->getProductUrl(), $productQty ? 'Disponibile' : 'Non disponibile', $productQty, implode(';', $category), $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'.$product->getImage(), 'N/A', number_format($product->getWeight(), 2), $product->getSku(), 'N/A');

            file_put_contents($outputFile, '<br />'.implode('|', $rowData).'|<endrecord>', FILE_APPEND);
        }
    }
}