<?php
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
class Salesoar_Feed_Model_Atom
{
    const NAMESPACE_URI_SALESOAR_FEED   = 'http://salesoar.com/ns/1.0';
    const NAMESPACE_TAG_SALESOAR_FEED   = 'sr';
    const NAMESPACE_URI_GMERCHANT_FEED  = 'http://base.google.com/ns/1.0';
    const NAMESPACE_TAG_GMERCHANT_FEED  = 'g';
    protected $output = null;

    public function createFile($storeId) {
        $baseDir = Mage::getBaseDir();
        $varDir = $baseDir.DS.'var';
        $file = new Varien_Io_File();
        $file->mkdir($varDir.DS.'salesoar');
        $this->output = fopen($varDir.DS.'salesoar'.DS.$storeId."_salesoar.xml", "w");
    }

    public function addHeader($data = array()) {
        $format = <<<EOT
<?xml version="1.0" encoding="%s"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:%s="%s" xmlns:%s="%s">
  <id>%s</id>
  <title><![CDATA[%s]]></title>
  <updated>%s</updated>
  <link rel="self" href="%s" hreflang="%s"/>\n
EOT;
        fwrite($this->output, utf8_encode(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', sprintf($format,
            $data['charset'],
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_GMERCHANT_FEED,
            Salesoar_Feed_Model_Atom::NAMESPACE_URI_GMERCHANT_FEED,
            Salesoar_Feed_Model_Atom::NAMESPACE_TAG_SALESOAR_FEED,
            Salesoar_Feed_Model_Atom::NAMESPACE_URI_SALESOAR_FEED,
            $data['link'],
            $data['title'],
            date("Y-m-d H:i:s"),
            $data['link'],
            $data['language']))));
    }

    public function addLandings($landings_group, $landings) {
        fwrite($this->output, "  <sr:landings>\n");
        foreach ($landings_group[0] as $landing) {
            if(count($landing['sr:landing']['sr:concepts'])<=1){
                $this->_addLanding($landing);
            }
            else {
                $this->_addLanding_group($landing);
            }
        }
        foreach ($landings as $landing) {
            $this->_addLanding($landing);
        }
        fwrite($this->output, "  </sr:landings>\n");
    }

    public function _addLandingConcept($concept) {
        fwrite($this->output, utf8_encode("        <sr:concept>"));
        fwrite($this->output, utf8_encode(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $concept['sr:concept'])));
        fwrite($this->output, utf8_encode("</sr:concept>\n"));
    }


    public function _addLanding_group($landing) {
        $formatBegin = <<<EOT
    <sr:landing>
      <sr:name><![CDATA[%s]]></sr:name>
      <sr:concepts>
        <group>\n
EOT;
        $formatEnd = <<<EOT
        </group>
      </sr:concepts>
      <sr:url><![CDATA[%s]]></sr:url>
    </sr:landing>\n
EOT;
        $landing = $landing['sr:landing'];
        fwrite($this->output, utf8_encode(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', sprintf($formatBegin, $landing['sr:name']))));
        foreach ($landing['sr:concepts'] as $concept) {
            $this->_addLandingConcept($concept);
        }
        fwrite($this->output, utf8_encode(sprintf($formatEnd, $landing['sr:url'])));
    }

    public function _addLanding($landing) {
        $formatBegin = <<<EOT
    <sr:landing>
      <sr:name><![CDATA[%s]]></sr:name>
      <sr:concepts>\n
EOT;
        $formatEnd = <<<EOT
      </sr:concepts>
      <sr:url><![CDATA[%s]]></sr:url>
    </sr:landing>\n
EOT;
        $landing = $landing['sr:landing'];
        fwrite($this->output, utf8_encode(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', sprintf($formatBegin, $landing['sr:name']))));
        foreach ($landing['sr:concepts'] as $concept) {
            $this->_addLandingConcept($concept);
        }
        fwrite($this->output, utf8_encode(sprintf($formatEnd, $landing['sr:url'])));
    }

    public function addEntry($entry) {
        if($entry['g:product_category'] == 0){
            $formatBegin = <<<EOT
  <entry>
    <id><![CDATA[%s]]></id>
    <title><![CDATA[%s]]></title>
    <updated>%s</updated>
    <link rel="alternate" href="%s"/>
    <summary><![CDATA[%s]]></summary>
    <g:image_link>%s</g:image_link>
    <g:product_type><![CDATA[%s]]></g:product_type>
    <g:price>%s</g:price>
    <g:sale_price>%s</g:sale_price>
    <g:brand><![CDATA[%s]]></g:brand>
    <sr:concepts>\n
EOT;

            $formatEnd = <<<EOT
    </sr:concepts>
  </entry>\n
EOT;
            fwrite($this->output, utf8_encode(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',sprintf($formatBegin,
                $entry['guid'],
                $entry['title'],
                date("Y-m-d H:i:s"),
                $entry['link'],
                $entry['description'],
                $entry['g:image_link'],
                $entry['g:product_type'],
                $entry['g:price'],
                $entry['g:sale_price'],
                $entry['g:brand']))));
            foreach ($entry['sr:concepts'] as $concept) {
                $this->_addConcept($concept);
            }
            fwrite($this->output, utf8_encode(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', sprintf($formatEnd))));
        }
        else{
            $formatBegin = <<<EOT
  <entry>
    <id><![CDATA[%s]]></id>
    <title><![CDATA[%s]]></title>
    <updated>%s</updated>
    <link rel="alternate" href="%s"/>
    <summary><![CDATA[%s]]></summary>
    <g:image_link>%s</g:image_link>
    <g:google_product_category><![CDATA[%d]]></g:google_product_category>
    <g:product_type><![CDATA[%s]]></g:product_type>
    <g:price>%s</g:price>
    <g:sale_price>%s</g:sale_price>
    <g:brand><![CDATA[%s]]></g:brand>
    <sr:concepts>\n
EOT;

            $formatEnd = <<<EOT
    </sr:concepts>
  </entry>\n
EOT;
            fwrite($this->output, utf8_encode(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', sprintf($formatBegin,
                $entry['guid'],
                $entry['title'],
                date("Y-m-d H:i:s"),
                $entry['link'],
                $entry['description'],
                $entry['g:image_link'],
                $entry['g:product_category'],
                $entry['g:product_type'],
                $entry['g:price'],
                $entry['g:sale_price'],
                $entry['g:brand']))));
            foreach ($entry['sr:concepts'] as $concept) {
                $this->_addConcept($concept);
            }
            fwrite($this->output, utf8_encode(sprintf($formatEnd)));
        }
    }

    public function _addConcept($concept) {
        $format = <<<EOT
      <sr:concept>
        <sr:name>%s</sr:name>
        <sr:value>%s</sr:value>
        <sr:label><![CDATA[%s]]></sr:label>
      </sr:concept>\n
EOT;
        $concept = $concept['sr:concept'];
        fwrite($this->output, utf8_encode(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', sprintf($format,
            $concept['sr:name'],
            $concept['sr:value'],
            $concept['sr:label']))));
    }

    public function addXMLEnd() {
        fwrite($this->output, "</feed>");
        fclose($this->output);
    }

    public function openFileToRead($storeId) {
        $baseDir = Mage::getBaseDir();
        $varDir = $baseDir.DS.'var';
        $this->output = fopen($varDir.DS.'salesoar'.DS.$storeId."_salesoar.xml", "r");
        return $this->output;
    }
}

