<?php

namespace Datacom\CuraNatura\Helper;

class Order extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_orderInterface;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface
    ) {
        $this->_orderInterface = $orderInterface;
        parent::__construct($context);
    }

    public function getOrderByIncrementId($orderIncrementId) {
        return $this->_orderInterface->loadByIncrementId($orderIncrementId);
    }
}