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
use Magetop\Osc\Helper\Address as HelperAddress;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class Address
 * @package Magetop\Osc\Block\Adminhtml\Field
 */
class AddressTest extends TestCase
{
    /**
     * @var Address
     */
    private $addressBlock;

    protected function setUp()
    {
        /**
         * @var Context|MockObject $contextMock
         */
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var HelperAddress|MockObject $helperAddressMock
         */
        $helperAddressMock = $this->getMockBuilder(HelperAddress::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperAddressMock->expects($this->once())
            ->method('getSortedField')
            ->with(false);

        $this->addressBlock = new Address(
            $contextMock,
            $helperAddressMock
        );
    }

    public function testGetBlockTitle()
    {
        $result = (string) new Phrase('Address Information');

        $this->assertEquals($result, $this->addressBlock->getBlockTitle());
    }

    public function testGetBlockId()
    {
        $this->assertEquals('mposc-address-information', $this->addressBlock->getBlockId());
    }
}
