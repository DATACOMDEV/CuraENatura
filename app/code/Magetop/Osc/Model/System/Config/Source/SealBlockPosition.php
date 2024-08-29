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
 * Class SealBlock
 * @package Magetop\Osc\Model\System\Config\Source
 */
class SealBlockPosition implements ArrayInterface
{
    const NOT_SHOW            = 0;
    const SELECT_STATIC_BLOCK = 1;
    const USE_DEFAULT_DESIGN  = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::NOT_SHOW            => __('No'),
            self::SELECT_STATIC_BLOCK => __('Select Static Block'),
            self::USE_DEFAULT_DESIGN  => __('Use Default Design')
        ];
    }
}
