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

use Magento\Framework\Event\Observer;
use Magetop\Osc\Model\CheckoutRegister;
use Magetop\Osc\Observer\PaypalExpressPlaceOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class PaypalExpressPlaceOrderTest
 * @package Magetop\Osc\Test\Unit\Observer
 */
class PaypalExpressPlaceOrderTest extends TestCase
{
    /**
     * @var CheckoutRegister|MockObject
     */
    private $checkoutRegisterMock;

    /**
     * @var PaypalExpressPlaceOrder
     */
    private $observer;

    protected function setUp()
    {
        $this->checkoutRegisterMock = $this->getMockBuilder(CheckoutRegister::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new PaypalExpressPlaceOrder($this->checkoutRegisterMock);
    }

    public function testExecute()
    {
        /**
         * @var Observer $observerMock
         */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutRegisterMock->expects($this->once())->method('checkRegisterNewCustomer');

        $this->observer->execute($observerMock);
    }
}
