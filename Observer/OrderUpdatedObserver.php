<?php
namespace BrewerDigital\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;

// TODO: Remove
ini_set('display_errors', 'On');
error_reporting(E_ALL);

class OrderUpdatedObserver implements ObserverInterface {
  protected $_logger;

  public function __construct(\Psr\Log\LoggerInterface $logger) {
    $this->_logger = $logger;
  }

  public function execute(\Magento\Framework\Event\Observer $observer) {
    // NOTE: This function will be called every time an order on the merchant
    // site is updated (whether or not the order is an affiliate order) -- We
    // will likely want to update this code to only run if the order is an
    // affiliate order.
    //
    // We are currently sending the order ID and the order status back to the
    // Pref-It API. The API will compare the order ID to its database to see if
    // the order is an affiliate order. If it is, it will update the Pref-It
    // database with the new order status.
    $this->_logger->addDebug('########## ORDER UPDATED ##########');

    $order = $observer->getEvent()->getOrder();
    $order_id = $order->getRealOrderId();
    $order_status = $order->getStatus();

    $this->_logger->addDebug('########## BEGIN ORDER ##########');
    $this->_logger->addDebug($order_id);
    $this->_logger->addDebug($order_status);
    $this->_logger->addDebug('########## END ORDER ##########');

    //$url = 'https://my.ngrok.io/order_placed';
    $url = 'https://8b8e9762.ngrok.io/orders/order_updated';
    //$url = 'https://api.domain.com/v1/orders/order_placed';

    $data = array( 'order' => array(
      'order_id' => $order_id,
      'order_status' => $order_status
      )
    );

    $options = array(
      'http' => array(
        'timeout'=>2,
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
