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

namespace Magetop\Osc\Model\System\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface as StoreConfig;
use Magento\Framework\Option\ArrayInterface;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Config as CarrierConfig;

/**
 * Class Methods
 * @package Magetop\Osc\Model\System\Config\Source\Shipping
 */
class ShippingMethods implements ArrayInterface
{
    /**
     * @var StoreConfig
     */
    protected $_scopeConfig;

    /**
     * @var CarrierFactory
     */
    protected $_carrierConfig;

    /**
     * @param StoreConfig $scopeConfig
     * @param CarrierConfig $carrierConfig
     */
    public function __construct(
        StoreConfig $scopeConfig,
        CarrierConfig $carrierConfig
    ) {
        $this->_scopeConfig   = $scopeConfig;
        $this->_carrierConfig = $carrierConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $shippingMethodsOptionArray = [
            [
                'label' => __('-- Please select --'),
                'value' => '',
            ],
        ];
        $carrierMethodsList         = $this->_carrierConfig->getActiveCarriers();
        ksort($carrierMethodsList);
        foreach ($carrierMethodsList as $carrierMethodCode => $carrierModel) {
            if ($carrierModel->getAllowedMethods()) {
                foreach ($carrierModel->getAllowedMethods() as $shippingMethodCode => $shippingMethodTitle) {
                    $shippingMethodsOptionArray[] = [
                        'label' => $this->_getShippingMethodTitle($carrierMethodCode) . ' - ' . $shippingMethodTitle,
                        'value' => $carrierMethodCode . '_' . $shippingMethodCode,
                    ];
                }
            }
        }

        return $shippingMethodsOptionArray;
    }

    /**
     * @param $shippingMethodCode
     *
     * @return mixed
     */
    protected function _getShippingMethodTitle($shippingMethodCode)
    {
        if (!$shippingMethodTitle = $this->_scopeConfig->getValue("carriers/$shippingMethodCode/title")) {
            $shippingMethodTitle = $shippingMethodCode;
        }

        return $shippingMethodTitle;
    }
}
