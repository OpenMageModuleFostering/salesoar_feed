<?php
/**
 * Salesoar
 *
 * @category    Salesoar
 * @package     Salesoar_Feed
 * @copyright   Copyright (c) 2012 Salesoar S.r.l. (http://salesoar.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Salesoar_Feed_Model_System_Config_Backend_Attributes extends Mage_Core_Model_Config_Data
{
    /**
     * Invalidate cache type, when value was changed
     *
     */
    protected function _afterSave()
    {
        if ($this->isValueChanged()) {
            Mage::app()->getCacheInstance()->invalidateType(Mage_Core_Block_Abstract::CACHE_GROUP);
        }
    }

}
