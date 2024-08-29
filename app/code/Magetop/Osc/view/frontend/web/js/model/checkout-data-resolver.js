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
        'Magento_Checkout/js/checkout-data'
    ],
    function (checkoutData) {
        'use strict';

        return {

            /**
             * Set default shipping method to local storage
             */
            resolveDefaultShippingMethod: function () {
                if (!checkoutData.getSelectedShippingRate() && window.checkoutConfig.selectedShippingRate) {
                    checkoutData.setSelectedShippingRate(window.checkoutConfig.selectedShippingRate);
                }
            },

            /**
             * Set default payment method to local storage
             */
            resolveDefaultPaymentMethod: function () {
                if (!checkoutData.getSelectedPaymentMethod() && window.checkoutConfig.selectedPaymentMethod) {
                    checkoutData.setSelectedPaymentMethod(window.checkoutConfig.selectedPaymentMethod);
                }
            }
        }
    }
);
