<?php
/**
 * Salesoar
 *
 * @category    Salesoar
 * @package     Salesoar_Feed
 * @copyright   Copyright (c) 2012 Salesoar S.r.l. (https://salesoar.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Poll index controller
 *
 * @file        IndexController.php
 * @author      Salesoar Team <support@salesoar.com>
 */

class Salesoar_Feed_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
        if (Mage::getStoreConfig('salesoar_feed/config/active')) {
            $this->_redirect("salesoar_feed/feed");
        } else {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
        }
    }
}