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

namespace Magetop\Osc\Model\Plugin;

use Closure;

/**
 * Class Quote
 * @package Magetop\Osc\Model\Plugin
 */
class Quote
{
    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param Closure $process
     * @param $itemId
     *
     * @return bool|mixed
     */
    public function aroundGetItemById(\Magento\Quote\Model\Quote $subject, Closure $process, $itemId)
    {
        foreach ($subject->getItemsCollection() as $item) {
            if ($item->getId() == $itemId) {
                return $item;
            }
        }

        return false;
    }
}
