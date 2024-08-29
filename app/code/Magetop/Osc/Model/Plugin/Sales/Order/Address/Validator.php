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

namespace Magetop\Osc\Model\Plugin\Sales\Order\Address;

use Magento\Sales\Model\Order\Address;
use Magetop\Osc\Helper\Data;

/**
 * Class Validator
 * @package Magetop\Osc\Model\Plugin\Sales\Order\Address
 */
class Validator
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * Validator constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Address\Validator $subject
     * @param Address $address
     *
     * @return array
     */
    public function beforeValidateForCustomer(Address\Validator $subject, Address $address)
    {
        if ($this->helper->isEnabled()) {
            $address->setShouldIgnoreValidation(true);
        }

        return [$address];
    }
}
