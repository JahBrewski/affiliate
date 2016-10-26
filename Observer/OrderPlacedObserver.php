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

    //$item_names = array();
    //$item_urls = array();
    $items_array = array();

    if(isset($_COOKIE[$url_cookie_name])) {
        //$this->_logger->addDebug('URL landing cookie set! Cookie is:');
        $url = $_COOKIE[$url_cookie_name];
        //$this->_logger->addDebug($url);
    }


    // TODO: Now that we are able to grab the URL associated with a product, we
    // need to build an items array to pass to the Pref-It API. The items array
    // should include:
    // Item name : string
    // Item SKU : string
    // Item amount : number
    // Is_converted_purchase : boolean
    
    $this->_logger->addDebug('########## BEFORE ITEM LOOP ##########');
    foreach($items as $item) {
      $new_item = array();
      //$this->_logger->addDebug('########## INSIDE ITEM LOOP ##########');
      $new_item['name'] = $item->getName();
      $new_item['sku'] = $item->getSku();
      $new_item['price'] = $item->getPrice();
      $new_item['is_converted_purchase'] = false;

      //$item_names[] = $item->getName();

      //$this->_logger->addDebug('########## ITEM NAME ##########');
      //$this->_logger->addDebug($item->getName());
      $product = $item->getProduct();
      //$this->_logger->addDebug('########## ITEM PRODUCT ##########');
      //$this->_logger->addDebug($item->getProduct());
      if ($product) {
        //$this->_logger->addDebug('########## INSIDE PRODUCT IF STATEMENT ##########');
        //$item_urls[] = $product->getProductUrl();
        //
        $product_url = $product->getProductUrl();
        $new_item['url'] = $product_url;

        if ($url && ($url == $product_url)) {
          $new_item['is_converted_purchase'] = true;
        }
        //$this->_logger->addDebug('########## PRODUCT URL ##########');
        //$this->_logger->addDebug($product->getProductUrl());
      }
      $this->_logger->addDebug('########## NEW ITEM ##########');
      $this->_logger->addDebug(implode(",", $new_item));
      $items_array[] = $new_item;
    }
      

    //$items_string = implode(",", $item_names);
    //$items_url_string = implode(",", $item_urls);

    //$this->_logger->addDebug('########## ITEM NAMES ##########');
    //$this->_logger->addDebug($items_string);

    //$this->_logger->addDebug('########## ITEM URLS ##########');
    //$this->_logger->addDebug($items_url_string);

    //$this->_logger->addDebug('########## ORDER PLACED BRO ##########');


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

      //$url = 'https://api.domain.com/v1/orders/order_placed';
      $url = 'https://8b8e9762.ngrok.io/v1/orders/order_placed';

      // TODO : Pass merchant ID so that API can associate order with a merchant
      $data = array( 'order' => array(
      'affiliate_id' => $affiliate_id,
      'user_id'  => $customer_id,
      'order_amount' => $order_amount,
      'merchant_order_id' => $order_id,
      'items' => $items_array)
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
}


