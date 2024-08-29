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

namespace Magetop\Osc\Model\Plugin\Customer;

use Magento\Customer\Api\Data\AddressInterface;

/**
 * Class Address
 * @package Magetop\Osc\Model\Plugin\Customer
 */
class Address
{
    /**
     * @param \Magento\Customer\Model\Address $subject
     * @param \Magento\Customer\Model\Address $result
     *
     * @return \Magento\Customer\Model\Address
     */
    public function afterUpdateData(\Magento\Customer\Model\Address $subject, $result)
    {
        $result->setShouldIgnoreValidation(true);

        return $result;
    }

    /**
     * @param \Magento\Customer\Model\Address $subject
     * @param AddressInterface $address
     *
     * @return mixed
     */
    public function beforeUpdateData(\Magento\Customer\Model\Address $subject, AddressInterface $address)
    {
        $customAttributes = $address->getCustomAttributes();
        foreach ($customAttributes as $key => $attribute) {
            if (($key === 'mposc_field_1' || $key === 'mposc_field_2' || $key === 'mposc_field_3') && !$attribute) {
                $address->setCustomAttribute($key, '');
            }
        }

        return [$address];
    }
}
