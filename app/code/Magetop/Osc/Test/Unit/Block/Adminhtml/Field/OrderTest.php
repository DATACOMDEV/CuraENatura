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

namespace Magetop\Osc\Test\Unit\Block\Adminhtml\Field;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Phrase;
use Magetop\Osc\Block\Adminhtml\Field\Address;
use Magetop\Osc\Block\Adminhtml\Field\Order;
use Magetop\Osc\Helper\Address as HelperAddress;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class Order
 * @package Magetop\Osc\Block\Adminhtml\Field
 */
class OrderTest extends TestCase
{
    /**
     * @var Address
     */
    private $orderBlock;

    /**
     * @var HelperAddress|MockObject $helperAddressMock
     */
    private $helperAddressMock;

    protected function setUp()
    {
        /**
         * @var Context|MockObject $contextMock
         */
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperAddressMock = $this->getMockBuilder(HelperAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderBlock = new Order(
            $contextMock,
            $this->helperAddressMock
        );
    }

    public function testGetFieldsWithEmptyField()
    {
        $this->helperAddressMock->expects($this->once())->method('isEnableOrderAttributes')->willReturn(false);
        $this->assertEquals([[], []], $this->orderBlock->getFields());
    }

    public function testGetFields()
    {
        //        $this->helperAddressMock->expects($this->once())->method('isEnableOrderAttributes')->willReturn(true);
        //        $this->helperAddressMock->expects($this->once())
        //            ->method('getObject')
        //            ->with(oaHelper::class)
        //            ->
    }

    public function testGetBlockTitle()
    {
        $result = (string) new Phrase('Order Summary');

        $this->assertEquals($result, $this->orderBlock->getBlockTitle());
    }

    public function testGetBlockId()
    {
        $this->assertEquals('mposc-order-summary', $this->orderBlock->getBlockId());
    }
}
