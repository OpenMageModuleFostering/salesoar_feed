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
 * Auth session model
 *
 * @category   Salesoar
 * @package    Salesoar_Feed
 * @author     Salesoar Team <support@salesoar.com>
 */
class Salesoar_Feed_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $this->init('Salesoar_Feed');
    }

    public function isAdminLoggedIn()
    {
        return $this->getAdmin() && $this->getAdmin()->getId();
    }

    public function isCustomerLoggedIn()
    {
        return $this->getCustomer() && $this->getCustomer()->getId();
    }
}
