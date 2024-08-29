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

namespace Magetop\Osc\Block\Adminhtml\Field;

/**
 * Class Order
 * @package Magetop\Osc\Block\Adminhtml\Field
 */
class Order extends AbstractOrderField
{
    const BLOCK_ID    = 'mposc-order-summary';
    const BLOCK_SCOPE = [6]; // position order summary

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string) __('Order Summary');
    }
}
