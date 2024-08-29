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

namespace Magetop\Osc\Model\Plugin\Eav\Model\Validator\Attribute;

use Magento\Eav\Model\AttributeDataFactory;
use Magetop\Osc\Helper\Data as HelperData;

/**
 * Class Data
 * @package Magetop\Osc\Model\Plugin\Eav\Model\Validator\Attribute
 */
class Data extends \Magento\Eav\Model\Validator\Attribute\Data
{
    /**
     * @var HelperData
     */
    protected $_oscHelperData;

    /**
     * Data constructor.
     *
     * @param AttributeDataFactory $attrDataFactory
     * @param HelperData $oscHelperData
     */
    public function __construct(
        AttributeDataFactory $attrDataFactory,
        HelperData $oscHelperData
    ) {
        $this->_oscHelperData = $oscHelperData;

        parent::__construct($attrDataFactory);
    }

    /**
     * @param \Magento\Eav\Model\Validator\Attribute\Data $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterIsValid(\Magento\Eav\Model\Validator\Attribute\Data $subject, $result)
    {
        if ($this->_oscHelperData->isFlagOscMethodRegister()) {
            $subject->_messages = [];

            return count($subject->_messages) === 0;
        }

        return $result;
    }
}
