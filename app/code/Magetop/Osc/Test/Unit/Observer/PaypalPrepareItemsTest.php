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

namespace Magetop\Osc\Test\Unit\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Phrase;
use Magento\Payment\Model\Cart;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magetop\Osc\Observer\PaypalPrepareItems;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class PaypalPrepareItemsTest
 * @package Magetop\Osc\Test\Unit\Observer
 */
class PaypalPrepareItemsTest extends TestCase
{
    /**
     * @var Session|MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var PaypalPrepareItems
     */
    protected $observer;

    protected function setUp()
    {
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new PaypalPrepareItems(
            $this->checkoutSessionMock
        );
    }

    public function testExecute()
    {
        /**
         * @var Observer $observerMock
         */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock = $this->getMockBuilder(Event::class)
            ->setMethods(['getCart'])
            ->disableOriginalConstructor()
            ->getMock();

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $cartMock = $this->getMockBuilder(Cart::class)->disableOriginalConstructor()->getMock();
        $eventMock->expects($this->once())->method('getCart')->willReturn($cartMock);
        $quoteMock = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $shippingAddressMock = $this->getMockBuilder(Address::class)
            ->setMethods(['getOscGiftWrapAmount'])
            ->disableOriginalConstructor()->getMock();
        $quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $giftWrapValue = 1000;
        $shippingAddressMock->expects($this->once())->method('getOscGiftWrapAmount')->willReturn($giftWrapValue);
        $cartMock->expects($this->once())->method('addCustomItem')
            ->with(
                new Phrase('Gift Wrap'),
                1,
                $giftWrapValue
            );

        $this->observer->execute($observerMock);
    }
}
