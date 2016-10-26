<?php
namespace BrewerDigital\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderPlacedObserver implements ObserverInterface {
  protected $_logger;

  public function __construct(\Psr\Log\LoggerInterface $logger) {
    $this->_logger = $logger;
  }
  
  public function execute(\Magento\Framework\Event\Observer $observer) {
    // Order defined here: 
    // https://github.com/magento/magento2/blob/6ea7d2d85cded3fa0fbcf4e7aa0dcd4edbf568a6/app/code/Magento/Sales/Model/Order.php

    $affiliate_id = $customer_id = $url = $content_post_id = null;
    $affiliate_cookie_name = "brewerdigital_affiliate_affiliate_id";
    $customer_cookie_name  = "brewerdigital_affiliate_customer_id";
    $url_cookie_name       = "brewerdigital_affiliate_customer_url_landing";
    $content_post_cookie_name   = "brewerdigital_affiliate_content_post_id";

    // TODO: This needs to be set in an admin page somewhere
    $merchant_uuid = "0001";

    $order = $observer->getEvent()->getOrder();
    $items = $order->getAllItems();

    $order_amount = $order->getGrandTotal();
    $order_id = $order->getRealOrderId();

    $items_array = array();

    if(isset($_COOKIE[$url_cookie_name])) {
        $this->_logger->addDebug('URL landing cookie set! Cookie is:');
        $url = $_COOKIE[$url_cookie_name];
        $this->_logger->addDebug($url);
    }

    if(isset($_COOKIE[$content_post_cookie_name])) {
        $this->_logger->addDebug('Content Post ID cookie set! Cookie is:');
        $content_post_id = $_COOKIE[$content_post_cookie_name];
        $this->_logger->addDebug($content_post_id);
    }

    foreach($items as $item) {
      $new_item = array();
      $new_item['name'] = $item->getName();
      $new_item['sku'] = $item->getSku();
      $new_item['price'] = $item->getPrice();
      $new_item['is_converted_purchase'] = false;

      $product = $item->getProduct();

      if ($product) {
        $product_url = $product->getProductUrl();
        $new_item['url'] = $product_url;

        if ($url && ($url == $product_url)) {
          $new_item['is_converted_purchase'] = true;
        }
      }

      // Don't add items with a price of zero This is used to filter out 
      // additional zero-priced items Magento adds to the order. From our
      // understanding, for products that have multiple colors/sizes/etc.
      // Magento adds two items -- one is the zero-priced child item (containing
      // size/color metadata), and one is the fully-priced parent item (does not
      // contain size/color metadata). This code might need to be revisited if
      // we find that items that should be sent to our server are not being
      // sent, or if converted purchases are not being correctly tracked.
      if ($new_item['price'] != "0") {
        $items_array[] = $new_item;
      }
    }

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


    if (!empty($affiliate_id) && !empty($customer_id) && !empty($content_post_id)) {
      $this->_logger->addDebug('Affiliate ID and Customer ID and content post ID are not empty');

      //$url = 'https://api.domain.com/v1/orders/order_placed';
      $url = 'https://8b8e9762.ngrok.io/v1/orders/order_placed';

      $data = array(
        'order' => array(
          'affiliate_id' => $affiliate_id,
          'user_id'  => $customer_id,
          'order_amount' => $order_amount,
          'merchant_order_id' => $order_id,
          'merchant_uuid' => $merchant_uuid,
          'content_post_id' => $content_post_id,
          'order_items_attributes' => $items_array
        )
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


