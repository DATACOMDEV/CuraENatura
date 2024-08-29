<?php

namespace Datacom\CuraNatura\Plugin\Block\Adminhtml\Order;

class View {
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        $message ='Are you sure you want to do this?';
        $url = $view->getUrl('route_id/path').$view->getOrderId();


        $view->addButton(
            'order_myaction',
            [
                'label' => __('My Action'),
                'class' => 'myclass',
                'onclick' => "confirmSetLocation('{$message}', '{$url}')"
            ]
        );


    }
}