<?php
namespace BrewerDigital\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;

// TODO: Remove
ini_set('display_errors', 'On');
error_reporting(E_ALL);

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

    // TODO: Grab content post id and store in a cookie so we can track
    // converted purchases and associate them with content posts
    if (isset($params["cpid"])) {
      $content_post_id = urldecode($params["cpid"]);
      $this->_logger->addDebug('########## Content Post ID ##########');
      $this->_logger->addDebug($content_post_id);
      setcookie($content_post_cookie_name, $content_post_id, time() + $cookie_length_in_seconds, "/");
    }

    // WIP: Grab page URL and store in cookie
    if (isset($params["url"])) {
      $url = urldecode($params["url"]);
      $this->_logger->addDebug('########## URL ##########');
      $this->_logger->addDebug($url);
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
    // TODO: Remove
    //echo '<pre>';
    //var_dump($affiliate_id);
    //var_dump($customer_id);
    //echo '</pre>';
  }
}
