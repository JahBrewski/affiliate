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

    $affiliate_id = $customer_id = $url = null;
    $affiliate_cookie_name = "brewerdigital_affiliate_affiliate_id";
    $customer_cookie_name  = "brewerdigital_affiliate_customer_id";
    $url_cookie_name       = "brewerdigital_affiliate_customer_url_landing";

    // TODO: This needs to be set in an admin page somewhere
    $merchant_uuid = 0001;


    $order = $observer->getEvent()->getOrder();
    $items = $order->getAllItems();

    $order_amount = $order->getGrandTotal();
    $order_id = $order->getRealOrderId();

    $item_names = array();
    $item_urls = array();

    foreach($items as $item) {
      $item_names[] = $item->getName();
      $product = $item->getProduct();
      $item_urls[] = $product->getProductUrl();
    }

    $items_string = implode(",", $item_names);
    $items_url_string = implode(",", $item_urls);

    $this->_logger->addDebug('########## ITEM NAMES ##########');
    $this->_logger->addDebug($items_string);

    $this->_logger->addDebug('########## ITEM URLS ##########');
    $this->_logger->addDebug($items_url_string);

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

    if(isset($_COOKIE[$url_cookie_name])) {
        $this->_logger->addDebug('URL landing cookie set! Cookie is:');
        $url = $_COOKIE[$url_cookie_name];
        $this->_logger->addDebug($url);
    }

    if (!empty($affiliate_id) && !empty($customer_id)) {
      $this->_logger->addDebug('Affiliate ID and Customer ID are not empty');

      //$url = 'https://api.domain.com/v1/orders/order_placed';
      $url = 'https://8b8e9762.ngrok.io/v1/orders/order_placed';

      // TODO: Loop through items and check if any items match the item
      // associated with the URL. If so, mark the item as a 'converted purchase'

      // TODO : Pass merchant ID so that API can associate order with a merchant
      $data = array( 'order' => array(
      'affiliate_id' => $affiliate_id,
      'user_id'  => $customer_id,
      'order_amount' => $order_amount,
      'merchant_order_id' => $order_id,
      'items' => $items_string)
    );  
      
      $options = array(
          'http' => array(
            'timeout' => 2,
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
            )
          );
      
      $context  = stream_context_create($options);
      $result = @file_get_contents($url, false, $context);
      $this->_logger->addDebug($result);
    }
  }

  private function getSKUFromURL(url) {


  }
}


