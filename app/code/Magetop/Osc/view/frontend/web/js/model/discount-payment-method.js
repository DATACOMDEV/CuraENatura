/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magetop.com license that is
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
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote'
    ],
    function (storage, errorProcessor, totals, quote) {
        'use strict';

        return function (serviceUrl, payload) {
            totals.isLoading(true);

            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).success(
                function (response) {
                    quote.setTotals(response);
                }
            ).always(
                function () {
                    totals.isLoading(false);
                }
            );
        };
    }
);

