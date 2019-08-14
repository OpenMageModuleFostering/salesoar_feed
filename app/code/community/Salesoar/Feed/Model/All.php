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

class Salesoar_Feed_Model_All
{
    public function createAllXml() {
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            $xml = Mage::getModel('Salesoar_Feed/Xml');
            $xml->createXml($store->getId());
        }
    }
}
