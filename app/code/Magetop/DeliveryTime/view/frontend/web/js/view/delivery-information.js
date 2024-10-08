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
 * @package     Magetop_DeliveryTime
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

define(
    [
        'jquery',
        'ko',
        'underscore',
        'uiComponent',
        'Magetop_DeliveryTime/js/model/mpdt-data',
        'Magetop_DeliveryTime/js/model/delivery-information',
        'jquery/ui',
        'jquery/jquery-ui-timepicker-addon'
    ],
    function ($, ko, _, Component, mpDtData, deliveryInformation) {
        'use strict';

        var cacheKeyDeliveryDate = 'deliveryDate',
            cacheKeyDeliveryTime = 'deliveryTime',
            cacheKeyHouseSecurityCode = 'houseSecurityCode',
            cacheKeyDeliveryComment = 'deliveryComment',
            dateFormat = window.checkoutConfig.mpDtConfig.deliveryDateFormat,
            daysOff = window.checkoutConfig.mpDtConfig.deliveryDaysOff || [],
            dateOff = [];

        function prepareSubscribeValue(object, cacheKey) {
            var deliveryDateOff = _.pluck(window.checkoutConfig.mpDtConfig.deliveryDateOff, 'date_off'),
                deliveryData = mpDtData.getData(cacheKey);

            if (deliveryData && cacheKey === cacheKeyDeliveryDate) {
                _.each(deliveryDateOff, function (dateoff) {
                    if (dateToString(deliveryData) === dateToString(dateoff)) {
                        mpDtData.setData(cacheKey, '');
                    }
                });

                if (daysOff.indexOf(dateToString(deliveryData, true)) !== -1) {
                    mpDtData.setData(cacheKey, '');
                }
            }

            object(mpDtData.getData(cacheKey));
            object.subscribe(function (newValue) {
                mpDtData.setData(cacheKey, newValue);
            });
        }

        function dateToString(dt, isDay = false) {
            var date = new Date(dt),
                month = date.getMonth() + 1;

            if (!month) {
                dt = dt.split(/[\./-]+/);
                date = new Date(dt[2], dt[1], dt[0]);
                month = date.getMonth();
            }

            if (isDay) {
                return date.getDay();
            }

            return date.getDate() + month + date.getFullYear();
        }

        function formatDeliveryTime(time) {
            var from = time['from'][0] + 'h' + time['from'][1],
                to = time['to'][0] + 'h' + time['to'][1];
            return from + ' - ' + to;
        }

        return Component.extend({
            defaults: {
                template: 'Magetop_DeliveryTime/container/delivery-information'
            },
            deliveryDate: deliveryInformation().deliveryDate,
            deliveryTime: deliveryInformation().deliveryTime,
            houseSecurityCode: deliveryInformation().houseSecurityCode,
            deliveryComment: deliveryInformation().deliveryComment,
            deliveryTimeOptions: deliveryInformation().deliveryTimeOptions,
            isVisible: ko.observable(mpDtData.getData(cacheKeyDeliveryDate)),

            initialize: function () {
                this._super();

                var self = this;

                dateOff = _.pluck(window.checkoutConfig.mpDtConfig.deliveryDateOff, 'date_off');
                ko.bindingHandlers.mpdatepicker = {
                    init: function (element) {
                        var options = {
                            minDate: 0,
                            showButtonPanel: false,
                            dateFormat: dateFormat,
                            showOn: 'both',
                            buttonText: '',
                            beforeShowDay: function (date) {
                                var currentDay = date.getDay();
                                var currentDate = date.getDate();
                                var currentMonth = date.getMonth() + 1;
                                var formatCurrentMonth = String(currentMonth).length > 1 ? currentMonth : '0' + currentMonth;
                                var currentYear = date.getFullYear();
                                var dateToCheck = ('0' + currentDate).slice(-2) + '/' + formatCurrentMonth + '/' + currentYear;
                                var isAvailableDay = daysOff.indexOf(currentDay) === -1;
                                var isAvailableDate = $.inArray(dateToCheck, dateOff) === -1;

                                return [isAvailableDay && isAvailableDate];
                            }
                        };
                        $(element).datepicker(options);
                    }
                };

                $.each(window.checkoutConfig.mpDtConfig.deliveryTime, function (index, item) {
                    self.deliveryTimeOptions.push(formatDeliveryTime(item));
                });

                prepareSubscribeValue(this.deliveryDate, cacheKeyDeliveryDate);
                prepareSubscribeValue(this.deliveryTime, cacheKeyDeliveryTime);
                prepareSubscribeValue(this.houseSecurityCode, cacheKeyHouseSecurityCode);
                prepareSubscribeValue(this.deliveryComment, cacheKeyDeliveryComment);

                this.isVisible = ko.computed(function () {
                    return !!self.deliveryDate();
                });

                return this;
            },

            removeDeliveryDate: function () {
                if (mpDtData.getData(cacheKeyDeliveryDate) && mpDtData.getData(cacheKeyDeliveryDate) != null) {
                    this.deliveryDate('');
                }
            }
        });
    }
);
