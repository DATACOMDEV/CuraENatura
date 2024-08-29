<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magetop.com license that is
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

namespace Magetop\Osc\Model\Plugin\Customer\Address;

use Closure;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;

/**
 * Class ConvertQuoteAddressToOrderAddress
 * @package Magetop\Osc\Model\Plugin\Customer\Address
 */
class ConvertQuoteAddressToOrderAddress
{
    /**
     * @param ToOrderAddress $subject
     * @param Closure $proceed
     * @param Address $quoteAddress
     * @param array $data
     *
     * @return mixed
     */
    public function aroundConvert(
        ToOrderAddress $subject,
        Closure $proceed,
        Address $quoteAddress,
        $data = []
    ) {
        $orderAddress = $proceed($quoteAddress, $data);

        for ($i = 1; $i <= 3; $i++) {
            $key = 'mposc_field_' . $i;
            if ($value = $quoteAddress->getData($key)) {
                $orderAddress->setData($key, $value);
            }
        }

        return $orderAddress;
    }
}
