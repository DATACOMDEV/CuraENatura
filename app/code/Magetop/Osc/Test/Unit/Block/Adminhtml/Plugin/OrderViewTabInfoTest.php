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

namespace Magetop\Osc\Test\Unit\Block\Adminhtml\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Info;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderViewTabInfoTest
 * @package Magetop\Osc\Test\Unit\Block\Adminhtml\Plugin
 */
class OrderViewTabInfoTest extends TestCase
{
    /**
     * Check function getGiftOptionsHtml is exist
     */
    public function testAfterGetGiftOptionsHtml()
    {
        $objectManagerHelper = new ObjectManager($this);
        /**
         * @var Info $saleTabInfo
         */
        $saleTabInfo = $objectManagerHelper->getObject(Info::class);

        $this->assertTrue(method_exists($saleTabInfo, 'getGiftOptionsHtml'));
    }
}
