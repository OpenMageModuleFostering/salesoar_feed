<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 11/01/16
 * Time: 12.38
 */

class Salesoar_Feed_Block_Config_Adminhtml_Form_Field_GoogleCategory
    extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        $options1 = Mage::getSingleton('Salesoar_Feed/system_config_backend_selectGoogleCategory')
            ->toOptionArray();
        foreach ($options1 as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}