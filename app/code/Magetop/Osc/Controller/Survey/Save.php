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

namespace Magetop\Osc\Controller\Survey;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Magento\Sales\Model\Order;
use Magetop\Osc\Helper\Data as OscHelper;

/**
 * Class Save
 * @package Magetop\Osc\Controller\Survey
 */
class Save extends Action
{
    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var OscHelper
     */
    protected $oscHelper;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param JsonHelper $jsonHelper
     * @param Session $checkoutSession
     * @param Order $order
     * @param OscHelper $oscHelper
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        Session $checkoutSession,
        Order $order,
        OscHelper $oscHelper
    ) {
        $this->jsonHelper       = $jsonHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_order           = $order;
        $this->oscHelper        = $oscHelper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|null
     */
    public function execute()
    {
        $response      = [];
        $answerChecked = $this->getRequest()->getParam('answerChecked', []);
        if ($answerChecked && isset($this->_checkoutSession->getOscData()['survey'])) {
            try {
                $order   = $this->_order->load($this->_checkoutSession->getOscData()['survey']['orderId']);
                $answers = '';
                foreach ($answerChecked as $item) {
                    $answers .= $item . ' - ';
                }
                $order->setData('osc_survey_question', $this->oscHelper->getSurveyQuestion());
                $order->setData('osc_survey_answers', substr($answers, 0, -2));
                $order->save();

                $response['status']  = 'success';
                $response['message'] = __('Thank you for completing our survey!');
                $this->_checkoutSession->unsOscData();
            } catch (Exception $e) {
                $response['status']  = 'error';
                $response['message'] = __('Can\'t save survey answer. Please try again!');
            }

            return $this->getResponse()->representJson($this->jsonHelper->serialize($response));
        }

        return null;
    }
}
