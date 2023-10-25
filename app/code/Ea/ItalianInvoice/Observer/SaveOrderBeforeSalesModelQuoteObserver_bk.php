<?php
namespace Ea\ItalianInvoice\Observer;


use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $helper;

    protected $logger;

    protected $quoteRepository;

    protected $_request;

    protected $_state;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Psr\Log\LoggerInterface $logger,
        \Ea\ItalianInvoice\Helper\Data $helper,
        \Magento\Framework\App\State $state
    )
    {
        $this->_request = $request;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->_state = $state;
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        ///////SAVE fiscal_sdi to BILLING
        $sdi = $quote->getBillingAddress()->getFiscalSdi();
        if (is_null($sdi)){
            $sdi_ext = $quote->getBillingAddress()->getExtensionAttributes()->getFiscalSdi();
            $order->getBillingAddress()->setFiscalSdi($sdi_ext);
            $quote->getBillingAddress()->setFiscalSdi($sdi_ext);
        }
        else{
            $order->getBillingAddress()->setFiscalSdi($sdi);
        }

        ///////SAVE fiscal_code to BILLING
        $cf = $quote->getBillingAddress()->getFiscalCode();
        if (is_null($cf)){
            $cf_ext = $quote->getBillingAddress()->getExtensionAttributes()->getFiscalCode();
            $order->getBillingAddress()->setFiscalCode($cf_ext);
            $quote->getBillingAddress()->setFiscalCode($cf_ext);
        }
        else{
            $order->getBillingAddress()->setFiscalCode($cf);
        }

        ///////SAVE fiscal_company to BILLING
        $company = $quote->getBillingAddress()->getFiscalCompany();

        if (is_null($company)){
            $company_ext = $quote->getBillingAddress()->getExtensionAttributes()->getFiscalCompany();
            $order->getBillingAddress()->setFiscalCompany($company_ext);
            $quote->getBillingAddress()->setFiscalCompany($company_ext);
        }
        else{
            $order->getBillingAddress()->setFiscalCompany($company);
        }

        if ($this->_state->getAreaCode() == 'adminhtml') {
            $postData = $this->_request->getParams();
            if (array_key_exists('shipping_same_as_billing', $postData)) {
                if ($postData['shipping_same_as_billing'] == 'on') {
                    $quote->getShippingAddress()->setFiscalSdi($quote->getBillingAddress()->getFiscalSdi());
                    $quote->getShippingAddress()->setFiscalCode($quote->getBillingAddress()->getFiscalCode());
                    $quote->getShippingAddress()->setFiscalCompany($quote->getBillingAddress()->getFiscalCompany());
                }
            }
        }

        if ($order->getShippingAddress()) {
            $this->helper->transportFieldsFromExtensionAttributesToObject(
                $quote->getShippingAddress(),
                $order->getShippingAddress(),
                'extra_checkout_shipping_address_fields'
            );
        }

    }
}
