/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magetop.com license that is
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

define(
    [
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote',
        'Magetop_Osc/js/model/resource-url-manager',
        'Magetop_Osc/js/model/discount-payment-method'
    ],
    function (totals,
              quote,
              resourceUrlManager,
              discountPaymentMethod) {
        'use strict';

        return function (paymentData) {
            totals.isLoading(true);

            if (paymentData && paymentData.hasOwnProperty('__disableTmpl')) {
                delete paymentData.__disableTmpl;
            }

            var payload;
            if (paymentData.hasOwnProperty('title')) {
                paymentData = {
                    additional_data: null,
                    method: paymentData.method,
                    po_number: null,
                }
            }

            payload = {
                cartId: quote.getQuoteId(),
                paymentMethod: paymentData,
                billingAddress: quote.billingAddress()
            };

            return discountPaymentMethod(resourceUrlManager.getUrlForDiscountPaymentMethod(quote), payload);
        };
    }
);
