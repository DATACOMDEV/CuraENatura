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

namespace Magetop\Osc\Test\Plugin\Model\Plugin\Customer\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address;
use Magetop\Osc\Model\Plugin\Customer\Address\ConvertQuoteAddressToCustomerAddress;
use PHPUnit\Framework\TestCase;

/**
 * Class ConvertQuoteAddressToCustomerAddressTest
 * @package Magetop\Osc\Test\Plugin\Model\Plugin\Customer\Address
 */
class ConvertQuoteAddressToCustomerAddressTest extends TestCase
{
    /**
     * @var ConvertQuoteAddressToCustomerAddress
     */
    private $plugin;

    protected function setUp()
    {
        $this->plugin = new ConvertQuoteAddressToCustomerAddress();
    }

    public function testMethod()
    {
        $methods = get_class_methods(Address::class);

        $this->assertTrue(in_array('exportCustomerAddress', $methods));
    }

    public function testAfterExportCustomerAddress()
    {
        /**
         * @var Address $subject
         */
        $subject = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->getMock();

        /**
         * @var AddressInterface $customerAddressMock
         */
        $customerAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $subject->expects($this->exactly(3))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(['mposc_field_1'], ['mposc_field_2'], ['mposc_field_3'])
            ->willReturnOnConsecutiveCalls('test1', 'test2', 'test3');
        $customerAddressMock->expects($this->exactly(3))
            ->method('setCustomAttribute')
            ->willReturnOnConsecutiveCalls(
                ['mposc_field_1', 'test1'],
                ['mposc_field_2', 'test2'],
                ['mposc_field_3', 'test3']
            );

        $this->plugin->afterExportCustomerAddress($subject, $customerAddressMock);
    }
}
