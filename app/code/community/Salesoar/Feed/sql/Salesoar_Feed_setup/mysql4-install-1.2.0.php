<?php
/**
 * Created by PhpStorm.
 * User: vittorio
 * Date: 08/01/16
 * Time: 10.20
 */

$installer = $this;
$installer->startSetup();
$installer->run("-- DROP TABLE IF EXISTS {$this->getTable('Salesoar_Feed')};
CREATE TABLE {$this->getTable('Salesoar_Feed')} (
	   `id_category` INT(11) UNSIGNED NOT NULL ,
		`google_id` INT(11) UNSIGNED  ,
		`google_name` VARCHAR( 100 )  DEFAULT '',
		PRIMARY KEY (`id_category`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");

$popolateSalesoarCategory ="INSERT INTO {$this->getTable('Salesoar_Feed')} (id_category) SELECT cs.entity_id FROM {$this->getTable('catalog_category_entity')} as cs GROUP BY cs.entity_id ";
$installer->run($popolateSalesoarCategory);
$installer->endSetup();

