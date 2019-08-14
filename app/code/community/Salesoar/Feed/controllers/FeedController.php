<?php
/**
 * Salesoar
 *
 * @category    Salesoar
 * @package     Salesoar_Feed
 * @copyright   Copyright (c) 2015 Salesoar S.r.l. (http://salesoar.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Poll feed controller
 *
 * @file        FeedController.php
 * @author      Salesoar Team <support@salesoar.com>
 */

class Salesoar_Feed_FeedController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action--- 	action : Click to open Feeds
     */
    public function indexAction()
    {
        if (Mage::getStoreConfig('Salesoar_Feed/config/Salesoar_Feed_create_enable')) {
            $this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8', true);
            $this->loadLayout(false);
            $this->renderLayout();
        } else {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
        }
    }

    /**
     * Xml action
     */
    public function xmlAction()
    {
        if (Mage::getStoreConfig('Salesoar_Feed/config/Salesoar_Feed_create_enable')) {
            $jsonResp = true;
            if ($this->getRequest()->getParam('store')) {
                if (is_numeric($this->getRequest()->getParam('store'))) {
                    $store = Mage::getModel('core/store')->load($this->getRequest()->getParam('store'));
                    if (!is_null($store) && $store->getIsActive()) {
                        $jsonResp = false;
                    }
                }
            }
            if ($jsonResp) {
                $this->_redirect("Salesoar_Feed/feed");
            }
            else {
                header('Content-Type: text/xml; charset=UTF-8');
                $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8', true);
                $this->loadLayout(false);
                $this->renderLayout();
            }
        } else {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
        }
    }

    /**
     * Create action
     */
    public function createAction()
    {
        if (Mage::getStoreConfig('Salesoar_Feed/config/Salesoar_Feed_create_enable')) {
            $this->loadLayout(false);
            $this->renderLayout();
        } else {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
        }
    }

}
