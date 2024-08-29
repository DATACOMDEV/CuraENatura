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

namespace Magetop\Osc\Test\Unit\Model\System\Config\Backend;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magetop\Osc\Model\System\Config\Backend\SealBlockImage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class SealBlockImageTest
 * @package Magetop\Osc\Test\Unit\Model\System\Config\Backend
 */
class SealBlockImageTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $mediaDirectory;

    /**
     * @var SealBlockImage
     */
    private $sealBlockImage;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->mediaDirectory = $this->getMockForAbstractClass(WriteInterface::class);
        $this->sealBlockImage = $objectManagerHelper->getObject(
            SealBlockImage::class,
            [
                '_mediaDirectory' => $this->mediaDirectory
            ]
        );
    }

    public function testGetUploadDir()
    {
        $this->mediaDirectory->expects($this->once())->method('getAbsolutePath')->with('magetop/osc/seal///');
        $helperDataObject = new ReflectionClass(SealBlockImage::class);
        $method           = $helperDataObject->getMethod('_getUploadDir');
        $method->setAccessible(true);

        $method->invokeArgs($this->sealBlockImage, []);
    }
}
