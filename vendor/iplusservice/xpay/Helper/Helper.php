<?php

/**
 * Nexi XPay Payment Module
 *
 * @author      iPlusService S.r.l. <assistenza@iplusservice.it>
 * @category    Payment Module
 * @package     Nexi XPay
 * @version     5.0.0
 * @copyright   2017-2019 Nexi Payments S.p.A. (https://ecommerce.nexi.it)
 * @license     GNU General Public License v3.0
 */

namespace IPlusService\XPay\Helper;

use IPlusService\XPay\Model\Gateway;
use Magento\Sales\Model\Order;

class Helper
{

    /**
     *
     * @var \Magento\Framework\Locale\Resolver
     */
    private $_localeResolver;

    /**
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $_orderCollection;

    /**
     *
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $_countryFactory;

    /**
     *
     * @var \Magento\Framework\Url
     */
    private $_url;

    /**
     *
     * @var \IPlusService\XPay\Helper\Cap
     */
    private $_capHelper;

    /**
     *
     * @var \IPlusService\XPay\Helper\VersionHelper
     */
    private $_versionHelper;

    /**
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $_cartRepository;

    /**
     *
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    private $_orderSender;

    /**
     *
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    private $_invoiceSender;

    /**
     * 
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $_websiteRepository;

    /**
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $_orderFactory;

    /**
     *
     * @var string
     */
    private const TRANSACTION_ID_DELIMITER = "-";
    private const XPAY_TRANSACTION_ID_LENGTH = 30;
    private const NPG_TRANSACTION_ID_LENGTH = 18;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\Url $url
     * @param \IPlusService\XPay\Helper\Cap $capHelper
     * @param \IPlusService\XPay\Helper\VersionHelper $versionHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Url $url,
        \IPlusService\XPay\Helper\Cap $capHelper,
        \IPlusService\XPay\Helper\VersionHelper $versionHelper,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        $this->_localeResolver = $localeResolver;
        $this->_orderCollection = $orderCollection;
        $this->_countryFactory = $countryFactory;
        $this->_url = $url;
        $this->_capHelper = $capHelper;
        $this->_versionHelper = $versionHelper;
        $this->_cartRepository = $cartRepository;
        $this->_orderSender = $orderSender;
        $this->_invoiceSender = $invoiceSender;
        $this->_websiteRepository = $websiteRepository;
        $this->_orderFactory = $orderFactory;
    }

    /**
     * 
     * @param int $websiteId
     * @return int
     */
    public function getDefaultStoreId($websiteId)
    {
        $website = $this->_websiteRepository->getById($websiteId);

        $store = $website->getDefaultStore();

        return $store->getId();
    }

    /**
     * 
     * @param string $websiteId
     * @param string $storeId
     * @return string|int|null
     */
    public function resolveStoreId($websiteId, $storeId)
    {
        if ($storeId !== "" && $storeId !== null) {
            return $storeId;
        }

        if ($websiteId !== "" && $websiteId !== null) {
            return $this->getDefaultStoreId($websiteId);
        }

        return null;
    }

    public function getOrderIncrementIdFromQuoteId($quoteId)
    {
        $quote = $this->_cartRepository->get($quoteId);

        $orderIncrementId = $quote->getReservedOrderId();

        return $orderIncrementId;
    }

    public function getOrderFromOrderIncrementId($orderIncrementId)
    {
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);

        if (!isset($order) || !($order instanceof Order)) {
            throw new \IPlusService\XPay\Exception\Exception("Order not found " . $orderIncrementId);
        }

        return $order;
    }

    public function getUrl()
    {
        return $this->_url->getUrl('');
    }

    /**
     *
     * @return string
     */
    public function getResultUrl()
    {
        return $this->_url->getUrl('xpay/back/result');
    }

    /**
     *
     * @return string
     */
    public function getPostUrl()
    {
        return $this->_url->getUrl('xpay/back/post');
    }

    /**
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->_url->getUrl('xpay/back/cancel');
    }

    /**
     *
     * @return string
     */
    public function getNpgResultUrl($orderId)
    {
        return $this->_url->getUrl('xpay/back/npgresult/order/' . $orderId);
    }

    /**
     *
     * @return string
     */
    public function getNpgPostUrl()
    {
        return $this->_url->getUrl('xpay/back/npgpost');
    }

    /**
     * 
     * @param string $orderId
     * @return string
     */
    public function getNpgCancelUrl($orderId)
    {
        return $this->_url->getUrl('xpay/back/npgcancel/order/' . $orderId);
    }

    /**
     * 
     * @param string $quoteId
     * @param string $orderId
     * @return string
     */
    public function getNpgResultBuildUrl($quoteId, $orderId)
    {
        return $this->_url->getUrl('xpay/back/npgresultbuild/quote/' . $quoteId . '/order/' . $orderId);
    }

    /**
     * 
     * @param string $quoteId
     * @param string $orderId
     * @return string
     */
    public function getNpgCancelBuildUrl($quoteId, $orderId)
    {
        return $this->_url->getUrl('xpay/back/npgcancelbuild/quote/' . $quoteId . '/order/' . $orderId);
    }

    /**
     * Get ISO Code
     *
     * @param string $countryCode
     * @return string|null
     */
    public function getISO3Code($countryCode)
    {
        if ($countryCode) {
            $country = $this->_countryFactory->create()->loadByCode($countryCode);

            if ($country) {
                return $country->getData('iso3_code');
            }
        }

        return null;
    }

    /**
     * Create transactionId
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    public function createXPayTransactionIdFromQuote($quote)
    {
        return substr($quote->getId() . self::TRANSACTION_ID_DELIMITER . time(), 0, self::XPAY_TRANSACTION_ID_LENGTH);
    }

    /**
     * Create transactionId
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    public function createNpgTransactionIdFromQuote($quote)
    {
        return substr($quote->getId() . self::TRANSACTION_ID_DELIMITER . time(), 0, self::NPG_TRANSACTION_ID_LENGTH);
    }

    /**
     * Create transactionId
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function createXPayTransactionIdFromOrder($order)
    {
        return substr($order->getIncrementId() . self::TRANSACTION_ID_DELIMITER . time(), 0, self::XPAY_TRANSACTION_ID_LENGTH);
    }

    /**
     * Create transactionId
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function createNpgTransactionIdFromOrder($order)
    {
        return substr($order->getIncrementId() . self::TRANSACTION_ID_DELIMITER . time(), 0, self::NPG_TRANSACTION_ID_LENGTH);
    }

    /**
     * Create Nb order completed
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return int
     */
    public function getNbOrderCompletedPerCustomer($customer)
    {
        $this->_orderCollection
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addAttributeToFilter('state', Order::STATE_COMPLETE)
            ->addAttributeToFilter('created_at', ['gteq' => date('Y-m-d', strtotime("-6 months"))])
            ->getData();

        return count($this->_orderCollection);
    }

    /**
     * Get PagoDIL language
     *
     * @return string
     */
    public function getPagodilLanguage()
    {
        return $this->calculateLanguage($this->getPagodilLanguages(), Gateway::PAGODIL_LANG_IT);
    }

    /**
     * Get XPay language
     *
     * @return string
     */
    public function getXPayLanguage()
    {
        return $this->calculateLanguage($this->getXPayLanguages(), Gateway::LANG_ENG);
    }

    /**
     * Get XPay language
     *
     * @return string
     */
    public function getNpgLanguage()
    {
        return strtolower($this->calculateLanguage($this->getNpgLanguages(), Gateway::LANG_ENG));
    }

    /**
     * Calculate language
     *
     * @param array $languages
     * @param string $default
     * @return string
     */
    private function calculateLanguage($languages, $default)
    {
        $association = $this->convertLanguagesArray($languages);

        $locale = $this->_localeResolver->getLocale();

        return array_key_exists($locale, $association) ? $association[$locale] : $default;
    }

    /**
     * Convert languages array
     *
     * @param array $languages
     * @return array
     */
    private function convertLanguagesArray($languages)
    {
        $array = [];

        foreach ($languages as $id => $locales) {
            foreach ($locales as $locale) {
                $array[$locale] = $id;
            }
        }

        return $array;
    }

    /**
     * Get all XPay languages
     *
     * @return array
     */
    private function getXPayLanguages()
    {
        return [
            Gateway::LANG_ENG => [
                'en_AU',
                'en_CA',
                'en_IE',
                'en_NZ',
                'en_GB',
                'en_US',
            ],
            Gateway::LANG_ITA => [
                'it_IT',
                'it_CH',
            ],
            Gateway::LANG_SPA => [
                'es_AR',
                'es_BO',
                'es_CL',
                'es_CO',
                'es_CR',
                'es_MX',
                'es_PA',
                'es_PE',
                'es_ES',
                'es_VE',
            ],
            Gateway::LANG_FRA => [
                'fr_BE',
                'fr_CA',
                'fr_FR',
            ],
            Gateway::LANG_GER => [
                'de_AT',
                'de_DE',
                'de_CH',
            ],
            Gateway::LANG_JPN => [
                'ja_JP',
            ],
            Gateway::LANG_POR => [
                'pt_BR',
                'pt_PT',
            ],
            Gateway::LANG_ARA => [
                'ar_DZ',
                'ar_EG',
                'ar_KW',
                'ar_MA',
                'ar_SA',
            ],
            Gateway::LANG_CHI => [
                'zh_Hans_CN',
                'zh_Hant_HK',
                'zh_Hant_TW',
            ],
            Gateway::LANG_RUS => [
                'ru_RU',
            ],
        ];
    }

    private function getNpgLanguages()
    {
        $languages = $this->getXPayLanguages();

        $languages[Gateway::LANG_GRE] = array(
            'el_GR',
        );

        return $languages;
    }

    /**
     * Get all PagoDIL languages
     *
     * @return array
     */
    private function getPagodilLanguages()
    {
        return [
            Gateway::PAGODIL_LANG_IT => [
                'it_IT',
                'it_CH',
            ],
            Gateway::PAGODIL_LANG_EN => [
                'en_AU',
                'en_CA',
                'en_IE',
                'en_NZ',
                'en_GB',
                'en_US',
            ],
            Gateway::PAGODIL_LANG_ES => [
                'es_AR',
                'es_BO',
                'es_CL',
                'es_CO',
                'es_CR',
                'es_MX',
                'es_PA',
                'es_PE',
                'es_ES',
                'es_VE',
            ],
            Gateway::PAGODIL_LANG_DE => [
                'de_AT',
                'de_DE',
                'de_CH',
            ],
            Gateway::PAGODIL_LANG_FR => [
                'fr_BE',
                'fr_CA',
                'fr_FR',
            ],
        ];
    }

    /**
     * Get order incrementID from transactionID
     *
     * @param string $transactionId
     * @return int|null
     */
    public function getOrderIncrementIdFromTransactionId($transactionId)
    {
        $exploded = explode(self::TRANSACTION_ID_DELIMITER, $transactionId);

        if (is_array($exploded)) {
            return str_replace(self::TRANSACTION_ID_DELIMITER . $exploded[count($exploded) - 1], "", $transactionId);
        } else {
            return null;
        }
    }

    public function truncateBaseGrandTotal($orderOrQuote)
    {
        return $this->truncateNumberWithCurrency($orderOrQuote->getBaseGrandTotal(), $orderOrQuote->getBaseCurrencyCode());
    }

    public function truncateNumberWithCurrency($number, $currencyCode)
    {
        return $this->truncateNumberBase($number, $this->getDecimalsFromCurrencyCode($currencyCode));
    }

    public function getDecimalsFromCurrencyCode($currencyCode)
    {
        $availableCurrencies = array(
            "AED" => 2,
            "AOA" => 2,
            "ARS" => 2,
            "AUD" => 2,
            "AZN" => 2,
            "BGN" => 2,
            "BHD" => 3,
            "BRL" => 2,
            "BYN" => 2,
            "BYR" => 0,
            "CAD" => 2,
            "CHF" => 2,
            "CLP" => 0,
            "CNY" => 2,
            "COP" => 2,
            "CZK" => 2,
            "DKK" => 2,
            "EGP" => 2,
            "EUR" => 2,
            "GBP" => 2,
            "HKD" => 2,
            "HRK" => 2,
            "HUF" => 2,
            "INR" => 2,
            "JOD" => 3,
            "JPY" => 0,
            "KRW" => 0,
            "KWD" => 3,
            "KZT" => 2,
            "LTL" => 2,
            "LVL" => 2,
            "MXN" => 2,
            "MYR" => 2,
            "NGN" => 2,
            "NOK" => 2,
            "PHP" => 2,
            "PLN" => 2,
            "QAR" => 2,
            "RON" => 2,
            "RSD" => 2,
            "RUB" => 2,
            "SAR" => 2,
            "SEK" => 2,
            "SGD" => 2,
            "THB" => 2,
            "TRY" => 2,
            "TWD" => 2,
            "UAH" => 2,
            "USD" => 2,
            "VEF" => 2,
            "VND" => 0,
            "ZAR" => 2,
            "MKD" => 2,
            "BAM" => 2,
            "ISK" => 0,
            "GIP" => 2
        );

        return $availableCurrencies[$currencyCode] ?? 2;
    }

    /**
     * Truncate number
     *
     * @param mixed $number
     * @param mixed $decimals
     * @return mixed
     */
    public function truncateNumberBase($number, $decimals)
    {
        $rounded = round($number, $decimals);

        if (extension_loaded("bcmath") && function_exists("bcmul") && function_exists("bcpow")) {
            $return = bcmul($rounded, bcpow(10, $decimals));
        } else {
            $return = $rounded * pow(10, $decimals);
        }

        return $return;
    }

    /**
     * Check if one product can payed in installments
     *
     * @param int[] $installableCategories
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function isProductInstallable($installableCategories, \Magento\Catalog\Model\Product $product)
    {
        $result = array_intersect($installableCategories, $product->getCategoryIds());

        return is_array($result) && count($result) !== 0;
    }

    /**
     * Check if quote product can payed in installments
     *
     * @param \IPlusService\XPay\Gateway\Config $gatewayConfig
     * @param \Magento\Quote\Model\Quote $quote
     * @return boolean
     */
    public function isQuoteInstallable(\IPlusService\XPay\Gateway\Config $gatewayConfig, \Magento\Quote\Model\Quote $quote)
    {
        return $this->totalNotBigEnough($gatewayConfig, $quote) && $this->totalTooBig($gatewayConfig, $quote) && $this->checkNumberOfProducts($gatewayConfig, $quote) && $this->checkCategories($gatewayConfig, $quote);
    }

    /**
     * Check if total is not big enough
     *
     * @param \IPlusService\XPay\Gateway\Config $gatewayConfig
     * @param \Magento\Quote\Model\Quote $quote
     * @return boolean
     */
    public function totalNotBigEnough(\IPlusService\XPay\Gateway\Config $gatewayConfig, \Magento\Quote\Model\Quote $quote)
    {
        $grandTotal = $this->truncateBaseGrandTotal($quote);

        if ($grandTotal < $gatewayConfig->getPagodilMinAmount($quote->getStoreId())) {
            return false;
        }

        return true;
    }

    /**
     * Check if total is too big
     *
     * @param \IPlusService\XPay\Gateway\Config $gatewayConfig
     * @param \Magento\Quote\Model\Quote $quote
     * @return boolean
     */
    public function totalTooBig(\IPlusService\XPay\Gateway\Config $gatewayConfig, $quote)
    {
        $grandTotal = $this->truncateBaseGrandTotal($quote);

        if ($grandTotal > $gatewayConfig->getPagodilMaxAmount($quote->getStoreId())) {
            return false;
        }

        return true;
    }

    /**
     * Check if number of products in cart is available
     *
     * @param \IPlusService\XPay\Gateway\Config $gatewayConfig
     * @param \Magento\Quote\Model\Quote $quote
     * @return boolean
     */
    public function checkNumberOfProducts(\IPlusService\XPay\Gateway\Config $gatewayConfig, \Magento\Quote\Model\Quote $quote)
    {
        $storeId = $quote->getStoreId();

        if ($gatewayConfig->getMaxNumberOfProducts($storeId)) {
            $allVisibleItems = $quote->getAllVisibleItems();

            // invalid number of products
            if (count($allVisibleItems) > $gatewayConfig->getMaxNumberOfProducts($storeId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if can skip categories control
     *
     * @param \IPlusService\XPay\Gateway\Config $gatewayConfig
     * @return boolean
     */
    public function canSkipCategoriesCheck(\IPlusService\XPay\Gateway\Config $gatewayConfig, $storeId)
    {
        return $gatewayConfig->getCheckModeCategories($storeId) == 'all_categories';
    }

    /**
     * Check categories
     *
     * @param \IPlusService\XPay\Gateway\Config $gatewayConfig
     * @param \Magento\Quote\Model\Quote $quote
     * @return boolean
     */
    public function checkCategories(\IPlusService\XPay\Gateway\Config $gatewayConfig, \Magento\Quote\Model\Quote $quote)
    {
        $storeId = $quote->getStoreId();

        if ($this->canSkipCategoriesCheck($gatewayConfig, $storeId)) {
            return true;
        }

        $installableCategories = $gatewayConfig->getCategories($storeId);

        // invalid installable categories
        if (!is_array($installableCategories) || count($installableCategories) === 0) {
            return false;
        }

        $allItems = $quote->getAllItems();

        foreach ($allItems as $item) {
            // product not installable
            if (!$this->isProductInstallable($installableCategories, $item->getProduct())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get JS layout
     *
     * @param string $scope
     * @param string $template
     * @param \IPlusService\XPay\Gateway\Config $gatewayConfig
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getJsLayout($scope, $template, \IPlusService\XPay\Gateway\Config $gatewayConfig, $storeId, \Magento\Catalog\Model\Product $product)
    {
        $productTypeId = $product->getTypeId();

        if ($productTypeId == "grouped") {
            $lowestPrice = 0;
            $count = 0;

            $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);

            foreach ($usedProds as $child) {
                if ($child->getId() != $product->getId()) {
                    if ($count == 0) {
                        $lowestPrice = $child->getPrice();
                    }

                    if ($child->getPrice() < $lowestPrice) {
                        $lowestPrice = $child->getPrice();
                    }
                }

                $count++;
            }

            $finalPrice = $lowestPrice;
        } elseif ($productTypeId == "bundle") {
            $price = $product->getPriceInfo()->getPrice('final_price');

            $minFinalPrice = $price->getMinimalPrice()->getValue();
            $maxFinalPrice = $price->getMaximalPrice()->getValue();

            $minFinalPriceInCent = $this->truncateNumberBase($minFinalPrice, 2);
            $maxFinalPriceInCent = $this->truncateNumberBase($maxFinalPrice, 2);

            if ($minFinalPriceInCent >= $gatewayConfig->getPagodilMinAmount($storeId) && $minFinalPriceInCent <= $gatewayConfig->getPagodilMaxAmount($storeId)) {
                $finalPrice = $minFinalPrice;
            } elseif ($maxFinalPriceInCent >= $gatewayConfig->getPagodilMinAmount($storeId) && $maxFinalPriceInCent <= $gatewayConfig->getPagodilMaxAmount($storeId)) {
                $finalPrice = $maxFinalPrice;
            } else {
                $finalPrice = 0;
            }
        } else {
            $finalPrice = $product->getPriceModel()->getFinalPrice(1, $product);
        }

        $pagodilEnabled = $gatewayConfig->getEnabledPagodil($storeId);

        $showWidget = $gatewayConfig->getShowWidget($storeId);

        return [
            'components' => [
                $scope => [
                    'component' => 'IPlusService_XPay/js/view/product/product',
                    'displayArea' => 'after_price',
                    'config' => [
                        'template' => $template,
                    ],
                    'options' => [
                        'product_id' => $product->getId(),
                        'product_type_id' => $productTypeId,
                        'product_final_price' => $finalPrice,
                        'product_final_price_in_cent' => $this->truncateNumberBase($finalPrice, 2),
                        'is_widget_visible' => $pagodilEnabled && $showWidget,
                        'is_product_installable' => $this->canSkipCategoriesCheck($gatewayConfig, $storeId) ? true : $this->isProductInstallable($gatewayConfig->getCategories($storeId), $product),
                        'pagodil_min_amount' => $gatewayConfig->getPagodilMinAmount($storeId),
                        'pagodil_max_amount' => $gatewayConfig->getPagodilMaxAmount($storeId),
                        'pagodil_installments_number' => $gatewayConfig->getInstallmentsNumber($storeId),
                        'pagodil_logo_kind' => $gatewayConfig->getLogoKind($storeId),
                        'pagodil_info_link' => $gatewayConfig->getInfoLink($storeId),
                        'pagodil_language' => $this->getPagodilLanguage(),
                    ],
                ],
            ],
        ];
    }

    /**
     * Get ship indicator
     *
     * @param mixed $shippingAddress
     * @param mixed $billingAddress
     * @return string
     */
    public function getShipIndicator($shippingAddress, $billingAddress)
    {
        if ($shippingAddress === null) {
            return "05";
        }

        if ($shippingAddress->getCity() == $billingAddress->getCity() && $this->getISO3Code($shippingAddress->getCountryId()) == $this->getISO3Code($billingAddress->getCountryId()) && implode("\n", $shippingAddress->getStreet()) == implode("\n", $billingAddress->getStreet()) && $shippingAddress->getPostcode() == $billingAddress->getPostcode()) {
            return "01";
        } else {
            return "03";
        }
    }

    /**
     * Get ChAcc age indicator
     *
     * @param mixed $customer
     * @return string
     */
    public function getChAccAgeIndicator($customer)
    {
        $now = new \DateTime();

        $chAccDate = new \DateTime($customer->getCreatedAt());

        $chAccDateAgeDays = (int) ($chAccDate->diff($now)->format('%a'));

        if ($chAccDateAgeDays < 30) {
            $chAccAgeIndicator = "03";
        } elseif ($chAccDateAgeDays < 60) {
            $chAccAgeIndicator = "04";
        } else {
            $chAccAgeIndicator = "05";
        }

        return $chAccAgeIndicator;
    }

    /**
     * Get ChAcc change indicator
     *
     * @param mixed $customer
     * @return string
     */
    public function getChAccChangeIndicator($customer)
    {
        $now = new \DateTime();

        $chAccChangeDate = new \DateTime($customer->getUpdatedAt());

        $chAccChangeDateAgeDays = (int) ($chAccChangeDate->diff($now)->format('%a'));

        if ($chAccChangeDateAgeDays < 30) {
            $chAccChangeIndicator = "02";
        } elseif ($chAccChangeDateAgeDays < 60) {
            $chAccChangeIndicator = "03";
        } else {
            $chAccChangeIndicator = "04";
        }

        return $chAccChangeIndicator;
    }

    public function getLastOperation($orderDetails)
    {
        if (isset($orderDetails['orderStatus'])) {
            $lastOperationType = $orderDetails['orderStatus']['lastOperationType'];
            $lastOperationTime = new \DateTime($orderDetails['orderStatus']['lastOperationTime']);

            if (isset($orderDetails['operations']) && is_array($orderDetails['operations'])) {
                foreach ($orderDetails['operations'] as $operation) {
                    $operationDate = new \DateTime($operation['operationTime']);

                    if ($operation['operationType'] == $lastOperationType && $operationDate->format('Y-m-d H:i:s.v') == $lastOperationTime->format('Y-m-d H:i:s.v')) {
                        return $operation;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get Npg order status
     *
     * @param mixed $orderDetails
     * @return mixed
     */
    public function getNpgOrderStatus($orderDetails)
    {
        $lastOperation = $this->getLastOperation($orderDetails);

        if (isset($lastOperation)) {
            return $lastOperation['operationResult'];
        }

        return null;
    }

    /**
     * Check order state and send email
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $accountingType
     */
    public function checkOrder($order, $accountingType)
    {
        $orderState = $this->getOrderState($order, $accountingType);

        $order->setState($orderState);
		
		if ($orderState == \Magento\Sales\Model\Order::STATE_PROCESSING) {
			$order->setStatus($orderState);
		}

        if (!$order->getEmailSent()) {
            $this->_orderSender->send($order);
        }

        $order->save();
    }

    /**
     * Check invoice and send email
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     */
    public function checkInvoice($payment)
    {
        $invoice = $payment->getCreatedInvoice();

        if ($invoice) {
            $invoice->pay();

            $invoice->setTransactionId($payment->getAdditionalInformation('transactionId'));

            $invoice->save();

            if (!$invoice->getEmailSent()) {
                $this->_invoiceSender->send($invoice);
            }
        }
    }

    /**
     * Get order state
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $accountingType
     * @return string
     */
    private function getOrderState($order, $accountingType)
    {
        return $this->isOrderVirtual($order) && $accountingType == Gateway::ACCOUNTING_TYPE_IMMEDIATE ? Order::STATE_COMPLETE : Order::STATE_PROCESSING;
    }

    /**
     * Get if order is virtual
     *
     * @param \Magento\Sales\Model\Order $order
     * @return boolean
     */
    private function isOrderVirtual($order)
    {
        $isVirtual = true;

        foreach ($order->getAllItems() as $items) {
            if ($items->getProductType() != 'virtual') {
                $isVirtual = false;
                break;
            }
        }

        return $isVirtual;
    }

    /**
     * Get if Npg order is success
     *
     * @param mixed $status
     * @return boolean
     */
    public function isNpgOrderSuccessful($status)
    {
        return in_array(strtoupper($status), [
            "AUTHORIZED",
            "EXECUTED"
        ]);
    }

    /**
     * Get if Npg order is failed
     *
     * @param mixed $status
     * @return boolean
     */
    public function isNpgOrderFailed($status)
    {
        return in_array(strtoupper($status), [
            "DECLINED",
            "FAILED",
            "3DS_FAILED",
            "THREEDS_FAILED",
            "CANCELLED",
            "CANCELED",
        ]);
    }

    /**
     * Get Npg order info for order creation
     * 
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Model\Order|Magento\Quote\Model\Quote $orderOrQuote
     * @param string $transactionId
     * @param \IPlusService\XPay\Gateway\Config $gatewayConfig
     * @return array
     */
    public function getNpgOrderInfo($customerRepository, $orderOrQuote, $transactionId, $gatewayConfig, $billingAddress = null, $payment = null)
    {
        $customer = null;

        if ($orderOrQuote->getCustomerIsGuest() != 1) {
            $customer = $customerRepository->getById($orderOrQuote->getCustomerId());
        }

        $data = array();

        $data['orderId'] = $transactionId;
        $data['amount'] = $this->truncateBaseGrandTotal($orderOrQuote);
        $data['currency'] = $orderOrQuote->getBaseCurrencyCode();

        if ($orderOrQuote->getCustomerId()) {
            $data['customerId'] = $orderOrQuote->getCustomerId();
        } else {
            $data['customerId'] = 0;
        }

        if ($orderOrQuote instanceof \Magento\Sales\Model\Order) {
            $data['description'] = 'Ordine ' . $orderOrQuote->getIncrementId();
        }

        $data['customField'] = "Magento v" . $this->_versionHelper->getMagentoVersionFormatted() . " mod" . $this->_versionHelper->getModuleVersion();

        $customerInfo = $this->getCustomerInfo($orderOrQuote, $customer, $gatewayConfig, $billingAddress);

        if (is_array($customerInfo) && count($customerInfo) > 0) {
            $data['customerInfo'] = $customerInfo;
        }

        $data['transactionSummary'] = array(
            $this->getTransactionSummaryInfo($orderOrQuote),
        );

        $installments = $payment->getAdditionalInformation("installments");

        if ($installments && $installments >= 2) {
            $data['plan'] = array(
//             'totalAmount' => $data['amount'],
//              'contractId' => $transactionId,
//              'status' => 'ACTIVE',
                "planType" => "ACQUIRER_AGREEMENT",
                "installmentQty" => $installments,
            );
        }

        return $data;
    }

    private function getCustomerInfo($orderOrQuote, $customer, $gatewayConfig, $billingAddressData)
    {
        $data = array();

        $billingAddress = $orderOrQuote->getBillingAddress();
        $shippingAddress = $orderOrQuote->getShippingAddress();

        if (isset($billingAddressData) && isset($billingAddressData['firstname']) && isset($billingAddressData['lastname'])) {
            $data['cardHolderName'] = $billingAddressData['firstname'] . " " . $billingAddressData['lastname'];
        } else if ($orderOrQuote->getCustomerFirstname() && $orderOrQuote->getCustomerLastname()) {
            $data['cardHolderName'] = $orderOrQuote->getCustomerFirstname() . " " . $orderOrQuote->getCustomerLastname();
        } else if ($billingAddress && $billingAddress->getFirstname() && $billingAddress->getLastname()) {
            $data['cardHolderName'] = $billingAddress->getFirstname() . " " . $billingAddress->getLastname();
        } else if ($shippingAddress && $shippingAddress->getFirstname() && $shippingAddress->getLastname()) {
            $data['cardHolderName'] = $shippingAddress->getFirstname() . " " . $shippingAddress->getLastname();
        }

        if ($orderOrQuote->getCustomerEmail()) {
            $data['cardHolderEmail'] = $orderOrQuote->getCustomerEmail();
        } else if ($billingAddress && $billingAddress->getEmail()) {
            $data['cardHolderEmail'] = $billingAddress->getEmail();
        } else if ($shippingAddress && $shippingAddress->getEmail()) {
            $data['cardHolderEmail'] = $shippingAddress->getEmail();
        }

        if ($gatewayConfig->getEnable3DSecure20($orderOrQuote->getStoreId())) {
            if ($billingAddress) {
                $billingValues = array(
                    'name' => $billingAddress->getFirstname() . " " . $billingAddress->getLastname(),
                    'street' => implode("\n", $billingAddress->getStreet()),
                    'additionalInfo' => implode("\n", $billingAddress->getStreet()),
                    'city' => $billingAddress->getCity(),
                    'postCode' => $billingAddress->getPostcode(),
                    'province' => $this->_capHelper->getStateCode($billingAddress->getPostcode()),
                    'country' => $this->getISO3Code($billingAddress->getCountryId()),
                );

                foreach ($billingValues as $name => $value) {
                    if ($value !== null && strlen($value) > 0) {
                        $data['billingAddress'][$name] = $value;
                    }
                }
            }

            if ($shippingAddress) {
                $shippingValues = array(
                    'name' => $shippingAddress->getFirstname() . " " . $shippingAddress->getLastname(),
                    'street' => implode("\n", $shippingAddress->getStreet()),
                    'additionalInfo' => implode("\n", $shippingAddress->getStreet()),
                    'city' => $shippingAddress->getCity(),
                    'postCode' => $shippingAddress->getPostcode(),
                    'province' => $this->_capHelper->getStateCode($shippingAddress->getPostcode()),
                    'country' => $this->getISO3Code($shippingAddress->getCountryId()),
                );

                foreach ($shippingValues as $name => $value) {
                    if ($value !== null && strlen($value) > 0) {
                        $data['shippingAddress'][$name] = $value;
                    }
                }

                $phone = $shippingAddress->getTelephone();

                if (isset($phone) && $phone != '' && preg_match('/^(\+)([0-9]{10,15})$/', $phone)) {
                    $data['homePhone'] = $phone;
                }
            }

            if ($customer) {
                $data['cardHolderAcctInfo']['chAccDate'] = substr($customer->getCreatedAt(), 0, 10);
                $data['cardHolderAcctInfo']['chAccAgeIndicator'] = $this->getChAccAgeIndicator($customer);

                $data['cardHolderAcctInfo']['chAccChangeDate'] = substr($customer->getUpdatedAt(), 0, 10);
                $data['cardHolderAcctInfo']['chAccChangeIndicator'] = $this->getChAccChangeIndicator($customer);

                $data['cardHolderAcctInfo']['nbPurchaseAccount'] = $this->getNbOrderCompletedPerCustomer($customer);
            }

            $data['merchantRiskIndicator']['deliveryEmail'] = $orderOrQuote->getCustomerEmail() ?: $billingAddress->getEmail();
            $data['merchantRiskIndicator']['shipIndicator'] = $this->getShipIndicator($shippingAddress, $billingAddress);
        }

        return $data;
    }

    private function getTransactionSummaryInfo($orderOrQuote)
    {
        $data = array();

        $data['language'] = $this->getNpgLanguage();

        $allVisibleItems = $orderOrQuote->getAllVisibleItems();

        $data['summaryList'] = array();

        foreach ($allVisibleItems as $item) {
            if (!$item->getData('has_children')) {
                $product = $item->getProduct();

                $data['summaryList'][] = array(
                    'label' => substr($product->getName(), 0, 50),
                    'value' => (string) $item->getQtyOrdered(),
                );
            }
        }

        return $data;
    }

    /**
     * 
     * @param \Magento\Sales\Model\Order $order
     * @param string $installationUniqueId
     * @return string
     */
    public function createCustomerToken(\Magento\Sales\Model\Order $order, $installationUniqueId)
    {
        $hash = hash('sha256', $order->getCustomerId() . "@" . $order->getCustomerEmail() . "@" . $installationUniqueId);

        return substr(base_convert($hash, 16, 36), 0, 30);
    }

}
