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

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Design
 * @package Magetop\Osc\Model\System\Config\Source
 */
class Design implements ArrayInterface
{
    const DESIGN_DEFAULT  = 'default';
    const DESIGN_FLAT     = 'flat';
    const DESIGN_MATERIAL = 'material';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Default'),
                'value' => self::DESIGN_DEFAULT
            ],
            [
                'label' => __('Flat'),
                'value' => self::DESIGN_FLAT
            ],
            [
                'label' => __('Material'),
                'value' => self::DESIGN_MATERIAL
            ]
        ];

        return $options;
    }
}
