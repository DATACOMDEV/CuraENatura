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
    'Magetop_Osc/js/model/address/type/google'
], function (googleAutoComplete) {
    'use strict';

    var addressType = {
        billing: 'checkout.steps.shipping-step.billingAddress.billing-address-fieldset',
        shipping: 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset'
    };

    return {
        register: function (type) {
            if (addressType.hasOwnProperty(type)) {
                switch (window.checkoutConfig.oscConfig.autocomplete.type) {
                    case 'google':
                        new googleAutoComplete(addressType[type]);
                        break;
                    case 'pca':
                        break;
                    default :
                        break;
                }
            }
        }
    };
});


