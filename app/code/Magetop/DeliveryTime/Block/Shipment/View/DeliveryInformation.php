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
 * @package     Magetop_DeliveryTime
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\DeliveryTime\Block\Shipment\View;

use Magetop\DeliveryTime\Block\DeliveryInformation as DeliveryInformationAbstract;

/**
 * Class DeliveryInformation
 * @package Magetop\DeliveryTime\Block\Order\View
 */
class DeliveryInformation extends DeliveryInformationAbstract
{
    /**
     * Get current order
     *
     * @return mixed
     */
    public function getOrder()
    {
        $shipment = $this->registry->registry('current_shipment');

        return $shipment ? $shipment->getOrder() : null;
    }
}
