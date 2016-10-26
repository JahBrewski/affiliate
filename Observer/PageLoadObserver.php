<?php
namespace BrewerDigital\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;

class PageLoadObserver implements ObserverInterface {
  protected $_logger;

  public function __construct(\Psr\Log\LoggerInterface $logger) {
    $this->_logger = $logger;
  }

  public function execute(\Magento\Framework\Event\Observer $observer) {
    $affiliate_id = $customer_id = $url = null;
    $affiliate_cookie_name      = "brewerdigital_affiliate_affiliate_id";
    $customer_cookie_name       = "brewerdigital_affiliate_customer_id";
    $url_cookie_name            = "brewerdigital_affiliate_customer_url_landing";
    $content_post_cookie_name   = "brewerdigital_affiliate_content_post_id";
    $seconds_in_day = 86400;
    $cookie_length_in_days = 30;
    $cookie_length_in_seconds = $seconds_in_day * $cookie_length_in_days;

    $params = $observer->getEvent()->getRequest()->getParams();

    if (isset($params["cpid"])) {
      $content_post_id = urldecode($params["cpid"]);
      setcookie($content_post_cookie_name, $content_post_id, time() + $cookie_length_in_seconds, "/");
    }

    if (isset($params["url"])) {
      $url = urldecode($params["url"]);
      setcookie($url_cookie_name, $url, time() + $cookie_length_in_seconds, "/");
    }

    if (isset($params["aid"])) {
      $affiliate_id = $params["aid"];
      setcookie($affiliate_cookie_name, $affiliate_id, time() + $cookie_length_in_seconds, "/");
    }

    if (isset($params["cid"])) {
      $customer_id = $params["cid"];
      setcookie($customer_cookie_name, $customer_id, time() + $cookie_length_in_seconds, "/");
    }
  }
}
