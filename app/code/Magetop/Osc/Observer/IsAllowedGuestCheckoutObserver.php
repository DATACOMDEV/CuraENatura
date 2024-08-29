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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magetop\Osc\Helper\Data;

/**
 * Class CheckoutSubmitBefore
 * @package Magetop\Osc\Observer
 */
class IsAllowedGuestCheckoutObserver extends \Magento\Downloadable\Observer\IsAllowedGuestCheckoutObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * IsAllowedGuestCheckoutObserver constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $helper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Data $helper
    ) {
        $this->_helper = $helper;

        parent::__construct($scopeConfig);
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            return $this;
        }

        return parent::execute($observer);
    }
}
