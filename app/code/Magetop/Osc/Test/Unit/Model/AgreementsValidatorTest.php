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

namespace Magetop\Osc\Test\Unit\Model;

use Magetop\Osc\Helper\Data as OscHelper;
use Magetop\Osc\Model\AgreementsValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AgreementsValidatorTest
 * @package Magetop\Osc\Test\Unit\Model
 */
class AgreementsValidatorTest extends TestCase
{
    /**
     * @var OscHelper|MockObject
     */
    private $oscHelperMock;

    /**
     * @var AgreementsValidator
     */
    private $model;

    protected function setUp()
    {
        $this->oscHelperMock = $this->getMockBuilder(OscHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AgreementsValidator($this->oscHelperMock);
    }

    public function testIsValid()
    {
        $this->oscHelperMock->expects($this->once())->method('isEnabledTOC')->willReturn(true);

        $this->assertTrue($this->model->isValid());
    }
}
