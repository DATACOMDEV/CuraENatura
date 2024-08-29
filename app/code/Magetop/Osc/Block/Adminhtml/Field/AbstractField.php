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

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Model\Attribute;
use Magetop\Osc\Helper\Address;

/**
 * Class AbstractField
 * @package Magetop\Osc\Block\Adminhtml\Field
 */
abstract class AbstractField extends Template
{
    const BLOCK_ID = '';

    /**
     * @var string
     */
    protected $_template = 'Magetop_Osc::field/position.phtml';

    /**
     * @var Address
     */
    protected $helper;

    /**
     * @var Attribute[]
     */
    protected $sortedFields = [];

    /**
     * @var Attribute[]
     */
    protected $availableFields = [];

    /**
     * AbstractField constructor.
     *
     * @param Context $context
     * @param Address $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Address $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve the header text
     *
     * @return string
     */
    abstract public function getBlockTitle();

    /**
     * @return string
     */
    public function getBlockId()
    {
        return static::BLOCK_ID;
    }

    /**
     * @return Attribute[]
     */
    public function getSortedFields()
    {
        return $this->sortedFields;
    }

    /**
     * @return Attribute[]
     */
    public function getAvailableFields()
    {
        return $this->availableFields;
    }

    /**
     * @return Address
     */
    public function getHelperData()
    {
        return $this->helper;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function hasFields()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getNoticeMessage()
    {
        return '';
    }
}
