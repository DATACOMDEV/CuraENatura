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
 * Class AllowGuestCheckout
 * @package Magetop\Osc\Model\System\Config\Source
 */
class AllowGuestCheckout implements ArrayInterface
{
    const YES                    = 2;
    const REQUIRE_CREATE_ACCOUNT = 1;
    const REQUIRE_CREATE_LOGIN   = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::YES                    => __('Yes'),
            self::REQUIRE_CREATE_ACCOUNT => __('No (require create account)'),
            self::REQUIRE_CREATE_LOGIN   => __('No (require login)')
        ];
    }
}
