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
    $affiliate_id = $customer_id = null;
    $affiliate_cookie_name = "brewerdigital_affiliate_affiliate_id";
    $customer_cookie_name  = "brewerdigital_affiliate_customer_id";
    $seconds_in_day = 86400;
    $cookie_length_in_days = 30;
    $cookie_length_in_seconds = $seconds_in_day * $cookie_length_in_days;

    $params = $observer->getEvent()->getRequest()->getParams();
    
    // WIP: Grab page URL and store in cookie
    $currentUrl = Mage::helper('core/url')->getCurrentUrl();
    $url = Mage::getSingleton('core/url')->parseUrl($currentUrl);
    $path = $url->getPath();

    $this->_logger->addDebug('########## currentURL ##########');
    $this->_logger->addDebug($currentUrl);

    $this->_logger->addDebug('########## URL ##########');
    $this->_logger->addDebug($url);

    $this->_logger->addDebug('########## Path ##########');
    $this->_logger->addDebug($path);

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
