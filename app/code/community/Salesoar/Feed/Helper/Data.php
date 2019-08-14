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
 * Rss data helper
 *
 * @category   Salesoar
 * @package    Salesoar_Feed
 * @author     Salesoar Team <support@salesoar.com>
 */
class Salesoar_Feed_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Authenticate customer on frontend
     *
     */
    public function authFrontend()
    {
        $session = Mage::getSingleton('salesoar_feed/session');
        if ($session->isCustomerLoggedIn()) {
            return;
        }
        list($username, $password) = $this->authValidate();
        $customer = Mage::getModel('customer/customer')->authenticate($username, $password);
        if ($customer && $customer->getId()) {
            Mage::getSingleton('salesoar_feed/session')->settCustomer($customer);
        } else {
            $this->authFailed();
        }
    }

    /**
     * Authenticate admin and check ACL
     *
     * @param string $path
     */
    public function authAdmin($path)
    {
        $session = Mage::getSingleton('salesoar_feed/session');
        if ($session->isAdminLoggedIn()) {
            return;
        }
        list($username, $password) = $this->authValidate();
        Mage::getSingleton('adminhtml/url')->setNoSecret(true);
        $adminSession = Mage::getSingleton('admin/session');
        $user = $adminSession->login($username, $password);
        //$user = Mage::getModel('admin/user')->login($username, $password);
        if($user && $user->getId() && $user->getIsActive() == '1' && $adminSession->isAllowed($path)){
            $session->setAdmin($user);
        } else {
            $this->authFailed();
        }
    }

    /**
     * Validate Authenticate
     *
     * @param array $headers
     * @return array
     */
    public function authValidate($headers=null)
    {
        $userPass = Mage::helper('core/http')->authValidate($headers);
        return $userPass;
    }

    /**
     * Send authenticate failed headers
     *
     */
    public function authFailed()
    {
        Mage::helper('core/http')->authFailed();
    }

    /**
     * Disable using of flat catalog and/or product model to prevent limiting results to single store. Probably won't
     * work inside a controller.
     *
     * @return null
     */
    public function disableFlat()
    {
        /* @var $flatHelper Mage_Catalog_Helper_Product_Flat */
        $flatHelper = Mage::helper('catalog/product_flat');
        if ($flatHelper->isEnabled()) {
            /* @var $emulationModel Mage_Core_Model_App_Emulation */
            $emulationModel = Mage::getModel('core/app_emulation');
            // Emulate admin environment to disable using flat model - otherwise we won't get global stats
            // for all stores
            $emulationModel->startEnvironmentEmulation(0, Mage_Core_Model_App_Area::AREA_ADMINHTML);
        }
    }
}
