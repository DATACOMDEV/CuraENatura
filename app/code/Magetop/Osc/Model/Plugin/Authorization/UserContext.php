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

namespace Magetop\Osc\Model\Plugin\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magetop\Osc\Helper\Data as OscHelper;

/**
 * Class UserContext
 * @package Magetop\Osc\Model\Plugin\Authorization
 */
class UserContext
{
    /**
     * @var OscHelper
     */
    protected $_oscHelper;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * UserContext constructor.
     *
     * @param OscHelper $oscHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        OscHelper $oscHelper,
        Session $checkoutSession
    ) {
        $this->_oscHelper       = $oscHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param UserContextInterface $userContext
     * @param $result
     *
     * @return int
     */
    public function afterGetUserType(UserContextInterface $userContext, $result)
    {
        if ($this->_oscHelper->isFlagOscMethodRegister()) {
            return UserContextInterface::USER_TYPE_CUSTOMER;
        }

        return $result;
    }

    /**
     * @param UserContextInterface $userContext
     * @param $result
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetUserId(UserContextInterface $userContext, $result)
    {
        if ($this->_oscHelper->isFlagOscMethodRegister()) {
            return $this->_checkoutSession->getQuote()->getCustomerId();
        }

        return $result;
    }
}
