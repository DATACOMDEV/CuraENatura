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

namespace Magetop\Osc\Model\Plugin\OrderAttributes;

use Magetop\OrderAttributes\Helper\Data;
use Magetop\OrderAttributes\Model\Attribute;
use Magetop\OrderAttributes\Model\Config\Source\Position;
use Magetop\Osc\Helper\Address;

/**
 * Class Helper
 * @package Magetop\Osc\Model\Plugin\OrderAttributes
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
    public function afterGetFilteredAttributes(Data $subject, $result)
    {
        if (!$this->helper->isOscPage()) {
            return $result;
        }

        $sortOrder = 0;

        $position = [];
        foreach ($this->helper->getOAFieldPosition() as $item) {
            $position[$item['code']] = $item;
        }

        $attributes = [];

        foreach ($result as $attribute) {
            $pos     = (int) $attribute->getPosition();
            $code    = (string) $attribute->getAttributeCode();
            $oaField = isset($position[$code]) ? $position[$code] : null;

            if (!$oaField) {
                if ($pos === 1) {
                    $attributes[] = $attribute;
                }
                continue;
            }

            switch ($pos) {
                case Position::SHIPPING_TOP:
                case Position::SHIPPING_BOTTOM:
                    $pos = !empty($oaField['bottom']) ? Position::SHIPPING_BOTTOM : Position::SHIPPING_TOP;
                    break;
                case Position::PAYMENT_TOP:
                case Position::PAYMENT_BOTTOM:
                    $pos = !empty($oaField['bottom']) ? Position::PAYMENT_BOTTOM : Position::PAYMENT_TOP;
                    break;
            }
            $attribute->setPosition($pos);
            $attribute->setSortOrder($sortOrder++);
            $attribute->setIsRequired($oaField['required']);

            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
