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
 * @package     Magetop_GeoIP
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\GeoIP\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magetop\GeoIP\Helper\Data as HelperData;

/**
 * Class Geoip
 * @package Magetop\GeoIP\Block\Adminhtml\System\Config
 */
class Geoip extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Magetop_GeoIP::system/config/geoip.phtml';

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Geoip constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('mpgeoip/system_config/geoip');
    }

    /**
     * Generate collect button html
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(Button::class)
            ->setData([
                'id'    => 'geoip_button',
                'label' => __('Download Library'),
            ]);

        return $button->toHtml();
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    public function isDisplayIcon()
    {
        return $this->_helperData->getAddressHelper()->checkHasLibrary() ? '' : 'hidden="hidden';
    }
}
