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

namespace Magetop\Osc\Test\Unit\Block\Order\View;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magetop\Osc\Block\Order\View\Comment;
use Magetop\Osc\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CommentTest
 * @package Magetop\Osc\Test\Unit\Block\Order\View
 */
class CommentTest extends TestCase
{
    /**
     * @var Registry|MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var Data|MockObject
     */
    protected $helperMock;

    /**
     * @var Comment
     */
    protected $commentBlock;

    public function setUp()
    {
        /**
         * @var Context|MockObject $contextMock
         */
        $contextMock            = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->coreRegistryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->commentBlock = new Comment(
            $contextMock,
            $this->coreRegistryMock,
            $this->helperMock
        );
    }

    public function testGetOrderComment()
    {
        $orderMock = $this->getMockBuilder(Order::class)
            ->setMethods(['getOscOrderComment'])
            ->disableOriginalConstructor()->getMock();
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('current_order')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getOscOrderComment')->willReturn('test');

        $this->assertEquals('test', $this->commentBlock->getOrderComment());
    }

    public function testGetOrderCommentWithEmptyOrder()
    {
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('current_order')
            ->willReturn(null);

        $this->assertEmpty($this->commentBlock->getOrderComment());
    }
}
