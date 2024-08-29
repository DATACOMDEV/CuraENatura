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

namespace Magetop\Osc\Test\Unit\Model\Plugin\Quote\Address;

use Magetop\Osc\Model\Plugin\Quote\Address\CustomAttributeList;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomAttributeListTest
 * @package Magetop\Osc\Test\Unit\Model\Plugin\Quote\Address
 */
class CustomAttributeListTest extends TestCase
{
    /**
     * @var \Magetop\Osc\Model\CustomAttributeList
     */
    private $customAttributeListMock;

    /**
     * @var CustomAttributeList
     */
    private $plugin;

    protected function setUp()
    {
        $this->customAttributeListMock = $this->getMockBuilder(\Magetop\Osc\Model\CustomAttributeList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new CustomAttributeList($this->customAttributeListMock);
    }

    public function testMethod()
    {
        $methods = get_class_methods(\Magento\Quote\Model\Quote\Address\CustomAttributeList::class);

        $this->assertTrue(in_array('getAttributes', $methods));
    }

    public function testAfterGetAttributes()
    {
        /**
         * @var \Magento\Quote\Model\Quote\Address\CustomAttributeList $subject
         */
        $subject    = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\CustomAttributeList::class)
            ->disableOriginalConstructor()->getMock();
        $attributes = [
            [
                'attribute_id' => 1
            ]
        ];
        $this->customAttributeListMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->assertEquals($attributes, $this->plugin->afterGetAttributes($subject, []));
    }
}
