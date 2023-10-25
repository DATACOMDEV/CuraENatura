<?php

namespace Datacom\CuraNatura\Block\Checkout;

class CouponCheck extends \Magento\Framework\View\Element\Template {
    
    protected $_customerSession;
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
    }

    public function isLoggedIn() {
        return $this->_customerSession->isLoggedIn();
    }

    public function getCustomer() {
        return $this->_customerSession->getCustomer();
    }

    public function getQuote() {
        return $this->_checkoutSession->getQuote();
    }
}