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

namespace Magetop\Osc\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class SocialLogin
 * @package Magetop\Osc\Observer
 */
class SocialLogin implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * SocialLogin constructor.
     * @param UrlInterface $url
     */
    public function __construct(
        UrlInterface $url
    ) {
        $this->url = $url;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $object = $observer->getEvent()->getObject();

        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();
        $backUrl = $request->getParam('back_url');
        if ($backUrl) {
            $url = $this->url->getUrl($backUrl);
            $object->setUrl($url);
        }

        return $this;
    }
}
