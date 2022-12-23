define([
    'Magento_Ui/js/form/element/select',
    'mage/translate',
    'jquery',
    'ko',
    'mageUtils',
    'rjsResolver',
    'Magento_Ui/js/lib/validation/validator'
], function (AbstractField, $t,$,ko,utils,resolver,validator) {
    'use strict';
    validator.addRule(
            'validate-fiscal_code',
            function(value) {
                if($('select[name="custom_attributes[fiscal_company]"]').val() == 2){
                    if (value.trim() == '') return true;
                    return /^[a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]$/.test(value);
                }
                else {
                    return true;
                }
            },
            $.mage.__("Fiscal Code is not valid.")
        );
    validator.addRule(
        'validate-fiscal_sdi',
        function (value, params, additionalParams) {
            if(jQuery('div[name="'+params+'"]:visible').length){
                return !utils.isEmpty(value);
            }
            return true;
        },
        $.mage.__("This is a required field.")
    );
    validator.addRule(
        'validate-company',
        function (value, params, additionalParams) {
            if(jQuery('div[name="'+params+'"]:visible').length){
                return !utils.isEmpty(value);
            }
            return true;
        },
        $.mage.__("This is a required field.")
    );
    validator.addRule(
        'validate-vat_id',
        function (value, params, additionalParams) {
            if(jQuery('div[name="'+params+'"]:visible').length){
                return !utils.isEmpty(value);
            }
            return true;
        },
        $.mage.__("This is a required field.")
    );
    validator.addRule(
        'validate-is_a_company',
        function (value, params, additionalParams) {
            if(jQuery('div[name="'+params+'"]:visible').length){
                return !utils.isEmpty(value);
            }
            return true;
        },
        $.mage.__("This is a required field.")
    );


    return AbstractField.extend({
        defaults: {
            modules : {
                country:'${ $.parentName }.country_id',
                fiscalCompany:'${ $.parentName }.fiscal_company',
                fiscalCode:'${ $.parentName }.fiscal_code',
                fiscalSdi:'${ $.parentName }.fiscal_sdi',
                vatId:'${ $.parentName }.vat_id',
                company:'${ $.parentName }.company',
            },
            imports:{
                updateFiscalForm:'${ $.parentName }.fiscal_company:value',
                updateCountryId:'${ $.parentName }.country_id:value',
            }
        },
        initialize: function () {
            var self = this;
            //initialize parent Component
            this._super();
            resolver(this.hideLoader, this);
            return this;
        },
        hideLoader: function(value) {
            var fiscalCompany = this.fiscalCompany();
            //IF FISCAL COMPANY DEFAULT VALUE == 1
            if(typeof fiscalCompany !== 'undefined' && fiscalCompany.fiscalCompanyDefautlValue == 1){
                this.fiscalCompany().set('value',2);
            }
            this.updateCountryId(value);
        },
        getFiscalCode : function () {
            var self = this;
            var fiscalCompany = this.fiscalCompany();
            var fiscalCode = this.fiscalCode();
            if (fiscalCompany.fiscalCodeBusiness === "1") {
                fiscalCode.show().required(false).reset();
            }
            else {
                fiscalCode.hide().required(false);
            }
        },
        updateCountryId: function (value) {
            var self = this;
            var countryValue = this.country().value();
            var fiscalCompany = this.fiscalCompany();
            var fiscalCode = this.fiscalCode();
            var fiscalSdi = this.fiscalSdi();
            var vatId = this.vatId();
            var company = this.company();
            if(typeof this.fiscal_company  !== 'undefined') {
                var fiscalCompanyValue = this.fiscal_company().value();
            }

            if(typeof fiscalCompany !== 'undefined'){
                // DEFAULT SHOP VERSION
                if(fiscalCompany.shopVersion == 'default'){
                    // COUNTRY NOT ITALY
                    if (!countryValue || countryValue !== 'IT') {
                        fiscalCode.hide();
                        fiscalSdi.hide();
                        fiscalCompany.hide();
                        if (fiscalCompanyValue  === "1"){
                            company.show();
                            vatId.show();
                        }
                        else if (fiscalCompanyValue === "2") {
                            company.hide();
                            vatId.hide();
                        }
                    }
                    // COUNTRY IS ITALY
                    if (countryValue === 'IT') {
                        if(typeof fiscalCompany  !== 'undefined') {
                            fiscalCompany.show();
                            fiscalCode.hide();
                            fiscalSdi.hide();
                            //IS BUSINESS
                            if (fiscalCompany.value() === "1") {
                                //CONFIG FISCAL CODE ALSO FOR BUSINESS
                                this.getFiscalCode();
                                fiscalSdi.show().required(true);
                                company.show();
                                vatId.show();
                            }
                            //NOT BUSINESS
                            else if (fiscalCompany.value() === "2") {
                                if(fiscalCompany.fiscalCodeIsRequired === 1) {
                                    fiscalCode.show().required(true);
                                }
                                else {
                                    fiscalCode.show().required(false);
                                }
                                fiscalSdi.hide();
                                company.hide();
                                vatId.hide();
                            }
                        }
                    }
                }
                // B2B SHOP VERSION
                else if (fiscalCompany.shopVersion == 'b2b'){
                    if (!countryValue || countryValue !== 'IT') {
                        fiscalCompany.hide();
                        fiscalSdi.hide();
                        this.getFiscalCode();
                    }
                    if (countryValue === 'IT') {
                        fiscalCompany.hide();
                        if (fiscalCompany.fiscalCodeBusiness === "1") {
                            fiscalCode.show().required(false).reset();
                        }
                        else {
                            fiscalCode.hide().required(false);
                        }
                        // company.show();
                        // vatId.show();
                        fiscalSdi.show().required(true);
                    }
                }
                // B2C SHOP VERSION
                else if (fiscalCompany.shopVersion == 'b2c'){
                    if (!countryValue || countryValue !== 'IT') {
                        fiscalCompany.hide();
                        fiscalCode.hide();
                        fiscalSdi.hide();
                        //   company.hide();
                        //  vatId.hide();
                    }
                    if (countryValue === 'IT') {
                        fiscalCompany.hide();
                        if (fiscalCompany.fiscalCodeIsRequired === 1) {
                            fiscalCode.show().required(true);
                        } else {
                            fiscalCode.show().required(false);
                        }
                        fiscalSdi.hide();
                        //  company.hide();
                        //  vatId.hide();
                    }
                }
            }


        },

        updateFiscalForm: function (value) {
            var self = this;
            if(typeof this.country()  !== 'undefined') {
                var countryValue = this.country().value();
            }

            var fiscalCompany = this.fiscalCompany();
            var fiscalCompanyValue = value;
            var fiscalCode = this.fiscalCode();
            var fiscalSdi = this.fiscalSdi();
            var vatId = this.vatId();
            var company = this.company();
            // IF IS DEFAULT VERSION
            if(typeof fiscalCompany !== 'undefined' && fiscalCompany.shopVersion == 'default') {
                if (fiscalCompanyValue === "1") {
                    this.getFiscalCode();
                    if (countryValue === 'IT') {
                        fiscalSdi.show().required(true);
                    }
                    vatId.show();
                    company.show();
                } else if (fiscalCompanyValue === "2") {
                    if (countryValue === 'IT') {
                        if (fiscalCompany.fiscalCodeIsRequired === 1) {
                            fiscalCode.show().required(true);
                        } else {
                            fiscalCode.show().required(false);
                        }
                    }
                    fiscalSdi.hide();
                    vatId.hide();
                    company.hide();
                }
            }
        }
    });

});
