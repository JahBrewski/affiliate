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

		$order = $observer->getEvent()->getOrder();
		$items = $order->getAllItems();

		$item_names = array();

		foreach($items as $item) {
			$item_names[] = $item->getName();
		}

		$items_string = implode(",", $item_names);

		$this->_logger->addDebug('########## ORDER PLACED BRO ##########');
		$this->_logger->addDebug($items_string);
		$this->_logger->addDebug($order->getGrandTotal());
		$this->_logger->addDebug($order->getRealOrderId());
		$this->_logger->addDebug('########## ORDER PLACED END ##########');
	}
}


