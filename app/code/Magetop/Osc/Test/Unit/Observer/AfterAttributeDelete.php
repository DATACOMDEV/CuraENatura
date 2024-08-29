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

namespace Magetop\Osc\Test\Unit\Observer;

use Magento\Customer\Model\Attribute as CustomerAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magetop\OrderAttributes\Model\Attribute as OrderAttribute;
use Magetop\Osc\Helper\Address;

/**
 * Class AfterAttributeDelete
 * @package Magetop\Osc\Test\Unit\Observer
 */
class AfterAttributeDelete implements ObserverInterface
{
    /**
     * @var Address
     */
    private $helper;

    /**
     * AfterAttributeDelete constructor.
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
            $this->deleteField($attribute, $this->helper->getOAFieldPosition(), ADDRESS::OA_FIELD_POSITION);
        } elseif ($attribute instanceof CustomerAttribute) {
            $this->deleteField($attribute, $this->helper->getFieldPosition(), ADDRESS::SORTED_FIELD_POSITION);
        }
    }

    /**
     * @param OrderAttribute|CustomerAttribute $attribute
     * @param array $fields
     * @param string $path
     */
    private function deleteField($attribute, $fields, $path)
    {
        if ($attribute->isObjectNew()) {
            return;
        }

        foreach ($fields as $key => $field) {
            if ($field['code'] === $attribute->getAttributeCode()) {
                unset($fields[$key]);
                break;
            }
        }

        $this->helper->saveOscConfig(Address::jsonEncode($fields), $path);
    }
}
