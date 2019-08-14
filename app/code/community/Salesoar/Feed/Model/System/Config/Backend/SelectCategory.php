<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 05/01/16
 * Time: 17.32
 */
class Salesoar_Feed_Model_System_Config_Backend_SelectCategory extends Mage_Core_Model_Config_Data
{
    public function toOptionArray()
    {
        $array = array();

        $category = Mage::getModel('catalog/category');

        $tree = $category->getTreeModel();
        $tree->load();
        $ids = $tree->getCollection()->getAllIds();

        if ($ids)
        {
            foreach ($ids as $id)
            {
                $separator = '';
                $cat = Mage::getModel('catalog/category')->load($id);
                $catnames = '';
                foreach ($cat->getParentCategories() as $parent) {
                        $catnames .= $parent->getName().' > ';
                }

                $catnames = substr($catnames,0,-2);

                if( $cat->getIsActive()==1)
                {
                    $array[] =  ['value' => $cat->getId(), 'label'=> $catnames ];
                }
            }
        }

        return $array;
    }


}