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

use Magento\Framework\Exception\LocalizedException;

/**
 * Class CustomAttributeList
 * @package Magetop\Osc\Model\Plugin\Customer\Address
 */
class CustomAttributeList
{
    /**
     * @var \Magetop\Osc\Model\CustomAttributeList
     */
    private $customAttributeList;

    /**
     * CustomAttributeList constructor.
     *
     * @param \Magetop\Osc\Model\CustomAttributeList $customAttributeList
     */
    public function __construct(
        \Magetop\Osc\Model\CustomAttributeList $customAttributeList
    ) {
        $this->customAttributeList = $customAttributeList;
    }

    /**
     * @param \Magento\Customer\Model\Address\CustomAttributeList $subject
     * @param array $result
     *
     * @return array
     * @throws LocalizedException
     */
    public function afterGetAttributes(
        \Magento\Customer\Model\Address\CustomAttributeList $subject,
        $result
    ) {
        return array_merge($result, $this->customAttributeList->getAttributes());
    }
}
