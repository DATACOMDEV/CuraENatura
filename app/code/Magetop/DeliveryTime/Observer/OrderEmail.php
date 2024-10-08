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
 * @package     Magetop_DeliveryTime
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\DeliveryTime\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magetop\DeliveryTime\Helper\Data;

/**
 * Class OrderEmail
 * @package Magetop\DeliveryTime\Observer
 */
class OrderEmail implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getTransport();
        $order     = &$transport['order'];
        if ($order->getMpDeliveryInformation()) {
            $mpDeliveryInformation = Data::jsonDecode($order->getMpDeliveryInformation());
            $order->addData($mpDeliveryInformation)->save();
        }
    }
}
