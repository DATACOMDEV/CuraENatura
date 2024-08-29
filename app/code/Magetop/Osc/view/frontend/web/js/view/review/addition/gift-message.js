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
        'jquery',
        'uiComponent',
        'Magetop_Osc/js/model/gift-message'
    ],
    function (ko, $, Component, giftMessageModel) {
        'use strict';
        return Component.extend({

            defaults: {
                template: 'Magetop_Osc/container/review/addition/gift-message'
            },
            formBlockVisibility: null,
            resultBlockVisibility: null,
            model: {},

            /**
             * Component init
             */
            initialize: function () {
                this._super()
                    .observe('formBlockVisibility')
                    .observe({
                        'resultBlockVisibility': false
                    });
                this.model = new giftMessageModel();
                this.isResultBlockVisible();
                this.isUseGiftMessage();
            },

            /**
             *
             * @returns {boolean}
             */
            isUseGiftMessage: function () {
                return !!window.checkoutConfig.oscConfig.giftMessageOptions.giftMessage.orderLevel.hasOwnProperty("gift_message_id");
            },

            /**
             * Is reslt block visible
             */
            isResultBlockVisible: function () {
                var self = this;

                if (this.model.getObservable('alreadyAdded')()) {
                    this.resultBlockVisibility(true);
                }
                this.model.getObservable('additionalOptionsApplied').subscribe(function (value) {
                    if (value == true) {
                        self.resultBlockVisibility(true);
                    }
                });
            },

            /**
             * @param {String} key
             * @return {*}
             */
            getObservable: function (key) {
                return this.model.getObservable(key);
            },

            /**
             * Hide\Show form block
             */
            toggleFormBlockVisibility: function () {
                if (!this.model.getObservable('alreadyAdded')()) {
                    this.formBlockVisibility(!this.formBlockVisibility());
                } else {
                    this.resultBlockVisibility(!this.resultBlockVisibility());
                }
                return true;
            },

            /**
             * @return {Boolean}
             */
            isActive: function () {
                return this.model.isGiftMessageAvailable();
            }
        });
    }
);
