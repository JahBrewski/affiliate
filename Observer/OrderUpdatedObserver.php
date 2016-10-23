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
    $this->_logger->addDebug('########## ORDER UPDATED ##########');

    $order = $observer->getEvent()->getOrder();
    $order_id = $order->getRealOrderId();
    $this->_logger->addDebug('########## BEGIN ORDER ##########');
    $this->_logger->addDebug($order_id);
    $this->_logger->addDebug('########## END ORDER ##########');
  }
}
