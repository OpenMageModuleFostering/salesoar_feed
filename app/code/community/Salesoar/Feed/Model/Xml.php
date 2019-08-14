<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 1200);

/**
 * Salesoar
 *
 * @category    Salesoar
 * @package     Salesoar_Feed
 * @copyright   Copyright (c) 2015 Salesoar S.r.l. (http://salesoar.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Auth session model
 *
 * @category   Salesoar
 * @package    Salesoar_Feed
 * @author     Salesoar Team <support@salesoar.com>
 */

class Salesoar_Feed_Model_Xml
{
    protected $all_categories = array();
    protected $all_attributes = array();
    protected $all_attribute_codes = array("description", "manufacturer");


    const COMBINATIONS_MAX_LENGTH = 1;
    const ADD_ATTRIBUTE_LANDING = true;

    public function getThumbnailUrl($product)
    {
        return Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail());
        //->resize(75, 75);
    }

    protected function _createLanding($name, $concepts, $url) {
        $concepts_tag = array();
        foreach ($concepts as $concept) {
            array_push($concepts_tag,
                array(
                    Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED.':concept' => $concept
                )
            );
        }
        $landing = array(
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED.':name' => $name,
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED.':concepts' => $concepts_tag,
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED.':url' => $url
        );
        return array(
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED.':landing' => $landing
        );
    }

    protected function combination_attributes($array) {
        $results = array(array());

        foreach ($array as $element) {
            foreach ($results as $combination) {
                $comb = array_merge(array($element), $combination);
                if (count($comb) <= Salesoar_Feed_Model_Xml::COMBINATIONS_MAX_LENGTH) {
                    array_push($results, $comb);
                }
            }
        }
        array_shift($results);
        return $results;
    }

    protected function getCategoryLandings($store, $domain) {
        $landings = array();
        $categories = Mage::getModel('catalog/category')
            ->setStoreId($store)
            ->getCollection()
            ->addAttributeToFilter('is_active', 1)
            ->setPageSize(false);

        $category_max_depth = 1;
        foreach ($categories as $category) {
            $path = explode('/', $category->getPath());
            if (count($path) > $category_max_depth) {
                $category_max_depth = count($path);
            }
        }

        for ($i = 0; $i < $category_max_depth - 2; $i++) {
            $categories = array();
            for ($j = 0; $j <= $i; $j++) {
                array_push($categories, "category_L" . $j);
            }
            array_push($landings, $this->_createLanding(
                "Category Level " . (count($categories) - 1) . " page",
                $categories,
                $domain . "{" . end($categories) . "}" . "?___store=" . $store));
        }

        return array($landings);
    }

    protected function getAttributeLandings($store, $domain) {
        $landings = array();
        $attribute_objs = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->addIsFilterableFilter()
            ->setPageSize(false)
            ->getItems();

        foreach ($attribute_objs as $attr_obj) {
            array_push($this->all_attributes, array($attr_obj->getAttributecode(), $attr_obj->getFrontendLabel()));
            array_push($this->all_attribute_codes, $attr_obj->getAttributecode());
        }

        $combinations = $this->combination_attributes($this->all_attributes);

        foreach ($combinations as $value) {
            $name = array("Category");
            $concepts = array("category");
            $url_params = array();
            foreach ($value as $v) {
                array_push($name, $v[1]);
                array_push($concepts, $v[0]);
                array_push($url_params, $v[0] . "={" . $v[0] . "}");
            }
            array_push($landings, $this->_createLanding(
                join(" | ", $name) . " Page",
                $concepts,
                $domain . "{category}?" . join("&", $url_params) . "&___store=" . $store));
        }
        return $landings;
    }

    protected function getQueryProducts($store) {
        $products = Mage::getResourceModel('catalog/product_collection')->setStore($store)
            ->addAttributeToSelect(array('name', 'sku', 'short_description', 'thumbnail'), 'inner')
            ->addAttributeToSelect(
                array(
                    'price', 'special_price', 'special_from_date', 'special_to_date',
                    'msrp_enabled', 'msrp_display_actual_price_type', 'msrp', 'visibility',
                    'type_id'
                ),
                'left')
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'type_id', '=' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE),
                    array('attribute' => 'type_id', '=' => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                ))
            ->applyFrontendPriceLimitations()
            ->setPageSize(false)
            ->addAttributeToFilter(
                'status',
                array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            );
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($products);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);
        return $products;
    }

    public function createXml($store)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $prefix = Mage::getConfig()->getTablePrefix();
        $query = 'SELECT * FROM  `'.$prefix.'Salesoar_Feed` ';
        $readConnection->fetchAll($query);
        $array = $readConnection->fetchAll($query);
        $this->arraySalesoar = array();
        foreach($array as $item => $value){
            $this->arraySalesoar[$value['id_category']] = $value['google_id'];
        }

        Mage::app()->setCurrentStore($store);
        $this->storeName = Mage::app()->getStore()->getGroup()->getName();
        $newurl = Mage::getUrl('salesoar/');
        $title = Mage::helper('Salesoar_Feed')->__('All Products from %s', $this->storeName);
        $lang = Mage::getStoreConfig('general/locale/code');

        $atomObj = Mage::getModel('Salesoar_Feed/Atom');
        $atomObj->createFile($store);
        $data = array('title' => $title,
            'description' => $title,
            'link' => $newurl,
            'charset' => 'UTF-8',
            'language' => $lang
        );

        $atomObj->addHeader($data);

        $domain = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $this->domainLength = strlen($domain);

        $landings = array();

        array_push($landings, $this->_createLanding(
            "Category page",
            array("category"),
            $domain . "{category}" . "?___store=" . $store));

        $landings_group = $this->getCategoryLandings($store, $domain);

        //ATTRIBUTES FOR LANDING
        if (Mage::getStoreConfig('salesoar_feed/attribute_category_settings/salesoar_feed_add_attributes')) {
            $landings = array_merge($landings, $this->getAttributeLandings($store, $domain));
        }

        $atomObj->addLandings($landings_group, $landings);

        foreach (Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('name') as $_cat) {
            $this->all_categories[$_cat->getId()] = $_cat;
        }

        $products = $this->getQueryProducts($store);

        foreach ($products->getData() as $productData) {
            $this->addNewItemXmlCallback(array(
                'atomObj' => $atomObj,
                'productData' => $productData,
                'store' => $store));
        }

        $atomObj->addXMLEnd();
        return "";
    }


    protected function _createConcept($name, $value, $label)
    {
        $concept = array(
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED . ':name' => $name,
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED . ':value' => $value,
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED . ':label' => $label
        );
        return array(
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED . ':concept' => $concept
        );
    }

    /**
     * Preparing data and adding to rss object
     *
     * @param array $args
     */
    public function addNewItemXmlCallback($args)
    {

        $productData = $args['productData'];
        $store = $args['store'];
        $atomObj = $args['atomObj'];
        $product = Mage::getModel('catalog/Product')->fromArray($productData);

        if ($product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE &&
            $product->getTypeID() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            // Skip simple products that are children of configurable product
            return;
        }

        if ($product->getTypeID() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $product->getConfigurableOptions($product);
        }

        $_weeeTaxAmount = Mage::helper('weee')->getAmount($product);
        $_priceInclTax = Mage::helper('tax')
            ->getPrice($product, $product
                ->getPrice());
        $_finalPriceInclTax = Mage::helper('tax')
            ->getPrice($product, $product
                ->getFinalPrice());
        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

        $productUrl = $product->getProductUrl(). "?___store=" . $store;

        $concepts = array();

        $cats = $product->getCategoryCollection()
            ->addAttributeToFilter('is_active', 1)
            ->setPageSize(false);

        $googleProductCategory= '';
        foreach ($cats as $_c) {
            $product_type = ($_c->getId());
            $_cat = $this->all_categories[$_c->getId()];
            $path = explode('/', $_cat->getPath());
            for ($i = 2; $i <count($path); $i++) {
                $cat = $this->all_categories[$path[$i]];
                $label = $cat->getName();
                $value = substr($cat->getUrl(), $this->domainLength);

                array_push($concepts, $this->_createConcept(
                    "category", $value, $label));
                array_push($concepts, $this->_createConcept(
                    "category_L" . ($i - 2), $value, $label));

                ////GOOGLE_CATEGORY////
                if ($i == count($path) - 1 && $this->arraySalesoar != null) {
                    if (array_key_exists($cat->getId(), $this->arraySalesoar) && $this->arraySalesoar[$cat->getId()] != 0 && $this->arraySalesoar[$cat->getId()] != NULL)
                        $googleProductCategory = $this->arraySalesoar[$cat->getId()];
                }
            }
        }

        if (Mage::getStoreConfig('salesoar_feed/config/salesoar_feed_add_attributes')) {
            foreach (Mage::getResourceModel('catalog/product')
                         ->getAttributeRawValue($product->getId(),
                             $this->all_attribute_codes, Mage::app()->getStore()) as $attrCode => $value) {
                if ($attrCode == "manufacturer") {
                    $label = $product->getResource()->getAttribute($attrCode)->getSource()->getOptionText($value);
                    if (!empty($value) && !empty($label)) {
                        array_push($concepts, $this->_createConcept(
                            $attrCode, $value, $label));
                        $product->setManufacturer($label);
                    }
                } else if ($attrCode == "description" && !$product->getDescription()) {
                    $product->setDescription($value);
                } else {
                    $label = $product->getResource()->getAttribute($attrCode)->getSource()->getOptionText($value);
                    if (!empty($value) && !empty($label)) {
                        array_push($concepts, $this->_createConcept(
                            $attrCode, $value, $label));
                    }
                }
            }

            if ($product->getTypeID() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
                foreach ($childProducts as $child) {
                    foreach (Mage::getResourceModel('catalog/product')
                                 ->getAttributeRawValue($child->getId(),
                                     $this->all_attribute_codes, Mage::app()->getStore()) as $attrCode => $value) {
                        if ($attrCode != "manufacturer" && $attrCode != "description") {
                            $label = $product->getResource()->getAttribute($attrCode)->getSource()->getOptionText($value);
                            if (!empty($value) && !empty($label)) {
                                array_push($concepts, $this->_createConcept(
                                    $attrCode, $value, $label));
                            }
                        }
                    }
                }
            }
        }

        $rty = $this->all_categories[$product_type];
        $product_type = '';
        $path = explode('/', $rty->getPath());
        for ($i = 2; $i < count($path); $i++) {
            $cat = $this->all_categories[$path[$i]];
            if ($i != count($path) - 1)
                $product_type .= ($label = $cat->getName()) . ' &amp; ';
            else
                $product_type .= ($label = $cat->getName()) ;
        }

        $data = array(
            'title' => $product->getName(),
            'link' => $productUrl,
            'description' => $product->getDescription(),
            'guid' => $product->getSku(),
            // Concatenate string to pass the value not the reference
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_GMERCHANT_FEED . ':image_link'
            => $this->getThumbnailUrl($product),
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_GMERCHANT_FEED . ':product_category'
            => $googleProductCategory,
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_GMERCHANT_FEED . ':product_type'
            => $product_type,
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_GMERCHANT_FEED . ':price'
            => Mage::helper('core')
                    ->currency($_priceInclTax + $_weeeTaxAmount, false, false)
                . " " .
                $currentCurrencyCode,
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_GMERCHANT_FEED . ':sale_price'
            => Mage::helper('core')
                    ->currency($_finalPriceInclTax + $_weeeTaxAmount, false, false)
                . " " .
                $currentCurrencyCode,
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_GMERCHANT_FEED . ':brand'
            => $product->getManufacturer() ? $product->getManufacturer() : $this->storeName,
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED . ':concepts'
            => $concepts,
        );

        $atomObj->addEntry($data);
    }
}

