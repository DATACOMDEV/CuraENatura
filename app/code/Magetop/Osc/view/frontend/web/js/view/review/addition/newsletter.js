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
        'uiComponent',
        'Magetop_Osc/js/model/osc-data'
    ],
    function (ko, Component, oscData) {
        "use strict";

        var cacheKey = 'is_subscribed';

        return Component.extend({
            defaults: {
                template: 'Magetop_Osc/container/review/addition/newsletter'
            },
            initObservable: function () {
                this._super()
                    .observe({
                        isRegisterNewsletter: (typeof oscData.getData(cacheKey) === 'undefined') ? window.checkoutConfig.oscConfig.newsletterDefault : oscData.getData(cacheKey)
                    });
                oscData.setData(cacheKey, this.isRegisterNewsletter());
                this.isRegisterNewsletter.subscribe(function (newValue) {
                    oscData.setData(cacheKey, newValue);
                });

                return this;
            }
        });
    }
);
