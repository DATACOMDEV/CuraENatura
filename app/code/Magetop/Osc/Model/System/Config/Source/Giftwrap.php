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
 * Class Giftwrap
 * @package Magetop\Osc\Model\System\Config\Source
 */
class Giftwrap implements ArrayInterface
{
    const PER_ORDER = 0;
    const PER_ITEM  = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::PER_ORDER => __('Per Order'),
            self::PER_ITEM  => __('Per Item')
        ];
    }
}
