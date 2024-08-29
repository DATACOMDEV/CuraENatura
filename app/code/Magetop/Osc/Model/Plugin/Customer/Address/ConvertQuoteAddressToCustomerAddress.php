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

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Class ConvertQuoteAddressToCustomerAddress
 * @package Magetop\Osc\Model\Plugin\Customer\Address
 */
class ConvertQuoteAddressToCustomerAddress
{
    /**
     * @param Address $quoteAddress
     * @param AddressInterface $customerAddress
     *
     * @return AddressInterface
     */
    public function afterExportCustomerAddress(
        Address $quoteAddress,
        AddressInterface $customerAddress
    ) {
        for ($i = 1; $i <= 3; $i++) {
            $key = 'mposc_field_' . $i;
            if ($value = $quoteAddress->getData($key)) {
                $customerAddress->setCustomAttribute($key, $value);
            }
        }

        return $customerAddress;
    }
}
