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

namespace Magetop\Osc\Block;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magetop\Osc\Helper\Data as OscHelper;

/**
 * Class Design
 * @package Magetop\Osc\Block
 */
class Design extends Template
{
    /**
     * @var OscHelper
     */
    protected $_oscHelper;

    /**
     * @var ThemeProviderInterface
     */
    protected $_themeProviderInterface;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * Design constructor.
     *
     * @param Context $context
     * @param OscHelper $oscHelper
     * @param ThemeProviderInterface $themeProviderInterface
     * @param CheckoutSession $checkoutSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        OscHelper $oscHelper,
        ThemeProviderInterface $themeProviderInterface,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->_oscHelper              = $oscHelper;
        $this->_themeProviderInterface = $themeProviderInterface;
        $this->checkoutSession         = $checkoutSession;

        parent::__construct($context, $data);
    }

    /**
     * @return OscHelper
     */
    public function getHelperConfig()
    {
        return $this->_oscHelper;
    }

    /**
     * @return bool
     */
    public function isEnableGoogleApi()
    {
        return $this->getHelperConfig()->getAutoDetectedAddress() === 'google';
    }

    /**
     * @return mixed
     */
    public function getGoogleApiKey()
    {
        return $this->getHelperConfig()->getGoogleApiKey();
    }

    /**
     * @return array
     */
    public function getDesignConfiguration()
    {
        return $this->getHelperConfig()->getDesignConfig();
    }

    /**
     * @return string
     */
    public function getCurrentTheme()
    {
        return $this->_themeProviderInterface->getThemeById($this->getHelperConfig()->getCurrentThemeId())->getCode();
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isVirtual()
    {
        return $this->checkoutSession->getQuote()->isVirtual();
    }
}
