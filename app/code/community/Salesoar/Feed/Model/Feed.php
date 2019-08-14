<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 08/01/16
 * Time: 15.40
 */

class Salesoar_Feed_Model_Feed extends  Mage_Core_Model_Abstract
{
    public function __construct()
    {
        parent::_construct();
        $this->_init('Salesoar_Feed/Salesoar_Feed');
    }


}
