<?php

namespace Datacom\CuraNatura\Block\Newsletter;

/**
 * @api
 * @since 100.0.2
 */
class Subscribe extends \Magento\Newsletter\Block\Subscribe
{
    protected $_request;
    protected $_urlEncoderInterface;
    protected $_urlDecoderInterface;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    )
    {
        $this->_request = $request;
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_urlEncoderInterface = $objectManager->create('Magento\Framework\Url\EncoderInterface');
        $this->_urlDecoderInterface = $objectManager->create('Magento\Framework\Url\DecoderInterface');
        $this->_storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');

        parent::__construct($context, $data);
    }

    public function getRequest() {
        return $this->_request;
    }

    public function getUrlEncoder() {
        return $this->_urlEncoderInterface;
    }

    public function getUrlDecoder() {
        return $this->_urlDecoderInterface;
    }

    public function getStoreManager() {
        return $this->_storeManager;
    }
}
