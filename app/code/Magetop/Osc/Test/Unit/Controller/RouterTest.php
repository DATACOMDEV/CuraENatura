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

namespace Magetop\Osc\Test\Unit\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magetop\Osc\Controller\Router;
use Magetop\Osc\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RouterTest
 * @package Magetop\Osc\Test\Unit\Controller
 */
class RouterTest extends TestCase
{
    /**
     * @var ActionFactory|MockObject
     */
    private $actionFactoryMock;

    /**
     * @var Data|MockObject
     */
    private $helperDataMock;

    /**
     * @var Router
     */
    private $routerController;

    protected function setUp()
    {
        $this->actionFactoryMock = $this->getMockBuilder(ActionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->routerController = new Router(
            $this->actionFactoryMock,
            $this->helperDataMock
        );
    }

    public function testMatchWithDisableModule()
    {
        /**
         * @var RequestInterface $requestMock
         */
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPathInfo'])
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())->method('getPathInfo')->willReturn('test/');
        $this->helperDataMock->expects($this->once())->method('isEnabled')->willReturn(false);

        $this->assertEmpty($this->routerController->match($requestMock));
    }

    public function testMatchWithInvalidOscRoute()
    {
        /**
         * @var RequestInterface $requestMock
         */
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPathInfo'])
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())->method('getPathInfo')->willReturn('checkout/');
        $this->helperDataMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->helperDataMock->expects($this->once())->method('getOscRoute')->willReturn('osc');

        $this->assertEmpty($this->routerController->match($requestMock));
    }

    public function testMatch()
    {
        /**
         * @var RequestInterface|MockObject $requestMock
         */
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(
                [
                    'getPathInfo',
                    'setModuleName',
                    'setControllerName',
                    'setPathInfo',
                    'setAlias'
                ]
            )->getMockForAbstractClass();
        $requestMock->expects($this->once())->method('getPathInfo')->willReturn('onestepcheckout/');
        $this->helperDataMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->helperDataMock->expects($this->once())->method('getOscRoute')->willReturn('onestepcheckout');
        $requestMock->expects($this->once())->method('setModuleName')->with('onestepcheckout')->willReturnSelf();
        $requestMock->expects($this->once())->method('setControllerName')->with('index')->willReturnSelf();
        $requestMock->expects($this->once())->method('setActionName')->with('index')->willReturnSelf();
        $requestMock->expects($this->once())->method('setPathInfo')
            ->with('/onestepcheckout/index/index')->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('setAlias')
            ->with('rewrite_request_path', 'onestepcheckout')
            ->willReturnSelf();

        $actionInterfaceMock = $this->getMockForAbstractClass(ActionInterface::class);
        $this->actionFactoryMock->expects($this->once())
            ->method('create')
            ->with(Forward::class)
            ->willReturn($actionInterfaceMock);

        $this->routerController->match($requestMock);
    }
}
