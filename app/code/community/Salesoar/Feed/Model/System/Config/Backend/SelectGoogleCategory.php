<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 05/01/16
 * Time: 17.32
 */

class Salesoar_Feed_Model_System_Config_Backend_selectGoogleCategory extends Mage_Core_Model_Config_Data
{
    public function toOptionArray($isoLocalLang)
    {
        //$file= dirname(__FILE__).'/Taxonomy/taxonomy_'.$isoLocalLang.'.txt';
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
            $array[]=array('value' => (int)$row[0], 'label' => $row[1]);
        };

        $array [0] = array('value' => 0, 'label' => '');
        return $array;
    }
}
