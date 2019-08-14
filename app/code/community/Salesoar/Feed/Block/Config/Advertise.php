<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 11/01/16
 * Time: 12.32
 */

class Salesoar_Feed_Block_Config_Advertise
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract

{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '
                 <span class="comment"  style="color:red;">If you want to use this option you must choose a specific store of yours. (Top left)</span>';

        return $html;
    }
}
