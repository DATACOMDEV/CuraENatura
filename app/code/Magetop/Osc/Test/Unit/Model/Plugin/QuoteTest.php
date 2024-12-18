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

namespace Magetop\Osc\Test\Unit\Model\Plugin;

use Closure;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote as QuoteCore;
use Magento\Quote\Model\Quote\Item;
use Magetop\Osc\Model\Plugin\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class QuoteTest
 * @package Magetop\Osc\Test\Unit\Model\Plugin
 */
class QuoteTest extends TestCase
{
    /**
     * @var Quote
     */
    protected $quotePlugin;

    /**
     * @var Item|MockObject
     */
    protected $quoteItemMock;

    /**
     * @var Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->quotePlugin = new Quote();
    }

    public function testMethod()
    {
        $objectManagerHelper = new ObjectManager($this);
        $result              = method_exists($objectManagerHelper->getObject(QuoteCore::class), 'getItemById');
        $this->assertTrue($result);
    }

    /**
     * @return array
     */
    public function providerTestAroundGetItemById()
    {
        return [
            [
                true,
                1
            ],
            [
                false,
                2
            ]
        ];
    }

    /**
     * @param boolean|MockObject $result
     * @param int $itemId
     *
     * @dataProvider providerTestAroundGetItemById
     */
    public function testAroundGetItemById($isReturnQuoteItem, $itemId)
    {
        /**
         * @var QuoteCore $quoteMock
         */
        $quoteMock           = $this->getMockBuilder(QuoteCore::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->once())
            ->method('getItemsCollection')
            ->willReturn([$this->quoteItemMock]);
        $this->quoteItemMock->expects($this->once())->method('getId')->willReturn(1);
        $closureMock = function () use ($quoteMock) {
            return $quoteMock;
        };

        $this->assertEquals(
            $isReturnQuoteItem ? $this->quoteItemMock : false,
            $this->quotePlugin->aroundGetItemById($quoteMock, $closureMock, $itemId)
        );
    }
}
