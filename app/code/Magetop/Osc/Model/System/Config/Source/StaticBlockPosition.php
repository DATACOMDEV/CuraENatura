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

namespace Magetop\Osc\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class StaticBlockPosition
 * @package Magetop\Osc\Model\System\Config\Source
 */
class StaticBlockPosition implements ArrayInterface
{
    const NOT_SHOW                     = 0;
    const SHOW_IN_SUCCESS_PAGE         = 1;
    const SHOW_AT_TOP_CHECKOUT_PAGE    = 2;
    const SHOW_AT_BOTTOM_CHECKOUT_PAGE = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::NOT_SHOW                     => __('None'),
            self::SHOW_IN_SUCCESS_PAGE         => __('In Success Page'),
            self::SHOW_AT_TOP_CHECKOUT_PAGE    => __('At Top of Checkout Page'),
            self::SHOW_AT_BOTTOM_CHECKOUT_PAGE => __('At Bottom of Checkout Page')
        ];
    }
}
