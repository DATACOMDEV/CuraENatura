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

namespace Magetop\Osc\Model\Plugin\Api;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magetop\Osc\Helper\Data;

/**
 * Class OrderComment
 * @package Magetop\Osc\Model\Plugin\Api
 */
class OrderComment
{
    /**
     * @var OrderExtensionFactory
     */
    protected $orderExtensionFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * OrderComment constructor.
     *
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param Data $helper
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        Data $helper
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->helper                = $helper;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultOrder
     *
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $resultOrder
    ) {
        if (!$this->helper->isEnabled($resultOrder->getStoreId())) {
            return $resultOrder;
        }

        /** @var OrderExtensionInterface $extensionAttributes */
        $extensionAttributes = $resultOrder->getExtensionAttributes() ?: $this->orderExtensionFactory->create();
        if ($extensionAttributes && $extensionAttributes->getOscOrderComment()) {
            return $resultOrder;
        }

        /** get osc comment from order */
        $comment = $resultOrder->getOscOrderComment();

        $extensionAttributes->setOscOrderComment($comment);
        $resultOrder->setExtensionAttributes($extensionAttributes);

        return $resultOrder;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param Collection $resultOrder
     *
     * @return Collection
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        Collection $resultOrder
    ) {
        /** @var OrderInterface $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }

        return $resultOrder;
    }
}
