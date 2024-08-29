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

namespace Magetop\Osc\Model\Plugin\Cart;

use Magento\Quote\Api\Data\TotalsItemExtensionInterfaceFactory;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magetop\Osc\Helper\Item;

/**
 * Class ItemConverterPlugin
 *
 * @package Magetop\Osc\Model\Plugin\Cart
 */
class ItemConverterPlugin
{
    /**
     * @var Item
     */
    private $helper;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var TotalsItemExtensionInterfaceFactory
     */
    private $totalsItemExtension;

    /**
     * ItemConverterPlugin constructor.
     *
     * @param Item                                $helper
     * @param Quote                               $quote
     * @param TotalsItemExtensionInterfaceFactory $totalsItemExtension
     */
    public function __construct(
        Item $helper,
        Quote $quote,
        TotalsItemExtensionInterfaceFactory $totalsItemExtension
    ) {
        $this->helper              = $helper;
        $this->quote               = $quote;
        $this->totalsItemExtension = $totalsItemExtension;
    }

    /**
     * @param ItemConverter       $subject
     * @param TotalsItemInterface $itemsData
     * @param QuoteItem           $item
     * @return TotalsItemInterface
     */
    public function afterModelToDataObject(
        ItemConverter $subject,
        TotalsItemInterface $itemsData,
        QuoteItem $item
    ) {
        if (!$this->helper->isEnabled($item->getStoreId())) {
            return $itemsData;
        }
        $totalsItem = $this->totalsItemExtension->create();
        $data       = ['product_url' => $item->getProduct()->getProductUrl()];

        $totalsItem->setMposc($this->helper->jsonEncodeData($data));
        $itemsData->setExtensionAttributes($totalsItem);

        return $itemsData;
    }
}
