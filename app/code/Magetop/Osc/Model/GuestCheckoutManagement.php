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

namespace Magetop\Osc\Model;

use Exception;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magetop\Osc\Api\CheckoutManagementInterface;
use Magetop\Osc\Api\GuestCheckoutManagementInterface;

/**
 * Class GuestCheckoutManagement
 * @package Magetop\Osc\Model
 */
class GuestCheckoutManagement implements GuestCheckoutManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var CheckoutManagementInterface
     */
    protected $checkoutManagement;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * GuestCheckoutManagement constructor.
     *
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CheckoutManagementInterface $checkoutManagement
     * @param CartRepositoryInterface $cartRepository
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CheckoutManagementInterface $checkoutManagement,
        CartRepositoryInterface $cartRepository,
        AccountManagementInterface $accountManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->checkoutManagement = $checkoutManagement;
        $this->cartRepository     = $cartRepository;
        $this->accountManagement  = $accountManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function updateItemQty($cartId, $itemId, $itemQty)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->updateItemQty($quoteIdMask->getQuoteId(), $itemId, $itemQty);
    }

    /**
     * {@inheritDoc}
     */
    public function removeItemById($cartId, $itemId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->removeItemById($quoteIdMask->getQuoteId(), $itemId);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentTotalInformation($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->getPaymentTotalInformation($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritDoc}
     */
    public function updateGiftWrap($cartId, $isUseGiftWrap)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->updateGiftWrap($quoteIdMask->getQuoteId(), $isUseGiftWrap);
    }

    /**
     * {@inheritDoc}
     */
    public function saveCheckoutInformation(
        $cartId,
        ShippingInformationInterface $addressInformation,
        $customerAttributes = [],
        $additionInformation = []
    ) {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->saveCheckoutInformation(
            $quoteIdMask->getQuoteId(),
            $addressInformation,
            $customerAttributes,
            $additionInformation
        );
    }

    /**
     * {@inheritDoc}
     */
    public function saveEmailToQuote($cartId, $email)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
        $quote->setCustomerEmail($email);

        try {
            $this->cartRepository->save($quote);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmailAvailable($cartId, $customerEmail, $websiteId = null)
    {
        $this->saveEmailToQuote($cartId, $customerEmail);

        return $this->accountManagement->isEmailAvailable($customerEmail, $websiteId);
    }

    /**
     * {@inheritdoc}
     */
    public function paymentMethodDiscount(
        $cartId,
        PaymentInterface $paymentMethod
    ) {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->paymentMethodDiscount(
            $quoteIdMask->getQuoteId(),
            $paymentMethod
        );
    }
}
