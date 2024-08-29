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

namespace Magetop\Osc\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class GiftWrap
 * @package Magetop\Osc\Model\Total\Invoice
 */
class GiftWrap extends AbstractTotal
{
    /**
     * @param Invoice $invoice
     *
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if ($order->getOscGiftWrapAmount() < 0.0001) {
            return $this;
        }

        $totalGiftWrapAmount     = 0;
        $totalBaseGiftWrapAmount = 0;

        if ($order->getGiftWrapType() == \Magetop\Osc\Model\System\Config\Source\Giftwrap::PER_ITEM) {
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy() || ($orderItem->getOscGiftWrapAmount() < 0.001)) {
                    continue;
                }
                $rate = $item->getQty() / $orderItem->getQtyOrdered();

                $totalBaseGiftWrapAmount += $orderItem->getBaseOscGiftWrapAmount() * $rate;
                $totalGiftWrapAmount     += $orderItem->getOscGiftWrapAmount() * $rate;
            }
        } else {
            $invoiceCollections = $order->getInvoiceCollection();
            if ($invoiceCollections->getSize() == 0) {
                $totalGiftWrapAmount     = $order->getOscGiftWrapAmount();
                $totalBaseGiftWrapAmount = $order->getBaseOscGiftWrapAmount();
            }
        }
        $invoice->setBaseOscGiftWrapAmount($totalBaseGiftWrapAmount);
        $invoice->setOscGiftWrapAmount($totalGiftWrapAmount);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalGiftWrapAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $totalBaseGiftWrapAmount);

        return $this;
    }
}
