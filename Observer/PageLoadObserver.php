<?php
namespace BrewerDigital\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;

// TODO: Remove
ini_set('display_errors', 'On');
error_reporting(E_ALL);

class PageLoadObserver implements ObserverInterface {
	public function execute(\Magento\Framework\Event\Observer $observer) {
		$affiliate_id = $customer_id = null;
		$affiliate_cookie_name = "brewerdigital_affiliate_affiliate_id";
		$customer_cookie_name  = "brewerdigital_affiliate_customer_id";
		$seconds_in_day = 86400;
		$cookie_length_in_days = 30;
		$cookie_length_in_seconds = $seconds_in_day * $cookie_length_in_days;

		$params = $observer->getEvent()->getRequest()->getParams();

		if (isset($params["a"])) {
			$affiliate_id = $params["a"];
			setcookie($affiliate_cookie_name, $affiliate_id, time() + $cookie_length_in_seconds, "/");
		}

		if (isset($params["cid"])) {
			$customer_id = $params["cid"];
			setcookie($customer_cookie_name, $customer_id, time() + $cookie_length_in_seconds, "/");
		}
		// TODO: Remove
		//echo '<pre>';
		//var_dump($affiliate_id);
		//var_dump($customer_id);
		//echo '</pre>';
	}
}
