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
    'ko',
    'Magento_Checkout/js/view/form/element/email',
    'Magento_Customer/js/model/customer',
    'Magetop_Osc/js/model/osc-data',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magetop_Osc/js/action/check-email-availability',
    'Magetop_Osc/js/model/compatible/amazon-pay',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/quote',
    'mage/url',
    'rjsResolver',
    'mage/validation'
], function ($,
             ko,
             Component,
             customer,
             oscData,
             additionalValidators,
             checkEmailAvailability,
             amazonPay,
             checkoutData,
             quote,
             urlBuilder,
             resolver) {
    'use strict';

    var cacheKey = 'form_register_chechbox',
        allowGuestCheckout = window.checkoutConfig.oscConfig.allowGuestCheckout,
        passwordMinLength = window.checkoutConfig.oscConfig.register.dataPasswordMinLength,
        passwordMinCharacter = window.checkoutConfig.oscConfig.register.dataPasswordMinCharacterSets,
        customerEmailElement = '.form-login #customer-email';

    if (!customer.isLoggedIn() && !allowGuestCheckout) {
        oscData.setData(cacheKey, true);
    }

    /**
     * Get Amazon customer email
     */
    function getAmazonCustomerEmail() {
        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        if (window.checkoutConfig.hasOwnProperty('amazonLogin') &&
            typeof window.checkoutConfig.amazonLogin.amazon_customer_email === 'string'
        ) {
            return window.checkoutConfig.amazonLogin.amazon_customer_email;
        }
        // jscs:enable requireCamelCaseOrUpperCaseIdentifiers

        return '';
    }

    return Component.extend({
        defaults: {
            email: checkoutData.getInputFieldEmailValue() || getAmazonCustomerEmail(),
            template: 'Magetop_Osc/container/form/element/email',
            isLoginVisible: false,
            listens: {
                email: ''
            }
        },
        savingEmailRequest: null,
        dataPasswordMinLength: passwordMinLength,
        dataPasswordMinCharacterSets: passwordMinCharacter,

        initialize: function () {
            this._super();

            if (!customer.isLoggedIn()) {
                if (!!this.email()) {
                    resolver(this.emailHasChanged.bind(this));
                }
            }

            additionalValidators.registerValidator(this);
        },

        initObservable: function () {
            this._super()
                .observe({
                    isCheckboxRegisterVisible: allowGuestCheckout,
                    isRegisterVisible: oscData.getData(cacheKey)
                });

            this.isRegisterVisible.subscribe(function (newValue) {
                oscData.setData(cacheKey, newValue);
            });

            if (window.checkoutConfig.hasOwnProperty('amazonLogin') && this.email()) {
                if ($.validator.methods['validate-email'].call(this, this.email())) {
                    quote.guestEmail = this.email();
                    checkoutData.setValidatedEmailValue(this.email());
                }

                checkoutData.setInputFieldEmailValue(this.email());
            }

            return this;
        },

        /**
         * Check email existing.
         */
        checkEmailAvailability: function () {
            var self = this;

            this.validateRequest();
            this.isLoading(true);
            this.checkRequest = checkEmailAvailability(this.email());
            this.checkRequest.done(function (isEmailAvailable) {
                self.isPasswordVisible(!isEmailAvailable);
            }).fail(function () {
                self.isPasswordVisible(false);
            }).always(function () {
                self.isLoading(false);
            });
        },

        triggerLogin: function () {
            if ($('.osc-authentication-wrapper a.action-auth-toggle').hasClass('osc-authentication-toggle')) {
                if(window.checkoutConfig.oscConfig.isPopupSlideSocialLogin){
                    $('.social-login-btn').trigger('click');
                }else {
                    $('.osc-authentication-toggle').trigger('click');
                }
            } else {
                window.location.href = urlBuilder.build("customer/account/login");
            }
        },

        validateEmail: function (focused) {
            var loginFormSelector = 'form[data-role=email-with-possible-login]',
                usernameSelector = loginFormSelector + ' input[name=username]',
                loginForm = $(loginFormSelector),
                validator;

            if (!loginForm.length) {
                return false;
            }

            this.checkDelay = 0;

            loginForm.validation();

            if (focused === false) {
                return !!$(usernameSelector).valid();
            }

            validator = loginForm.validate();

            return validator.check(usernameSelector);
        },

        validate: function (type) {

            if (customer.isLoggedIn() || !this.isRegisterVisible() || this.isPasswordVisible()) {
                oscData.setData('register', false);
                return true;
            }

            if (typeof type !== 'undefined' && typeof type !== 'boolean') {
                var selector = $('#osc-' + type);

                selector.parents('form').validation();

                return !!selector.valid();
            }

            var passwordSelector = $('#osc-password');

            passwordSelector.parents('form').validation();
            var password = !!passwordSelector.valid(),
                confirm = !!$('#osc-password-confirmation').valid(),
                result = password && confirm;

            if (result) {
                oscData.setData('register', true);
                oscData.setData('password', passwordSelector.val());
            } else if (!password) {
                passwordSelector.focus();
            } else if (!confirm) {
                $('#osc-password-confirmation').focus();
            }

            return result;

        },

        /** Move label element when input has value */
        hasValue: function () {
            if (window.checkoutConfig.oscConfig.isUsedMaterialDesign) {
                $(customerEmailElement).val() ?
                    $(customerEmailElement).addClass('active') :
                    $(customerEmailElement).removeClass('active');
            }
        }
    });
});
