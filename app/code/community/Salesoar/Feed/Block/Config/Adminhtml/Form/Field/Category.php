<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 11/01/16
 * Time: 14.32
*/

class Salesoar_Feed_Block_Config_Adminhtml_Form_Field_Category
    extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        $options = Mage::getSingleton('Salesoar_Feed/system_config_backend_selectCategory')
            ->toOptionArray();
        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}