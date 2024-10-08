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
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils',
        'Magetop_Osc/js/action/gift-wrap'
    ],
    function ($,
              ko,
              Component,
              quote,
              totals,
              priceUtils,
              giftWrapAction) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Magetop_Osc/container/review/addition/gift-wrap'
            },
            quoteIsVirtual: quote.isVirtual(),
            initialAmount: ko.computed(function () {
                var gwAmount = 0;

                var gwSegment = totals.getSegment('osc_gift_wrap');
                if (gwSegment && gwSegment.extension_attributes) {
                    gwAmount = gwSegment.extension_attributes.gift_wrap_amount;
                }

                if (gwAmount >= 0) {
                    return priceUtils.formatPrice(gwAmount, quote.getPriceFormat());
                }

                return '';
            }),
            initObservable: function () {
                this._super()
                    .observe({
                        isUseGiftWrap: window.checkoutConfig.oscConfig.isUsedGiftWrap
                    });

                this.isUseGiftWrap.subscribe(function (newValue) {
                    var payload = {
                        is_use_gift_wrap: newValue
                    };

                    giftWrapAction(payload);
                });

                return this;
            }
        });
    }
);
