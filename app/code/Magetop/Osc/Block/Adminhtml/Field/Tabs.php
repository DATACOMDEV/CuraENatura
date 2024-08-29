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

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magetop\Osc\Helper\Address;
use Magetop\Osc\Helper\Data;

/**
 * Class Tabs
 * @package Magetop\Osc\Block\Adminhtml\Field
 */
class Tabs extends Container
{
    /**
     * @var Address
     */
    protected $helper;

    /**
     * Position constructor.
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
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addButton('save', [
            'label' => __('Save Position'),
            'class' => 'save primary mposc-save-position',
        ]);
    }

    /**
     * Retrieve the header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return (string)__('Manage Fields');
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('*/*/save');
    }
}
