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

namespace Magetop\Osc\Model\Plugin\Checkout;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magetop\Osc\Helper\Item;

/**
 * Class DefaultConfigProvider
 * @package Magetop\Osc\Model\Plugin\Checkout
 */
class DefaultConfigProvider
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Item
     */
    private $itemHelper;

    /**
     * DefaultConfigProvider constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param Item $itemHelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Item $itemHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->itemHelper      = $itemHelper;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $config
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, $config)
    {
        if (!$this->itemHelper->isOscPage()) {
            return $config;
        }

        $quote = $this->checkoutSession->getQuote();

        foreach ($config['quoteItemData'] as &$item) {
            $item['mposc'] = $this->itemHelper->getItemOptionsConfig($quote, $item['item_id']);
        }

        return $config;
    }
}
