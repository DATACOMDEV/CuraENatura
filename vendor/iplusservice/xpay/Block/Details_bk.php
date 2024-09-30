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

namespace IPlusService\XPay\Block;

class Details extends \Magento\Sales\Block\Order\Info
{

    /**
     *
     * @var \IPlusService\XPay\Model\Api
     */
    private $_api;

    /**
     *
     * @var \IPlusService\XPay\Model\NpgApi
     */
    private $_npgApi;

    /**
     *
     * @var \IPlusService\XPay\Helper\Api
     */
    private $_apiHelper;

    /**
     *
     * @var \IPlusService\XPay\Helper\Helper
     */
    private $_helper;

    /**
     *
     * @var \IPlusService\XPay\Gateway\Config
     */
    private $_gatewayConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \IPlusService\XPay\Model\Api $api
     * @param \IPlusService\XPay\Model\NpgApi $npgApi
     * @param \IPlusService\XPay\Helper\Api $apiHelper
     * @param \IPlusService\XPay\Helper\Helper $helper
     * @param \IPlusService\XPay\Gateway\Config $gatewayConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \IPlusService\XPay\Model\Api $api,
        \IPlusService\XPay\Model\NpgApi $npgApi,
        \IPlusService\XPay\Helper\Api $apiHelper,
        \IPlusService\XPay\Helper\Helper $helper,
        \IPlusService\XPay\Gateway\Config $gatewayConfig,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $paymentHelper, $addressRenderer, $data);

        $this->_api = $api;
        $this->_npgApi = $npgApi;
        $this->_apiHelper = $apiHelper;
        $this->_helper = $helper;
        $this->_gatewayConfig = $gatewayConfig;
    }

    /**
     *  To html
     *
     * @return mixed
     */
    public function toHtml()
    {
        $methodCode = $this->getOrder()->getPayment()->getMethod();

        if ((stripos($methodCode, 'xpay') !== false)) {
            return parent::toHtml();
        } else {
            return '';
        }
    }

    /**
     *  Return transaction info
     *
     * @return array
     */
    public function getTransactionInfo()
    {
        $payment = $this->getOrder()->getPayment();

        $methodCode = $payment->getMethod();

        $xpayMethods = array(
            'xpay',
            'xpaybuild_oneclick',
        );

        $npgMethods = array(
            'xpay_npg',
            'xpay_npg_oneclick',
        );

        $apms = array(
            'paymail',
            'alipay',
            'amazonpay',
            'applepay',
            'googlepay',
            'klarna',
            'paypal',
            'wechatpay',
            'giropay',
            'ideal',
            'bancontact',
            'eps',
            'przelewy24',
            'mybank',
            'sct',
            'bancomatpay',
            'skrill',
            'skrill1tap',
            'pagoinconto',
            'satispay',
            'multibanco',
            'payu',
            'blik',
            'oney',
            'pagodil',
            'poli',
        );

        foreach ($apms as $apm) {
            $xpayMethods[] = 'xpay_' . $apm;
            $npgMethods[] = 'xpay_npg_' . $apm;
        }

        $storeId = $this->getOrder()->getStoreId();

        if (in_array($methodCode, $xpayMethods)) {
            $transactionAlias = $payment->getAdditionalInformation('alias');
            $transactionId = $payment->getAdditionalInformation('transactionId');

            if ($transactionAlias && $transactionId) {
                try {
                    list($alias, $mac) = $this->_apiHelper->getCredentials($transactionAlias, $storeId);

                    $this->_api->setCredentials($alias, $mac);

                    $this->_api->setTestMode($this->_gatewayConfig->getTestMode($storeId));

                    $response = $this->_api->getTransactionInfo($transactionId);

                    $info = [
                        'card_holder' => [
                            'Name' => $response['dettaglio'][0]['nome'] . ' ' . $response['dettaglio'][0]['cognome'],
                            'Email' => $response['dettaglio'][0]['mail']
                        ],
                        'payment_method' => [
                            'Method' => $response['brand'],
                            'Nationality' => $response['nazione']
                        ],
                        'transaction_details' => [
                            'Transaction date' => $this->formatNexiDate($response['dataTransazione']),
                            'Transaction status' => $response['stato'],
                            'Amount' => $this->formatNexiAmount($response['importo'], $response['divisa']),
                            'Transaction ID' => $response['codiceTransazione']
                        ],
                        'operations' => array(),
                    ];

                    foreach ($response['dettaglio'][0]['operazioni'] as $xpayOperation) {
                        $xpayOperation['dataOperazione'] = $this->formatNexiDate($xpayOperation['dataOperazione'], 'd/m/Y H:i');
                        $xpayOperation['importo'] = $this->formatNexiAmount($xpayOperation['importo'], $xpayOperation['divisa']);

                        $info['operations'][] = $xpayOperation;
                    }

                    if ($response['pan']) {
                        $info['payment_method']['Pan'] = $response['pan'];
                    }

                    if ($response['scadenza']) {
                        $info['payment_method']['Expiration date'] = \DateTime::createFromFormat('Ym', $response['scadenza'])->format("m/Y");
                    }

                    $this->clearArray($info);
                } catch (\Exception $ex) {
                    $info = [];
                }
            } else {
                $info = [];
            }
        } else if (in_array($methodCode, $npgMethods)) {
            $orderId = $payment->getAdditionalInformation('transactionId');

            $this->_npgApi->setApiKey($this->_gatewayConfig->getNpgApiKey($storeId));

            $this->_npgApi->setTestMode($this->_gatewayConfig->getTestMode($storeId));

            $orderDetails = $this->_npgApi->getNpgOrderDetails($orderId);

            $transaction = $this->getNpgTransaction($orderDetails);

            $order = $orderDetails['orderStatus']['order'];

            $info = [
                'card_holder' => [
                    'Name' => $order['customerInfo']['cardHolderName'],
                    'Email' => $order['customerInfo']['cardHolderEmail'],
                ],
                'transaction_details' => [
                    'Transaction date' => isset($transaction['operationTime']) ? $this->formatNexiDate($transaction['operationTime']) : null,
                    'Transaction status' => $this->_helper->getNpgOrderStatus($orderDetails),
                    'Amount' => $this->formatNexiAmount($order['amount'], $order['currency']),
                    'Transaction ID' => $order['orderId'],
                ],
                'payment_method' => [
                    'Method' => isset($transaction['paymentMethod']) ? $transaction['paymentMethod'] : null,
                    'Circuit' => isset($transaction['paymentCircuit']) ? $transaction['paymentCircuit'] : null,
                ],
            ];

            $installments = $payment->getAdditionalInformation('installments');

            if (isset($installments) && $installments >= 2) {
                $info['transaction_details']['Installments'] = $installments;
            }

            foreach ($orderDetails['operations'] as $npgOperation) {
                $operation = array(
                    'tipoOperazione' => $npgOperation['operationType'],
                    'dataOperazione' => $this->formatNexiDate($npgOperation['operationTime'], 'd/m/Y H:i'),
                    'importo' => $this->formatNexiAmount($npgOperation['operationAmount'], $npgOperation['operationCurrency']),
                    'divisa' => $npgOperation['operationCurrency'],
                    'stato' => $npgOperation['operationResult'],
                );

                $info['operations'][] = $operation;
            }
        } else {
            $info = [];
        }

        return $info;
    }

    /**
     * Retun amount formatted
     *
     * @param float $amount
     * @param string $currencyCode
     * @return mixed
     */
    private function formatNexiAmount($amount, $currencyCode)
    {
        $amountToUse = $amount ?: 0;

        $decimals = $this->_helper->getDecimalsFromCurrencyCode($currencyCode);

        if (extension_loaded("bcmath") && function_exists("bcdiv") && function_exists("bcpow")) {
            $amountWithDecimals = bcdiv($amountToUse, bcpow(10, $decimals));
        } else {
            $amountWithDecimals = $amountToUse / pow(10, $decimals);
        }

        return number_format($amountWithDecimals, $decimals, ",", " ") . ' ' . $currencyCode;
    }

    /**
     * Retun date formatted
     *
     * @param string $date
     * @param string $format
     * @return mixed
     */
    private function formatNexiDate($date, $format = 'd/m/Y H:i:s')
    {
        try {
            $oDate = new \DateTime($date);

            return $oDate->format($format);
        } catch (\Exception $ex) {
            return '';
        }

        return '';
    }

    /**
     * Get Npg transaction
     *
     * @param mixed $orderDetails
     * @return mixed
     */
    private function getNpgTransaction($orderDetails)
    {
        foreach ($orderDetails['operations'] as $operation) {
            if ($operation['operationType'] == "AUTHORIZATION" || $operation['operationType'] == "CAPTURE") {
                return $operation;
            }
        }
    }

    /**
     * Clear array
     *
     * @param mixed $info
     */
    private function clearArray(&$info)
    {
        foreach ($info as $block => $values) {
            if ($block != 'operations') {
                $blockInvalid = true;

                foreach ($values as $name => $value) {
                    if (!trim($value ?: "")) {
                        unset($info[$block][$name]);
                    } else {
                        $blockInvalid = false;
                    }
                }

                if ($blockInvalid) {
                    unset($info[$block]);
                }
            }
        }
    }

}
