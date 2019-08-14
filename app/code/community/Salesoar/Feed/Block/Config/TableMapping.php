<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 11/01/16
 * Time: 12.32
 */

class Salesoar_Feed_Block_Config_TableMapping
    extends Mage_Adminhtml_Block_System_Config_Form_Field

{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html ='';
        $this->setElement($element);
        $storeStructure = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true); $i=0;

        ////////// Select box  Shop ////////////
        $html .= '<tr>
                  <td class="label"><label><b>Choose Shop:</b></label></td>
                  <td><select id="storeSelect"><option selected>Choose a shop</option>';

        foreach($storeStructure as $item => $value){
            if($i!=0 && $i!= 1) {
                $html .= '<optgroup label="' . $value['label'] . '" style="padding-left:32px;">';
                foreach ($value['value'] as $item2 => $fieldShop) {
                    $html .= '<option value="' . $fieldShop['value'] . '">' . $fieldShop['label'] . '</option>';
                }
                $html .= '</optgroup>';
            }
            $i++;
        }
        $html .= '</select></td></tr>';
        ////////////////////////////////////////////

        $array = array();
        $allStores = Mage::app()->getStores();

        foreach ($allStores as $_eachStoreId => $val)  //Get only active Categories for each shop
        {
            $storeID = Mage::app()->getStore($_eachStoreId)->getId();
            $categories = Mage::getModel('catalog/category')  //Shop $storeID
            ->setStoreId($storeID)
                ->getCollection()
                ->addAttributeToFilter('is_active', 1)
                ->setPageSize(false);

            foreach ($categories as $_c) {
                $CAT= Mage::getModel('catalog/category')->load($_c->getId());
                $catnames = '';
                foreach ($CAT->getParentCategories() as $parent) {
                    $catnames .= $parent->getName().' > ';
                }
                $catnames = substr($catnames,0,-2);
                if( $CAT->getIsActive()==1)
                {
                    $array[$storeID][] =  ['value' => $CAT->getId(), 'label'=> $catnames ];
                }
            }
        }

        //$isoLocalLang = Mage::app()->getLocale()->getLocaleCode(); //Get iso language of current admin interface ex (de_DE)
        $googleValue = Mage::getSingleton('Salesoar_Feed/system_config_backend_selectGoogleCategory')
            ->toOptionArray();

        ////////// Table Mapping ////////////////
        $html .='
            <tr class="hidden" style="display:none">
                  <td class="label"><label id="title_column_shop"><b>Shop Category:</b></label></td>
                  <td><label id="title_column_google"><b>Google Category:</b></label></td>
            </tr>
            <tr class="hidden" style="display:none">
                  <td><label><br></label></td>
                  <td><label><br></label></td>
            </tr>';
        ////////////////////////////////////////

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $prefix = Mage::getConfig()->getTablePrefix();
        $query = 'SELECT * FROM  `'.$prefix.'Salesoar_Feed` ';
        $results = $readConnection->fetchAll($query);
        foreach($results as $item => $value){                           //Format array [id_category] => (0 => [google_name], [1] => [google_id])
            $arraySalesoar[$value['id_category']][0] = $value['google_name'];
            $arraySalesoar[$value['id_category']][1] = $value['google_id'].' £$%& '.$value['google_name'];
        }
        $googleValue = json_encode($googleValue, JSON_HEX_APOS);        //Taxonomy
        $array = json_encode($array, JSON_HEX_APOS);                    //Categories Shop [id_shop] => ([0]=> ([value]=>id_category, [label]=>categoryName), [1]=>.... )
        $arraySalesoar = json_encode($arraySalesoar, JSON_HEX_APOS);

       ?>
            <style>
                #addElement {
                    border-collapse: collapse;
                    width: 100%;
                }
                #labelTable {
                    padding: 8px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                #trTable:hover{
                    background-color:#99cce5;
                    }
                .ui-autocomplete {width:100px;  height: 400px; overflow-y: scroll; overflow-x: hidden;}

            </style>
            <action method="addJs"><link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"></action>
            <action method="addJs"><script src="http://code.jquery.com/jquery-1.10.2.js"></script></action>
            <action method="addJs"><script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script></action>
            <script>
                $j = jQuery.noConflict();
                $j( document ).ready(function() {
                    document.getElementById("title_column_shop").style.color = "red";
                    document.getElementById("title_column_google").style.color = "red";
                    var availableTags = <?php echo  $googleValue ?>;
                    var arraySalesoar =   <?php echo  $arraySalesoar ?>;
                    $j("#storeSelect").on("change", function() {
                        var $array = <?php echo  $array ?>;
                        $j("#salesoar_feed_map_google_categories").append('<table id="addElement" style="width:100%"></table>');
                        $j("#addElement").html("");
                        for (i = 0; i < $array[$j(this).val()].length; i++) {
                            var id = $array[$j(this).val()][i]["value"];
                            $j("#addElement").append('<tr id="trTable"><td id="labelTable"><label>'+$array[$j(this).val()][i]["label"]+'</label>'+
                                                         '<td id="labelTable"><input id="googleCategory_'+id+'" class="autocomplete" type="text" style="width:100%"></td>'+
                                                         '<input id="hidden_googleCategory_'+id+'" name="googleSalesoar['+id+']" value="" type="hidden"></tr>');
                            if(arraySalesoar != null){
                                if(arraySalesoar[id]){
                                    if(arraySalesoar[id][0] != "not set" && arraySalesoar[id][0] != "" ){
                                        $j("#googleCategory_"+id+"").val(arraySalesoar[id][0]);
                                        $j("#hidden_googleCategory_"+id+"").val(arraySalesoar[id][1]);
                                    }
                                }
                            }
                        }

                        $j( ".autocomplete" ).autocomplete({
                            autofocus : true,
                            source: function (request, response){
                                var results = $j.ui.autocomplete.filter(availableTags, request.term);
                                response(results.slice(0, 1000));
                            },
                             select: function (event, ui) {
                             var itemValue = ui.item.value;
                                 $j("#"+this.id+"").val(ui.item.label); // display the selected text
                                 $j("#hidden_"+this.id+"").val(ui.item.value +" £$%& "+ ui.item.label); // save selected id to hidden input
                                 if (ui.item.value == ""){
                                    $j("#hidden_"+this.id+"").val("");
                                    }
                                 return false;
                             },
                             change: function (event, ui) {
                                 /* mustmatch: */
                                 if (!ui.item) {
                                     this.value = "";
                                     $j("#hidden_"+this.id+"").val("");
                                 }
                             }
                        });
                    });
                });
            </script>
        <?php
        return $html;
    }
}
