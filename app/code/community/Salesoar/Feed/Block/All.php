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
 * Review form block
 *
 * @category   Salesoar
 * @package    Salesoar_Feed
 * @author     Salesoar Team <support@salesoar.com>
 */

class Salesoar_Feed_Block_All extends Salesoar_Feed_Block_Abstract
{
    protected function _toHtml()
    {
        $resultArr = array();
        $feedUrl = Mage::getUrl('salesoar/feed/xml')."store/%s/salesoar.xml";
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            $resultArr[$store->getName()] = sprintf($feedUrl, $store->getId());
        }
        return json_encode($resultArr, JSON_UNESCAPED_SLASHES);
    }
}
