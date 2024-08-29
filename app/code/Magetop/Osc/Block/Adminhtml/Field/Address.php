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

namespace Magetop\Osc\Block\Adminhtml\Field;

/**
 * Class Address
 * @package Magetop\Osc\Block\Adminhtml\Field
 */
class Address extends AbstractField
{
    const BLOCK_ID = 'mposc-address-information';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        /** Prepare collection */
        [$this->sortedFields, $this->availableFields] = $this->helper->getSortedField(false);
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string) __('Address Information');
    }
}
