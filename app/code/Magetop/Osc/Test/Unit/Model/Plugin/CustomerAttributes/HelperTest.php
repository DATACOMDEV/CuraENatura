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

namespace Magetop\Osc\Test\Unit\Model\Plugin\CustomerAttributes;

use Magento\Eav\Model\Attribute;
use Magetop\CustomerAttributes\Helper\Data;
use Magetop\Osc\Helper\Address;
use Magetop\Osc\Model\Plugin\CustomerAttributes\Helper;
use PHPUnit\Framework\TestCase;

/**
 * Class HelperTest
 * @package Magetop\Osc\Test\Unit\Model\Plugin\CustomerAttributes
 */
class HelperTest extends TestCase
{
    /**
     * @var Address
     */
    private $helperMock;

    /**
     * @var Helper
     */
    private $plugin;

    protected function setUp()
    {
        $this->helperMock = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->getMock();

        $this->plugin = new Helper($this->helperMock);
    }

    public function testAfterGetAttributeWithFiltersWithInvalidOscPage()
    {
        $subject = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();

        $this->helperMock->expects($this->once())->method('isOscPage')->willReturn(false);

        $this->plugin->afterGetAttributeWithFilters($subject, []);
    }

    public function testAfterGetAttributeWithFilters()
    {
        $subject = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();

        $this->helperMock->expects($this->once())->method('isOscPage')->willReturn(true);
        $fieldPosition = [
            [
                'code' => 'my_attribute',
            ]
        ];
        $this->helperMock->expects($this->once())->method('getFieldPosition')->willReturn($fieldPosition);

        $attributeMock = $this->getMockBuilder(Attribute::class)->disableOriginalConstructor()->getMock();
        $resultMock    = [$attributeMock];
        $attributeMock->expects($this->once())->method('getAttributeCode')->willReturn('my_attribute');

        $this->assertEquals(
            $resultMock,
            $this->plugin->afterGetAttributeWithFilters($subject, $resultMock)
        );
    }

    public function testAfterGetAttributeWithEmptyAttributes()
    {
        $subject = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();

        $this->helperMock->expects($this->once())->method('isOscPage')->willReturn(true);
        $fieldPosition = [
            [
                'code' => 'my_attribute',
            ]
        ];
        $this->helperMock->expects($this->once())->method('getFieldPosition')->willReturn($fieldPosition);

        $attributeMock = $this->getMockBuilder(Attribute::class)->disableOriginalConstructor()->getMock();
        $resultMock    = [$attributeMock];
        $attributeMock->expects($this->once())->method('getAttributeCode')->willReturn('test');

        $this->assertEquals(
            [],
            $this->plugin->afterGetAttributeWithFilters($subject, $resultMock)
        );
    }
}
