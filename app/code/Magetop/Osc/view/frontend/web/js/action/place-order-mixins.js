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
    'Magetop_Osc/js/action/set-checkout-information',
], function ($, wrapper, setCheckoutInformationAction) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            var deferred = $.Deferred();

            if (paymentData && paymentData.method === 'braintree_paypal') {
                setCheckoutInformationAction().done(function () {
                    originalAction(paymentData, messageContainer).done(function (response) {
                        deferred.resolve(response);
                    }).fail(function (response) {
                        deferred.reject(response);
                    });
                }).fail(function (response) {
                    deferred.reject(response);
                });
            } else {
                return originalAction(paymentData, messageContainer).fail(function (response) {
                    if(paymentData && paymentData.method === 'stripe_payments'){
                        deferred.reject(response);
                    }else {
                        if ($('.message-error').length) {
                            $('html, body').scrollTop(
                                $('.message-error:visible:first').closest('div').offset().top - $(window).height() / 2
                            );
                        }
                    }
                });
            }

            return deferred;
        });
    };
});
