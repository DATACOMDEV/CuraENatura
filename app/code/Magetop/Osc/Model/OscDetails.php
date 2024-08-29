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

use Magento\Framework\Model\AbstractExtensibleModel;
use Magetop\Osc\Api\Data\OscDetailsInterface;

/**
 * @codeCoverageIgnoreStart
 */
class OscDetails extends AbstractExtensibleModel implements OscDetailsInterface
{
    /**
     * {@inheritDoc}
     */
    public function getShippingMethods()
    {
        return $this->getData(self::SHIPPING_METHODS);
    }

    /**
     * {@inheritDoc}
     */
    public function setShippingMethods($shippingMethods)
    {
        return $this->setData(self::SHIPPING_METHODS, $shippingMethods);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethods()
    {
        return $this->getData(self::PAYMENT_METHODS);
    }

    /**
     * {@inheritDoc}
     */
    public function setPaymentMethods($paymentMethods)
    {
        return $this->setData(self::PAYMENT_METHODS, $paymentMethods);
    }

    /**
     * {@inheritDoc}
     */
    public function getTotals()
    {
        return $this->getData(self::TOTALS);
    }

    /**
     * {@inheritDoc}
     */
    public function setTotals($totals)
    {
        return $this->setData(self::TOTALS, $totals);
    }

    /**
     * {@inheritDoc}
     */
    public function getRedirectUrl()
    {
        return $this->getData(self::REDIRECT_URL);
    }

    /**
     * {@inheritDoc}
     */
    public function setRedirectUrl($url)
    {
        return $this->setData(self::REDIRECT_URL, $url);
    }

    /**
     * {@inheritDoc}
     */
    public function getImageData()
    {
        return $this->getData(self::IMAGE_DATA);
    }

    /**
     * {@inheritDoc}
     */
    public function setImageData($value)
    {
        return $this->setData(self::IMAGE_DATA, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return $this->getData(self::OPTIONS);
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions($value)
    {
        return $this->setData(self::OPTIONS, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestPath()
    {
        return $this->getData(self::REQUEST_PATH);
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestPath($value)
    {
        return $this->setData(self::REQUEST_PATH, $value);
    }
}
