<?php

namespace Elgentos\ServerSideAnalytics\Observer;

use Elgentos\ServerSideAnalytics\Model\GAClient;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class SendPurchaseEventAfter implements ObserverInterface
{
    const PAYMENT_METHODS_NOT_AUTOMATIC = [
        'msp_cashondelivery',
        'phoenix_cashondelivery',
        'banktransfer'
    ];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Elgentos\ServerSideAnalytics\Model\GAClient
     */
    private $gaclient;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $event;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Elgentos\ServerSideAnalytics\Model\GAClient $gaclient,
        \Magento\Framework\Event\ManagerInterface $event
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->gaclient = $gaclient;
        $this->event = $event;
    }

    /**
     * @param $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->scopeConfig->getValue(GAClient::GOOGLE_ANALYTICS_SERVERSIDE_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return;
        }
        $ua = $this->scopeConfig->getValue(GAClient::GOOGLE_ANALYTICS_SERVERSIDE_UA, ScopeInterface::SCOPE_STORE);
        if (!$ua) {
            $this->logger->info('No Google Analytics account number has been found in the ServerSideAnalytics configuration.');
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();

        if (!$order->getData('ga_user_id')) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();

        if (!in_array($payment->getMethod(), self::PAYMENT_METHODS_NOT_AUTOMATIC)) return;

        $uas = explode(',', $ua);
        $uas = array_filter($uas);
        $uas = array_map('trim', $uas);

        $products = [];
        /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
        $curPositionIndex = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            if (!$item->isDeleted() && !$item->getParentItemId()) {
                $product = new \Magento\Framework\DataObject([
                    'sku' => $item->getSku(),
                    'name' => $item->getName(),
                    'price' => $this->getPaidProductPrice($item),
                    'quantity' => $item->getQtyOrdered(),
                    //'position' => $item->getId()
                    'position' => $curPositionIndex
                ]);
                $this->event->dispatch('elgentos_serversideanalytics_product_item_transport_object',
                    ['product' => $product, 'item' => $item]);
                $products[] = $product;

                $curPositionIndex++;
            }
        }

        $trackingDataObject = new \Magento\Framework\DataObject([
            'client_id' => $order->getData('ga_user_id'),
            'ip_override' => $order->getRemoteIp(),
            'document_path' => '/checkout/onepage/success/'
        ]);

        try {
            /** @var \Elgentos\ServerSideAnalytics\Model\GAClient $client */
            $client = $this->gaclient;
            
            $client->setTransactionData(
                new \Magento\Framework\DataObject(
                    [
                        'transaction_id' => $order->getIncrementId(),
                        'affiliation' => $order->getStoreName(),
                        'revenue' => $order->getBaseGrandTotal(),
                        'tax' => $order->getTaxAmount(),
                        'shipping' => ($this->getPaidShippingCosts($order) ?? 0),
                        'coupon_code' => $order->getCouponCode()
                    ]
                )
            );

            $client->addProducts($products);
        } catch (\Exception $e) {
            $this->logger->info($e);
            return;
        }

        foreach ($uas as $ua) {
            try {
                $trackingDataObject->setData('tracking_id', $ua);
                $client->setTrackingData($trackingDataObject);
                $this->event->dispatch('elgentos_serversideanalytics_tracking_data_transport_object',
                    ['tracking_data_object' => $trackingDataObject]);
                $client->firePurchaseEvent();
            } catch (\Exception $e) {
                $this->logger->info($e);
            }
        }
    }

    /**
     * Get the actual price the customer also saw in it's cart.
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     *
     * @return float
     */
    private function getPaidProductPrice(\Magento\Sales\Model\Order\Item $orderItem)
    {
        return $this->scopeConfig->getValue('tax/display/type') == \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX
            ? $orderItem->getPrice()
            : $orderItem->getPriceInclTax();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return float
     */
    private function getPaidShippingCosts(\Magento\Sales\Model\Order $order)
    {
        return $this->scopeConfig->getValue('tax/display/type') == \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX
            ? $order->getShippingAmount()
            : $order->getShippingInclTax();
    }

}
