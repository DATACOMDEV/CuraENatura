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

namespace Magetop\Osc\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\Api\MetadataObjectInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CustomAttributeList
 * @package Magetop\Osc\Model
 */
class CustomAttributeList
{
    /**
     * @var AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var MetadataObjectInterface[]
     */
    protected $attributes;

    /**
     * @param AddressMetadataInterface $addressMetadata
     */
    public function __construct(AddressMetadataInterface $addressMetadata)
    {
        $this->addressMetadata = $addressMetadata;
    }

    /**
     * Retrieve list of quote address custom fields
     *
     * @return array
     * @throws LocalizedException
     */
    public function getAttributes()
    {
        if ($this->attributes === null) {
            $this->attributes = [];

            for ($i = 1; $i <= 3; $i++) {
                $attribute = $this->addressMetadata->getAttributeMetadata('mposc_field_' . $i);

                $this->attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        return $this->attributes;
    }
}
