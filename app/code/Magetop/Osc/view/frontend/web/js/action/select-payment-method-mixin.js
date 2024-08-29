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

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'Magento_Checkout/js/action/set-payment-information-extended',
    'Magento_Checkout/js/action/get-totals',
    'Magento_SalesRule/js/model/coupon'
], function ($, wrapper, quote, messageContainer, setPaymentInformationExtended, getTotalsAction, coupon) {
    'use strict';

    return function (selectPaymentMethodAction) {

        return wrapper.wrap(selectPaymentMethodAction, function (originalSelectPaymentMethodAction, paymentMethod) {

            originalSelectPaymentMethodAction(paymentMethod);

            if (paymentMethod === null || quote.guestEmail === null) {
                return;
            }

            $.when(
                setPaymentInformationExtended(
                    messageContainer,
                    {
                        method: paymentMethod.method
                    },
                    true
                )
            ).done(
                function () {
                    var deferred = $.Deferred(),

                        /**
                         * Update coupon form.
                         */
                        updateCouponCallback = function () {
                            if (quote.totals() && !quote.totals()['coupon_code']) {
                                coupon.setCouponCode('');
                                coupon.setIsApplied(false);
                            }
                        };

                    getTotalsAction([], deferred);
                    $.when(deferred).done(updateCouponCallback);
                }
            );
        });
    };

});
