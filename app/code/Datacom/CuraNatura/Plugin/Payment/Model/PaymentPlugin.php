<?php 

namespace Datacom\CuraNatura\Plugin\Payment\Model;

class PaymentPlugin {

    /*protected $_modelSession;
    protected $_customerFactory;*/
    protected $_checkoutSession;
    protected $_request;

    public function __construct(
        /*\Magento\Customer\Model\Session $modelSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,*/
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request
    ) {
        /*$this->_modelSession = $modelSession;
        $this->_customerFactory = $customerFactory;*/
        $this->_checkoutSession = $checkoutSession;
        $this->_request = $request;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function aroundGetIsActive(
        \Magento\Payment\Model\PaymentMethod $subject,
        \Closure $proceed
    ) {
        if ($subject->getCode() == 'msp_cashondelivery') return $this->getIsActiveMspCashondelivery($proceed, $subject);

        return $proceed();
    }

    private function getIsActiveMspCashondelivery($proceed, $subject) {
        if ($this->_request->getRequestString() == '/onestepcheckout/') return $proceed();
        //file_put_contents(dirname(__FILE__).'/test.txt', $this->_request->getRequestString()."\r\n", FILE_APPEND);
        //disattivato se il metodo di spedizione è Fermo Point
        if ($this->_checkoutSession->getQuote()->getShippingAddress()->getShippingMethod() == 'sendcloud_sendcloud') return false;
        
        return $proceed();
    }
}