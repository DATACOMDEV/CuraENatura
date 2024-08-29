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

namespace Magetop\Osc\Model\Plugin\Catalog\Product\View\Options;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractOptions
 * @package Magetop\Osc\Model\Plugin\Catalog\Product\View\Options
 */
class AbstractOptions
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AbstractOptions constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject
     */
    public function beforeGetOption(\Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject)
    {
        try {
            $layout = $subject->getLayout()->getUpdate();
            if (empty($layout->getHandles())) {
                $layout->addHandle('default');
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }
    }
}
