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
 * Class Checkbox Style
 * @package Magetop\Osc\Model\System\Config\Source
 */
class CheckboxStyle implements ArrayInterface
{
    const STYLE_DEFAULT = 'default';
    const FILLED_IN     = 'filled_in';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Default'),
                'value' => self::STYLE_DEFAULT
            ],
            [
                'label' => __('Filled In'),
                'value' => self::FILLED_IN
            ]
        ];

        return $options;
    }
}
