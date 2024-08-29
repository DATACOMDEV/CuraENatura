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

namespace Magetop\Osc\Model\Plugin\Quote;

use Magento\Quote\Model\Quote;

/**
 * Class QuoteValidator
 * @package Magetop\Osc\Model\Plugin\Quote
 */
class QuoteValidator
{
    /**
     * @param \Magento\Quote\Model\QuoteValidator $subject
     * @param Quote $quote
     *
     * @return mixed
     */
    public function beforeValidateBeforeSubmit(
        \Magento\Quote\Model\QuoteValidator $subject,
        Quote $quote
    ) {
        if (!$quote->isVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }
        $quote->getBillingAddress()->setShouldIgnoreValidation(true);

        return [$quote];
    }
}
