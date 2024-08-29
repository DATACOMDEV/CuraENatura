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

namespace Magetop\Osc\Test\Unit\Block;

use Magento\Framework\View\Element\Template\Context;
use Magetop\Osc\Block\Container;
use Magetop\Osc\Helper\Data as OscHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ContainerTest
 * @package Magetop\Osc\Test\Unit\Block
 */
class ContainerTest extends TestCase
{
    /**
     * @var OscHelper|MockObject
     */
    private $oscHelperMock;

    /**
     * @var Container|MockObject
     */
    private $containerBock;

    protected function setUp()
    {
        /**
         * @var Context $contextMock
         */
        $contextMock         = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->oscHelperMock = $this->getMockBuilder(OscHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->containerBock = new Container(
            $contextMock,
            $this->oscHelperMock
        );
    }

    public function testGetCheckoutDescription()
    {
        $this->oscHelperMock->expects($this->once())
            ->method('getConfigGeneral')
            ->with('description');
        $this->containerBock->getCheckoutDescription();
    }
}
