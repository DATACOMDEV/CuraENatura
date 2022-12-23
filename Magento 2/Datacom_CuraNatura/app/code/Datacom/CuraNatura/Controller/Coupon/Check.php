<?php

namespace Datacom\CuraNatura\Controller\Coupon;

class Check extends \Magento\Framework\App\Action\Action
{

    protected $_resourceConnection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection
	)
	{
        parent::__construct($context);

        $this->_resourceConnection = $resourceConnection;
	}

	public function execute()
	{
        $email = $this->getRequest()->getParam('email', -1);
        $customerId = $this->getRequest()->getParam('customer_id', -1);
        $coupon = $this->getRequest()->getParam('coupon', -1);
        
        if ($coupon == -1) {
            $this->sendResponse(0);
            return;
        }

        if ($email == -1 && $customerId == -1) {
            $this->sendResponse(0);
            return;
        }
        
        $conn = $this->_resourceConnection->getConnection();

        $query = 'SELECT customer_email FROM mg_sales_order WHERE status<>\'canceled\' AND coupon_code=\''.$coupon.'\' AND ';
        if ($email == -1) {
            $query .= 'customer_id='.$customerId;
        } else {
            $query .= 'customer_email=\''.$email.'\'';
        }

        $rows = $conn->fetchAll($query);
        foreach ($rows as $r) {
            $this->sendResponse(1);
            return;
        }

        $this->sendResponse(0);
		return;
    }
    
    private function sendResponse($data) {
        $this->getResponse()->setNoCacheHeaders();
		//$this->getResponse()->setHeader('Content-type', 'application/json');
        //$this->getResponse()->setHttpResponseCode(201);
		$this->getResponse()->setBody($data);
    }
}
