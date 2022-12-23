define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function($){
    'use strict';
    return function() {
        $.validator.addMethod(
            "validate-fiscal_code-edit-address",
            function(value, element) {
                if($('#fiscal_company').val() == 2){
                    if (value.trim() == '') return true;
                    return /^[a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]$/.test(value);
                }
                else {
                    return true;
                }
            },
            $.mage.__("Fiscal Code is not valid.")
        );
    }
});
