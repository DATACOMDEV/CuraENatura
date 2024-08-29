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

namespace Magetop\Osc\Test\Unit\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magetop\Osc\Model\CustomAttributeList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomAttributeListTest
 * @package Magetop\Osc\Test\Unit\Model
 */
class CustomAttributeListTest extends TestCase
{
    /**
     * @var AddressMetadataInterface|MockObject
     */
    private $addressMetadataMock;

    /**
     * @var CustomAttributeList
     */
    private $model;

    protected function setUp()
    {
        $this->addressMetadataMock = $this->getMockBuilder(AddressMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CustomAttributeList($this->addressMetadataMock);
    }

    public function testGetAttributes()
    {
        $attribute = $this->getMockForAbstractClass(AttributeMetadataInterface::class);

        $this->addressMetadataMock->expects($this->exactly(3))
            ->method('getAttributeMetadata')
            ->withConsecutive(['mposc_field_1'], ['mposc_field_2'], ['mposc_field_3'])
            ->willReturnOnConsecutiveCalls($attribute, $attribute, $attribute);

        $attribute->expects($this->exactly(3))
            ->method('getAttributeCode')
            ->willReturnOnConsecutiveCalls('attribute1', 'attribute2', 'attribute3');

        $this->assertEquals(
            [
                'attribute1' => $attribute,
                'attribute2' => $attribute,
                'attribute3' => $attribute
            ],
            $this->model->getAttributes()
        );
    }
}
