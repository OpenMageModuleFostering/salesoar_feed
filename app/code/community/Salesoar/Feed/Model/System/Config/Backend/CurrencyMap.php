<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 11/01/16
 * Time: 11.43
 */

class Salesoar_Feed_Model_System_Config_Backend_CurrencyMap extends Mage_Core_Model_Config_Data
{

    public function toOptionArray()
    {
        $file= dirname(__FILE__).'/Taxonomy/taxonomy_en_US.txt';
        $fopen = fopen($file, "r");
        $fread = fread($fopen,filesize("$file"));
        fclose($fopen);
        $remove = "\n";
        $split = explode($remove, $fread);
        $array[] = null;
        $tab = "-";
        foreach ($split as $string)
        {
            $row = explode($tab, $string);
            $array[]=$row ;
        }

        foreach ( $array as $k => $v )
        {
            $array[$k] ['value'] = $array[$k] [0];
            $array[$k] ['label'] = $array[$k] [1];
            unset($array[$k]['fee_id']);
        }

        $array [0] = array('value' => 0, 'label' => 'Unset value');
        return $array;
        /*
        $array = array();
        $array2 = array('value' => 'provaaaaa', 'label' => 'Unset value');
        $array[0] = $array2;
        echo '<pre>';
        print_r($array);
        echo '</pre>';

        return $array;
        */
    }


}