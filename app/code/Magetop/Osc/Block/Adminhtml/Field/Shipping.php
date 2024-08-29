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

namespace Magetop\Osc\Block\Adminhtml\Field;

/**
 * Class Shipping
 * @package Magetop\Osc\Block\Adminhtml\Field
 */
class Shipping extends AbstractOrderField
{
    const BLOCK_ID    = 'mposc-shipping-method';
    const BLOCK_SCOPE = [2, 3]; // position shipping

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string) __('Shipping Method');
    }
}
