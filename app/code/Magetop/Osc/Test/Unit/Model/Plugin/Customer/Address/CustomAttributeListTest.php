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

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Address\CustomAttributeList as AddressCustomAttributeList;
use Magetop\Osc\Model\CustomAttributeList;
use Magetop\Osc\Model\Plugin\Customer\Address\CustomAttributeList as PluginCustomerAttributeList;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomAttributeListTest
 * @package Magetop\Osc\Test\Unit\Model\Plugin\Customer\Address
 */
class CustomAttributeListTest extends TestCase
{
    /**
     * @var CustomAttributeList
     */
    private $customAttributeListMockMock;

    /**
     * @var CustomAttributeList
     */
    private $plugin;

    protected function setUp()
    {
        $this->customAttributeListMockMock = $this->getMockBuilder(CustomAttributeList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new PluginCustomerAttributeList(
            $this->customAttributeListMockMock
        );
    }

    public function testMethod()
    {
        $methods = get_class_methods(AddressCustomAttributeList::class);

        $this->assertTrue(in_array('getAttributes', $methods));
    }

    public function testAfterGetAttributes()
    {
        /**
         * @var AddressCustomAttributeList $subject
         */
        $subject   = $this->getMockBuilder(AddressCustomAttributeList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attribute = $this->getMockForAbstractClass(AttributeMetadataInterface::class);
        $this->customAttributeListMockMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn([$attribute]);

        $this->assertEquals([$attribute], $this->plugin->afterGetAttributes($subject, []));
    }
}
