<?php
namespace BrewerDigital\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;

// TODO: Remove
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);

class OrderPlacedObserver implements ObserverInterface {
  protected $_logger;

  public function __construct(\Psr\Log\LoggerInterface $logger) {
    $this->_logger = $logger;
  }
  
  public function execute(\Magento\Framework\Event\Observer $observer) {
    // Order defined here: 
    // https://github.com/magento/magento2/blob/6ea7d2d85cded3fa0fbcf4e7aa0dcd4edbf568a6/app/code/Magento/Sales/Model/Order.php

    $affiliate_id = $customer_id = null;
    $affiliate_cookie_name = "brewerdigital_affiliate_affiliate_id";
    $customer_cookie_name  = "brewerdigital_affiliate_customer_id";

    $order = $observer->getEvent()->getOrder();
    $items = $order->getAllItems();

    $order_amount = $order->getGrandTotal();
    $order_id = $order->getRealOrderId();

    $item_names = array();

    foreach($items as $item) {
      $item_names[] = $item->getName();
    }

    $items_string = implode(",", $item_names);

    $this->_logger->addDebug('########## ORDER PLACED BRO ##########');


    if(isset($_COOKIE[$affiliate_cookie_name])) {
        $this->_logger->addDebug('Affiliate cookie set! Cookie is:');
        $affiliate_id = $_COOKIE[$affiliate_cookie_name];
        $this->_logger->addDebug($affiliate_id);
    }

    if(isset($_COOKIE[$customer_cookie_name])) {
        $this->_logger->addDebug('Customer cookie set! Cookie is:');
        $customer_id = $_COOKIE[$customer_cookie_name];
        $this->_logger->addDebug($customer_id);
    }

    if (!empty($affiliate_id) && !empty($customer_id)) {
      $this->_logger->addDebug('Affiliate ID and Customer ID are not empty');

      //$url = 'https://my.ngrok.io/order_placed';
      $url = 'https://api.domain.com/v1/orders/order_placed';

      $data = array( 'order' => array(
      'affiliate_id' => $affiliate_id,
      'user_id'  => $customer_id,
      'order_amount' => $order_amount,
      'merchant_order_id' => $order_id,
      'items' => $items_string)
    );  
      
      $options = array(
          'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
            )
          );
      
      $context  = stream_context_create($options);
      $result = file_get_contents($url, false, $context);
      $this->_logger->addDebug($result);
    }
  }
}


