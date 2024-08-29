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

namespace Magetop\Osc\Test\Unit\Block\Checkout;

use Magento\Framework\View\Element\Template\Context;
use Magetop\Osc\Block\Checkout\CompatibleConfig;
use Magetop\Osc\Helper\Data as OscHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CompatibleConfigTest
 * @package Magetop\Osc\Test\Unit\Block\Checkout
 */
class CompatibleConfigTest extends TestCase
{
    /**
     * @var OscHelper|MockObject
     */
    private $oscHelperMock;

    /**
     * @var CompatibleConfig
     */
    private $compatibleConfigBlock;

    protected function setup()
    {
        /**
         * @var Context $contextMock
         */
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->oscHelperMock = $this->getMockBuilder(OscHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->compatibleConfigBlock = new CompatibleConfig($contextMock, $this->oscHelperMock);
    }

    public function testIsEnableModulePostNL()
    {
        $this->oscHelperMock->expects($this->once())->method('isEnableModulePostNL');

        $this->compatibleConfigBlock->isEnableModulePostNL();
    }
}
