<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 08/01/16
 * Time: 12.11
 */


class Salesoar_Feed_Model_Observer
{

    public function __construct()
    {
    }

    public function handle_adminSystemConfigChangedSection()
    {
	    $prefix = Mage::getConfig()->getTablePrefix();
        $googleSalesoar = $_POST['googleSalesoar'];
        $sql = '';
        foreach($googleSalesoar as $idCat => $string ){
            if($string == '') {
                $idGoogle = 0;
                $nameGoogle = 'not set';
            }
            else{
                $idGoogle = (int)substr($string,0,strpos($string, '£$%&'));
                $nameGoogle = (string)trim(substr($string, strpos($string, '£$%&')+5));
            }
            $sql .= 'INSERT INTO `'.$prefix.'salesoar_feed` (`id_category`, `google_id`, `google_name`) VALUES ('.$idCat.', '. $idGoogle.', \''.$nameGoogle.'\' )
                      ON DUPLICATE KEY UPDATE  `google_id` = '.$idGoogle.', `google_name` = \''.$nameGoogle.'\' ; ';
        }
        if ($sql != '') {
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $writeConnection->query($sql);
        }
    }
}

