<?php

namespace Datacom\CuraNatura\Controller\User;

class Info extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper;
    protected $_session;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\Session $session
	)
	{
        parent::__construct($context);

        $this->_jsonHelper = $jsonHelper;
        $this->_session = $session;

        //$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	}

	public function execute()
	{
        $response = [
            'errors' => [],
            'content' => [
                'logged' => 0
            ]
        ];

        if (!$this->_session->isLoggedIn()) {
            $this->sendResponse($response);
            return;
        }

        $response['content']['logged'] = 1;
        
        $loggedInCustomer = $this->_session->getCustomer();
        
        $response['content']['email'] = $loggedInCustomer->getEmail();


        $this->sendResponse($response);
		return;
    }
    
    private function sendResponse($data) {
        $this->getResponse()->setNoCacheHeaders();
		$this->getResponse()->setHeader('Content-type', 'application/json');
        //$this->getResponse()->setHttpResponseCode(201);
		$this->getResponse()->setBody($this->_jsonHelper->jsonEncode($data));
    }
}
