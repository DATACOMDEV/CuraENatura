<?php
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

namespace Magetop\Osc\Observer;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 * Class ReindexCustomer
 * @package Magetop\Osc\Observer
 */
class ReindexCustomer implements ObserverInterface
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ResourceCustomer
     */
    protected $resourceCustomer;

    /**
     * ReindexCustomer constructor.
     *
     * @param CustomerFactory $customerFactory
     * @param ResourceCustomer $resourceCustomer
     */
    public function __construct(CustomerFactory $customerFactory, ResourceCustomer $resourceCustomer)
    {
        $this->customerFactory  = $customerFactory;
        $this->resourceCustomer = $resourceCustomer;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof Order) {
            $customerId = $order->getCustomerId();
            if ($customerId && !$this->isExistCustomerGrid($customerId)) {
                $customer = $this->customerFactory->create();
                $customer->load($customerId)->reindex();
            }
        }
    }

    /**
     * @param $customerId
     *
     * @return int
     */
    public function isExistCustomerGrid($customerId)
    {
        $table      = $this->resourceCustomer->getTable('customer_grid_flat');
        $connection = $this->resourceCustomer->getConnection();
        $select     = $connection->select();
        $select->from($table, 'COUNT(*)')->where('entity_id = ?', $customerId);

        return (int) $connection->fetchOne($select);
    }
}
