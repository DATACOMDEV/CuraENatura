<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Osc
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Osc\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class QuoteSubmitBefore
 * @package Magetop\Osc\Observer
 */
class QuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @param Session $checkoutSession
     *
     * @codeCoverageIgnore
     */
    public function __construct(Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $oscData = $this->checkoutSession->getOscData();
        if (isset($oscData['comment'])) {
            $order->setData('osc_order_comment', $oscData['comment']);
        }

        if (isset($oscData['deliveryTime'])) {
            $order->setData('osc_delivery_time', $oscData['deliveryTime']);
        }

        if (isset($oscData['houseSecurityCode'])) {
            $order->setData('osc_order_house_security_code', $oscData['houseSecurityCode']);
        }

        $address = $quote->getShippingAddress();
        if ($address->getUsedGiftWrap() && $address->hasData('osc_gift_wrap_amount')) {
            $order->setData('gift_wrap_type', $address->getGiftWrapType())
                ->setData('osc_gift_wrap_amount', $address->getOscGiftWrapAmount())
                ->setData('base_osc_gift_wrap_amount', $address->getBaseOscGiftWrapAmount());

            foreach ($order->getItems() as $item) {
                $quoteItem = $quote->getItemById($item->getQuoteItemId());
                if ($quoteItem && $quoteItem->hasData('osc_gift_wrap_amount')) {
                    $item->setData('osc_gift_wrap_amount', $quoteItem->getOscGiftWrapAmount())
                        ->setData('base_osc_gift_wrap_amount', $quoteItem->getBaseOscGiftWrapAmount());
                }
            }
        }
    }
}
