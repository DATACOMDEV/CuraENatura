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
    'underscore',
    'Magento_Ui/js/form/element/region',
    'mageUtils',
    'uiLayout'
], function (_, Component, utils, layout) {
    'use strict';
    var template = window.checkoutConfig.oscConfig.isUsedMaterialDesign ? 'Magetop_Osc/container/form/field' : '${ $.$data.template }';
    var inputNode = {
        parent: '${ $.$data.parentName }',
        component: 'Magento_Ui/js/form/element/abstract',
        template: template,
        elementTmpl: 'Magetop_Osc/container/form/element/input',
        provider: '${ $.$data.provider }',
        name: '${ $.$data.index }_input',
        dataScope: '${ $.$data.customEntry }',
        customScope: '${ $.$data.customScope }',
        sortOrder: '${ $.$data.sortOrder }',
        displayArea: 'body',
        label: '${ $.$data.label }'
    };

    return Component.extend({
        initInput: function () {
            layout([utils.template(_.extend(inputNode, {additionalClasses: this.additionalClasses}), this)]);

            return this;
        }
    });
});

