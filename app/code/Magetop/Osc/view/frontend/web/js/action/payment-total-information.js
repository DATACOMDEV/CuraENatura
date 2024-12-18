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
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magetop_Osc/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/shipping-service',
        'Magetop_Osc/js/model/osc-loader',
        'uiRegistry'
    ],
    function ($,
              quote,
              resourceUrlManager,
              storage,
              errorProcessor,
              customer,
              methodConverter,
              paymentService,
              shippingService,
              oscLoader,
              registry) {
        'use strict';

        return function () {
            oscLoader.startLoader();

            return storage.post(
                resourceUrlManager.getUrlForUpdatePaymentTotalInformation(quote)
            ).done(
                function (response) {
                    var options, paths ,
                        thumbnailComponent = registry.get('checkout.sidebar.summary.cart_items.details.thumbnail');

                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                        return;
                    }

                    // remove downloadable options on cart item reload
                    $('#downloadable-links-list').remove();
                    $('#links-advice-container').remove();

                    if (response.image_data && thumbnailComponent) {
                        thumbnailComponent.imageData = JSON.parse(response.image_data);
                    }

                    if (response.options) {
                        options = JSON.parse(response.options);

                        response.totals.items.forEach(function (item) {
                            item.mposc = options[item.item_id];
                        });
                    }

                    if (response.request_path) {
                        paths = JSON.parse(response.request_path);

                        response.totals.items.forEach(function (item) {
                            item.request_path = paths[item.item_id];
                        });
                    }

                    quote.setTotals(response.totals);
                    paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(
                function () {
                    oscLoader.stopLoader();
                }
            );
        };
    }
);
