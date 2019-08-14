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
 * Review form block
 *
 * @category   Salesoar
 * @package    Salesoar_Feed
 * @author     Salesoar Team <support@salesoar.com>
 */
class Salesoar_Feed_Block_Xml extends Salesoar_Feed_Block_Abstract
{
    protected function _toHtml()
    {
        $xml = Mage::getModel('Salesoar_Feed/Atom');
        $file = $xml->openFileToRead($this->getRequest()->getParam('store'));
	if ($file) {
            while (!feof($file)) {
                echo fread($file, 4096);
            }
            fclose($file);
        }
    }
}
