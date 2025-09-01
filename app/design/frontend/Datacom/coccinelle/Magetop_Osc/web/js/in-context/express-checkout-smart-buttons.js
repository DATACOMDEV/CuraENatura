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
    'underscore',
    'paypalInContextExpressCheckout',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magetop_Osc/js/action/set-checkout-information',
    'Magento_Checkout/js/action/select-payment-method',
    'Magetop_Osc/js/model/osc-data'
], function ($, _, paypal, additionalValidators, setCheckoutInformationAction, selectPaymentMethodAction, oscData) {
    'use strict';

    /**
     * Returns array of allowed funding
     *
     * @param {Object} config
     * @return {Array}
     */
    function getFunding(config) {
        return _.map(config, function (name) {
            return paypal.FUNDING[name];
        });
    }

    /**
     * @param scrollTop
     * @returns {*|jQuery}
     */
    function preparePlaceOrderPayPalExpress(scrollTop) {
        var scrollTop = (scrollTop !== undefined) ? scrollTop : true;
        var deferer = $.when(setCheckoutInformationAction());

        return scrollTop ? deferer.done(function () {
            $("body").animate({scrollTop: 0}, "slow");
        }) : deferer;
    }

    function checkFermoPointWarning() {
        if (!($('input[id="s_method_sendcloud_sendcloud"]').is(':checked'))) return true;
        if ($('#selectedServicePoint > .col').length == 0) return false;
        return true;
    }

    function storeFermoPointData() {
        let commentField = $('#comments');
        let selectedInfo = $('#selectedServicePoint > .col');
        let index = commentField.val().indexOf('[FERMO POINT]');
        if (index != -1) {
            commentField.val(commentField.val().substring(0, index));
        }
        if (!($('input[id="s_method_sendcloud_sendcloud"]').is(':checked'))) return;
        if (selectedInfo.length == 0) return;
        let comments = document.createElement('div');
        comments.innerHTML = selectedInfo.html().trim();
        comments.innerHTML = comments.innerHTML.replace(/\s+/g, " ");
        comments = commentField.val() + "\n[FERMO POINT]\n" + (comments.textContent || comments.innerText || '');
        commentField.val(comments);
        commentField.trigger('focusout');
        oscData.setData('comment', comments);
    }

    return function (clientConfig, element) {
        paypal.Button.render({
            env: clientConfig.environment,
            client: clientConfig.client,
            locale: clientConfig.locale,
            funding: {
                allowed: getFunding(clientConfig.allowedFunding),
                disallowed: getFunding(clientConfig.disallowedFunding)
            },
            style: clientConfig.styles,

            // Enable Pay Now checkout flow (optional)
            commit: clientConfig.commit,

            /**
             * Execute logic on Paypal button click
             */
            onClick: function () {
                if (!checkFermoPointWarning()) return;
                storeFermoPointData();
                if (additionalValidators.validate()) {
                    selectPaymentMethodAction(clientConfig.rendererComponent.getData());
                    preparePlaceOrderPayPalExpress().done(function () {
                        clientConfig.rendererComponent.onClick();
                    });
                }
            },

            /**
             * Set up a payment
             *
             * @return {*}
             */
            payment: function () {
                if (additionalValidators.validate()) {
                    var params = {
                        'quote_id': clientConfig.quoteId,
                        'customer_id': clientConfig.customerId || '',
                        'form_key': clientConfig.formKey,
                        button: clientConfig.button
                    };

                    return new paypal.Promise(function (resolve, reject) {
                        clientConfig.rendererComponent.beforePayment(resolve, reject).then(function () {
                            paypal.request.post(clientConfig.getTokenUrl, params).then(function (res) {
                                return clientConfig.rendererComponent.afterPayment(res, resolve, reject);
                            }).catch(function (err) {
                                return clientConfig.rendererComponent.catchPayment(err, resolve, reject);
                            });
                        });
                    });
                } else {
                    var offsetHeight = $(window).height() / 2,
                        errorMsgSelector = $('#maincontent .mage-error:visible:first').closest('.field');
                    errorMsgSelector = errorMsgSelector.length ? errorMsgSelector :
                        $('#maincontent .field-error:visible:first').closest('.field');

                    if (errorMsgSelector.length) {
                        if (errorMsgSelector.find('select').length) {
                            $('html, body').scrollTop(
                                errorMsgSelector.find('select').offset().top - offsetHeight
                            );
                            errorMsgSelector.find('select').focus();
                        } else if (errorMsgSelector.find('input').length) {
                            $('html, body').scrollTop(
                                errorMsgSelector.find('input').offset().top - offsetHeight
                            );
                            errorMsgSelector.find('input').focus();
                        }
                    } else if ($('.message-error:visible').length) {
                        $('html, body').scrollTop(
                            $('.message-error:visible:first').closest('div').offset().top - offsetHeight
                        );
                    }
                    return this;
                }
            },

            /**
             * Execute the payment
             *
             * @param {Object} data
             * @param {Object} actions
             * @return {*}
             */
            onAuthorize: function (data, actions) {
                var params = {
                    paymentToken: data.paymentToken,
                    payerId: data.payerID,
                    quoteId: clientConfig.quoteId || '',
                    customerId: clientConfig.customerId || '',
                    'form_key': clientConfig.formKey
                };

                return new paypal.Promise(function (resolve, reject) {
                    clientConfig.rendererComponent.beforeOnAuthorize(resolve, reject, actions).then(function () {
                        paypal.request.post(clientConfig.onAuthorizeUrl, params).then(function (res) {
                            clientConfig.rendererComponent.afterOnAuthorize(res, resolve, reject, actions);
                        }).catch(function (err) {
                            return clientConfig.rendererComponent.catchOnAuthorize(err, resolve, reject);
                        });
                    });
                });

            },

            /**
             * Process cancel action
             *
             * @param {Object} data
             * @param {Object} actions
             */
            onCancel: function (data, actions) {
                clientConfig.rendererComponent.onCancel(data, actions);
            },

            /**
             * Process errors
             */
            onError: function (err) {
                clientConfig.rendererComponent.onError(err);
            }
        }, element);
    };
});