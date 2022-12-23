<?php


namespace Ea\ItalianInvoice\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    protected $helper;

    public function __construct(
        \Ea\ItalianInvoice\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }
    public function getShippingFormFields($result)
    {
        //Se è presente indirizzo di spedizione
        if(isset($result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset'])
        ) {
            //array dei custom fields
            $customShippingFields = $this->getFields('shippingAddress.custom_attributes','shipping');

            $shippingFields = $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'];

            //aggiungo i fields custom al layout
            $shippingFields = array_replace_recursive($shippingFields,$customShippingFields);

            $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] = $shippingFields;


            $fiscalCodeIsRequired =  $this->isFiscalCodeRequired();
            $shopVersion =  $this->getShopVersion();
            $fiscalCodeBusiness =  $this->getFiscalCodeBusiness();
            $fiscalCompanyDefautlValue =  $this->getFiscalCompanyDefaultValue();
            //inietto un altro custom field
            $customAttributeCode = 'fiscal_company';
            $customField = [
              //  'component' => 'Magento_Ui/js/form/element/select',
                'component' => 'Ea_ItalianInvoice/js/fiscal-company',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/select',
                    'id' => 'fiscal_company',
                    'fiscalCodeIsRequired' => $fiscalCodeIsRequired,
                    'shopVersion' => $shopVersion,
                    'fiscalCodeBusiness' => $fiscalCodeBusiness,
                    'fiscalCompanyDefautlValue' => $fiscalCompanyDefautlValue,
                ],
                'dataScope' => 'shippingAddress.custom_attributes.fiscal_company',
                'label' => __('Are you a Company?'),
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => 'required-entry',
                'sortOrder' => 117,
                'id' => 'fiscal_company',
                'default' => '',
                'options' => [
                    [
                        'value' => '',
                        'label' => __(''),
                    ],
                    [
                        'value' => '1',
                        'label' => __('Yes'),
                    ],
                    [
                        'value' => '2',
                        'label' => __('No'),
                    ]
                ]
            ];

            $taxvatShow = $this->helper->getConfig('customer/address/taxvat_show');
            $companyShow = $this->helper->getConfig('customer/address/company_show');

            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $customField;
            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id']['sortOrder'] = 119;
            $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['company']['sortOrder'] = 118;

            // Disabling core validations and adding our custom validator.
            if($taxvatShow == 'req' AND (int)$this->helper->getConfig('ea_setting/general/enable')) {
                unset($result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id']['validation']['required-entry']);
                $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id']['validation']['validate-vat_id'] = 'shippingAddress.vat_id';
            }
            if($companyShow == 'req'AND (int)$this->helper->getConfig('ea_setting/general/enable')) {
                unset($result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['company']['validation']['required-entry']);
                $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['company']['validation']['validate-company'] = 'shippingAddress.company';
            }
        }


        return $result;
    }

    public function getBillingFormFields($result)
    {
        if(isset($result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list'])
        ) {
            $paymentForms = $result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'];

            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);

                if (!isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
                    continue;
                }

                $billingFields = $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'];

                $customBillingFields = $this->getFields('billingAddress' . $paymentMethodCode . '.custom_attributes','billing');

                $billingFields = array_replace_recursive($billingFields, $customBillingFields);

                $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'] = $billingFields;


                //inietto un altro custom field
                $fiscalCodeIsRequired =  $this->isFiscalCodeRequired();
                $shopVersion =  $this->getShopVersion();
                $fiscalCodeBusiness =  $this->getFiscalCodeBusiness();
                $fiscalCompanyDefautlValue =  $this->getFiscalCompanyDefaultValue();
                $customAttributeCode = 'fiscal_company';
                $customField = [
                    //  'component' => 'Magento_Ui/js/form/element/select',
                    'component' => 'Ea_ItalianInvoice/js/fiscal-company',
                    'config' => [
                        'customScope' => 'billingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/select',
                        'id' => 'fiscal_company',
                        'fiscalCodeIsRequired' => $fiscalCodeIsRequired,
                        'shopVersion' => $shopVersion,
                        'fiscalCodeBusiness' => $fiscalCodeBusiness,
                        'fiscalCompanyDefautlValue' => $fiscalCompanyDefautlValue,
                    ],
                    'dataScope' => 'billingAddress.custom_attributes.fiscal_company',
                    'label' => __('Are you a Company?'),
                    'provider' => 'checkoutProvider',
                    'visible' => false,
                    'validation' => 'required-entry',
                    'sortOrder' => 117,
                    'id' => 'fiscal_company',
                    'default' => '',
                    'options' => [
                        [
                            'value' => '',
                            'label' => __(''),
                        ],
                        [
                            'value' => '1',
                            'label' => __('Yes'),
                        ],
                        [
                            'value' => '2',
                            'label' => __('No'),
                        ]
                    ]
                ];

                $taxvatShow = $this->helper->getConfig('customer/address/taxvat_show');
                $companyShow = $this->helper->getConfig('customer/address/company_show');

                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'][$customAttributeCode] = $customField;
                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children']['company']['sortOrder'] = 118;
                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children']['vat_id']['sortOrder'] = 119;

                // Disabling core validations and adding our custom validator.
                if($companyShow == 'req' AND (int)$this->helper->getConfig('ea_setting/general/enable')) {
                    unset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children']['company']['validation']['required-entry']);// = 0;
                    $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children']['company']['validation']['validate-company'] = 'billingAddress' . $paymentMethodCode . '.company';
                }
                if($taxvatShow == 'req' AND (int)$this->helper->getConfig('ea_setting/general/enable')) {
                    unset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children']['vat_id']['validation']['required-entry']);// = 0;
                    $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children']['vat_id']['validation']['validate-vat_id'] = 'billingAddress' . $paymentMethodCode . '.vat_id';
                }
        }
        }

        return $result;
    }


    public function getBillingSharedFormFields($result){
        if(isset($result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['afterMethods'])
        ) {
            $paymentForms = $result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['afterMethods']['children'];

            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                $paymentMethodCode = 'shared';

                if (!isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form'])) {
                    continue;
                }

                $billingFields = $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['afterMethods']['children']['billing-address-form']['children']['form-fields']['children'];

                $customBillingFields = $this->getFields('billingAddress' . $paymentMethodCode . '.custom_attributes','billing');

                $billingFields = array_replace_recursive($billingFields, $customBillingFields);

                $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['afterMethods']['children']['billing-address-form']['children']['form-fields']['children'] = $billingFields;

                //inietto un altro custom field
                $customAttributeCode = 'fiscal_company';
                $fiscalCodeIsRequired =  $this->isFiscalCodeRequired();
                $shopVersion =  $this->getShopVersion();
                $fiscalCodeBusiness =  $this->getFiscalCodeBusiness();
                $fiscalCompanyDefautlValue =  $this->getFiscalCompanyDefaultValue();
                $customField = [
                    //  'component' => 'Magento_Ui/js/form/element/select',
                    'component' => 'Ea_ItalianInvoice/js/fiscal-company',
                    'config' => [
                        'customScope' => 'billingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/select',
                        'id' => 'fiscal_company',
                        'fiscalCodeIsRequired' => $fiscalCodeIsRequired,
                        'shopVersion' => $shopVersion,
                        'fiscalCodeBusiness' => $fiscalCodeBusiness,
                        'fiscalCompanyDefautlValue' => $fiscalCompanyDefautlValue,
                    ],
                    'dataScope' => 'billingAddress.custom_attributes.fiscal_company',
                    'label' => __('Are you a Company?'),
                    'provider' => 'checkoutProvider',
                    'visible' => false,
                    'validation' => 'required-entry',
                    'sortOrder' => 117,
                    'id' => 'fiscal_company',
                    'default' => '',
                    'options' => [
                        [
                            'value' => '',
                            'label' => __(''),
                        ],
                        [
                            'value' => '1',
                            'label' => __('Yes'),
                        ],
                        [
                            'value' => '2',
                            'label' => __('No'),
                        ]
                    ]
                ];

                $taxvatShow = $this->helper->getConfig('customer/address/taxvat_show');
                $companyShow = $this->helper->getConfig('customer/address/company_show');

                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children'][$customAttributeCode] = $customField;
                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['company']['sortOrder'] = 118;
                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['vat_id']['sortOrder'] = 119;

                // Disabling core validations and adding our custom validator.
                if($companyShow == 'req' AND (int)$this->helper->getConfig('ea_setting/general/enable')) {
                    unset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['company']['validation']['required-entry']);// = 0;
                    $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['company']['validation']['validate-company'] = 'billingAddress' . $paymentMethodCode . '.company';
                }
                if($taxvatShow == 'req' AND (int)$this->helper->getConfig('ea_setting/general/enable')) {
                    unset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['vat_id']['validation']['required-entry']);// = 0;
                    $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['vat_id']['validation']['validate-vat_id'] = 'billingAddress' . $paymentMethodCode . '.vat_id';
                }
            }
        }

        return $result;
    }


    public function process($result)
    {
        $enableModule = (int)$this->helper->getConfig('ea_setting/general/enable');

        if ($enableModule == 1) {
            $result = $this->getShippingFormFields($result);
            $result = $this->getBillingFormFields($result);
            $result = $this->getBillingSharedFormFields($result);
        } else {
            unset($result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['fiscal_code']);
            unset($result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['fiscal_sdi']);
            unset($result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['fiscal_company']);
        }
        return $result;
    }

    public function getFields($scope, $addressType)
    {
        $fields = [];
        foreach($this->getAdditionalFields($addressType) as $field){
            $fields[$field] = $this->getField($field,$scope);
        }

        return $fields;
    }

    public function getField($attributeCode, $scope)
    {
        //IF FiscalCode is NOT required
        if($this->isFiscalCodeRequired() == 0){
            $field = [
                'config' => [
                    'customScope' => $scope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input'
                ],
                'dataScope' => $scope . '.' . $attributeCode,
                'sortOrder' =>  120,
                'visible' => false,
                'provider' => 'checkoutProvider',
                'options' => []
            ];
        }
        //IF FiscalCode is required
        else{
            $field = [
                'config' => [
                    'customScope' => $scope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input'
                ],
                'dataScope' => $scope . '.' . $attributeCode,
                'sortOrder' =>  120,
                'visible' => false,
                'provider' => 'checkoutProvider',
                'validation' => [
                    'validate-'.$attributeCode => $scope . '.'.$attributeCode
                ],
                'options' => []
            ];
        }


        return $field;
    }

    public function getAdditionalFields($addressType='shipping')
    {
        $shippingAttributes = [];
        $billingAttributes = [];
        $shippingAttributes[] = 'fiscal_code';
        $billingAttributes[] = 'fiscal_code';


        $shippingAttributes[] = 'fiscal_sdi';
        $billingAttributes[] = 'fiscal_sdi';


        $shippingAttributes[] = 'fiscal_company';
        $billingAttributes[] = 'fiscal_company';

        return $addressType == 'shipping' ? $shippingAttributes : $billingAttributes;

    }

    public function isFiscalCodeRequired(){
        return (int)$this->helper->getConfig('ea_setting/general/fiscal_code_required');
    }

    public function getShopVersion(){
        return $this->helper->getConfig('ea_setting/general/shop_version');
    }

    public function getFiscalCodeBusiness(){
        return $this->helper->getConfig('ea_setting/general/fiscal_code_business');
    }

    public function getFiscalCompanyDefaultValue(){
        return $this->helper->getConfig('ea_setting/general/fiscal_company_value');
    }

}
