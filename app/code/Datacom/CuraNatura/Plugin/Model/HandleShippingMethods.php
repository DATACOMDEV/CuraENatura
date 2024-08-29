<?php
/**
 * @copyright Copyright (c) sbdevblog (http://www.sbdevblog.com)
 */
namespace Datacom\CuraNatura\Plugin\Model;

class HandleShippingMethods
{
    protected $_checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure $proceed,
         $carrierCode,
         $request
    ) {
        /*if ($carrierCode == 'flatrate' || $carrierCode == 'tablerate') { // add relevant shipping code
            $quote = $this->_checkoutSession->getQuote();
            if ($quote && $quote->getGrandTotal() >= 50) return false;
        }*/
        $retval =  $proceed($carrierCode, $request);
        /*if ($carrierCode == 'flatrate' || $carrierCode == 'tablerate') { // add relevant shipping code
            $quote = $this->_checkoutSession->getQuote();
            if ($quote && $quote->getGrandTotal() >= 50) return false;
        }*/
       
        $quote = $this->_checkoutSession->getQuote();
        
        if (!$quote) return $retval;
        if ($carrierCode != 'flatrate' && $carrierCode != 'tablerate') return $retval;
        //if ($quote && $quote->getGrandTotal() < 50) return $retval;
        if ($this->getQuoteEuroAmount($quote) < 50) return $retval;
        if ($quote->getShippingAddress()->getCountryId() != 'IT') return $retval;
        
        foreach ($retval->getResult()->getAllRates() as $rate) {            
            if ($rate->getCarrier() == 'flatrate') {
                $rate->setPrice(2.0);
                $rate->setCost(2.0);
                continue;
            }
            if ($rate->getCarrier() == 'tablerate') {
                $rate->setPrice(0.0);
                $rate->setCost(0.0);
                continue;
            }
        }
        return $retval;
    }

    private function getQuoteEuroAmount($quote) {
        //$retval = $quote->getSubtotal();
        $retval = 0;
        $items = $quote->getAllVisibleItems();
        foreach ($items as $itm) {
            $retval += $itm->getRowTotalInclTax();
        }
        return $retval;
    }
}
