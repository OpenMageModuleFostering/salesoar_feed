<?php
/**
 * Salesoar
 *
 * @category    Salesoar
 * @package     Salesoar_Feed
 * @copyright   Copyright (c) 2012 Salesoar S.r.l. (http://salesoar.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Salesoar_Feed_Block_Adminhtml_Frontend_Links extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return '<a href="'.Mage::app()->getStore(1)->getUrl('salesoar/feed').'">Click to open Feeds</a>';
    }
}