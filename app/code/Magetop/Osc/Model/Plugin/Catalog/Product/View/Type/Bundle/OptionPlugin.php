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

namespace Magetop\Osc\Model\Plugin\Catalog\Product\View\Type\Bundle;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option;
use Magetop\Osc\Helper\Data;

/**
 * Class OptionPlugin
 * @package Magetop\Osc\Model\Plugin\Catalog\Product\View\Type\Bundle
 */
class OptionPlugin
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * OptionPlugin constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Option $subject
     */
    public function beforeGetData(Option $subject)
    {
        if (class_exists('Magento\Bundle\Block\DataProviders\OptionPriceRenderer')) {
            $subject->setTierPriceRenderer(
                $this->helper->getObject('Magento\Bundle\Block\DataProviders\OptionPriceRenderer')
            );
        }
    }
}
