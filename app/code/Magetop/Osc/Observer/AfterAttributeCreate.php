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

namespace Magetop\Osc\Observer;

use Magento\Customer\Model\Attribute as CustomerAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magetop\OrderAttributes\Model\Attribute as OrderAttribute;
use Magetop\OrderAttributes\Model\Config\Source\Position;
use Magetop\Osc\Helper\Address;

/**
 * Class AfterAttributeCreate
 * @package Magetop\Osc\Observer
 */
class AfterAttributeCreate implements ObserverInterface
{
    /**
     * @var Address
     */
    private $helper;

    /**
     * OrderAttributeCreate constructor.
     *
     * @param Address $helper
     */
    public function __construct(Address $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();

        if ($attribute instanceof OrderAttribute) {
            $this->addField($attribute, $this->helper->getOAFieldPosition(), ADDRESS::OA_FIELD_POSITION);
        } elseif ($attribute instanceof CustomerAttribute) {
            $this->addField($attribute, $this->helper->getFieldPosition(), ADDRESS::SORTED_FIELD_POSITION);
        }
    }

    /**
     * @param OrderAttribute|CustomerAttribute $attribute
     * @param array $fields
     * @param string $path
     */
    private function addField($attribute, $fields, $path)
    {
        foreach ($fields as &$field) {
            if ($field['code'] === $attribute->getAttributeCode()) {
                $field['required'] = (bool) $attribute->getIsRequired();
                break;
            }
        }

        unset($field);

        if ($attribute->isObjectNew()) {
            $newField = [
                'code'     => $attribute->getAttributeCode(),
                'colspan'  => 6,
                'required' => (bool) $attribute->getIsRequired(),
            ];

            switch ($path) {
                case ADDRESS::OA_FIELD_POSITION:
                    $isBottomPos        = [
                        Position::SHIPPING_BOTTOM,
                        Position::PAYMENT_BOTTOM,
                        Position::ORDER_SUMMARY
                    ];
                    $newField['bottom'] = in_array((int) $attribute->getPosition(), $isBottomPos, true);

                    $fields[] = $newField;
                    break;
                case ADDRESS::SORTED_FIELD_POSITION:
                    if (in_array('onestepcheckout_index_index', $attribute->getUsedInForms(), true)) {
                        $fields[] = $newField;
                    }
                    break;
            }
        }

        $this->helper->saveOscConfig($this->helper->jsonEncodeData($fields), $path);
    }
}
