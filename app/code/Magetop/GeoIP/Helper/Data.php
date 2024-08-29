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

namespace Magetop\GeoIP\Helper;

use Magetop\GeoIP\Helper\AbstractData;

/**
 * Class Data
 * @package Magetop\GeoIP\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'geoip';

    /**
     * @var Address
     */
    protected $_addressHelper;

    /**
     * @return Address
     */
    public function getAddressHelper()
    {
        if (!$this->_addressHelper) {
            $this->_addressHelper = $this->objectManager->get(Address::class);
        }

        return $this->_addressHelper;
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function getDownloadPath($store = null)
    {
        $token = $this->getConfigGeneral('token', $store);

        return 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=' . $token . '&suffix=tar.gz';
    }
}
