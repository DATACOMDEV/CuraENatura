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

namespace Magetop\Osc\Model\Plugin\Quote;

use Closure;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Address
 * @package Magetop\Osc\Model\Plugin\Customer
 */
class Processor
{
    /**
     * @var StockStateInterface
     */
    protected $_stockState;

    /**
     * @param StockStateInterface $stockState
     */
    public function __construct(StockStateInterface $stockState)
    {
        $this->_stockState = $stockState;
    }

    /**
     * @param Item\Processor $subject
     * @param Closure $proceed
     * @param Item $item
     * @param DataObject $request
     * @param Product $candidate
     */
    public function aroundPrepare(
        Item\Processor $subject,
        Closure $proceed,
        Item $item,
        DataObject $request,
        Product $candidate
    ) {
        if ($this->_stockState->getStockQty($candidate->getId()) == $candidate->getCartQty()) {
            /**
             * We specify qty after we know about parent (for stock)
             */
            if ($request->getResetCount() && !$candidate->getStickWithinParent() && $item->getId() == $request->getId()) {
                $item->setData(CartItemInterface::KEY_QTY, 0);
            }
            $item->setQty($candidate->getCartQty());

            $customPrice = $request->getCustomPrice();
            if (!empty($customPrice)) {
                $item->setCustomPrice($customPrice);
                $item->setOriginalCustomPrice($customPrice);
            }
        } else {
            $proceed($item, $request, $candidate);
        }
    }
}
