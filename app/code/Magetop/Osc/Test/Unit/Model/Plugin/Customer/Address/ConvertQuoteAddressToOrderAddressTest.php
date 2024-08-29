<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magetop.com license that is
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

namespace Magetop\Osc\Test\Unit\Model\Plugin\Customer\Address;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;
use Magetop\Osc\Model\Plugin\Customer\Address\ConvertQuoteAddressToOrderAddress;
use PHPUnit\Framework\TestCase;

/**
 * Class ConvertQuoteAddressToOrderAddress
 * @package Magetop\Osc\Model\Plugin\Customer\Address
 */
class ConvertQuoteAddressToOrderAddressTest extends TestCase
{
    /**
     * @var ConvertQuoteAddressToOrderAddress
     */
    private $plugin;

    protected function setUp()
    {
        $this->plugin = new ConvertQuoteAddressToOrderAddress();
    }

    public function testMethod()
    {
        $methods = get_class_methods(ToOrderAddress::class);

        $this->assertTrue(in_array('convert', $methods));
    }

    public function testAroundConvert()
    {
        /**
         * @var ToOrderAddress $subject
         */
        $subject = $this->getMockBuilder(ToOrderAddress::class)->disableOriginalConstructor()->getMock();

        /**
         * @var Address $quoteAddressMock
         */
        $quoteAddressMock = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->getMock();
        $orderAddressMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $closureMock = function () use ($orderAddressMock) {
            return $orderAddressMock;
        };

        $quoteAddressMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(['mposc_field_1'], ['mposc_field_2'], ['mposc_field_3'])
            ->willReturnOnConsecutiveCalls('test1', 'test2', 'test3');
        $orderAddressMock->expects($this->exactly(3))
            ->method('setData')
            ->willReturnOnConsecutiveCalls(
                ['mposc_field_1', 'test1'],
                ['mposc_field_2', 'test2'],
                ['mposc_field_3', 'test3']
            );

        $this->plugin->aroundConvert($subject, $closureMock, $quoteAddressMock);
    }
}
