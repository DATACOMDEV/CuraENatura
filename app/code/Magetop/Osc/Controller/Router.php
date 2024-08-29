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

namespace Magetop\Osc\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;
use Magetop\Osc\Helper\Data;

/**
 * Class Router
 * @package Magetop\Osc\Controller
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param Data $helperData
     */
    public function __construct(
        ActionFactory $actionFactory,
        Data $helperData
    ) {
        $this->actionFactory = $actionFactory;
        $this->helper        = $helperData;
    }

    /**
     * @param RequestInterface|Http $request
     *
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');

        if (!$this->helper->isEnabled() || $identifier !== $this->helper->getOscRoute()) {
            return null;
        }

        $request->setModuleName('onestepcheckout')
            ->setControllerName('index')
            ->setActionName('index')
            ->setPathInfo('/onestepcheckout/index/index')
            ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

        return $this->actionFactory->create(Forward::class);
    }
}
