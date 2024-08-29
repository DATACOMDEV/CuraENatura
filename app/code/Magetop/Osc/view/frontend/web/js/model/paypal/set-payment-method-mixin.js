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

/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magetop_Osc/js/action/set-payment-method'
], function ($, wrapper, setPaymentMethodAction) {
    'use strict';

    return function (originalSetPaymentMethodAction) {
        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(originalSetPaymentMethodAction, function (originalAction, messageContainer) {
            return setPaymentMethodAction(messageContainer);
        });
    };
});
