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

define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magetop_Osc/js/model/checkout-data-resolver',
        'Magetop_Osc/js/model/payment-service',
        'Magetop_Osc/js/model/paypal_express_compatible',
        'Magento_Customer/js/customer-data',
        'Magetop_Osc/js/action/discount-payment-method',
        'rjsResolver',
        'mage/translate'
    ],
    function (ko,
              $,
              Component,
              quote,
              stepNavigator,
              additionalValidators,
              oscDataResolver,
              oscPaymentService,
              paypalExpressCompatible,
              customerData,
              discountPaymentMethodAction,
              resolver) {
        'use strict';

        oscDataResolver.resolveDefaultPaymentMethod();
        var isReload = true;

        return Component.extend({
            defaults: {
                template: 'Magetop_Osc/container/payment'
            },
            isLoading: oscPaymentService.isLoading,
            errorValidationMessage: ko.observable(false),

            initialize: function () {
                var self = this;

                this._super();

                stepNavigator.steps.removeAll();

                additionalValidators.registerValidator(this);

                quote.paymentMethod.subscribe(function (value) {
                    paypalExpressCompatible.togglePlaceOrderButton(quote.paymentMethod());
                    self.errorValidationMessage(false);
                    if($.type(value) === 'object') {
                        discountPaymentMethodAction(value);
                    }
                });

                if ($('.page.messages')) {
                    setTimeout(function () {
                        $('.page.messages').remove()
                    }, 8000);
                }

                if (isReload) {
                    customerData.reload(['cart'], false);
                    isReload = false;
                }
                this.customer = customerData.get('cart');

                resolver(this.afterResolveDocument.bind(this));

                return this;
            },

            validate: function () {
                if (!quote.paymentMethod()) {
                    this.errorValidationMessage($.mage.__('Please specify a payment method.'));

                    return false;
                }

                return true;
            },

            afterResolveDocument: function () {
                if($.type(quote.paymentMethod()) === 'object'){
                    discountPaymentMethodAction(quote.paymentMethod());
                }
            },
        });
    }
);
