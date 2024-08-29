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

var config = {};
if (typeof window.oscRoute !== 'undefined' && window.location.href.indexOf(window.oscRoute) !== -1) {
    config = {
        map: {
            '*': {
                'Magento_Checkout/js/model/shipping-rate-service': 'Magetop_Osc/js/model/shipping-rate-service',
                'Magento_Checkout/js/model/shipping-rates-validator': 'Magetop_Osc/js/model/shipping-rates-validator',
                'Magento_CheckoutAgreements/js/model/agreements-assigner': 'Magetop_Osc/js/model/agreements-assigner',
                'Magento_Paypal/js/in-context/express-checkout-smart-buttons': 'Magetop_Osc/js/in-context/express-checkout-smart-buttons',
                'Magento_SalesRule/js/action/select-payment-method-mixin': 'Magetop_Osc/js/action/select-payment-method-mixin'
            },
            'Magetop_Osc/js/model/shipping-rates-validator': {
                'Magento_Checkout/js/model/shipping-rates-validator': 'Magento_Checkout/js/model/shipping-rates-validator'
            },
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'Magento_Checkout/js/model/full-screen-loader': 'Magetop_Osc/js/model/osc-loader'
            },
            'Magento_Checkout/js/action/set-billing-address': {
                'Magento_Checkout/js/model/full-screen-loader': 'Magetop_Osc/js/model/osc-loader'
            },
            'Magento_SalesRule/js/action/set-coupon-code': {
                'Magento_Checkout/js/model/full-screen-loader': 'Magetop_Osc/js/model/osc-loader/discount'
            },
            'Magento_SalesRule/js/action/cancel-coupon': {
                'Magento_Checkout/js/model/full-screen-loader': 'Magetop_Osc/js/model/osc-loader/discount'
            },
            'Magetop_Osc/js/model/osc-loader': {
                'Magento_Checkout/js/model/full-screen-loader': 'Magento_Checkout/js/model/full-screen-loader'
            }
        },
        config: {
            mixins: {
                'Magento_Braintree/js/view/payment/method-renderer/paypal': {
                    'Magetop_Osc/js/view/payment/method-renderer/braintree-paypal-mixins': true
                },
                'Magento_Checkout/js/action/place-order': {
                    'Magetop_Osc/js/action/place-order-mixins': true
                },
                'Magento_Paypal/js/action/set-payment-method': {
                    'Magetop_Osc/js/model/paypal/set-payment-method-mixin': true
                },
                'Magento_Checkout/js/action/set-billing-address': {
                    'Magetop_Osc/js/action/set-billing-address-mixin': true
                }
            }
        }
    };

    if (window.isEnableAmazonPayCv2 === 1) {
        config.config.mixins['Magetop_Osc/js/view/shipping-address/address-renderer/default'] = {
            'Amazon_Pay/js/view/shipping-address/address-renderer/default': true
        };
    }

    if (window.location.href.indexOf('#') !== -1) {
        window.history.pushState("", document.title, window.location.pathname);
    }
}
