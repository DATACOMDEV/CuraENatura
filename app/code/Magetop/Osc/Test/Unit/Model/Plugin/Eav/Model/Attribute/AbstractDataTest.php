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

namespace Magetop\Osc\Test\Unit\Model\Plugin\Eav\Model\Attribute;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Attribute\Data\AbstractData as CoreAbstractData;
use Magento\Framework\Exception\LocalizedException;
use Magetop\Osc\Helper\Address;
use Magetop\Osc\Model\Plugin\Eav\Model\Attribute\AbstractData;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractDataTest
 * @package Magetop\Osc\Test\Unit\Model\Plugin\Eav\Model\Attribute
 */
class AbstractDataTest extends TestCase
{
    /**
     * @var Address|MockObject
     */
    private $helperMock;

    /**
     * @var AbstractData
     */
    private $plugin;

    /**
     * @var CoreAbstractData|MockObject
     */
    private $subject;

    protected function setUp()
    {
        $this->helperMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()->getMock();
        $this->subject    = $this->getMockBuilder(CoreAbstractData::class)
            ->disableOriginalConstructor()->getMock();
        $this->plugin     = new AbstractData($this->helperMock);
    }

    public function testMethod()
    {
        $methods = get_class_methods(CoreAbstractData::class);

        $this->assertTrue(in_array('validateValue', $methods));
    }

    /**
     * @return array
     */
    public function providerTestBeforeValidateValue()
    {
        return [
            [
                [''],
                '',
                [],
                self::never()
            ],
            [
                [1],
                1,
                [
                    ['code' => 'my_attribute']
                ],
                self::once()
            ],
            [
                [1],
                1,
                [
                    ['code' => 'my_attribute', 'required' => true]
                ],
                self::once()
            ]
        ];
    }

    /**
     * @param boolean $result
     * @param string $value
     * @param array $fieldPosition
     * @param InvokedCountMatcher $attributeCodeExpect
     *
     * @dataProvider providerTestBeforeValidateValue
     *
     * @throws LocalizedException
     */
    public function testBeforeValidateValue($result, $value, $fieldPosition, $attributeCodeExpect)
    {
        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject->expects($this->once())->method('getAttribute')->willReturn($attributeMock);
        $this->helperMock->expects($this->once())->method('getFieldPosition')->willReturn($fieldPosition);
        $attributeMock->expects($attributeCodeExpect)->method('getAttributeCode')->willReturn('my_attribute');
        if (empty($fieldPosition[0]['required'])) {
            $attributeMock->expects($this->once())->method('setIsRequired')->with(false);
        }

        $this->assertEquals($result, $this->plugin->beforeValidateValue($this->subject, $value));
    }
}
