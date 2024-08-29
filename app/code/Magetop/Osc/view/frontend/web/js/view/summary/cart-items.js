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
    'ko',
    'underscore',
    'jquery',
    'Magento_Checkout/js/view/summary/cart-items',
    'Magetop_Osc/js/model/osc-data'
], function (ko, _, $, Component, oscData) {
    "use strict";

    var cacheKey = 'is_cart_expanded';

    return Component.extend({
        toggleCart: function () {
            oscData.setData(cacheKey, !this.isCartExpanded());

            return true;
        },

        isCartExpanded: function () {
            var isExpanded           = oscData.getData(cacheKey),
                isShowItemListToggle = window.checkoutConfig.oscConfig.isShowItemListToggle;

            return typeof isExpanded === 'undefined' || !isShowItemListToggle ? true : isExpanded;
        }
    });
});
