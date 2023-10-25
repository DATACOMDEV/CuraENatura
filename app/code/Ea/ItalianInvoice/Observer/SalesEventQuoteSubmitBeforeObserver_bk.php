<?php
namespace Ea\ItalianInvoice\Observer;

use \Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;


use \Magento\Checkout\Model\Session as CheckoutSession;

class SalesEventQuoteSubmitBeforeObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $helper;

    protected $logger;

    protected $quoteRepository;

    /** @var CheckoutSession */
    protected $checkoutSession;
    protected $addressInformation;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Psr\Log\LoggerInterface $logger,
        \Ea\ItalianInvoice\Helper\Data $helper,
        CheckoutSession $checkoutSession,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->addressInformation = $addressInformation;
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $quote = $this->checkoutSession->getQuote();


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('quote_address');

        //echo "<pre>";
        //print_r($quote->getBillingAddress()->getData());die

        $company = $quote->getBillingAddress()->getFiscalCompany();

        if($quote->getId()){
            ///////SAVE fiscal_company to BILLING
            if (is_null($company)){
                $company_ext = $quote->getBillingAddress()->getExtensionAttributes()->getFiscalCompany();
                if (!is_null($company_ext)) {
                    $quote->getBillingAddress()->setFiscalCompany($company_ext);
                }
                else{
                    $sql_company = "Select fiscal_company FROM $tableName where address_type = 'billing' and quote_id = ".$quote->getId()."  ORDER BY quote_id DESC LIMIT 1";
                    $result_company = $connection->fetchOne($sql_company);
                    $quote->getBillingAddress()->setFiscalCompany($result_company);


                }
            }
            else{
                $quote->getBillingAddress()->setFiscalCompany($company);
            }

            ///////SAVE fiscal_sdi to BILLING
            $sdi = $quote->getBillingAddress()->getFiscalSdi();
            if (is_null($sdi)){
                $sdi_ext = $quote->getBillingAddress()->getExtensionAttributes()->getFiscalSdi();
                if (!is_null($sdi_ext)) {
                    $quote->getBillingAddress()->setFiscalSdi($sdi_ext);
                }
                else{
                    $sql_sdi = "Select fiscal_sdi FROM $tableName where address_type = 'billing' and quote_id = ".$quote->getId()." ORDER BY quote_id DESC LIMIT 1";
                    $result_sdi = $connection->fetchOne($sql_sdi);
                    $quote->getBillingAddress()->setFiscalSdi($result_sdi);
                }
            }
            else{
                $quote->getBillingAddress()->setFiscalSdi($sdi);
            }

            ///////SAVE fiscal_code to BILLING
            $cf = $quote->getBillingAddress()->getFiscalCode();
            if (is_null($cf)){
                $cf_ext = $quote->getBillingAddress()->getExtensionAttributes()->getFiscalCode();
                if (!is_null($cf_ext)) {
                    $quote->getBillingAddress()->setFiscalCode($cf_ext);
                }
                else{
                    $sql_cf = "Select fiscal_code FROM $tableName where address_type = 'billing' and quote_id = ".$quote->getId()." ORDER BY quote_id DESC LIMIT 1";
                    $result_cf = $connection->fetchOne($sql_cf);
                    $quote->getBillingAddress()->setFiscalCode($result_cf);
                }
            }
            else{
                $quote->getBillingAddress()->setFiscalCode($cf);
            }
        }

    }
}
