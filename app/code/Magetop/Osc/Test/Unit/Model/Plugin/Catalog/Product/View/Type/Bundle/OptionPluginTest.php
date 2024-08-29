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

namespace Magetop\Osc\Test\Unit\Model\Plugin\Catalog\Product\View\Type\Bundle;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option;
use Magento\Bundle\Block\DataProviders\OptionPriceRenderer;
use Magetop\Osc\Helper\Data;
use Magetop\Osc\Model\Plugin\Catalog\Product\View\Type\Bundle\OptionPlugin;
use PHPUnit\Framework\TestCase;

/**
 * Class OptionPlugin
 * @package Magetop\Osc\Model\Plugin\Catalog\Product\View\Type\Bundle
 */
class OptionPluginTest extends TestCase
{
    /**
     * @var Data
     */
    private $helperMock;

    /**
     * @var OptionPlugin
     */
    private $plugin;

    protected function setUp()
    {
        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()->getMock();
        $this->plugin     = new OptionPlugin($this->helperMock);
    }

    public function testBeforeGetData()
    {
        /**
         * @param Option $optionMock
         */
        $optionMock = $this->getMockBuilder(Option::class)
            ->setMethods(['setTierPriceRenderer'])
            ->disableOriginalConstructor()->getMock();
        if (class_exists('Magento\Bundle\Block\DataProviders\OptionPriceRenderer')) {
            $optionPriceRendererMock = $this->getMockBuilder(OptionPriceRenderer::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->helperMock->expects($this->once())
                ->method('getObject')
                ->with('Magento\Bundle\Block\DataProviders\OptionPriceRenderer')
                ->willReturn($optionPriceRendererMock);
            $optionMock->expects($this->once())->method('setTierPriceRenderer')->with($optionPriceRendererMock);
        }

        $this->plugin->beforeGetData($optionMock);
    }
}
