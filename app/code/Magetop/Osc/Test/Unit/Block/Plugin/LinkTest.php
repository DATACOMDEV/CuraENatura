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

namespace Magetop\Osc\Test\Unit\Block\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url;
use Magetop\Osc\Block\Plugin\Link;
use Magetop\Osc\Helper\Data as OscHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class LinkTest
 * @package Magetop\Osc\Test\Unit\Block\Plugin
 */
class LinkTest extends TestCase
{
    /**
     * Request object
     *
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var OscHelper|MockObject
     */
    protected $oscHelperMock;

    /**
     * @var Link
     */
    protected $linkPlugin;

    protected function setUp()
    {
        $this->requestMock   = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getFullActionName'])
            ->getMockForAbstractClass();
        $this->oscHelperMock = $this->getMockBuilder(OscHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->linkPlugin = new Link($this->requestMock, $this->oscHelperMock);
    }

    public function testMethod()
    {
        $methods = get_class_methods(Url::class);

        $this->assertTrue(in_array('getUrl', $methods));
    }

    public function testGetUrl()
    {
        $this->oscHelperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->requestMock->expects($this->once())->method('getFullActionName')->willReturn('osc_index_index');
        $this->oscHelperMock->expects($this->once())->method('getOscRoute')->willReturn('osc');
        /**
         * @var Url $subject
         */
        $subject = $this->getMockBuilder(Url::class)->disableOriginalConstructor()->getMock();

        $this->assertEquals(['osc', null], $this->linkPlugin->beforeGetUrl($subject, 'checkout'));
    }
}
