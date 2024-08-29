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

namespace Magetop\Osc\Model\Plugin\CustomerAttributes;

use Magento\Eav\Model\Attribute;
use Magetop\CustomerAttributes\Helper\Data;
use Magetop\Osc\Helper\Address;

/**
 * Class Helper
 * @package Magetop\Osc\Model\Plugin\CustomerAttributes
 */
class Helper
{
    /**
     * @var Address
     */
    private $helper;

    /**
     * Helper constructor.
     *
     * @param Address $helper
     */
    public function __construct(Address $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Data $subject
     * @param Attribute[] $result
     *
     * @return Attribute[]
     */
    public function afterGetAttributeWithFilters(Data $subject, $result)
    {
        if (!$this->helper->isOscPage()) {
            return $result;
        }

        $position = [];
        foreach ($this->helper->getFieldPosition() as $item) {
            $position[$item['code']] = $item;
        }

        $attributes = [];

        foreach ($result as $attribute) {
            if (!isset($position[$attribute->getAttributeCode()])) {
                continue;
            }

            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
