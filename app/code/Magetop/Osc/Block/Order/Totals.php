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

namespace Magetop\Osc\Block\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;

/**
 * Class GiftWrap
 * @package Magetop\Osc\Block\Totals\Order
 */
class Totals extends Template
{
    /**
     * Init Totals
     */
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $source      = $totalsBlock->getSource();
        if ($source && !empty($source->getOscGiftWrapAmount())) {
            $totalsBlock->addTotal(new DataObject([
                'code'  => 'gift_wrap',
                'field' => 'osc_gift_wrap_amount',
                'label' => __('Gift Wrap'),
                'value' => $source->getOscGiftWrapAmount(),
                'base_value' => $source->getBaseOscGiftWrapAmount(),
            ]));
        }
    }
}
