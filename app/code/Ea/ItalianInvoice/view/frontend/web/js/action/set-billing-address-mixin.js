define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper,quote) {
    'use strict';

    return function (setBillingAddressAction) {
        return wrapper.wrap(setBillingAddressAction, function (originalAction, messageContainer) {

            var billingAddress = quote.billingAddress();

            if(billingAddress != undefined) {

                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }

                if (billingAddress.customAttributes == undefined) {
                    var billingVal = $('.billing-address-form input[name="fiscal_code"]').val();
                    if (!billingVal || billingVal.length == 0) {
                        if ($('input[name="billing-address-same-as-shipping"]:checked').length > 0) {
                            billingVal = $('.form-shipping-address input[name="fiscal_code"]').val();
                        }
                    }
                    if (billingVal && billingVal.length > 0) {
                        billingAddress.customAttributes = {'fiscal_code': billingVal};
                    }
                }

                if (billingAddress.customAttributes != undefined) {
                    $.each(billingAddress.customAttributes, function (key, value) {

                        if($.isPlainObject(value)){
                            key = value.attribute_code;
                            value = value.value;
                        }

                        billingAddress['extension_attributes'][key] = value;
                    });
                }

            }

            return originalAction(messageContainer);
        });
    };
});