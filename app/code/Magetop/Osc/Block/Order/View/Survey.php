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

namespace Magetop\Osc\Block\Order\View;

/**
 * Class Survey
 * @package Magetop\Osc\Block\Order\View
 */
class Survey extends AbstractView
{
    /**
     * @return string
     */
    public function getSurveyQuestion()
    {
        if ($order = $this->getOrder()) {
            return $order->getOscSurveyQuestion();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getSurveyAnswers()
    {
        if ($order = $this->getOrder()) {
            return $order->getOscSurveyAnswers();
        }

        return '';
    }
}
