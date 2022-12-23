<?php

namespace Datacom\CuraNatura\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_request;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_request = $request;
        parent::__construct($context);
    }

    public function getRequestParam($name) {
        return $this->_request->getParam($name);
    }
}