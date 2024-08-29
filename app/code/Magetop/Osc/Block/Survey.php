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

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magetop\Osc\Helper\Data as OscHelper;
use Zend_Serializer_Exception;

/**
 * Class Survey
 * @package Magetop\Osc\Block\Survey
 */
class Survey extends Template
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
     * Survey constructor.
     *
     * @param Template\Context $context
     * @param OscHelper $oscHelper
     * @param Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        OscHelper $oscHelper,
        Session $checkoutSession,
        array $data = []
    ) {
        $this->_oscHelper       = $oscHelper;
        $this->_checkoutSession = $checkoutSession;

        parent::__construct($context, $data);

        $this->getLastOrderId();
    }

    /**
     * @return bool
     */
    public function isEnableSurvey()
    {
        return $this->_oscHelper->isEnabled() && !$this->_oscHelper->isDisableSurvey();
    }

    /**
     * get Last order id
     */
    public function getLastOrderId()
    {
        $orderId = $this->_checkoutSession->getLastRealOrder()->getEntityId();
        $this->_checkoutSession->setOscData(['survey' => ['orderId' => $orderId]]);
    }

    /**
     * @return mixed
     */
    public function getSurveyQuestion()
    {
        return $this->_oscHelper->getSurveyQuestion();
    }

    /**
     * @return array
     * @throws Zend_Serializer_Exception
     */
    public function getAllSurveyAnswer()
    {
        $answers = [];
        foreach ($this->_oscHelper->getSurveyAnswers() as $key => $item) {
            $answers[] = ['id' => $key, 'value' => $item['value']];
        }

        return $answers;
    }

    /**
     * @return mixed
     */
    public function isAllowCustomerAddOtherOption()
    {
        return $this->_oscHelper->isAllowCustomerAddOtherOption();
    }

    /**
     * @return mixed|string
     */
    public function getOscRoute()
    {
        return $this->_oscHelper->getOscRoute();
    }
}
